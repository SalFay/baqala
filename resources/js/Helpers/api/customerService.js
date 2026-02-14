import axios from 'axios';

export const customerService = {
    getAll: (params = {}) => axios.get('/customers', { params }),

    getById: (id) => axios.get(`/customers/${id}`),

    create: (data) => axios.post('/customers', data),

    update: (id, data) => axios.put(`/customers/${id}`, data),

    delete: (id) => axios.delete(`/customers/${id}`),

    search: (query) => axios.get('/customers/search', { params: { q: query } }),

    getOrders: (id, params = {}) => axios.get(`/customers/${id}/orders`, { params }),

    getLoyaltyPoints: (id) => axios.get(`/customers/${id}/loyalty-points`),
};

export default customerService;
