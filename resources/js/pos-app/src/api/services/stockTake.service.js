import api from '../axios';

export const stockTakeService = {
  async getStockTakes(filters = {}) {
    const response = await api.get(route('pos.stock-takes.index'), { params: filters });
    return response.data;
  },

  async getStockTake(id) {
    const response = await api.get(route('pos.stock-takes.show', { stockTake: id }));
    return response.data.data;
  },

  async getSummary() {
    const response = await api.get(route('pos.stock-takes.summary'));
    return response.data.data;
  },

  async createStockTake(data) {
    const response = await api.post(route('pos.stock-takes.store'), data);
    return response.data.data;
  },

  async deleteStockTake(id) {
    await api.delete(route('pos.stock-takes.destroy', { stockTake: id }));
  },

  async startStockTake(id) {
    const response = await api.post(route('pos.stock-takes.start', { stockTake: id }));
    return response.data.data;
  },

  async completeStockTake(id, applyAdjustments = true) {
    const response = await api.post(route('pos.stock-takes.complete', { stockTake: id }), {
      apply_adjustments: applyAdjustments,
    });
    return response.data.data;
  },

  async cancelStockTake(id) {
    const response = await api.post(route('pos.stock-takes.cancel', { stockTake: id }));
    return response.data.data;
  },

  async countItem(stockTakeId, itemId, countedQuantity, notes = null) {
    const response = await api.post(
      route('pos.stock-takes.count-item', { stockTake: stockTakeId, item: itemId }),
      { counted_quantity: countedQuantity, notes }
    );
    return response.data.data;
  },

  async scanBarcode(stockTakeId, barcode, quantity = 1) {
    const response = await api.post(route('pos.stock-takes.scan', { stockTake: stockTakeId }), {
      barcode,
      quantity,
    });
    return response.data.data;
  },
};
