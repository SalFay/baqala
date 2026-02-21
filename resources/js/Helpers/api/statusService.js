const axios = window.axios;

export const statusService = {
    getCategories: () => axios.get('/statuses/categories'),

    getByCategory: (category) => axios.get(`/statuses/${category}`),

    getDefault: (category) => axios.get(`/statuses/${category}/default`),
};

export default statusService;
