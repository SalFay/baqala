import api from '../axios';

export const stockTransferService = {
  async getStockTransfers(filters = {}) {
    const response = await api.get('/stock-transfers', { params: filters });
    return response.data;
  },

  async getStockTransfer(id) {
    const response = await api.get(`/stock-transfers/${id}`);
    return response.data.data;
  },

  async createStockTransfer(data) {
    const response = await api.post('/stock-transfers', data);
    return response.data.data;
  },

  async updateStockTransfer(id, data) {
    const response = await api.put(`/stock-transfers/${id}`, data);
    return response.data.data;
  },

  async deleteStockTransfer(id) {
    await api.delete(`/stock-transfers/${id}`);
  },

  async submitStockTransfer(id) {
    const response = await api.post(`/stock-transfers/${id}/submit`);
    return response.data.data;
  },

  async shipStockTransfer(id, items) {
    const response = await api.post(`/stock-transfers/${id}/ship`, { items });
    return response.data.data;
  },

  async receiveStockTransfer(id, items) {
    const response = await api.post(`/stock-transfers/${id}/receive`, { items });
    return response.data.data;
  },

  async cancelStockTransfer(id) {
    const response = await api.post(`/stock-transfers/${id}/cancel`);
    return response.data.data;
  },
};
