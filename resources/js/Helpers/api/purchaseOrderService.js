const axios = window.axios;

export const purchaseOrderService = {
    getAll: (params = {}) => axios.get('/purchase-orders', { params }),

    getById: (id) => axios.get(`/purchase-orders/${id}`),

    create: (data) => axios.post('/purchase-orders', data),

    update: (id, data) => axios.put(`/purchase-orders/${id}`, data),

    delete: (id) => axios.delete(`/purchase-orders/${id}`),

    submit: (id) => axios.post(`/purchase-orders/${id}/submit`),

    approve: (id) => axios.post(`/purchase-orders/${id}/approve`),

    order: (id) => axios.post(`/purchase-orders/${id}/order`),

    receive: (id, data) => axios.post(`/purchase-orders/${id}/receive`, data),

    cancel: (id) => axios.post(`/purchase-orders/${id}/cancel`),

    updateStatus: (id, status, reason = null) =>
        axios.post(`/purchase-orders/${id}/status`, { status, reason }),

    getStatusHistory: (id) => axios.get(`/purchase-orders/${id}/status-history`),

    getAvailableStatuses: (id) => axios.get(`/purchase-orders/${id}/available-statuses`),
};

export default purchaseOrderService;
