import axios from 'axios';

/**
 * Promotion Engine API Service
 * Handles coupon application and discount calculation
 */

// Apply a coupon code to the cart
export const applyCoupon = (code) =>
    axios.post(route('pos.cart.coupon'), { code });

// Remove coupon from cart
export const removeCoupon = () =>
    axios.delete(route('pos.cart.coupon.remove'));

// Get cart with all calculated promotions/discounts
export const getCartWithPromotions = (paymentMethod = null) =>
    axios.get(route('pos.cart.promotions'), {
        params: { payment_method: paymentMethod },
    });

// Get available promotions that could apply to the cart
export const getAvailablePromotions = () =>
    axios.get(route('pos.cart.available-promotions'));

export default {
    applyCoupon,
    removeCoupon,
    getCartWithPromotions,
    getAvailablePromotions,
};
