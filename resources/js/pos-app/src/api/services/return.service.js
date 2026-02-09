import api from '../axios';

export const returnService = {
  getReturns: async (filters = {}) => {
    const { data } = await api.get('/returns', { params: filters });
    return data;
  },

  getReturn: async (id) => {
    const { data } = await api.get(`/returns/${id}`);
    return data.data;
  },

  getReturnableItems: async (orderId) => {
    const { data } = await api.get(`/returns/order/${orderId}`);
    return data.data;
  },

  createReturn: async (returnData) => {
    const { data } = await api.post('/returns', returnData);
    return data.data;
  },

  approveReturn: async (id) => {
    const { data } = await api.post(`/returns/${id}/approve`);
    return data.data;
  },

  rejectReturn: async (id, reason) => {
    const { data } = await api.post(`/returns/${id}/reject`, { reason });
    return data.data;
  },

  processReturn: async (id) => {
    const { data } = await api.post(`/returns/${id}/process`);
    return data.data;
  },

  getReturnReasons: async () => {
    const { data } = await api.get('/return-reasons');
    return data.data;
  },
};
