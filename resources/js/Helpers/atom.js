import { atom, selector } from 'recoil'

// Theme atom - SparkCRM pattern
const getInitialTheme = () => {
  if (typeof window === 'undefined') return 'light'
  return localStorage.getItem('baqala-theme') || 'light'
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
