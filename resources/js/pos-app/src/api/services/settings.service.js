import api from '../axios';

export const settingsService = {
  getSettings: async () => {
    const { data } = await api.get(route('settings.index'));
    return data.data;
  },

  getSettingGroups: async () => {
    const { data } = await api.get(route('settings.index') + '/groups');
    return data.data;
  },

  updateSettings: async (settings) => {
    await api.put(route('settings.update'), { settings });
  },

  uploadLogo: async (file) => {
    const formData = new FormData();
    formData.append('logo', file);
    const { data } = await api.post(route('settings.index') + '/logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data.url;
  },

  // Tax rates
  getTaxRates: async () => {
    const { data } = await api.get(route('settings.tax-rates'));
    return data.data;
  },

  createTaxRate: async (taxRate) => {
    const { data } = await api.post(route('settings.tax-rates.store'), taxRate);
    return data.data;
  },

  updateTaxRate: async (id, taxRate) => {
    const { data } = await api.put(route('settings.tax-rates.update', { taxRate: id }), taxRate);
    return data.data;
  },

  deleteTaxRate: async (id) => {
    await api.delete(route('settings.tax-rates.destroy', { taxRate: id }));
  },

  // Business Types
  getBusinessTypes: async () => {
    const { data } = await api.get(route('pos.business-types.index'));
    return data.data;
  },

  getCurrentBusinessType: async () => {
    const { data } = await api.get(route('pos.business-types.current'));
    return data.data;
  },

  previewBusinessType: async (id) => {
    const { data } = await api.get(route('pos.business-types.preview', { businessType: id }));
    return data.data;
  },

  applyBusinessType: async (id, options = {}) => {
    const { data } = await api.post(route('pos.business-types.apply', { businessType: id }), options);
    return data;
  },

  seedBusinessTypes: async () => {
    const { data } = await api.post(route('pos.business-types.seed'));
    return data;
  },
};
