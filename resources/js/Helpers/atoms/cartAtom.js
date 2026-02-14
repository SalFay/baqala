import { atom, selector } from 'recoil';

export const cartAtom = atom({
    key: 'cartState',
    default: {
        items: [],
        customer: null,
        discount: 0,
        discountType: 'fixed',
        loyaltyPointsToRedeem: 0,
        notes: '',
    },
});

export const cartItemsSelector = selector({
    key: 'cartItemsSelector',
    get: ({ get }) => get(cartAtom).items,
});

export const cartSubtotalSelector = selector({
    key: 'cartSubtotalSelector',
    get: ({ get }) => {
        const cart = get(cartAtom);
        return cart.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
    },
});

export const cartTaxSelector = selector({
    key: 'cartTaxSelector',
    get: ({ get }) => {
        const cart = get(cartAtom);
        return cart.items.reduce((sum, item) => sum + (item.taxAmount || 0), 0);
    },
});

export const cartDiscountSelector = selector({
    key: 'cartDiscountSelector',
    get: ({ get }) => {
        const cart = get(cartAtom);
        const subtotal = cart.items.reduce((sum, item) => sum + item.quantity * item.price, 0);

        if (cart.discountType === 'percentage') {
            return (subtotal * cart.discount) / 100;
        }
        return cart.discount;
    },
});

export const cartTotalSelector = selector({
    key: 'cartTotalSelector',
    get: ({ get }) => {
        const subtotal = get(cartSubtotalSelector);
        const tax = get(cartTaxSelector);
        const discount = get(cartDiscountSelector);
        return Math.max(0, subtotal + tax - discount);
    },
});

export const cartItemCountSelector = selector({
    key: 'cartItemCountSelector',
    get: ({ get }) => {
        const cart = get(cartAtom);
        return cart.items.reduce((sum, item) => sum + item.quantity, 0);
    },
});
