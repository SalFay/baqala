import api from '../axios';

export const productAttributeService = {
  async getAttributes(params = {}) {
    const response = await api.get('/product-attributes', { params });
    return response.data.data;
  },

  async getAttribute(id) {
    const response = await api.get(`/product-attributes/${id}`);
    return response.data.data;
  },

  async createAttribute(data) {
    const response = await api.post('/product-attributes', data);
    return response.data.data;
  },

  async updateAttribute(id, data) {
    const response = await api.put(`/product-attributes/${id}`, data);
    return response.data.data;
  },

  async deleteAttribute(id) {
    await api.delete(`/product-attributes/${id}`);
  },

  async reorderAttributes(order) {
    await api.post('/product-attributes/reorder', { order });
  },

  async createValue(attributeId, data) {
    const response = await api.post(`/product-attributes/${attributeId}/values`, data);
    return response.data.data;
  },

  async updateValue(valueId, data) {
    const response = await api.put(`/product-attributes/values/${valueId}`, data);
    return response.data.data;
  },

  async deleteValue(valueId) {
    await api.delete(`/product-attributes/values/${valueId}`);
  },

  async reorderValues(attributeId, order) {
    await api.post(`/product-attributes/${attributeId}/values/reorder`, { order });
  },
};
