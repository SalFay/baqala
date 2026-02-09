import api from '../axios';

export const taxRateService = {
  async getTaxRates(params = {}) {
    const response = await api.get('/tax-rates', { params });
    return response.data.data;
  },

  async getTaxRate(id) {
    const response = await api.get(`/tax-rates/${id}`);
    return response.data.data;
  },

  async getDefaultTaxRate() {
    const response = await api.get('/tax-rates/default');
    return response.data.data;
  },

  async createTaxRate(data) {
    const response = await api.post('/tax-rates', data);
    return response.data.data;
  },

  async updateTaxRate(id, data) {
    const response = await api.put(`/tax-rates/${id}`, data);
    return response.data.data;
  },

  async deleteTaxRate(id) {
    await api.delete(`/tax-rates/${id}`);
  },

  async setDefaultTaxRate(id) {
    const response = await api.post(`/tax-rates/${id}/set-default`);
    return response.data.data;
  },

  async reorderTaxRates(order) {
    await api.post('/tax-rates/reorder', { order });
  },
};
