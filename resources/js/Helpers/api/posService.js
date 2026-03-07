// Use window.axios which is configured with CSRF token in bootstrap.js
const axios = window.axios;

export const posService = {
    // Products
    getProducts: (params = {}) => axios.get('/pos/products', { params }),

    getCategories: () => axios.get('/pos/categories'),

    // Cart Operations
    getCart: () => axios.get('/pos/cart'),

    addItem: (productId, quantity = 1, variantId = null) =>
        axios.post('/pos/cart/items', { product_id: productId, quantity, variant_id: variantId }),

    updateItem: (itemId, quantity) =>
        axios.put(`/pos/cart/items/${itemId}`, { quantity }),

    removeItem: (itemId) =>
        axios.delete(`/pos/cart/items/${itemId}`),

    clearCart: () =>
        axios.delete('/pos/cart'),

    setCustomer: (customerId) =>
        axios.post('/pos/cart/customer', { customer_id: customerId }),

    removeCustomer: () =>
        axios.post('/pos/cart/customer', { customer_id: null }),

    scanBarcode: (barcode) =>
        axios.post('/pos/cart/scan', { barcode }),

    // Discount
    applyDiscount: (amount, type, reason) =>
        axios.post('/pos/cart/discount', { amount, type, reason }),

    removeDiscount: () =>
        axios.delete('/pos/cart/discount'),

    // Coupon
    applyCoupon: (code) =>
        axios.post('/pos/cart/coupon', { code }),

    removeCoupon: () =>
        axios.delete('/pos/cart/coupon'),

    // Promotions
    getCartWithPromotions: (paymentMethod = null) =>
        axios.get('/pos/cart/promotions', { params: { payment_method: paymentMethod } }),

    getAvailablePromotions: () =>
        axios.get('/pos/cart/available-promotions'),

    // Hold Cart
    holdCart: (name) =>
        axios.post('/pos/cart/hold', { name }),

    getHeldCarts: () =>
        axios.get('/pos/cart/hold'),

    restoreHeldCart: (cartId) =>
        axios.post(`/pos/cart/hold/${cartId}/restore`),

    deleteHeldCart: (cartId) =>
        axios.delete(`/pos/cart/hold/${cartId}`),

    // Checkout
    checkout: (data) =>
        axios.post('/pos/cart/checkout', data),

    // Customers
    searchCustomers: (query) =>
        axios.get('/pos/customers/search', { params: { q: query } }),

    quickCreateCustomer: (data) =>
        axios.post('/pos/customers', data),

    getCustomerLoyalty: (customerId) =>
        axios.get(`/pos/customers/${customerId}/loyalty`),

    // Orders
    getOrders: (params = {}) =>
        axios.get('/pos/orders', { params }),

    getOrderDetail: (orderId) =>
        axios.get(`/pos/orders/${orderId}`),

    getOrderReceipt: (orderId) =>
        axios.get(`/pos/orders/${orderId}/receipt`),

    cancelOrder: (orderId, reason) =>
        axios.post(`/pos/orders/${orderId}/cancel`, { reason }),

    getTodayOrders: () =>
        axios.get('/pos/today-orders'),

    getRecentOrders: (limit = 10) =>
        axios.get('/pos/recent-orders', { params: { limit } }),

    // Returns
    searchOrdersForReturn: (query) =>
        axios.get('/pos/orders/search', { params: { q: query } }),

    processReturn: (orderId, data) =>
        axios.post(`/pos/orders/${orderId}/return`, data),

    processExchange: (orderId, data) =>
        axios.post(`/pos/orders/${orderId}/exchange`, data),
};

export default posService;
