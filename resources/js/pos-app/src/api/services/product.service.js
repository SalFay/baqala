import api from '../axios';

export const productService = {
  async getProducts(filters = {}) {
    const response = await api.get(route('pos.products'), { params: filters });
    return response.data;
  },

  async getProduct(id) {
    const response = await api.get(route('pos.products.show', { product: id }));
    return response.data;
  },

  async createProduct(data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value);
      }
    });
    const response = await api.post(route('pos.products.store'), formData, {
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
    const response = await api.post(route('pos.products.show', { product: id }), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async deleteProduct(id) {
    await api.delete(route('pos.products.destroy', { product: id }));
  },

  async searchProducts(query) {
    const response = await api.get(route('pos.products'), { params: { search: query } });
    return response.data.data;
  },

  async findByBarcode(barcode) {
    const response = await api.get(route('pos.cart.scan'), { params: { barcode } });
    return response.data;
  },
};
