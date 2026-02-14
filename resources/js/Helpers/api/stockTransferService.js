import axios from 'axios';

export const stockTransferService = {
    getAll: (params = {}) => axios.get('/stock-transfers', { params }),

    getById: (id) => axios.get(`/stock-transfers/${id}`),

    create: (data) => axios.post('/stock-transfers', data),

    update: (id, data) => axios.put(`/stock-transfers/${id}`, data),

    delete: (id) => axios.delete(`/stock-transfers/${id}`),

    ship: (id, items) => axios.post(`/stock-transfers/${id}/ship`, { items }),

    receive: (id, items) => axios.post(`/stock-transfers/${id}/receive`, { items }),

    cancel: (id) => axios.post(`/stock-transfers/${id}/cancel`),

    updateStatus: (id, status, reason = null) =>
        axios.post(`/stock-transfers/${id}/status`, { status, reason }),

    getStatusHistory: (id) => axios.get(`/stock-transfers/${id}/status-history`),

    getAvailableStatuses: (id) => axios.get(`/stock-transfers/${id}/available-statuses`),
};

export default stockTransferService;
