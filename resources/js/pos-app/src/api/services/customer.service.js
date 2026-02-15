import api from '../axios';

export const customerService = {
  async getCustomers(filters = {}) {
    const response = await api.get('/customers', { params: filters });
    return response.data;
  },

  async getCustomer(id) {
    const response = await api.get(`/customers/${id}`);
    return response.data;
  },

  async createCustomer(data) {
    const response = await api.post('/customers', data);
    return response.data;
  },

  async updateCustomer(id, data) {
    const response = await api.put(`/customers/${id}`, data);
    return response.data;
  },

  async deleteCustomer(id) {
    await api.delete(`/customers/${id}`);
  },

  async searchCustomers(query) {
    const response = await api.get('/pos/customers/search', { params: { q: query } });
    return response.data.data;
  },

  async getCustomerOrders(id) {
    const response = await api.get(`/customers/${id}/orders`);
    return response.data;
  },

  async getCustomerLoyalty(id) {
    const response = await api.get(`/customers/${id}/loyalty`);
    return response.data;
  },

  async adjustCredit(id, amount, reason) {
    const response = await api.post(`/customers/${id}/credit`, { amount, reason });
    return response.data;
  },
};
