import api from '../axios';

export const cartService = {
  async getCart(storeId) {
    const response = await api.get('/cart', { params: { store_id: storeId } });
    return response.data;
  },

  async addItem(productId, quantity = 1, variantId, storeId) {
    const response = await api.post('/cart/items', {
      product_id: productId,
      variant_id: variantId,
      quantity,
      store_id: storeId,
    });
    return response.data;
  },

  async updateItem(itemId, quantity) {
    const response = await api.put(`/cart/items/${itemId}`, { quantity });
    return response.data;
  },

  async removeItem(itemId) {
    const response = await api.delete(`/cart/items/${itemId}`);
    return response.data;
  },

  async clear() {
    await api.delete('/cart');
  },

  async setCustomer(customerId) {
    const response = await api.post('/cart/customer', { customer_id: customerId });
    return response.data;
  },

  async applyDiscount(amount, type, reason) {
    const response = await api.post('/cart/discount', { amount, type, reason });
    return response.data;
  },

  async removeDiscount() {
    const response = await api.delete('/cart/discount');
    return response.data;
  },

  async setLoyaltyPoints(points) {
    const response = await api.post('/cart/loyalty-points', { points });
    return response.data;
  },

  async checkout(paymentType, paymentDetails) {
    const response = await api.post('/cart/checkout', {
      payment_type: paymentType,
      ...paymentDetails,
    });
    return response.data;
  },

  async getHeldCarts(storeId) {
    const response = await api.get('/cart/hold', { params: { store_id: storeId } });
    return response.data;
  },

  async holdCart(name) {
    await api.post('/cart/hold', { name });
  },

  async restoreHeldCart(cartId) {
    const response = await api.post(`/cart/hold/${cartId}/restore`);
    return response.data;
  },

  async scanBarcode(barcode, storeId) {
    const response = await api.post('/cart/scan', { barcode, store_id: storeId });
    return response.data;
  },
};
