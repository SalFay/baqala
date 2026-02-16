import api from '../axios';

export const storeService = {
  async getStores(filters = {}) {
    const response = await api.get(route('settings.stores'), { params: filters });
    return response.data.data;
  },

  async getStore(id) {
    const response = await api.get(route('settings.stores') + `/${id}`);
    return response.data.data;
  },

  async createStore(data) {
    const response = await api.post(route('settings.stores.store'), data);
    return response.data.data;
  },

  async updateStore(id, data) {
    const response = await api.put(route('settings.stores.update', { store: id }), data);
    return response.data.data;
  },

  async deleteStore(id) {
    await api.delete(route('settings.stores.destroy', { store: id }));
  },

  async getStoreInventory(storeId, params = {}) {
    const response = await api.get(route('settings.stores') + `/${storeId}/inventory`, { params });
    return response.data;
  },

  async getStoreStats(storeId) {
    const response = await api.get(route('settings.stores') + `/${storeId}/stats`);
    return response.data.data;
  },

  async assignUsers(storeId, userIds) {
    const response = await api.post(route('settings.stores') + `/${storeId}/users`, { user_ids: userIds });
    return response.data.data;
  },

  async toggleStatus(storeId) {
    const response = await api.post(route('settings.stores') + `/${storeId}/toggle-status`);
    return response.data.data;
  },
};
