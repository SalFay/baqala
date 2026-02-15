import api from '../axios';
import route from '@pos/utils/route';

export const cartService = {
  async getCart(storeId) {
    const response = await api.get(route('pos.cart'), { params: { store_id: storeId } });
    return response.data;
  },

  async addItem(productId, quantity = 1, variantId, storeId) {
    const response = await api.post(route('pos.cart.add'), {
      product_id: productId,
      variant_id: variantId,
      quantity,
      store_id: storeId,
    });
    return response.data;
  },

  async updateItem(itemId, quantity) {
    const response = await api.put(route('pos.cart.update', { itemId }), { quantity });
    return response.data;
  },

  async removeItem(itemId) {
    const response = await api.delete(route('pos.cart.remove', { itemId }));
    return response.data;
  },

  async clear() {
    await api.delete(route('pos.cart.clear'));
  },

  async setCustomer(customerId) {
    const response = await api.post(route('pos.cart.customer'), { customer_id: customerId });
    return response.data;
  },

  async checkout(paymentMethod, paymentDetails) {
    const response = await api.post(route('pos.checkout'), {
      payment_method: paymentMethod,
      ...paymentDetails,
    });
    return response.data;
  },

  async getHeldCarts(storeId) {
    const response = await api.get(route('pos.cart.held'), { params: { store_id: storeId } });
    return response.data;
  },

  async holdCart(name) {
    await api.post(route('pos.cart.hold'), { name });
  },

  async restoreHeldCart(cartId) {
    const response = await api.post(route('pos.cart.restore', { cartId }));
    return response.data;
  },

  async scanBarcode(barcode, storeId) {
    const response = await api.post(route('pos.cart.scan'), { barcode, store_id: storeId });
    return response.data;
  },
};
