import api from '../axios';
import type { PaginatedResponse } from '../../types';

export interface Vendor {
  id: number;
  name: string;
  mobile: string | null;
  address: string | null;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

interface VendorFilters {
  search?: string;
  status?: string;
  per_page?: number;
  page?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

export const vendorService = {
  async getVendors(filters: VendorFilters = {}): Promise<PaginatedResponse<Vendor>> {
    const response = await api.get('/vendors', { params: filters });
    return response.data;
  },

  async getVendor(id: number): Promise<Vendor> {
    const response = await api.get(`/vendors/${id}`);
    return response.data.data;
  },

  async createVendor(data: Partial<Vendor>): Promise<Vendor> {
    const response = await api.post('/vendors', data);
    return response.data.data;
  },

  async updateVendor(id: number, data: Partial<Vendor>): Promise<Vendor> {
    const response = await api.put(`/vendors/${id}`, data);
    return response.data.data;
  },

  async deleteVendor(id: number): Promise<void> {
    await api.delete(`/vendors/${id}`);
  },

  async searchVendors(query: string): Promise<Vendor[]> {
    const response = await api.get('/vendors/search', { params: { q: query } });
    return response.data.data;
  },

  async getVendorPurchaseOrders(vendorId: number, page: number = 1): Promise<PaginatedResponse<any>> {
    const response = await api.get(`/vendors/${vendorId}/purchase-orders`, { params: { page } });
    return response.data;
  },

  async getVendorBalance(vendorId: number): Promise<{ balance: number }> {
    const response = await api.get(`/vendors/${vendorId}/balance`);
    return response.data.data;
  },
};
