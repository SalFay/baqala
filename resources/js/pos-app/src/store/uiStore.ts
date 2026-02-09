import { create } from 'zustand';

interface UIState {
  sidebarCollapsed: boolean;
  toggleSidebar: () => void;
  setSidebarCollapsed: (collapsed: boolean) => void;

  currentStoreId: number | null;
  setCurrentStoreId: (storeId: number | null) => void;

  theme: 'light' | 'dark';
  setTheme: (theme: 'light' | 'dark') => void;
}

export const useUIStore = create<UIState>((set) => ({
  sidebarCollapsed: false,
  toggleSidebar: () => set((state) => ({ sidebarCollapsed: !state.sidebarCollapsed })),
  setSidebarCollapsed: (collapsed) => set({ sidebarCollapsed: collapsed }),

  currentStoreId: null,
  setCurrentStoreId: (storeId) => set({ currentStoreId: storeId }),

  theme: 'light',
  setTheme: (theme) => set({ theme }),
}));
