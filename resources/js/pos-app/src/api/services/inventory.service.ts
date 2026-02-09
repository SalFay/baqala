import api from '../axios';
import type { PaginatedResponse } from '../../types';

export interface InventoryItem {
  id: number;
  product_id: number;
  product_variant_id?: number;
  store_id: number;
  product_name: string;
  variant_name?: string;
  sku: string;
  barcode?: string;
  quantity: number;
  reserved_quantity: number;
  available_quantity: number;
  low_stock_threshold: number;
  location?: string;
  last_counted_at?: string;
  store_name: string;
}

export interface InventoryMovement {
  id: number;
  product_name: string;
  variant_name?: string;
  type: string;
  quantity: number;
  quantity_before: number;
  quantity_after: number;
  unit_cost?: number;
  reason?: string;
  notes?: string;
  created_by_name: string;
  created_at: string;
}

export interface InventoryFilters {
  search?: string;
  store_id?: number;
  category_id?: number;
  low_stock?: boolean;
  page?: number;
  per_page?: number;
}

export interface AdjustmentData {
  product_id: number;
  product_variant_id?: number;
  store_id: number;
  type: 'adjustment_add' | 'adjustment_remove' | 'damage' | 'count';
  quantity: number;
  reason?: string;
  notes?: string;
}

export const inventoryService = {
  getInventory: async (filters: InventoryFilters = {}): Promise<PaginatedResponse<InventoryItem>> => {
    const { data } = await api.get('/inventory', { params: filters });
    return data;
  },

  getLowStock: async (): Promise<InventoryItem[]> => {
    const { data } = await api.get('/inventory/low-stock');
    return data.data;
  },

  getMovements: async (filters: any = {}): Promise<PaginatedResponse<InventoryMovement>> => {
    const { data } = await api.get('/inventory/movements', { params: filters });
    return data;
  },

  adjustStock: async (adjustment: AdjustmentData): Promise<any> => {
    const { data } = await api.post('/inventory/adjust', adjustment);
    return data.data;
  },

  countStock: async (countData: {
    store_id: number;
    items: { product_id: number; product_variant_id?: number; counted_quantity: number }[];
  }): Promise<any> => {
    const { data } = await api.post('/inventory/count', countData);
    return data.data;
  },

  getStores: async (): Promise<{ id: number; name: string; code: string }[]> => {
    const { data } = await api.get('/stores');
    return data.data;
  },
};
