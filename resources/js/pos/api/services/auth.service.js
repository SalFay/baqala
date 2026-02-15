import api from '../axios';
import route from '@pos/utils/route';

export const authService = {
  // Check if user is authenticated (via Laravel session)
  async me() {
    const response = await api.get(route('pos.auth.me'));
    return response.data;
  },

  // Logout via Laravel's logout route
  async logout() {
    await api.post(route('logout'));
    // Redirect to login page after logout
    window.location.href = route('login');
  },
};
