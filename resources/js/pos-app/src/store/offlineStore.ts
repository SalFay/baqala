import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { syncEngine } from '../sync/syncEngine';

interface OfflineState {
  // Connection status
  isOnline: boolean;
  isOfflineMode: boolean;

  // Offline data stats
  localProductCount: number;
  localCategoryCount: number;
  localCustomerCount: number;
  pendingOrderCount: number;
  failedOrderCount: number;

  // Bootstrap status
  isBootstrapped: boolean;
  bootstrapProgress: string;
  isBootstrapping: boolean;

  // Actions
  setOnlineStatus: (isOnline: boolean) => void;
  setOfflineMode: (enabled: boolean) => void;
  updateLocalStats: () => Promise<void>;
  bootstrap: (storeId: number) => Promise<void>;
  forceResync: (storeId: number) => Promise<void>;
  checkBootstrapStatus: () => Promise<boolean>;
}

export const useOfflineStore = create<OfflineState>()(
  persist(
    (set, get) => ({
      // Initial state
      isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
      isOfflineMode: false,
      localProductCount: 0,
      localCategoryCount: 0,
      localCustomerCount: 0,
      pendingOrderCount: 0,
      failedOrderCount: 0,
      isBootstrapped: false,
      bootstrapProgress: '',
      isBootstrapping: false,

      // Set online status
      setOnlineStatus: (isOnline: boolean) => {
        set({ isOnline });

        // If we're online and have pending orders, try to sync
        if (isOnline) {
          const storeId = localStorage.getItem('current_store_id');
          if (storeId) {
            syncEngine.pushPendingChanges(parseInt(storeId));
          }
        }
      },

      // Toggle offline mode (force offline even when online)
      setOfflineMode: (enabled: boolean) => {
        set({ isOfflineMode: enabled });
      },

      // Update local data stats
      updateLocalStats: async () => {
        const stats = await syncEngine.getLocalStats();
        set({
          localProductCount: stats.products,
          localCategoryCount: stats.categories,
          localCustomerCount: stats.customers,
          pendingOrderCount: stats.pendingOrders,
          failedOrderCount: stats.failedOrders,
        });
      },

      // Bootstrap initial data
      bootstrap: async (storeId: number) => {
        if (get().isBootstrapping) {
          return;
        }

        set({ isBootstrapping: true, bootstrapProgress: 'Starting...' });

        try {
          await syncEngine.bootstrap(storeId, (message) => {
            set({ bootstrapProgress: message });
          });

          set({ isBootstrapped: true });
          await get().updateLocalStats();

        } catch (error) {
          console.error('Bootstrap failed:', error);
          set({ bootstrapProgress: 'Bootstrap failed. Please try again.' });
          throw error;
        } finally {
          set({ isBootstrapping: false });
        }
      },

      // Force full resync
      forceResync: async (storeId: number) => {
        if (get().isBootstrapping) {
          return;
        }

        set({ isBootstrapping: true, bootstrapProgress: 'Clearing local data...' });

        try {
          await syncEngine.forceResync(storeId, (message) => {
            set({ bootstrapProgress: message });
          });

          set({ isBootstrapped: true });
          await get().updateLocalStats();

        } catch (error) {
          console.error('Resync failed:', error);
          set({ bootstrapProgress: 'Resync failed. Please try again.' });
          throw error;
        } finally {
          set({ isBootstrapping: false });
        }
      },

      // Check if bootstrap is needed
      checkBootstrapStatus: async () => {
        const needsBootstrap = await syncEngine.needsBootstrap();
        set({ isBootstrapped: !needsBootstrap });
        return !needsBootstrap;
      },
    }),
    {
      name: 'offline-storage',
      partialize: (state) => ({
        isOfflineMode: state.isOfflineMode,
        isBootstrapped: state.isBootstrapped,
      }),
    }
  )
);

// Setup network status listeners
if (typeof window !== 'undefined') {
  window.addEventListener('online', () => {
    useOfflineStore.getState().setOnlineStatus(true);
  });

  window.addEventListener('offline', () => {
    useOfflineStore.getState().setOnlineStatus(false);
  });
}
