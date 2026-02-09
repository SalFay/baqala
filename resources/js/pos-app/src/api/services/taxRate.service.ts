import api from '../axios';

export interface TaxRate {
  id: number;
  name: string;
  name_ar: string | null;
  code: string;
  rate: number;
  is_active: boolean;
  is_default: boolean;
  description: string | null;
  sort_order: number;
  created_at: string;
}

export const taxRateService = {
  async getTaxRates(params: { search?: string; is_active?: boolean } = {}): Promise<TaxRate[]> {
    const response = await api.get('/tax-rates', { params });
    return response.data.data;
  },

  async getTaxRate(id: number): Promise<TaxRate> {
    const response = await api.get(`/tax-rates/${id}`);
    return response.data.data;
  },

  async getDefaultTaxRate(): Promise<TaxRate | null> {
    const response = await api.get('/tax-rates/default');
    return response.data.data;
  },

  async createTaxRate(data: Partial<TaxRate>): Promise<TaxRate> {
    const response = await api.post('/tax-rates', data);
    return response.data.data;
  },

  async updateTaxRate(id: number, data: Partial<TaxRate>): Promise<TaxRate> {
    const response = await api.put(`/tax-rates/${id}`, data);
    return response.data.data;
  },

  async deleteTaxRate(id: number): Promise<void> {
    await api.delete(`/tax-rates/${id}`);
  },

  async setDefaultTaxRate(id: number): Promise<TaxRate> {
    const response = await api.post(`/tax-rates/${id}/set-default`);
    return response.data.data;
  },

  async reorderTaxRates(order: number[]): Promise<void> {
    await api.post('/tax-rates/reorder', { order });
  },
};
