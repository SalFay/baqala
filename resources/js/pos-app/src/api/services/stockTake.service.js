import api from '../axios';

export const stockTakeService = {
  async getStockTakes(filters = {}) {
    const response = await api.get('/pos/stock-takes', { params: filters });
    return response.data;
  },

  async getStockTake(id) {
    const response = await api.get(`/pos/stock-takes/${id}`);
    return response.data.data;
  },

  async getSummary() {
    const response = await api.get('/pos/stock-takes/summary');
    return response.data.data;
  },

  async createStockTake(data) {
    const response = await api.post('/pos/stock-takes', data);
    return response.data.data;
  },

  async deleteStockTake(id) {
    await api.delete(`/pos/stock-takes/${id}`);
  },

  async startStockTake(id) {
    const response = await api.post(`/pos/stock-takes/${id}/start`);
    return response.data.data;
  },

  async completeStockTake(id, applyAdjustments = true) {
    const response = await api.post(`/pos/stock-takes/${id}/complete`, {
      apply_adjustments: applyAdjustments,
    });
    return response.data.data;
  },

  async cancelStockTake(id) {
    const response = await api.post(`/pos/stock-takes/${id}/cancel`);
    return response.data.data;
  },

  async countItem(stockTakeId, itemId, countedQuantity, notes = null) {
    const response = await api.post(
      `/pos/stock-takes/${stockTakeId}/items/${itemId}/count`,
      { counted_quantity: countedQuantity, notes }
    );
    return response.data.data;
  },

  async scanBarcode(stockTakeId, barcode, quantity = 1) {
    const response = await api.post(`/pos/stock-takes/${stockTakeId}/scan`, {
      barcode,
      quantity,
    });
    return response.data.data;
  },
};
