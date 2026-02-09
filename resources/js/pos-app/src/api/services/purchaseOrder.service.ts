import api from '../axios';
import type { PaginatedResponse } from '../../types';

export type PurchaseOrderStatus = 'draft' | 'pending_approval' | 'approved' | 'ordered' | 'partial' | 'received' | 'cancelled';

export interface PurchaseOrderItem {
  id: number;
  purchase_order_id: number;
  product_id: number;
  product_variant_id: number | null;
  quantity_ordered: number;
  quantity_received: number;
  unit_cost: number;
  tax_rate: number;
  tax_amount: number;
  line_total: number;
  product?: any;
  product_variant?: any;
}

export interface PurchaseOrder {
  id: number;
  po_number: string;
  vendor_id: number;
  store_id: number;
  created_by: number;
  approved_by: number | null;
  status: PurchaseOrderStatus;
  order_date: string;
  expected_date: string | null;
  received_date: string | null;
  subtotal: number;
  tax_amount: number;
  total: number;
  notes: string | null;
  vendor?: any;
  store?: any;
  items?: PurchaseOrderItem[];
  created_at: string;
}

interface PurchaseOrderFilters {
  search?: string;
  status?: PurchaseOrderStatus;
  vendor_id?: number;
  store_id?: number;
  from_date?: string;
  to_date?: string;
  per_page?: number;
  page?: number;
}

interface CreatePurchaseOrderData {
  vendor_id: number;
  store_id: number;
  order_date: string;
  expected_date?: string;
  notes?: string;
  items: {
    product_id: number;
    product_variant_id?: number;
    quantity_ordered: number;
    unit_cost: number;
    tax_rate?: number;
  }[];
}

interface ReceiveItemData {
  purchase_order_item_id: number;
  quantity_received: number;
  condition?: 'good' | 'damaged';
  notes?: string;
}

export const purchaseOrderService = {
  async getPurchaseOrders(filters: PurchaseOrderFilters = {}): Promise<PaginatedResponse<PurchaseOrder>> {
    const response = await api.get('/purchase-orders', { params: filters });
    return response.data;
  },

  async getPurchaseOrder(id: number): Promise<PurchaseOrder> {
    const response = await api.get(`/purchase-orders/${id}`);
    return response.data.data;
  },

  async createPurchaseOrder(data: CreatePurchaseOrderData): Promise<PurchaseOrder> {
    const response = await api.post('/purchase-orders', data);
    return response.data.data;
  },

  async updatePurchaseOrder(id: number, data: Partial<CreatePurchaseOrderData>): Promise<PurchaseOrder> {
    const response = await api.put(`/purchase-orders/${id}`, data);
    return response.data.data;
  },

  async deletePurchaseOrder(id: number): Promise<void> {
    await api.delete(`/purchase-orders/${id}`);
  },

  async submitPurchaseOrder(id: number): Promise<PurchaseOrder> {
    const response = await api.post(`/purchase-orders/${id}/submit`);
    return response.data.data;
  },

  async approvePurchaseOrder(id: number): Promise<PurchaseOrder> {
    const response = await api.post(`/purchase-orders/${id}/approve`);
    return response.data.data;
  },

  async orderPurchaseOrder(id: number): Promise<PurchaseOrder> {
    const response = await api.post(`/purchase-orders/${id}/order`);
    return response.data.data;
  },

  async receivePurchaseOrder(id: number, items: ReceiveItemData[], notes?: string): Promise<PurchaseOrder> {
    const response = await api.post(`/purchase-orders/${id}/receive`, { items, notes });
    return response.data.data;
  },

  async cancelPurchaseOrder(id: number): Promise<PurchaseOrder> {
    const response = await api.post(`/purchase-orders/${id}/cancel`);
    return response.data.data;
  },
};
