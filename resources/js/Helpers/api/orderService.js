import axios from 'axios';

export const orderService = {
    getAll: (params = {}) => axios.get('/orders', { params }),

    getById: (id) => axios.get(`/orders/${id}`),

    getTodayOrders: (params = {}) => axios.get('/orders/today', { params }),

    getRecentOrders: (params = {}) => axios.get('/orders/recent', { params }),

    getReceipt: (id) => axios.get(`/orders/${id}/receipt`),

    cancel: (id, reason) => axios.post(`/orders/${id}/cancel`, { reason }),

    updateStatus: (id, status, reason = null) =>
        axios.post(`/orders/${id}/status`, { status, reason }),

    getStatusHistory: (id) => axios.get(`/orders/${id}/status-history`),

    getActivityLog: (id) => axios.get(`/orders/${id}/activity-log`),

    getAvailableStatuses: (id) => axios.get(`/orders/${id}/available-statuses`),
};

export default orderService;
