import api from '../axios';

export const authService = {
  // Check if user is authenticated (via Laravel session)
  async me() {
    const response = await api.get('/pos/auth/me');
    return response.data;
  },

  // Logout via Laravel's logout route
  async logout() {
    await api.post('/logout');
    // Redirect to login page after logout
    window.location.href = '/login';
  },
};
