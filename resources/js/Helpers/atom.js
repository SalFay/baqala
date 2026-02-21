import { atom, selector, useRecoilState } from 'recoil'

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
