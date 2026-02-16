import api from '../axios';

export const orderService = {
  async getOrders(filters = {}) {
    const response = await api.get(route('pos.orders.index'), { params: filters });
    return response.data;
  },

  async getOrder(id) {
    const response = await api.get(route('pos.orders.show', { id }));
    return response.data;
  },

  async getReceipt(id) {
    const response = await api.get(route('pos.orders.receipt', { id }));
    return response.data;
  },

  async cancelOrder(id, reason) {
    const response = await api.post(route('pos.orders.cancel', { id }), { reason });
    return response.data.order;
  },

  async getTodayOrders(storeId) {
    const response = await api.get(route('pos.orders.today'), { params: { store_id: storeId } });
    return response.data;
  },

  async getRecentOrders(storeId, limit = 10) {
    const response = await api.get(route('pos.orders.recent'), { params: { store_id: storeId, limit } });
    return response.data;
  },
};
