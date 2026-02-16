import api from '../axios';

export const customerService = {
  async getCustomers(filters = {}) {
    const response = await api.get('/pos/customers', { params: filters });
    return response.data;
  },

  async getCustomer(id) {
    const response = await api.get(`/pos/customers/${id}`);
    return response.data;
  },

  async createCustomer(data) {
    const response = await api.post('/pos/customers', data);
    return response.data;
  },

  async updateCustomer(id, data) {
    const response = await api.put(`/pos/customers/${id}`, data);
    return response.data;
  },

  async deleteCustomer(id) {
    await api.delete(`/pos/customers/${id}`);
  },

  async searchCustomers(query) {
    const response = await api.get('/pos/customers/search', { params: { q: query } });
    return response.data.data;
  },

  async getCustomerOrders(id) {
    const response = await api.get(`/pos/customers/${id}/orders`);
    return response.data;
  },

  async getCustomerLoyalty(id) {
    const response = await api.get(`/pos/customers/${id}/loyalty`);
    return response.data;
  },

  async adjustCredit(id, amount, reason) {
    const response = await api.post(`/pos/customers/${id}/credit`, { amount, reason });
    return response.data;
  },
};
