import api from '../axios';
import type { PaginatedResponse } from '../../types';

export type StockTransferStatus = 'draft' | 'pending' | 'in_transit' | 'received' | 'cancelled';

export interface StockTransferItem {
  id: number;
  stock_transfer_id: number;
  product_id: number;
  product_variant_id: number | null;
  quantity_requested: number;
  quantity_sent: number;
  quantity_received: number;
  notes?: string;
  product?: any;
  product_variant?: any;
}

export interface StockTransfer {
  id: number;
  transfer_number: string;
  from_store_id: number;
  to_store_id: number;
  created_by: number;
  approved_by: number | null;
  received_by: number | null;
  status: StockTransferStatus;
  shipped_at: string | null;
  received_at: string | null;
  notes: string | null;
  from_store?: any;
  to_store?: any;
  items?: StockTransferItem[];
  created_at: string;
}

interface StockTransferFilters {
  search?: string;
  status?: StockTransferStatus;
  from_store_id?: number;
  to_store_id?: number;
  per_page?: number;
  page?: number;
}

interface CreateStockTransferData {
  from_store_id: number;
  to_store_id: number;
  notes?: string;
  items: {
    product_id: number;
    product_variant_id?: number;
    quantity_requested: number;
  }[];
}

interface ShipItemData {
  stock_transfer_item_id: number;
  quantity_sent: number;
}

interface ReceiveItemData {
  stock_transfer_item_id: number;
  quantity_received: number;
  notes?: string;
}

export const stockTransferService = {
  async getStockTransfers(filters: StockTransferFilters = {}): Promise<PaginatedResponse<StockTransfer>> {
    const response = await api.get('/stock-transfers', { params: filters });
    return response.data;
  },

  async getStockTransfer(id: number): Promise<StockTransfer> {
    const response = await api.get(`/stock-transfers/${id}`);
    return response.data.data;
  },

  async createStockTransfer(data: CreateStockTransferData): Promise<StockTransfer> {
    const response = await api.post('/stock-transfers', data);
    return response.data.data;
  },

  async updateStockTransfer(id: number, data: Partial<CreateStockTransferData>): Promise<StockTransfer> {
    const response = await api.put(`/stock-transfers/${id}`, data);
    return response.data.data;
  },

  async deleteStockTransfer(id: number): Promise<void> {
    await api.delete(`/stock-transfers/${id}`);
  },

  async submitStockTransfer(id: number): Promise<StockTransfer> {
    const response = await api.post(`/stock-transfers/${id}/submit`);
    return response.data.data;
  },

  async shipStockTransfer(id: number, items: ShipItemData[]): Promise<StockTransfer> {
    const response = await api.post(`/stock-transfers/${id}/ship`, { items });
    return response.data.data;
  },

  async receiveStockTransfer(id: number, items: ReceiveItemData[]): Promise<StockTransfer> {
    const response = await api.post(`/stock-transfers/${id}/receive`, { items });
    return response.data.data;
  },

  async cancelStockTransfer(id: number): Promise<StockTransfer> {
    const response = await api.post(`/stock-transfers/${id}/cancel`);
    return response.data.data;
  },
};
