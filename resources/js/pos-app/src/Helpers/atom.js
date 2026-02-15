import { create } from 'zustand';
import { persist } from 'zustand/middleware';

// Theme store - SparkCRM pattern
export const useThemeStore = create(
  persist(
    (set) => ({
      theme: 'light',
      setTheme: (theme) => set({ theme }),
      toggleTheme: () => set((state) => ({ theme: state.theme === 'dark' ? 'light' : 'dark' })),
    }),
    { name: 'baqala-theme' }
  )
);

// Menu store - SparkCRM pattern
export const useMenuStore = create(
  persist(
    (set) => ({
      openKeys: ['dashboard'],
      selectedKeys: ['/dashboard'],
      collapsed: false,
      setOpenKeys: (keys) => set({ openKeys: keys }),
      setSelectedKeys: (keys) => set({ selectedKeys: keys }),
      setCollapsed: (collapsed) => set({ collapsed }),
      toggleCollapsed: () => set((state) => ({ collapsed: !state.collapsed })),
    }),
    { name: 'baqala-menu' }
  )
);

// UI store
export const useUIStore = create((set) => ({
  isMobile: typeof window !== 'undefined' ? window.innerWidth < 768 : false,
  drawerVisible: false,
  setIsMobile: (isMobile) => set({ isMobile }),
  setDrawerVisible: (visible) => set({ drawerVisible: visible }),
}));
