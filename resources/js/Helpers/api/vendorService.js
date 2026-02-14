import axios from 'axios';

export const vendorService = {
    getAll: (params = {}) => axios.get('/vendors', { params }),

    getById: (id) => axios.get(`/vendors/${id}`),

    create: (data) => axios.post('/vendors', data),

    update: (id, data) => axios.put(`/vendors/${id}`, data),

    delete: (id) => axios.delete(`/vendors/${id}`),

    search: (query) => axios.get('/vendors/search', { params: { q: query } }),

    getPurchaseOrders: (id, params = {}) => axios.get(`/vendors/${id}/purchase-orders`, { params }),
};

export default vendorService;
