import api from '../axios';

export const reportService = {
  getSalesReport: async (filters) => {
    const { data } = await api.get(route('reports.sales'), { params: filters });
    return data.data;
  },

  getSalesByProduct: async (filters) => {
    const { data } = await api.get(route('reports.sales') + '/by-product', { params: filters });
    return data.data;
  },

  getSalesByCategory: async (filters) => {
    const { data } = await api.get(route('reports.sales') + '/by-category', { params: filters });
    return data.data;
  },

  getInventoryReport: async (storeId) => {
    const { data } = await api.get(route('reports.inventory'), { params: { store_id: storeId } });
    return data.data;
  },

  getProfitLossReport: async (filters) => {
    const { data } = await api.get(route('reports.index') + '/profit-loss', { params: filters });
    return data.data;
  },

  exportReport: async (type, format, filters) => {
    const { data } = await api.post(
      route('reports.export') + `/${type}`,
      { format, ...filters },
      { responseType: 'blob' }
    );
    return data;
  },
};
