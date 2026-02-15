import api from '../axios';

export const purchaseOrderService = {
  async getPurchaseOrders(filters = {}) {
    const response = await api.get('/purchase-orders', { params: filters });
    return response.data;
  },

  async getPurchaseOrder(id) {
    const response = await api.get(`/purchase-orders/${id}`);
    return response.data.data;
  },

  async createPurchaseOrder(data) {
    const response = await api.post('/purchase-orders', data);
    return response.data.data;
  },

  async updatePurchaseOrder(id, data) {
    const response = await api.put(`/purchase-orders/${id}`, data);
    return response.data.data;
  },

  async deletePurchaseOrder(id) {
    await api.delete(`/purchase-orders/${id}`);
  },

  async submitPurchaseOrder(id) {
    const response = await api.post(`/purchase-orders/${id}/submit`);
    return response.data.data;
  },

  async approvePurchaseOrder(id) {
    const response = await api.post(`/purchase-orders/${id}/approve`);
    return response.data.data;
  },

  async orderPurchaseOrder(id) {
    const response = await api.post(`/purchase-orders/${id}/order`);
    return response.data.data;
  },

  async receivePurchaseOrder(id, items, notes) {
    const response = await api.post(`/purchase-orders/${id}/receive`, { items, notes });
    return response.data.data;
  },

  async cancelPurchaseOrder(id) {
    const response = await api.post(`/purchase-orders/${id}/cancel`);
    return response.data.data;
  },
};
