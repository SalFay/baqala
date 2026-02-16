import api from '../axios';

export const returnService = {
  getReturns: async (filters = {}) => {
    const { data } = await api.get(route('pos.returns.index'), { params: filters });
    return data;
  },

  getReturn: async (id) => {
    const { data } = await api.get(route('pos.returns.show', { return: id }));
    return data.data;
  },

  getReturnableItems: async (orderId) => {
    const { data } = await api.get(route('pos.returns.returnable', { order: orderId }));
    return data.data;
  },

  createReturn: async (returnData) => {
    const { data } = await api.post(route('pos.returns.store'), returnData);
    return data.data;
  },

  approveReturn: async (id) => {
    const { data } = await api.post(route('pos.returns.approve', { return: id }));
    return data.data;
  },

  rejectReturn: async (id, reason) => {
    const { data } = await api.post(route('pos.returns.reject', { return: id }), { reason });
    return data.data;
  },

  processReturn: async (id) => {
    const { data } = await api.post(route('pos.returns.process', { return: id }));
    return data.data;
  },

  getReturnReasons: async () => {
    const { data } = await api.get(route('pos.returns.reasons'));
    return data.data;
  },
};
