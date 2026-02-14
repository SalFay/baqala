import Dexie, { Table } from 'dexie';

// Type definitions for offline storage
export interface OfflineProduct {
  id: number;
  name: string;
  name_ar?: string;
  sku?: string;
  barcode?: string;
  type: 'simple' | 'variable';
  category_id?: number;
  cost_price: number;
  sale_price: number;
  compare_price?: number;
  track_inventory: boolean;
  low_stock_threshold: number;
  image_url?: string;
  stock_quantity: number;
  variants: OfflineVariant[];
  updated_at: string;
}

export interface OfflineVariant {
  id: number;
  name: string;
  sku?: string;
  barcode?: string;
  cost_price: number;
  sale_price: number;
  stock_quantity: number;
}

export interface OfflineCategory {
  id: number;
  name: string;
  code: string;
  parent_id?: number;
  image?: string;
  sort_order: number;
  updated_at: string;
}

export interface OfflineCustomer {
  id: number;
  first_name: string;
  last_name?: string;
  full_name: string;
  business_name?: string;
  email?: string;
  phone?: string;
  loyalty_card_number?: string;
  credit_balance: number;
  credit_limit: number;
  loyalty_points: number;
  updated_at: string;
  // For offline-created customers
  offline_id?: string;
  synced?: boolean;
}

export interface OfflineOrder {
  offline_id: string;
  store_id: number;
  customer_id?: number;
  customer_name?: string;
  user_id?: number;
  cashier_name?: string;
  items: OfflineOrderItem[];
  subtotal: number;
  tax_amount: number;
  discount: number;
  discount_type?: string;
  total: number;
  payment_type: string;
  payment_reference?: string;
  loyalty_points_redeemed: number;
  loyalty_discount: number;
  notes?: string;
  status: 'pending' | 'synced' | 'failed';
  error_message?: string;
  retry_count: number;
  created_offline_at: string;
  synced_at?: string;
  server_order_id?: number;
  server_order_number?: string;
}

export interface OfflineOrderItem {
  product_id: number;
  variant_id?: number;
  sku: string;
  product_name: string;
  variant_name?: string;
  quantity: number;
  unit_price: number;
  cost_price: number;
  tax_rate: number;
  tax_amount: number;
  discount: number;
  line_total: number;
}

export interface OfflineSettings {
  key: string;
  value: unknown;
  updated_at: string;
}

export interface SyncMeta {
  key: string;
  value: string | number | boolean;
  updated_at: string;
}

export interface SyncQueueItem {
  id?: number;
  type: 'order' | 'customer' | 'inventory_adjustment';
  data: unknown;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  error_message?: string;
  retry_count: number;
  created_at: string;
  processed_at?: string;
}

export interface OfflineCart {
  id: string;
  store_id: number;
  customer_id?: number;
  customer?: OfflineCustomer;
  items: OfflineCartItem[];
  subtotal: number;
  tax_amount: number;
  discount: number;
  discount_type?: 'fixed' | 'percentage';
  discount_reason?: string;
  total: number;
  loyalty_points_to_redeem: number;
  loyalty_discount: number;
  notes?: string;
  status: 'active' | 'held';
  hold_name?: string;
  created_at: string;
  updated_at: string;
}

export interface OfflineCartItem {
  id: string;
  product_id: number;
  variant_id?: number;
  product: OfflineProduct;
  variant?: OfflineVariant;
  sku: string;
  product_name: string;
  variant_name?: string;
  quantity: number;
  unit_price: number;
  cost_price: number;
  tax_rate: number;
  tax_amount: number;
  discount: number;
  discount_type?: string;
  line_total: number;
}

// Dexie database class
class OfflineDatabase extends Dexie {
  products!: Table<OfflineProduct, number>;
  categories!: Table<OfflineCategory, number>;
  customers!: Table<OfflineCustomer, number>;
  offlineOrders!: Table<OfflineOrder, string>;
  settings!: Table<OfflineSettings, string>;
  syncMeta!: Table<SyncMeta, string>;
  syncQueue!: Table<SyncQueueItem, number>;
  carts!: Table<OfflineCart, string>;

  constructor() {
    super('BaqalaPOS');

    this.version(1).stores({
      products: 'id, sku, barcode, category_id, name, updated_at',
      categories: 'id, code, parent_id, sort_order, updated_at',
      customers: 'id, phone, email, loyalty_card_number, offline_id, updated_at',
      offlineOrders: 'offline_id, store_id, status, created_offline_at',
      settings: 'key',
      syncMeta: 'key',
      syncQueue: '++id, type, status, created_at',
      carts: 'id, store_id, status, updated_at',
    });
  }

  // Clear all data (for logout or reset)
  async clearAll(): Promise<void> {
    await Promise.all([
      this.products.clear(),
      this.categories.clear(),
      this.customers.clear(),
      this.offlineOrders.clear(),
      this.settings.clear(),
      this.syncMeta.clear(),
      this.syncQueue.clear(),
      this.carts.clear(),
    ]);
  }

  // Get sync metadata
  async getSyncMeta(key: string): Promise<string | number | boolean | undefined> {
    const meta = await this.syncMeta.get(key);
    return meta?.value;
  }

  // Set sync metadata
  async setSyncMeta(key: string, value: string | number | boolean): Promise<void> {
    await this.syncMeta.put({
      key,
      value,
      updated_at: new Date().toISOString(),
    });
  }

  // Get setting
  async getSetting<T>(key: string, defaultValue: T): Promise<T> {
    const setting = await this.settings.get(key);
    return (setting?.value as T) ?? defaultValue;
  }

  // Set setting
  async setSetting(key: string, value: unknown): Promise<void> {
    await this.settings.put({
      key,
      value,
      updated_at: new Date().toISOString(),
    });
  }

  // Product methods
  async searchProducts(term: string, categoryId?: number): Promise<OfflineProduct[]> {
    const searchTerm = term.toLowerCase();

    let products = await this.products.toArray();

    if (categoryId) {
      products = products.filter(p => p.category_id === categoryId);
    }

    if (term) {
      products = products.filter(p =>
        p.name.toLowerCase().includes(searchTerm) ||
        p.name_ar?.toLowerCase().includes(searchTerm) ||
        p.sku?.toLowerCase().includes(searchTerm) ||
        p.barcode?.includes(searchTerm)
      );
    }

    return products;
  }

  async findProductByBarcode(barcode: string): Promise<{ product: OfflineProduct; variant?: OfflineVariant } | null> {
    // First check products
    const product = await this.products.where('barcode').equals(barcode).first();
    if (product) {
      return { product };
    }

    // Then check variants
    const products = await this.products.toArray();
    for (const p of products) {
      const variant = p.variants?.find(v => v.barcode === barcode);
      if (variant) {
        return { product: p, variant };
      }
    }

    return null;
  }

  // Customer methods
  async searchCustomers(term: string): Promise<OfflineCustomer[]> {
    const searchTerm = term.toLowerCase();
    const customers = await this.customers.toArray();

    return customers.filter(c =>
      c.first_name.toLowerCase().includes(searchTerm) ||
      c.last_name?.toLowerCase().includes(searchTerm) ||
      c.full_name.toLowerCase().includes(searchTerm) ||
      c.phone?.includes(searchTerm) ||
      c.email?.toLowerCase().includes(searchTerm) ||
      c.loyalty_card_number?.includes(searchTerm)
    );
  }

  // Offline order methods
  async getPendingOrders(): Promise<OfflineOrder[]> {
    return this.offlineOrders.where('status').equals('pending').toArray();
  }

  async getFailedOrders(): Promise<OfflineOrder[]> {
    return this.offlineOrders.where('status').equals('failed').toArray();
  }

  async addOfflineOrder(order: OfflineOrder): Promise<void> {
    await this.offlineOrders.put(order);

    // Add to sync queue
    await this.syncQueue.add({
      type: 'order',
      data: order,
      status: 'pending',
      retry_count: 0,
      created_at: new Date().toISOString(),
    });
  }

  async markOrderSynced(offlineId: string, serverOrderId: number, serverOrderNumber: string): Promise<void> {
    await this.offlineOrders.update(offlineId, {
      status: 'synced',
      server_order_id: serverOrderId,
      server_order_number: serverOrderNumber,
      synced_at: new Date().toISOString(),
    });
  }

  async markOrderFailed(offlineId: string, errorMessage: string): Promise<void> {
    const order = await this.offlineOrders.get(offlineId);
    if (order) {
      await this.offlineOrders.update(offlineId, {
        status: 'failed',
        error_message: errorMessage,
        retry_count: order.retry_count + 1,
      });
    }
  }

  // Cart methods
  async getActiveCart(storeId: number): Promise<OfflineCart | undefined> {
    return this.carts
      .where('store_id')
      .equals(storeId)
      .and(c => c.status === 'active')
      .first();
  }

  async getHeldCarts(storeId: number): Promise<OfflineCart[]> {
    return this.carts
      .where('store_id')
      .equals(storeId)
      .and(c => c.status === 'held')
      .toArray();
  }

  // Sync queue methods
  async getPendingQueueItems(): Promise<SyncQueueItem[]> {
    return this.syncQueue.where('status').equals('pending').toArray();
  }

  async processQueueItem(id: number): Promise<void> {
    await this.syncQueue.update(id, {
      status: 'processing',
    });
  }

  async completeQueueItem(id: number): Promise<void> {
    await this.syncQueue.update(id, {
      status: 'completed',
      processed_at: new Date().toISOString(),
    });
  }

  async failQueueItem(id: number, errorMessage: string): Promise<void> {
    const item = await this.syncQueue.get(id);
    if (item) {
      await this.syncQueue.update(id, {
        status: 'failed',
        error_message: errorMessage,
        retry_count: item.retry_count + 1,
      });
    }
  }
}

// Export singleton instance
export const db = new OfflineDatabase();

// Export for type access
export type { OfflineDatabase };
