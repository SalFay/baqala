import api from '../axios';

export const inventoryService = {
  getInventory: async (filters = {}) => {
    const { data } = await api.get(route('inventory.index'), { params: filters });
    return data;
  },

  getLowStock: async () => {
    const { data } = await api.get(route('inventory.low-stock'));
    return data.data;
  },

  getMovements: async (filters = {}) => {
    const { data } = await api.get(route('inventory.movements'), { params: filters });
    return data;
  },

  adjustStock: async (adjustment) => {
    const { data } = await api.post(route('inventory.adjust'), adjustment);
    return data.data;
  },

  countStock: async (countData) => {
    const { data } = await api.post(route('inventory.index') + '/count', countData);
    return data.data;
  },

  getStores: async () => {
    const { data } = await api.get(route('settings.stores'));
    return data.data;
  },
};
