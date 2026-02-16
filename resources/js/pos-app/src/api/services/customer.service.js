import api from '../axios';

export const customerService = {
  async getCustomers(filters = {}) {
    const response = await api.get(route('pos.customers.index'), { params: filters });
    return response.data;
  },

  async getCustomer(id) {
    const response = await api.get(route('pos.customers.show', { customer: id }));
    return response.data;
  },

  async createCustomer(data) {
    const response = await api.post(route('pos.customers.store'), data);
    return response.data;
  },

  async updateCustomer(id, data) {
    const response = await api.put(route('pos.customers.update', { customer: id }), data);
    return response.data;
  },

  async deleteCustomer(id) {
    await api.delete(route('pos.customers.destroy', { customer: id }));
  },

  async searchCustomers(query) {
    const response = await api.get(route('pos.customers.search'), { params: { q: query } });
    return response.data.data;
  },

  async getCustomerOrders(id) {
    const response = await api.get(route('pos.customers.show', { customer: id }) + '/orders');
    return response.data;
  },

  async getCustomerLoyalty(id) {
    const response = await api.get(route('pos.customers.show', { customer: id }) + '/loyalty');
    return response.data;
  },

  async adjustCredit(id, amount, reason) {
    const response = await api.post(route('pos.statements.customer.credits.add', { customer: id }), { amount, reason });
    return response.data;
  },
};
