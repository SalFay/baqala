import api from '../axios';

export const userService = {
  async getUsers(filters = {}) {
    const response = await api.get('/users', { params: filters });
    return response.data;
  },

  async getUser(id) {
    const response = await api.get(`/users/${id}`);
    return response.data.data;
  },

  async createUser(data) {
    const response = await api.post('/users', data);
    return response.data.data;
  },

  async updateUser(id, data) {
    const response = await api.put(`/users/${id}`, data);
    return response.data.data;
  },

  async deleteUser(id) {
    await api.delete(`/users/${id}`);
  },

  async assignStores(userId, storeIds, primaryStoreId) {
    const response = await api.post(`/users/${userId}/stores`, {
      store_ids: storeIds,
      primary_store_id: primaryStoreId,
    });
    return response.data.data;
  },

  async changePassword(userId, password, passwordConfirmation) {
    await api.post(`/users/${userId}/password`, {
      password,
      password_confirmation: passwordConfirmation,
    });
  },

  async toggleStatus(userId) {
    const response = await api.post(`/users/${userId}/toggle-status`);
    return response.data.data;
  },
};
