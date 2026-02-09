import api from '../axios';
import type { Customer, PaginatedResponse } from '../../types';

interface CustomerFilters {
  search?: string;
  status?: string;
  per_page?: number;
  page?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

export const customerService = {
  async getCustomers(filters: CustomerFilters = {}): Promise<PaginatedResponse<Customer>> {
    const response = await api.get('/customers', { params: filters });
    return response.data;
  },

  async getCustomer(id: number): Promise<Customer> {
    const response = await api.get(`/customers/${id}`);
    return response.data;
  },

  async createCustomer(data: Partial<Customer>): Promise<Customer> {
    const response = await api.post('/customers', data);
    return response.data;
  },

  async updateCustomer(id: number, data: Partial<Customer>): Promise<Customer> {
    const response = await api.put(`/customers/${id}`, data);
    return response.data;
  },

  async deleteCustomer(id: number): Promise<void> {
    await api.delete(`/customers/${id}`);
  },

  async searchCustomers(query: string): Promise<Customer[]> {
    const response = await api.get('/customers/search', { params: { q: query } });
    return response.data;
  },

  async getCustomerOrders(id: number): Promise<PaginatedResponse<any>> {
    const response = await api.get(`/customers/${id}/orders`);
    return response.data;
  },

  async getCustomerLoyalty(id: number): Promise<any> {
    const response = await api.get(`/customers/${id}/loyalty`);
    return response.data;
  },

  async adjustCredit(id: number, amount: number, reason?: string): Promise<{ credit_balance: number }> {
    const response = await api.post(`/customers/${id}/credit`, { amount, reason });
    return response.data;
  },
};
