import api from '../axios';

export const authService = {
  async login(email, password) {
    const response = await api.post('/auth/login', { email, password });
    return response.data;
  },

  async logout() {
    await api.post('/auth/logout');
  },

  async me() {
    const response = await api.get('/auth/me');
    return response.data;
  },

  async changePassword(currentPassword, password, passwordConfirmation) {
    await api.post('/auth/change-password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    });
  },
};
