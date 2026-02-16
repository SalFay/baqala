import api from '../axios';

export const vendorService = {
  async getVendors(filters = {}) {
    const response = await api.get(route('pos.vendors.index'), { params: filters });
    return response.data;
  },

  async getVendor(id) {
    const response = await api.get(route('pos.vendors.show', { vendor: id }));
    return response.data.data;
  },

  async createVendor(data) {
    const response = await api.post(route('pos.vendors.store'), data);
    return response.data.data;
  },

  async updateVendor(id, data) {
    const response = await api.put(route('pos.vendors.update', { vendor: id }), data);
    return response.data.data;
  },

  async deleteVendor(id) {
    await api.delete(route('pos.vendors.destroy', { vendor: id }));
  },

  async searchVendors(query) {
    const response = await api.get(route('pos.vendors.index'), { params: { search: query } });
    return response.data.data;
  },

  async getVendorStatement(vendorId, params = {}) {
    const response = await api.get(route('pos.statements.vendor', { vendor: vendorId }), { params });
    return response.data;
  },
};
