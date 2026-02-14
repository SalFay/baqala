import { create } from 'zustand';
import type { Cart, CartSummary, Customer, Product, ProductVariant } from '../types';
import { cartService } from '../api/services/cart.service';

interface CartState {
  cart: Cart | null;
  summary: CartSummary | null;
  heldCarts: Cart[];
  isLoading: boolean;
  error: string | null;

  // Actions
  fetchCart: (storeId?: number) => Promise<void>;
  addItem: (product: Product, quantity?: number, variant?: ProductVariant | null) => Promise<void>;
  updateItemQuantity: (itemId: number, quantity: number) => Promise<void>;
  removeItem: (itemId: number) => Promise<void>;
  clearCart: () => Promise<void>;
  setCustomer: (customer: Customer | null) => Promise<void>;
  applyDiscount: (amount: number, type: 'fixed' | 'percentage', reason?: string) => Promise<void>;
  removeDiscount: () => Promise<void>;
  setLoyaltyPoints: (points: number) => Promise<void>;
  checkout: (paymentType: string, paymentDetails?: Record<string, string>) => Promise<any>;
  holdCart: (name: string) => Promise<void>;
  restoreHeldCart: (cartId: number) => Promise<void>;
  fetchHeldCarts: (storeId?: number) => Promise<void>;
  scanBarcode: (barcode: string, storeId?: number) => Promise<void>;
}

export const useCartStore = create<CartState>((set, get) => ({
  cart: null,
  summary: null,
  heldCarts: [],
  isLoading: false,
  error: null,

  fetchCart: async (storeId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.getCart(storeId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error: any) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  addItem: async (product, quantity = 1, variant = null) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.addItem(product.id, quantity, variant?.id);
      set({ cart: response.cart, summary: response.summary });
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
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
    } catch (error: any) {
      set({ error: error.message });
    } finally {
      set({ isLoading: false });
    }
  },

  fetchHeldCarts: async (storeId) => {
    try {
      const carts = await cartService.getHeldCarts(storeId);
      set({ heldCarts: carts });
    } catch (error: any) {
      console.error('Failed to fetch held carts:', error);
    }
  },

  scanBarcode: async (barcode, storeId) => {
    try {
      set({ isLoading: true, error: null });
      const response = await cartService.scanBarcode(barcode, storeId);
      set({ cart: response.cart, summary: response.summary });
    } catch (error: any) {
      set({ error: error.message });
      throw error;
    } finally {
      set({ isLoading: false });
    }
  },
}));
