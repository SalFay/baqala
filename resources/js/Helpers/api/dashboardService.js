import axios from 'axios';

export const dashboardService = {
    getStats: () => axios.get('/dashboard/stats'),

    getSalesChart: (params = {}) => axios.get('/dashboard/sales-chart', { params }),

    getRecentOrders: (params = {}) => axios.get('/dashboard/recent-orders', { params }),

    getLowStockProducts: (params = {}) => axios.get('/dashboard/low-stock', { params }),

    getTopProducts: (params = {}) => axios.get('/dashboard/top-products', { params }),
};

export default dashboardService;
