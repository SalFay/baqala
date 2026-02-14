import api from '../axios';
import type { User, Store, PaginatedResponse } from '../../types';

interface UserFilters {
  search?: string;
  role_id?: number;
  status?: string;
  per_page?: number;
  page?: number;
  sort_by?: string;
  sort_direction?: 'asc' | 'desc';
}

interface CreateUserData {
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  role_id: number;
  status?: string;
  store_id?: number;
}

interface UpdateUserData {
  first_name?: string;
  last_name?: string;
  email?: string;
  phone?: string;
  password?: string;
  password_confirmation?: string;
  role_id?: number;
  status?: string;
}

export const userService = {
  async getUsers(filters: UserFilters = {}): Promise<PaginatedResponse<User>> {
    const response = await api.get('/users', { params: filters });
    return response.data;
  },

  async getUser(id: number): Promise<User & { stores: Store[] }> {
    const response = await api.get(`/users/${id}`);
    return response.data.data;
  },

  async createUser(data: CreateUserData): Promise<User> {
    const response = await api.post('/users', data);
    return response.data.data;
  },

  async updateUser(id: number, data: UpdateUserData): Promise<User> {
    const response = await api.put(`/users/${id}`, data);
    return response.data.data;
  },

  async deleteUser(id: number): Promise<void> {
    await api.delete(`/users/${id}`);
  },

  async assignStores(userId: number, storeIds: number[], primaryStoreId?: number): Promise<User> {
    const response = await api.post(`/users/${userId}/stores`, {
      store_ids: storeIds,
      primary_store_id: primaryStoreId,
    });
    return response.data.data;
  },

  async changePassword(userId: number, password: string, passwordConfirmation: string): Promise<void> {
    await api.post(`/users/${userId}/password`, {
      password,
      password_confirmation: passwordConfirmation,
    });
  },

  async toggleStatus(userId: number): Promise<User> {
    const response = await api.post(`/users/${userId}/toggle-status`);
    return response.data.data;
  },
};
