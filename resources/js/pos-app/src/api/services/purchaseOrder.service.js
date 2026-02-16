import api from '../axios';

export const purchaseOrderService = {
  async getPurchaseOrders(filters = {}) {
    const response = await api.get(route('purchase-orders.index'), { params: filters });
    return response.data;
  },

  async getPurchaseOrder(id) {
    const response = await api.get(route('purchase-orders.show', { purchaseOrder: id }));
    return response.data.data;
  },

  async createPurchaseOrder(data) {
    const response = await api.post(route('purchase-orders.store'), data);
    return response.data.data;
  },

  async updatePurchaseOrder(id, data) {
    const response = await api.put(route('purchase-orders.update', { purchaseOrder: id }), data);
    return response.data.data;
  },

  async deletePurchaseOrder(id) {
    await api.delete(route('purchase-orders.index') + `/${id}`);
  },

  async submitPurchaseOrder(id) {
    const response = await api.post(route('purchase-orders.index') + `/${id}/submit`);
    return response.data.data;
  },

  async approvePurchaseOrder(id) {
    const response = await api.post(route('purchase-orders.index') + `/${id}/approve`);
    return response.data.data;
  },

  async orderPurchaseOrder(id) {
    const response = await api.post(route('purchase-orders.index') + `/${id}/order`);
    return response.data.data;
  },

  async receivePurchaseOrder(id, items, notes) {
    const response = await api.post(route('purchase-orders.receive', { purchaseOrder: id }), { items, notes });
    return response.data.data;
  },

  async cancelPurchaseOrder(id) {
    const response = await api.post(route('purchase-orders.index') + `/${id}/cancel`);
    return response.data.data;
  },
};
