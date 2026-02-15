import api from '../axios';

export const loyaltyService = {
  async getOverview() {
    const response = await api.get('/loyalty/overview');
    return response.data.data;
  },

  async getTiers() {
    const response = await api.get('/loyalty/tiers');
    return response.data.data;
  },

  async createTier(data) {
    const response = await api.post('/loyalty/tiers', data);
    return response.data.data;
  },

  async updateTier(id, data) {
    const response = await api.put(`/loyalty/tiers/${id}`, data);
    return response.data.data;
  },

  async deleteTier(id) {
    await api.delete(`/loyalty/tiers/${id}`);
  },

  async getCustomerLoyalty(customerId) {
    const response = await api.get(`/loyalty/customer/${customerId}`);
    return response.data.data;
  },

  async enrollCustomer(customerId, cardNumber) {
    const response = await api.post(`/loyalty/customer/${customerId}/enroll`, { card_number: cardNumber });
    return response.data.data;
  },

  async awardPoints(customerId, points, description) {
    const response = await api.post(`/loyalty/customer/${customerId}/award`, { points, description });
    return response.data.data;
  },

  async redeemPoints(customerId, points, description) {
    const response = await api.post(`/loyalty/customer/${customerId}/redeem`, { points, description });
    return response.data.data;
  },

  async calculatePoints(amount, customerId) {
    const response = await api.post('/loyalty/calculate', { amount, customer_id: customerId });
    return response.data.data;
  },

  async getTransactions(filters = {}) {
    const response = await api.get('/loyalty/transactions', { params: filters });
    return response.data;
  },

  async getPointsValue(points) {
    const response = await api.post('/loyalty/points-value', { points });
    return response.data.data;
  },
};
