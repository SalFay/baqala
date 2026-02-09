import api from '../axios';
import type { User } from '../../types';

interface LoginResponse {
  user: User;
  token: string;
}

export const authService = {
  async login(email: string, password: string): Promise<LoginResponse> {
    const response = await api.post('/auth/login', { email, password });
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout');
  },

  async me(): Promise<{ user: User }> {
    const response = await api.get('/auth/me');
    return response.data;
  },

  async changePassword(currentPassword: string, password: string, passwordConfirmation: string): Promise<void> {
    await api.post('/auth/change-password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    });
  },
};
