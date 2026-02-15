import api from '../axios';

export const expenseService = {
  async getExpenses(filters = {}) {
    const response = await api.get('/pos/expenses', { params: filters });
    return response.data;
  },

  async getExpense(id) {
    const response = await api.get(`/pos/expenses/${id}`);
    return response.data.data;
  },

  async getSummary() {
    const response = await api.get('/pos/expenses/summary');
    return response.data.data;
  },

  async getCategories() {
    const response = await api.get('/pos/expenses/categories');
    return response.data.data;
  },

  async getCategoriesFlat() {
    const response = await api.get('/pos/expenses/categories/flat');
    return response.data.data;
  },

  async getVendors() {
    const response = await api.get('/pos/expenses/vendors');
    return response.data.data;
  },

  async createExpense(data) {
    const response = await api.post('/pos/expenses', data);
    return response.data.data;
  },

  async updateExpense(id, data) {
    const response = await api.put(`/pos/expenses/${id}`, data);
    return response.data.data;
  },

  async deleteExpense(id) {
    await api.delete(`/pos/expenses/${id}`);
  },

  async approveExpense(id) {
    const response = await api.post(`/pos/expenses/${id}/approve`);
    return response.data.data;
  },

  async rejectExpense(id, reason) {
    const response = await api.post(`/pos/expenses/${id}/reject`, { reason });
    return response.data.data;
  },

  async markPaid(id) {
    const response = await api.post(`/pos/expenses/${id}/paid`);
    return response.data.data;
  },

  async uploadReceipt(id, file) {
    const formData = new FormData();
    formData.append('receipt', file);
    const response = await api.post(`/pos/expenses/${id}/receipt`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data.data;
  },
};
