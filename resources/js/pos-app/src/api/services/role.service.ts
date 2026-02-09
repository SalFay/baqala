import api from '../axios';
import type { Role } from '../../types';

export interface PermissionGroup {
  title: string;
  description: string;
  permissions: Record<string, { title: string; description: string }>;
}

export interface PermissionSet {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  permissions: string[];
  created_at: string;
}

export const roleService = {
  async getRoles(): Promise<Role[]> {
    const response = await api.get('/roles');
    return response.data.data;
  },

  async getRole(id: number): Promise<Role & { users_count: number }> {
    const response = await api.get(`/roles/${id}`);
    return response.data.data;
  },

  async createRole(data: Partial<Role> & { permission_set_ids?: number[] }): Promise<Role> {
    const response = await api.post('/roles', data);
    return response.data.data;
  },

  async updateRole(id: number, data: Partial<Role> & { permission_set_ids?: number[] }): Promise<Role> {
    const response = await api.put(`/roles/${id}`, data);
    return response.data.data;
  },

  async deleteRole(id: number): Promise<void> {
    await api.delete(`/roles/${id}`);
  },

  async duplicateRole(id: number): Promise<Role> {
    const response = await api.post(`/roles/${id}/duplicate`);
    return response.data.data;
  },

  async getPermissions(): Promise<Record<string, PermissionGroup>> {
    const response = await api.get('/roles/permissions');
    return response.data.data;
  },

  async getPermissionSets(): Promise<PermissionSet[]> {
    const response = await api.get('/roles/permission-sets');
    return response.data.data;
  },

  async createPermissionSet(data: Partial<PermissionSet>): Promise<PermissionSet> {
    const response = await api.post('/roles/permission-sets', data);
    return response.data.data;
  },

  async updatePermissionSet(id: number, data: Partial<PermissionSet>): Promise<PermissionSet> {
    const response = await api.put(`/roles/permission-sets/${id}`, data);
    return response.data.data;
  },

  async deletePermissionSet(id: number): Promise<void> {
    await api.delete(`/roles/permission-sets/${id}`);
  },
};
