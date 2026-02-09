import api from '../axios';

export const orderService = {
  async getOrders(filters = {}) {
    const response = await api.get('/orders', { params: filters });
    return response.data;
  },

  async getOrder(id) {
    const response = await api.get(`/orders/${id}`);
    return response.data;
  },

  async getReceipt(id) {
    const response = await api.get(`/orders/${id}/receipt`);
    return response.data;
  },

  async cancelOrder(id, reason) {
    const response = await api.post(`/orders/${id}/cancel`, { reason });
    return response.data.order;
  },

  async getTodayOrders(storeId) {
    const response = await api.get('/orders/today', { params: { store_id: storeId } });
    return response.data;
  },

  async getRecentOrders(storeId, limit = 10) {
    const response = await api.get('/orders/recent', { params: { store_id: storeId, limit } });
    return response.data;
  },
};
