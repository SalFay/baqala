import api from '../axios';

export const dashboardService = {
  async getStats(storeId) {
    const response = await api.get('/pos/dashboard/stats', { params: { store_id: storeId } });
    return response.data;
  },

  async getSalesChart(fromDate, toDate, storeId) {
    const response = await api.get('/pos/dashboard/sales-chart', {
      params: { from_date: fromDate, to_date: toDate, store_id: storeId },
    });
    return response.data;
  },

  async getTopProducts(fromDate, toDate, storeId, limit = 10) {
    const response = await api.get('/pos/dashboard/top-products', {
      params: { from_date: fromDate, to_date: toDate, store_id: storeId, limit },
    });
    return response.data;
  },

  async getLowStock(storeId) {
    const response = await api.get('/pos/dashboard/low-stock', { params: { store_id: storeId } });
    return response.data;
  },

  async getRecentOrders(storeId, limit = 10) {
    const response = await api.get('/pos/dashboard/recent-orders', {
      params: { store_id: storeId, limit },
    });
    return response.data;
  },
};
