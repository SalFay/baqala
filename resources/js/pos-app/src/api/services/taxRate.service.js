import api from '../axios';

export const taxRateService = {
  async getTaxRates(params = {}) {
    const response = await api.get(route('settings.tax-rates'), { params });
    return response.data.data;
  },

  async getTaxRate(id) {
    const response = await api.get(route('settings.tax-rates') + `/${id}`);
    return response.data.data;
  },

  async getDefaultTaxRate() {
    const response = await api.get(route('settings.tax-rates') + '/default');
    return response.data.data;
  },

  async createTaxRate(data) {
    const response = await api.post(route('settings.tax-rates.store'), data);
    return response.data.data;
  },

  async updateTaxRate(id, data) {
    const response = await api.put(route('settings.tax-rates.update', { taxRate: id }), data);
    return response.data.data;
  },

  async deleteTaxRate(id) {
    await api.delete(route('settings.tax-rates.destroy', { taxRate: id }));
  },

  async setDefaultTaxRate(id) {
    const response = await api.post(route('settings.tax-rates') + `/${id}/set-default`);
    return response.data.data;
  },

  async reorderTaxRates(order) {
    await api.post(route('settings.tax-rates') + '/reorder', { order });
  },
};
