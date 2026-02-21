const axios = window.axios;

export const categoryService = {
    getAll: (params = {}) => axios.get('/categories', { params }),

    getById: (id) => axios.get(`/categories/${id}`),

    create: (data) => axios.post('/categories', data),

    update: (id, data) => axios.put(`/categories/${id}`, data),

    delete: (id) => axios.delete(`/categories/${id}`),

    getTree: () => axios.get('/categories/tree'),

    getProducts: (id, params = {}) => axios.get(`/categories/${id}/products`, { params }),
};

export default categoryService;
