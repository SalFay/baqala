import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

// Configure dayjs with Pakistan timezone
dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.tz.setDefault('Asia/Karachi');

// Date format constants
export const DATE_FORMAT = 'DD-MM-YYYY';
export const DATE_TIME_FORMAT = 'DD-MM-YYYY HH:mm';
export const TIME_FORMAT = 'HH:mm';

// Export configured dayjs
export { dayjs };

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
