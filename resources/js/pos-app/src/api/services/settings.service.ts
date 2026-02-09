import api from '../axios';

export interface SettingGroup {
  id: number;
  name: string;
  slug: string;
  icon: string;
  description?: string;
  settings: Setting[];
}

export interface Setting {
  id: number;
  key: string;
  value: any;
  type: 'text' | 'number' | 'boolean' | 'json' | 'image' | 'select';
  options?: { label: string; value: string }[];
  label: string;
  description?: string;
}

export interface TaxRate {
  id: number;
  name: string;
  rate: number;
  is_default: boolean;
  is_active: boolean;
  description?: string;
}

export const settingsService = {
  getSettings: async (): Promise<SettingGroup[]> => {
    const { data } = await api.get('/settings');
    return data.data;
  },

  getSettingGroups: async (): Promise<SettingGroup[]> => {
    const { data } = await api.get('/settings/groups');
    return data.data;
  },

  updateSettings: async (settings: Record<string, any>): Promise<void> => {
    await api.put('/settings', { settings });
  },

  uploadLogo: async (file: File): Promise<string> => {
    const formData = new FormData();
    formData.append('logo', file);
    const { data } = await api.post('/settings/logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return data.url;
  },

  // Tax rates
  getTaxRates: async (): Promise<TaxRate[]> => {
    const { data } = await api.get('/tax-rates');
    return data.data;
  },

  createTaxRate: async (taxRate: Omit<TaxRate, 'id'>): Promise<TaxRate> => {
    const { data } = await api.post('/tax-rates', taxRate);
    return data.data;
  },

  updateTaxRate: async (id: number, taxRate: Partial<TaxRate>): Promise<TaxRate> => {
    const { data } = await api.put(`/tax-rates/${id}`, taxRate);
    return data.data;
  },

  deleteTaxRate: async (id: number): Promise<void> => {
    await api.delete(`/tax-rates/${id}`);
  },
};
