import api from '../axios';

export const categoryService = {
  async getCategories(search) {
    const response = await api.get(route('pos.categories.index'), { params: { search } });
    // API returns { data: [...] }, unwrap to return the array directly
    return response.data?.data || response.data || [];
  },

  async getCategory(id) {
    const response = await api.get(route('pos.categories.index') + `/${id}`);
    return response.data;
  },

  async createCategory(data) {
    const response = await api.post(route('pos.categories.store'), data);
    return response.data;
  },

  async updateCategory(id, data) {
    const response = await api.put(route('pos.categories.update', { category: id }), data);
    return response.data;
  },

  async deleteCategory(id) {
    await api.delete(route('pos.categories.destroy', { category: id }));
  },
};
