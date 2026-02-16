import api from '../axios';

export const userService = {
  async getUsers(filters = {}) {
    const response = await api.get(route('settings.users'), { params: filters });
    return response.data;
  },

  async getUser(id) {
    const response = await api.get(route('settings.users') + `/${id}`);
    return response.data.data;
  },

  async createUser(data) {
    const response = await api.post(route('settings.users.store'), data);
    return response.data.data;
  },

  async updateUser(id, data) {
    const response = await api.put(route('settings.users.update', { user: id }), data);
    return response.data.data;
  },

  async deleteUser(id) {
    await api.delete(route('settings.users.destroy', { user: id }));
  },

  async assignStores(userId, storeIds, primaryStoreId) {
    const response = await api.post(route('settings.users') + `/${userId}/stores`, {
      store_ids: storeIds,
      primary_store_id: primaryStoreId,
    });
    return response.data.data;
  },

  async changePassword(userId, password, passwordConfirmation) {
    await api.post(route('settings.users') + `/${userId}/password`, {
      password,
      password_confirmation: passwordConfirmation,
    });
  },

  async toggleStatus(userId) {
    const response = await api.post(route('settings.users') + `/${userId}/toggle-status`);
    return response.data.data;
  },
};
