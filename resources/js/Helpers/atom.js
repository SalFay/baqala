import { atom, selector, useRecoilState, useRecoilValue } from 'recoil'

// Theme atom - SparkCRM pattern
const getInitialTheme = () => {
  if (typeof window === 'undefined') return 'light'
  const stored = localStorage.getItem('baqala-theme')
  if (!stored) return 'light'
  // Handle both simple string ('light'/'dark') and JSON format
  if (stored === 'light' || stored === 'dark') return stored
  try {
    const parsed = JSON.parse(stored)
    // Handle zustand-style format: {"state":{"theme":"dark"},"version":0}
    if (parsed?.state?.theme) return parsed.state.theme
    if (parsed?.theme) return parsed.theme
  } catch {
    // Not JSON, just use as-is or default to light
  }
  return 'light'
}

export const themeAtom = atom({
  key: 'themeAtom',
  default: getInitialTheme(),
  effects: [
    ({ onSet }) => {
      onSet((newValue) => {
        localStorage.setItem('baqala-theme', newValue)
      })
    },
  ],
})

// App Settings atom - stores global app settings from backend
export const appSettingsAtom = atom({
  key: 'appSettingsAtom',
  default: {
    store_name: 'Baqala POS',
    store_address: '',
    store_phone: '',
    store_email: '',
    tax_number: '',
    currency: 'SAR',
    currency_symbol: '',
    currency_position: 'before',
    default_tax_rate: 15,
    tax_name: 'VAT',
    prices_include_tax: false,
    receipt_header: '',
    receipt_footer: 'Thank you for your purchase!',
    auto_print_receipt: false,
    low_stock_threshold: 10,
    allow_negative_stock: false,
    loyalty_enabled: false,
    loyalty_points_per_currency: 1,
    loyalty_point_value: 0.01,
  },
})

// Hook to use app settings
export const useAppSettings = () => {
  return useRecoilValue(appSettingsAtom)
}

// User atom
export const userAtom = atom({
  key: 'userAtom',
  default: null,
})

// Permissions atom
export const permissionsAtom = atom({
  key: 'permissionsAtom',
  default: [],
})

// Menu state atom
export const menuStateAtom = atom({
  key: 'menuStateAtom',
  default: {
    openKeys: ['dashboard'],
    selectedKeys: ['/dashboard'],
    collapsed: false,
  },
  effects: [
    ({ setSelf, onSet }) => {
      if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('baqala-menu')
        if (saved) {
          try {
            setSelf(JSON.parse(saved))
          } catch (e) {
            // ignore
          }
        }
      }
      onSet((newValue) => {
        localStorage.setItem('baqala-menu', JSON.stringify(newValue))
      })
    },
  ],
})

// UI state atom
export const uiStateAtom = atom({
  key: 'uiStateAtom',
  default: {
    isMobile: typeof window !== 'undefined' ? window.innerWidth < 768 : false,
    drawerVisible: false,
  },
})

// Custom hook for theme (for components that need object return)
export const useThemeStore = () => {
  const [theme, setTheme] = useRecoilState(themeAtom)
  return {
    theme,
    setTheme,
    toggleTheme: () => setTheme((current) => (current === 'light' ? 'dark' : 'light')),
  }
}
