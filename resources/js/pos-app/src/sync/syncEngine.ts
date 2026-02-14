import { db, OfflineOrder, OfflineProduct, OfflineCategory, OfflineCustomer } from '../db/offlineDatabase';
import axios from '../api/axios';

// Generate UUID for offline IDs
export function generateUUID(): string {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

// Get or generate terminal ID
export async function getTerminalId(): Promise<string> {
  let terminalId = await db.getSyncMeta('terminal_id') as string | undefined;

  if (!terminalId) {
    terminalId = generateUUID();
    await db.setSyncMeta('terminal_id', terminalId);
  }

  return terminalId;
}

interface BootstrapResponse {
  products: OfflineProduct[];
  categories: OfflineCategory[];
  customers: OfflineCustomer[];
  settings: {
    store: Record<string, unknown>;
    pos: Record<string, unknown>;
  };
  tax_rates: Array<{
    id: number;
    name: string;
    rate: number;
    is_default: boolean;
  }>;
  payment_methods: Array<{
    id: number;
    name: string;
    code: string;
  }>;
  store: Record<string, unknown>;
  meta: {
    sync_version: number;
    synced_at: string;
    duration_ms: number;
  };
}

interface PullResponse {
  products: OfflineProduct[];
  categories: OfflineCategory[];
  customers: OfflineCustomer[];
  settings: Record<string, unknown>;
  inventory: Array<{
    product_id: number;
    product_variant_id?: number;
    quantity: number;
  }>;
  deleted: {
    products: number[];
    categories: number[];
    customers: number[];
  };
  meta: {
    sync_version: number;
    synced_at: string;
    has_more: boolean;
  };
}

interface PushResponse {
  orders: Array<{
    offline_id: string;
    status: 'synced' | 'failed' | 'already_synced';
    order_id?: number;
    order_number?: string;
    error?: string;
  }>;
  customers: Array<{
    offline_id: string;
    status: 'synced' | 'failed';
    customer_id?: number;
    error?: string;
  }>;
  conflicts: Array<{
    conflict_id: string;
    entity_type: string;
    entity_id: number;
  }>;
  meta: {
    synced_at: string;
    success: boolean;
  };
}

interface SyncStatus {
  terminal_registered: boolean;
  terminal_active: boolean;
  last_sync_at: string | null;
  pending_conflicts: number;
  pending_orders: number;
  server_version: number;
  server_time: string;
}

class SyncEngine {
  private isSyncing = false;
  private syncInterval: NodeJS.Timeout | null = null;
  private retryTimeout: NodeJS.Timeout | null = null;
  private syncIntervalMs = 60000; // 1 minute

  // Start automatic sync
  startAutoSync(storeId: number): void {
    if (this.syncInterval) {
      return;
    }

    // Run initial sync
    this.syncDelta(storeId);

    // Schedule periodic sync
    this.syncInterval = setInterval(() => {
      if (navigator.onLine) {
        this.syncDelta(storeId);
        this.pushPendingChanges(storeId);
      }
    }, this.syncIntervalMs);
  }

  // Stop automatic sync
  stopAutoSync(): void {
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
      this.syncInterval = null;
    }
    if (this.retryTimeout) {
      clearTimeout(this.retryTimeout);
      this.retryTimeout = null;
    }
  }

  // Initial bootstrap sync
  async bootstrap(storeId: number, onProgress?: (message: string) => void): Promise<void> {
    if (this.isSyncing) {
      throw new Error('Sync already in progress');
    }

    this.isSyncing = true;

    try {
      const terminalId = await getTerminalId();

      onProgress?.('Registering terminal...');

      // Register terminal first
      await axios.post('/api/v1/sync/register-terminal', {
        terminal_id: terminalId,
        store_id: storeId,
        device_info: navigator.userAgent,
        app_version: '2.0.0',
      });

      onProgress?.('Downloading data...');

      // Bootstrap full data
      const response = await axios.post<{ data: BootstrapResponse }>('/api/v1/sync/bootstrap', {
        terminal_id: terminalId,
        store_id: storeId,
      });

      const data = response.data.data;

      onProgress?.('Saving products...');
      await db.products.clear();
      await db.products.bulkPut(data.products);

      onProgress?.('Saving categories...');
      await db.categories.clear();
      await db.categories.bulkPut(data.categories);

      onProgress?.('Saving customers...');
      await db.customers.clear();
      await db.customers.bulkPut(data.customers);

      onProgress?.('Saving settings...');
      await db.setSetting('store', data.store);
      await db.setSetting('pos', data.settings.pos);
      await db.setSetting('tax_rates', data.tax_rates);
      await db.setSetting('payment_methods', data.payment_methods);

      // Update sync metadata
      await db.setSyncMeta('last_sync_at', data.meta.synced_at);
      await db.setSyncMeta('sync_version', data.meta.sync_version);
      await db.setSyncMeta('store_id', storeId);
      await db.setSyncMeta('bootstrap_completed', true);

      onProgress?.('Sync complete!');

    } finally {
      this.isSyncing = false;
    }
  }

  // Delta sync - pull changes since last sync
  async syncDelta(storeId: number): Promise<void> {
    if (this.isSyncing || !navigator.onLine) {
      return;
    }

    this.isSyncing = true;

    try {
      const terminalId = await getTerminalId();
      const lastSyncAt = await db.getSyncMeta('last_sync_at') as string | undefined;

      const response = await axios.get<{ data: PullResponse }>('/api/v1/sync/pull', {
        params: {
          terminal_id: terminalId,
          store_id: storeId,
          last_sync_at: lastSyncAt,
        },
      });

      const data = response.data.data;

      // Update products
      if (data.products.length > 0) {
        await db.products.bulkPut(data.products);
      }

      // Update categories
      if (data.categories.length > 0) {
        await db.categories.bulkPut(data.categories);
      }

      // Update customers
      if (data.customers.length > 0) {
        await db.customers.bulkPut(data.customers);
      }

      // Update inventory
      if (data.inventory.length > 0) {
        for (const inv of data.inventory) {
          const product = await db.products.get(inv.product_id);
          if (product) {
            if (inv.product_variant_id) {
              const variant = product.variants?.find(v => v.id === inv.product_variant_id);
              if (variant) {
                variant.stock_quantity = inv.quantity;
              }
            } else {
              product.stock_quantity = inv.quantity;
            }
            await db.products.put(product);
          }
        }
      }

      // Handle deletions
      if (data.deleted.products.length > 0) {
        await db.products.bulkDelete(data.deleted.products);
      }
      if (data.deleted.categories.length > 0) {
        await db.categories.bulkDelete(data.deleted.categories);
      }
      if (data.deleted.customers.length > 0) {
        await db.customers.bulkDelete(data.deleted.customers);
      }

      // Update sync metadata
      await db.setSyncMeta('last_sync_at', data.meta.synced_at);
      await db.setSyncMeta('sync_version', data.meta.sync_version);

    } catch (error) {
      console.error('Delta sync failed:', error);
      // Don't throw - delta sync failures shouldn't break the app
    } finally {
      this.isSyncing = false;
    }
  }

  // Push pending changes to server
  async pushPendingChanges(storeId: number): Promise<PushResponse | null> {
    if (this.isSyncing || !navigator.onLine) {
      return null;
    }

    this.isSyncing = true;

    try {
      const terminalId = await getTerminalId();

      // Get pending orders
      const pendingOrders = await db.getPendingOrders();

      if (pendingOrders.length === 0) {
        return null;
      }

      const response = await axios.post<{ data: PushResponse }>('/api/v1/sync/push', {
        terminal_id: terminalId,
        store_id: storeId,
        orders: pendingOrders.map(order => ({
          offline_id: order.offline_id,
          data: {
            customer_id: order.customer_id,
            customer_name: order.customer_name,
            user_id: order.user_id,
            cashier_name: order.cashier_name,
            items: order.items,
            subtotal: order.subtotal,
            tax_amount: order.tax_amount,
            discount: order.discount,
            discount_type: order.discount_type,
            total: order.total,
            payment_type: order.payment_type,
            payment_reference: order.payment_reference,
            loyalty_points_redeemed: order.loyalty_points_redeemed,
            loyalty_discount: order.loyalty_discount,
            notes: order.notes,
            status: 'completed',
            payment_status: 'paid',
          },
          created_offline_at: order.created_offline_at,
        })),
      });

      const result = response.data.data;

      // Update order statuses based on response
      for (const orderResult of result.orders) {
        if (orderResult.status === 'synced' || orderResult.status === 'already_synced') {
          await db.markOrderSynced(
            orderResult.offline_id,
            orderResult.order_id!,
            orderResult.order_number!
          );
        } else if (orderResult.status === 'failed') {
          await db.markOrderFailed(orderResult.offline_id, orderResult.error || 'Unknown error');
        }
      }

      return result;

    } catch (error) {
      console.error('Push sync failed:', error);
      return null;
    } finally {
      this.isSyncing = false;
    }
  }

  // Get sync status
  async getStatus(storeId: number): Promise<SyncStatus | null> {
    if (!navigator.onLine) {
      return null;
    }

    try {
      const terminalId = await getTerminalId();

      const response = await axios.get<{ data: SyncStatus }>('/api/v1/sync/status', {
        params: {
          terminal_id: terminalId,
          store_id: storeId,
        },
      });

      return response.data.data;

    } catch (error) {
      console.error('Failed to get sync status:', error);
      return null;
    }
  }

  // Check if bootstrap is needed
  async needsBootstrap(): Promise<boolean> {
    const completed = await db.getSyncMeta('bootstrap_completed');
    return !completed;
  }

  // Get local stats
  async getLocalStats(): Promise<{
    products: number;
    categories: number;
    customers: number;
    pendingOrders: number;
    failedOrders: number;
    lastSyncAt: string | null;
  }> {
    const [products, categories, customers, pendingOrders, failedOrders, lastSyncAt] = await Promise.all([
      db.products.count(),
      db.categories.count(),
      db.customers.count(),
      db.offlineOrders.where('status').equals('pending').count(),
      db.offlineOrders.where('status').equals('failed').count(),
      db.getSyncMeta('last_sync_at') as Promise<string | null>,
    ]);

    return {
      products,
      categories,
      customers,
      pendingOrders,
      failedOrders,
      lastSyncAt,
    };
  }

  // Create offline order
  async createOfflineOrder(
    storeId: number,
    order: Omit<OfflineOrder, 'offline_id' | 'status' | 'retry_count' | 'created_offline_at'>
  ): Promise<OfflineOrder> {
    const offlineOrder: OfflineOrder = {
      ...order,
      offline_id: generateUUID(),
      store_id: storeId,
      status: 'pending',
      retry_count: 0,
      created_offline_at: new Date().toISOString(),
    };

    await db.addOfflineOrder(offlineOrder);

    // Decrement local inventory
    for (const item of order.items) {
      const product = await db.products.get(item.product_id);
      if (product && product.track_inventory) {
        if (item.variant_id) {
          const variant = product.variants?.find(v => v.id === item.variant_id);
          if (variant) {
            variant.stock_quantity = Math.max(0, variant.stock_quantity - item.quantity);
          }
        } else {
          product.stock_quantity = Math.max(0, product.stock_quantity - item.quantity);
        }
        await db.products.put(product);
      }
    }

    // Try to sync immediately if online
    if (navigator.onLine) {
      setTimeout(() => this.pushPendingChanges(storeId), 1000);
    }

    return offlineOrder;
  }

  // Force full resync
  async forceResync(storeId: number, onProgress?: (message: string) => void): Promise<void> {
    await db.clearAll();
    await this.bootstrap(storeId, onProgress);
  }
}

// Export singleton instance
export const syncEngine = new SyncEngine();
