import api from '../axios';

export const vendorService = {
  async getVendors(filters = {}) {
    const response = await api.get('/vendors', { params: filters });
    return response.data;
  },

  async getVendor(id) {
    const response = await api.get(`/vendors/${id}`);
    return response.data.data;
  },

  async createVendor(data) {
    const response = await api.post('/vendors', data);
    return response.data.data;
  },

  async updateVendor(id, data) {
    const response = await api.put(`/vendors/${id}`, data);
    return response.data.data;
  },

  async deleteVendor(id) {
    await api.delete(`/vendors/${id}`);
  },

  async searchVendors(query) {
    const response = await api.get('/vendors/search', { params: { q: query } });
    return response.data.data;
  },

  async getVendorPurchaseOrders(vendorId, page = 1) {
    const response = await api.get(`/vendors/${vendorId}/purchase-orders`, { params: { page } });
    return response.data;
  },

  async getVendorBalance(vendorId) {
    const response = await api.get(`/vendors/${vendorId}/balance`);
    return response.data.data;
  },
};
