import api from '../axios';
import type { Store, PaginatedResponse } from '../../types';

interface StoreFilters {
  search?: string;
  is_active?: boolean;
  is_warehouse?: boolean;
}

interface StoreStats {
  total_products: number;
  total_orders: number;
  today_orders: number;
  today_sales: number;
  low_stock_items: number;
  active_users: number;
}

export const storeService = {
  async getStores(filters: StoreFilters = {}): Promise<Store[]> {
    const response = await api.get('/stores', { params: filters });
    return response.data.data;
  },

  async getStore(id: number): Promise<Store> {
    const response = await api.get(`/stores/${id}`);
    return response.data.data;
  },

  async createStore(data: Partial<Store>): Promise<Store> {
    const response = await api.post('/stores', data);
    return response.data.data;
  },

  async updateStore(id: number, data: Partial<Store>): Promise<Store> {
    const response = await api.put(`/stores/${id}`, data);
    return response.data.data;
  },

  async deleteStore(id: number): Promise<void> {
    await api.delete(`/stores/${id}`);
  },

  async getStoreInventory(storeId: number, params: { search?: string; low_stock?: boolean; page?: number; per_page?: number } = {}): Promise<PaginatedResponse<any>> {
    const response = await api.get(`/stores/${storeId}/inventory`, { params });
    return response.data;
  },

  async getStoreStats(storeId: number): Promise<StoreStats> {
    const response = await api.get(`/stores/${storeId}/stats`);
    return response.data.data;
  },

  async assignUsers(storeId: number, userIds: number[]): Promise<Store> {
    const response = await api.post(`/stores/${storeId}/users`, { user_ids: userIds });
    return response.data.data;
  },

  async toggleStatus(storeId: number): Promise<Store> {
    const response = await api.post(`/stores/${storeId}/toggle-status`);
    return response.data.data;
  },
};
