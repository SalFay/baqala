import api from '../axios';

export const roleService = {
  async getRoles() {
    const response = await api.get('/roles');
    return response.data.data;
  },

  async getRole(id) {
    const response = await api.get(`/roles/${id}`);
    return response.data.data;
  },

  async createRole(data) {
    const response = await api.post('/roles', data);
    return response.data.data;
  },

  async updateRole(id, data) {
    const response = await api.put(`/roles/${id}`, data);
    return response.data.data;
  },

  async deleteRole(id) {
    await api.delete(`/roles/${id}`);
  },

  async duplicateRole(id) {
    const response = await api.post(`/roles/${id}/duplicate`);
    return response.data.data;
  },

  async getPermissions() {
    const response = await api.get('/roles/permissions');
    return response.data.data;
  },

  async getPermissionSets() {
    const response = await api.get('/roles/permission-sets');
    return response.data.data;
  },

  async createPermissionSet(data) {
    const response = await api.post('/roles/permission-sets', data);
    return response.data.data;
  },

  async updatePermissionSet(id, data) {
    const response = await api.put(`/roles/permission-sets/${id}`, data);
    return response.data.data;
  },

  async deletePermissionSet(id) {
    await api.delete(`/roles/permission-sets/${id}`);
  },
};
