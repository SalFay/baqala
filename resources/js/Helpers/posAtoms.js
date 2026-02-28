import { atom, selector } from 'recoil'

// Cart atom - holds the full cart data from API
export const cartAtom = atom({
  key: 'cartAtom',
  default: {
    id: null,
    items: [],
    customer: null,
    subtotal: 0,
    tax_amount: 0,
    tax_rate: 0,
    discount: 0,
    discount_type: null,
    discount_reason: null,
    total: 0,
    loyalty_points_to_redeem: 0,
    loyalty_discount: 0,
    notes: null,
  },
})

// Cart summary selector - derived from cartAtom
export const cartSummarySelector = selector({
  key: 'cartSummarySelector',
  get: ({ get }) => {
    const cart = get(cartAtom)
    return {
      subtotal: cart.subtotal || 0,
      tax: cart.tax_amount || 0,
      tax_rate: cart.tax_rate || 0,
      discount: cart.discount || 0,
      discount_value: cart.discount_value || 0,
      discount_type: cart.discount_type || null,
      discount_reason: cart.discount_reason || null,
      loyaltyDiscount: cart.loyalty_discount || 0,
      total: cart.total || 0,
      itemCount: cart.items?.reduce((sum, item) => sum + item.quantity, 0) || 0,
    }
  },
})

// Cart item count selector
export const cartItemCountSelector = selector({
  key: 'cartItemCountSelector',
  get: ({ get }) => {
    const cart = get(cartAtom)
    return cart.items?.reduce((sum, item) => sum + item.quantity, 0) || 0
  },
})

// POS UI state atom
export const posUIAtom = atom({
  key: 'posUIAtom',
  default: {
    selectedCategory: null,
    searchQuery: '',
    isCheckoutModalOpen: false,
    isCustomerModalOpen: false,
    isHeldCartsModalOpen: false,
    isHoldCartModalOpen: false,
    isDiscountModalOpen: false,
    isReceiptModalOpen: false,
    lastCompletedOrder: null,
    barcodeInput: '',
  },
})

// Held carts atom
export const heldCartsAtom = atom({
  key: 'heldCartsAtom',
  default: [],
})

// Active payment method atom
export const paymentMethodAtom = atom({
  key: 'paymentMethodAtom',
  default: 'cash',
})

// Checkout state atom
export const checkoutStateAtom = atom({
  key: 'checkoutStateAtom',
  default: {
    paymentMethod: 'cash',
    amountTendered: 0,
    change: 0,
    notes: '',
    isProcessing: false,
  },
})
