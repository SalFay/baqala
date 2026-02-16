import { create } from 'zustand';
import { cartService } from '../api/services/cart.service';

export const useCartStore = create((set, get) => ({
  cart: null,
  summary: null,
  heldCarts: [],
  isLoading: false,
  error: null,
  heldCartsLoading: false,
  heldCartsError: null,

  fetchCart: async (storeId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.getCart(storeId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  addItem: async (product, quantity = 1, variant = null, storeId = null) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.addItem(product.id, quantity, variant?.id, storeId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
      throw error;
    } finally {
      set({ isLoading: false });
    }
  },

  updateItemQuantity: async (itemId, quantity) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.updateItem(itemId, quantity);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  removeItem: async (itemId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.removeItem(itemId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  clearCart: async () => {
    try {
      set({ isLoading: true, error: null });
      await cartService.clear();
      set({ cart: null, summary: null });
      await get().fetchCart();
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  setCustomer: async (customer) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.setCustomer(customer?.id || null);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  applyDiscount: async (amount, type, reason) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.applyDiscount(amount, type, reason);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  removeDiscount: async () => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.removeDiscount();
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  setLoyaltyPoints: async (points) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.setLoyaltyPoints(points);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
      throw error;
    } finally {
      set({ isLoading: false });
    }
  },

  checkout: async (paymentType, paymentDetails) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.checkout(paymentType, paymentDetails);
      set({ cart: null, summary: null });
      await get().fetchCart();
      return response;
    } catch (error) {
      set({ error: error.message });
      throw error;
    } finally {
      set({ isLoading: false });
    }
  },

  holdCart: async (name) => {
    try {
      set({ isLoading: true, error: null });
      await cartService.holdCart(name);
      set({ cart: null, summary: null });
      await get().fetchCart();
      await get().fetchHeldCarts();
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  restoreHeldCart: async (cartId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.restoreHeldCart(cartId);
      set({ cart: response.cart, summary: response.summary });
      await get().fetchHeldCarts();
    } catch (error) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  fetchHeldCarts: async (storeId) => {
    try {
      set({ heldCartsLoading: true, heldCartsError: null });
      const carts = await cartService.getHeldCarts(storeId);
      set({ heldCarts: carts });
    } catch (error) {
      set({ heldCartsError: error.message });
    } finally {
      set({ heldCartsLoading: false });
    }
  },

  scanBarcode: async (barcode, storeId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.scanBarcode(barcode, storeId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error) {
      set({ error: error.message });
      throw error;
    } finally {
      set({ isLoading: false });
    }
  },

  // Clear error state
  clearError: () => set({ error: null, heldCartsError: null }),
}));
