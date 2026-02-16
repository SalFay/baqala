import { create } from 'zustand';

export const useUIStore = create((set) => ({
  sidebarCollapsed: false,
  toggleSidebar: () => set((state) => ({ sidebarCollapsed: !state.sidebarCollapsed })),
  setSidebarCollapsed: (collapsed) => set({ sidebarCollapsed: collapsed }),

  currentStoreId: null,
  setCurrentStoreId: (storeId) => set({ currentStoreId: storeId }),

  theme: 'light',
  setTheme: (theme) => set({ theme }),
}));

// Selectors for performance optimization
// Use these to prevent unnecessary re-renders when only specific state is needed

export const useSidebarCollapsed = () => useUIStore((state) => state.sidebarCollapsed);
export const useToggleSidebar = () => useUIStore((state) => state.toggleSidebar);
export const useCurrentStoreId = () => useUIStore((state) => state.currentStoreId);
export const useSetCurrentStoreId = () => useUIStore((state) => state.setCurrentStoreId);
export const useTheme = () => useUIStore((state) => state.theme);
export const useSetTheme = () => useUIStore((state) => state.setTheme);
