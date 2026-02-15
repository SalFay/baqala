import api from '../axios';

export const storeService = {
  async getStores(filters = {}) {
    const response = await api.get('/stores', { params: filters });
    return response.data.data;
  },

  async getStore(id) {
    const response = await api.get(`/stores/${id}`);
    return response.data.data;
  },

  async createStore(data) {
    const response = await api.post('/stores', data);
    return response.data.data;
  },

  async updateStore(id, data) {
    const response = await api.put(`/stores/${id}`, data);
    return response.data.data;
  },

  async deleteStore(id) {
    await api.delete(`/stores/${id}`);
  },

  async getStoreInventory(storeId, params = {}) {
    const response = await api.get(`/stores/${storeId}/inventory`, { params });
    return response.data;
  },

  async getStoreStats(storeId) {
    const response = await api.get(`/stores/${storeId}/stats`);
    return response.data.data;
  },

  async assignUsers(storeId, userIds) {
    const response = await api.post(`/stores/${storeId}/users`, { user_ids: userIds });
    return response.data.data;
  },

  async toggleStatus(storeId) {
    const response = await api.post(`/stores/${storeId}/toggle-status`);
    return response.data.data;
  },
};
