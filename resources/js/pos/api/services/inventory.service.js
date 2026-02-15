import api from '../axios';

export const inventoryService = {
  getInventory: async (filters = {}) => {
    const { data } = await api.get('/inventory', { params: filters });
    return data;
  },

  getLowStock: async () => {
    const { data } = await api.get('/inventory/low-stock');
    return data.data;
  },

  getMovements: async (filters = {}) => {
    const { data } = await api.get('/inventory/movements', { params: filters });
    return data;
  },

  adjustStock: async (adjustment) => {
    const { data } = await api.post('/inventory/adjust', adjustment);
    return data.data;
  },

  countStock: async (countData) => {
    const { data } = await api.post('/inventory/count', countData);
    return data.data;
  },

  getStores: async () => {
    const { data } = await api.get('/stores');
    return data.data;
  },
};
