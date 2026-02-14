import { create } from 'zustand';
import { syncEngine } from '../sync/syncEngine';

interface SyncState {
  // Sync status
  isSyncing: boolean;
  lastSyncAt: string | null;
  syncError: string | null;
  syncProgress: number;
  syncMessage: string;

  // Pending changes
  pendingOrders: number;
  pendingCustomers: number;
  totalPending: number;

  // Conflict state
  hasConflicts: boolean;
  conflictCount: number;

  // Server connection
  serverVersion: number | null;
  isServerReachable: boolean;

  // Actions
  startSync: (storeId: number) => Promise<void>;
  pushChanges: (storeId: number) => Promise<void>;
  pullChanges: (storeId: number) => Promise<void>;
  checkStatus: (storeId: number) => Promise<void>;
  resetSyncError: () => void;
  updatePendingCounts: () => Promise<void>;
}

export const useSyncStore = create<SyncState>((set, get) => ({
  // Initial state
  isSyncing: false,
  lastSyncAt: null,
  syncError: null,
  syncProgress: 0,
  syncMessage: '',
  pendingOrders: 0,
  pendingCustomers: 0,
  totalPending: 0,
  hasConflicts: false,
  conflictCount: 0,
  serverVersion: null,
  isServerReachable: true,

  // Start full sync (pull + push)
  startSync: async (storeId: number) => {
    if (get().isSyncing || !navigator.onLine) {
      return;
    }

    set({
      isSyncing: true,
      syncError: null,
      syncProgress: 0,
      syncMessage: 'Connecting to server...',
    });

    try {
      // Pull changes first
      set({ syncProgress: 20, syncMessage: 'Downloading updates...' });
      await syncEngine.syncDelta(storeId);

      // Push pending changes
      set({ syncProgress: 60, syncMessage: 'Uploading changes...' });
      await syncEngine.pushPendingChanges(storeId);

      // Update counts
      set({ syncProgress: 90, syncMessage: 'Finalizing...' });
      await get().updatePendingCounts();

      // Check status
      const status = await syncEngine.getStatus(storeId);

      set({
        syncProgress: 100,
        syncMessage: 'Sync complete!',
        lastSyncAt: new Date().toISOString(),
        hasConflicts: (status?.pending_conflicts ?? 0) > 0,
        conflictCount: status?.pending_conflicts ?? 0,
        serverVersion: status?.server_version ?? null,
        isServerReachable: true,
      });

      // Clear message after delay
      setTimeout(() => {
        set({ syncMessage: '' });
      }, 2000);

    } catch (error) {
      const message = error instanceof Error ? error.message : 'Sync failed';
      set({
        syncError: message,
        syncMessage: '',
        isServerReachable: false,
      });
    } finally {
      set({ isSyncing: false, syncProgress: 0 });
    }
  },

  // Push only - send local changes to server
  pushChanges: async (storeId: number) => {
    if (get().isSyncing || !navigator.onLine) {
      return;
    }

    set({
      isSyncing: true,
      syncError: null,
      syncMessage: 'Uploading changes...',
    });

    try {
      await syncEngine.pushPendingChanges(storeId);
      await get().updatePendingCounts();

      set({
        syncMessage: 'Changes uploaded!',
        lastSyncAt: new Date().toISOString(),
      });

      setTimeout(() => {
        set({ syncMessage: '' });
      }, 2000);

    } catch (error) {
      const message = error instanceof Error ? error.message : 'Push failed';
      set({ syncError: message, syncMessage: '' });
    } finally {
      set({ isSyncing: false });
    }
  },

  // Pull only - get server changes
  pullChanges: async (storeId: number) => {
    if (get().isSyncing || !navigator.onLine) {
      return;
    }

    set({
      isSyncing: true,
      syncError: null,
      syncMessage: 'Downloading updates...',
    });

    try {
      await syncEngine.syncDelta(storeId);

      set({
        syncMessage: 'Updates downloaded!',
        lastSyncAt: new Date().toISOString(),
        isServerReachable: true,
      });

      setTimeout(() => {
        set({ syncMessage: '' });
      }, 2000);

    } catch (error) {
      const message = error instanceof Error ? error.message : 'Pull failed';
      set({
        syncError: message,
        syncMessage: '',
        isServerReachable: false,
      });
    } finally {
      set({ isSyncing: false });
    }
  },

  // Check server status
  checkStatus: async (storeId: number) => {
    try {
      const status = await syncEngine.getStatus(storeId);

      if (status) {
        set({
          hasConflicts: status.pending_conflicts > 0,
          conflictCount: status.pending_conflicts,
          serverVersion: status.server_version,
          isServerReachable: true,
        });
      } else {
        set({ isServerReachable: false });
      }

    } catch {
      set({ isServerReachable: false });
    }
  },

  // Reset sync error
  resetSyncError: () => {
    set({ syncError: null });
  },

  // Update pending change counts
  updatePendingCounts: async () => {
    const stats = await syncEngine.getLocalStats();
    set({
      pendingOrders: stats.pendingOrders,
      pendingCustomers: 0, // TODO: Track offline-created customers
      totalPending: stats.pendingOrders,
    });
  },
}));

// Auto-check for pending changes on load
if (typeof window !== 'undefined') {
  setTimeout(() => {
    useSyncStore.getState().updatePendingCounts();
  }, 1000);
}
