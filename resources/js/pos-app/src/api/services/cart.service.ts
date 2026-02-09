import api from '../axios';
import type { Cart, CartSummary, Order } from '../../types';

interface CartResponse {
  cart: Cart;
  summary: CartSummary;
}

interface CheckoutResponse {
  order: Order;
  receipt: any;
}

export const cartService = {
  async getCart(storeId?: number): Promise<CartResponse> {
    const response = await api.get('/cart', { params: { store_id: storeId } });
    return response.data;
  },

  async addItem(productId: number, quantity: number = 1, variantId?: number, storeId?: number): Promise<CartResponse> {
    const response = await api.post('/cart/items', {
      product_id: productId,
      variant_id: variantId,
      quantity,
      store_id: storeId,
    });
    return response.data;
  },

  async updateItem(itemId: number, quantity: number): Promise<CartResponse> {
    const response = await api.put(`/cart/items/${itemId}`, { quantity });
    return response.data;
  },

  async removeItem(itemId: number): Promise<CartResponse> {
    const response = await api.delete(`/cart/items/${itemId}`);
    return response.data;
  },

  async clear(): Promise<void> {
    await api.delete('/cart');
  },

  async setCustomer(customerId: number | null): Promise<CartResponse> {
    const response = await api.post('/cart/customer', { customer_id: customerId });
    return response.data;
  },

  async applyDiscount(amount: number, type: 'fixed' | 'percentage', reason?: string): Promise<CartResponse> {
    const response = await api.post('/cart/discount', { amount, type, reason });
    return response.data;
  },

  async removeDiscount(): Promise<CartResponse> {
    const response = await api.delete('/cart/discount');
    return response.data;
  },

  async setLoyaltyPoints(points: number): Promise<CartResponse> {
    const response = await api.post('/cart/loyalty-points', { points });
    return response.data;
  },

  async checkout(paymentType: string, paymentDetails?: Record<string, string>): Promise<CheckoutResponse> {
    const response = await api.post('/cart/checkout', {
      payment_type: paymentType,
      ...paymentDetails,
    });
    return response.data;
  },

  async getHeldCarts(storeId?: number): Promise<Cart[]> {
    const response = await api.get('/cart/hold', { params: { store_id: storeId } });
    return response.data;
  },

  async holdCart(name: string): Promise<void> {
    await api.post('/cart/hold', { name });
  },

  async restoreHeldCart(cartId: number): Promise<CartResponse> {
    const response = await api.post(`/cart/hold/${cartId}/restore`);
    return response.data;
  },

  async scanBarcode(barcode: string, storeId?: number): Promise<CartResponse> {
    const response = await api.post('/cart/scan', { barcode, store_id: storeId });
    return response.data;
  },
};
