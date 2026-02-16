import api from '../axios';

export const stockTransferService = {
  async getStockTransfers(filters = {}) {
    const response = await api.get(route('stock-transfers.index'), { params: filters });
    return response.data;
  },

  async getStockTransfer(id) {
    const response = await api.get(route('stock-transfers.show', { stockTransfer: id }));
    return response.data.data;
  },

  async createStockTransfer(data) {
    const response = await api.post(route('stock-transfers.store'), data);
    return response.data.data;
  },

  async updateStockTransfer(id, data) {
    const response = await api.put(route('stock-transfers.index') + `/${id}`, data);
    return response.data.data;
  },

  async deleteStockTransfer(id) {
    await api.delete(route('stock-transfers.index') + `/${id}`);
  },

  async submitStockTransfer(id) {
    const response = await api.post(route('stock-transfers.index') + `/${id}/submit`);
    return response.data.data;
  },

  async shipStockTransfer(id, items) {
    const response = await api.post(route('stock-transfers.ship', { stockTransfer: id }), { items });
    return response.data.data;
  },

  async receiveStockTransfer(id, items) {
    const response = await api.post(route('stock-transfers.receive', { stockTransfer: id }), { items });
    return response.data.data;
  },

  async cancelStockTransfer(id) {
    const response = await api.post(route('stock-transfers.index') + `/${id}/cancel`);
    return response.data.data;
  },
};
