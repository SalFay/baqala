import api from '../axios';

export const productService = {
  async getProducts(filters = {}) {
    const response = await api.get('/products', { params: filters });
    return response.data;
  },

  async getProduct(id) {
    const response = await api.get(`/products/${id}`);
    return response.data;
  },

  async createProduct(data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value);
      }
    });
    const response = await api.post('/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async updateProduct(id, data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value);
      }
    });
    formData.append('_method', 'PUT');
    const response = await api.post(`/products/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async deleteProduct(id) {
    await api.delete(`/products/${id}`);
  },

  async searchProducts(query) {
    const response = await api.get('/products/search', { params: { q: query } });
    return response.data;
  },

  async findByBarcode(barcode) {
    const response = await api.get(`/products/barcode/${barcode}`);
    return response.data;
  },

  async createVariant(productId, data) {
    const response = await api.post(`/products/${productId}/variants`, data);
    return response.data;
  },

  async updateVariant(productId, variantId, data) {
    const response = await api.put(`/products/${productId}/variants/${variantId}`, data);
    return response.data;
  },
};
