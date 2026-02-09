import api from '../axios';
import type { DashboardStats, Product } from '../../types';

export const dashboardService = {
  async getStats(storeId?: number): Promise<DashboardStats> {
    const response = await api.get('/dashboard/stats', { params: { store_id: storeId } });
    return response.data;
  },

  async getSalesChart(fromDate?: string, toDate?: string, storeId?: number): Promise<any[]> {
    const response = await api.get('/dashboard/sales-chart', {
      params: { from_date: fromDate, to_date: toDate, store_id: storeId },
    });
    return response.data;
  },

  async getTopProducts(fromDate?: string, toDate?: string, storeId?: number, limit: number = 10): Promise<any[]> {
    const response = await api.get('/dashboard/top-products', {
      params: { from_date: fromDate, to_date: toDate, store_id: storeId, limit },
    });
    return response.data;
  },

  async getLowStock(storeId?: number): Promise<any[]> {
    const response = await api.get('/dashboard/low-stock', { params: { store_id: storeId } });
    return response.data;
  },

  async getRecentOrders(storeId?: number, limit: number = 10): Promise<any[]> {
    const response = await api.get('/dashboard/recent-orders', {
      params: { store_id: storeId, limit },
    });
    return response.data;
  },
};
