import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useBusinessTypeStore = create(
  persist(
    (set) => ({
      currentType: null,
      availableTypes: [],
      isLoading: false,
      previewData: null,

      setCurrentType: (type) => {
        set({ currentType: type });
      },

      setAvailableTypes: (types) => {
        set({ availableTypes: types });
      },

      setLoading: (loading) => {
        set({ isLoading: loading });
      },

      setPreviewData: (data) => {
        set({ previewData: data });
      },

      clearPreview: () => {
        set({ previewData: null });
      },
    }),
    {
      name: 'business-type-storage',
      partialize: (state) => ({
        currentType: state.currentType,
      }),
    }
  )
);
