import api from '../axios';
import route from '@pos/utils/route';

export const productService = {
  async getProducts(filters = {}) {
    const response = await api.get(route('pos.products'), { params: filters });
    return response.data;
  },

  async searchProducts(query) {
    const response = await api.get(route('pos.products'), { params: { search: query } });
    return response.data.data;
  },
};
