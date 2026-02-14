import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { ConfigProvider, theme as antTheme } from 'antd';
import arEG from 'antd/locale/ar_EG';
import enUS from 'antd/locale/en_US';

type ThemeMode = 'light' | 'dark';
type IndustryMode = 'general' | 'medical' | 'restaurant' | 'factory';
type Direction = 'ltr' | 'rtl';
type Language = 'en' | 'ar';

interface IndustryTheme {
  primaryColor: string;
  secondaryColor: string;
  accentColor: string;
}

const industryThemes: Record<IndustryMode, IndustryTheme> = {
  general: {
    primaryColor: '#1890ff', // Blue
    secondaryColor: '#52c41a',
    accentColor: '#722ed1',
  },
  medical: {
    primaryColor: '#13c2c2', // Teal
    secondaryColor: '#1890ff',
    accentColor: '#eb2f96',
  },
  restaurant: {
    primaryColor: '#fa8c16', // Orange
    secondaryColor: '#52c41a',
    accentColor: '#f5222d',
  },
  factory: {
    primaryColor: '#595959', // Gray
    secondaryColor: '#1890ff',
    accentColor: '#faad14',
  },
};

interface ThemeContextType {
  mode: ThemeMode;
  industryMode: IndustryMode;
  direction: Direction;
  language: Language;
  colors: IndustryTheme;
  isDark: boolean;
  isRTL: boolean;
  setMode: (mode: ThemeMode) => void;
  setIndustryMode: (mode: IndustryMode) => void;
  setLanguage: (lang: Language) => void;
  toggleMode: () => void;
  toggleDirection: () => void;
}

const ThemeContext = createContext<ThemeContextType | undefined>(undefined);

interface ThemeProviderProps {
  children: ReactNode;
}

export function ThemeProvider({ children }: ThemeProviderProps) {
  const [mode, setModeState] = useState<ThemeMode>(() => {
    const saved = localStorage.getItem('theme_mode');
    return (saved as ThemeMode) || 'light';
  });

  const [industryMode, setIndustryModeState] = useState<IndustryMode>(() => {
    const saved = localStorage.getItem('industry_mode');
    return (saved as IndustryMode) || 'general';
  });

  const [language, setLanguageState] = useState<Language>(() => {
    const saved = localStorage.getItem('language');
    return (saved as Language) || 'en';
  });

  const direction: Direction = language === 'ar' ? 'rtl' : 'ltr';
  const colors = industryThemes[industryMode];

  // Persist to localStorage
  useEffect(() => {
    localStorage.setItem('theme_mode', mode);
    document.documentElement.setAttribute('data-theme', mode);
  }, [mode]);

  useEffect(() => {
    localStorage.setItem('industry_mode', industryMode);
  }, [industryMode]);

  useEffect(() => {
    localStorage.setItem('language', language);
    document.documentElement.setAttribute('dir', direction);
    document.documentElement.setAttribute('lang', language);
  }, [language, direction]);

  const setMode = (newMode: ThemeMode) => setModeState(newMode);
  const setIndustryMode = (newMode: IndustryMode) => setIndustryModeState(newMode);
  const setLanguage = (lang: Language) => setLanguageState(lang);
  const toggleMode = () => setModeState(m => m === 'light' ? 'dark' : 'light');
  const toggleDirection = () => setLanguageState(l => l === 'en' ? 'ar' : 'en');

  const contextValue: ThemeContextType = {
    mode,
    industryMode,
    direction,
    language,
    colors,
    isDark: mode === 'dark',
    isRTL: direction === 'rtl',
    setMode,
    setIndustryMode,
    setLanguage,
    toggleMode,
    toggleDirection,
  };

  // Ant Design theme configuration
  const antdTheme = {
    algorithm: mode === 'dark' ? antTheme.darkAlgorithm : antTheme.defaultAlgorithm,
    token: {
      colorPrimary: colors.primaryColor,
      colorSuccess: colors.secondaryColor,
      colorInfo: colors.primaryColor,
      borderRadius: 6,
      fontFamily: language === 'ar'
        ? "'Cairo', 'Segoe UI', Tahoma, sans-serif"
        : "'Inter', 'Segoe UI', Tahoma, sans-serif",
    },
    components: {
      Button: {
        primaryShadow: 'none',
        defaultShadow: 'none',
      },
      Card: {
        paddingLG: 16,
      },
      Table: {
        headerBg: mode === 'dark' ? '#1f1f1f' : '#fafafa',
      },
    },
  };

  return (
    <ThemeContext.Provider value={contextValue}>
      <ConfigProvider
        theme={antdTheme}
        direction={direction}
        locale={language === 'ar' ? arEG : enUS}
      >
        <div
          className={`app-wrapper ${mode} ${direction}`}
          style={{
            minHeight: '100vh',
            backgroundColor: mode === 'dark' ? '#141414' : '#f0f2f5',
            direction,
          }}
        >
          {children}
        </div>
      </ConfigProvider>
    </ThemeContext.Provider>
  );
}

export function useTheme() {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
}

// Export types
export type { ThemeMode, IndustryMode, Direction, Language };
