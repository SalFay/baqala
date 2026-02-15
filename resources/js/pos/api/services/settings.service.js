import api from '../axios';

export const settingsService = {
  getSettings: async () => {
    const { data } = await api.get('/settings');
    return data.data;
  },

  getSettingGroups: async () => {
    const { data } = await api.get('/settings/groups');
    return data.data;
  },

  updateSettings: async (settings) => {
    await api.put('/settings', { settings });
  },

  uploadLogo: async (file) => {
    const formData = new FormData();
    formData.append('logo', file);
    const { data } = await api.post('/settings/logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data.url;
  },

  // Tax rates
  getTaxRates: async () => {
    const { data } = await api.get('/tax-rates');
    return data.data;
  },

  createTaxRate: async (taxRate) => {
    const { data } = await api.post('/tax-rates', taxRate);
    return data.data;
  },

  updateTaxRate: async (id, taxRate) => {
    const { data } = await api.put(`/tax-rates/${id}`, taxRate);
    return data.data;
  },

  deleteTaxRate: async (id) => {
    await api.delete(`/tax-rates/${id}`);
  },

  // Business Types
  getBusinessTypes: async () => {
    const { data } = await api.get('/pos/business-types');
    return data.data;
  },

  getCurrentBusinessType: async () => {
    const { data } = await api.get('/pos/business-types/current');
    return data.data;
  },

  previewBusinessType: async (id) => {
    const { data } = await api.get(`/pos/business-types/${id}/preview`);
    return data.data;
  },

  applyBusinessType: async (id, options = {}) => {
    const { data } = await api.post(`/pos/business-types/${id}/apply`, options);
    return data;
  },

  seedBusinessTypes: async () => {
    const { data } = await api.post('/pos/business-types/seed');
    return data;
  },
};
