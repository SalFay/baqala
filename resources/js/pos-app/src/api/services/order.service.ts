import api from '../axios';
import type { Order, PaginatedResponse } from '../../types';

interface OrderFilters {
  store_id?: number;
  customer_id?: number;
  status?: string;
  payment_status?: string;
  from_date?: string;
  to_date?: string;
  search?: string;
  per_page?: number;
  page?: number;
}

export const orderService = {
  async getOrders(filters: OrderFilters = {}): Promise<PaginatedResponse<Order>> {
    const response = await api.get('/orders', { params: filters });
    return response.data;
  },

  async getOrder(id: number): Promise<Order> {
    const response = await api.get(`/orders/${id}`);
    return response.data;
  },

  async getReceipt(id: number): Promise<any> {
    const response = await api.get(`/orders/${id}/receipt`);
    return response.data;
  },

  async cancelOrder(id: number, reason?: string): Promise<Order> {
    const response = await api.post(`/orders/${id}/cancel`, { reason });
    return response.data.order;
  },

  async getTodayOrders(storeId?: number): Promise<{ orders: Order[]; stats: any }> {
    const response = await api.get('/orders/today', { params: { store_id: storeId } });
    return response.data;
  },

  async getRecentOrders(storeId?: number, limit: number = 10): Promise<Order[]> {
    const response = await api.get('/orders/recent', { params: { store_id: storeId, limit } });
    return response.data;
  },
};
