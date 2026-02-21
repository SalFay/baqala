const axios = window.axios;

export const productService = {
    getAll: (params = {}) => axios.get('/products', { params }),

    getById: (id) => axios.get(`/products/${id}`),

    create: (data) => axios.post('/products', data),

    update: (id, data) => axios.put(`/products/${id}`, data),

    delete: (id) => axios.delete(`/products/${id}`),

    search: (query) => axios.get('/products/search', { params: { q: query } }),

    getByBarcode: (barcode) => axios.get(`/products/barcode/${barcode}`),

    updateStock: (id, quantity, type, reason) =>
        axios.post(`/products/${id}/stock`, { quantity, type, reason }),

    getLowStock: (params = {}) => axios.get('/products/low-stock', { params }),
};

export default productService;
