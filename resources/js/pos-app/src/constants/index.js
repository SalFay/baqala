/**
 * Application constants
 * Centralized configuration values to avoid hardcoding
 */

// Tax configuration
export const VAT_RATE = 0.15;
export const VAT_PERCENTAGE = 15;

// Order status colors for consistent UI
export const ORDER_STATUS_COLORS = {
  pending: 'orange',
  processing: 'blue',
  completed: 'green',
  cancelled: 'red',
  refunded: 'purple',
};

// Payment status colors
export const PAYMENT_STATUS_COLORS = {
  pending: 'orange',
  paid: 'green',
  partially_paid: 'blue',
  failed: 'red',
  refunded: 'purple',
};

// Timezone options
export const TIMEZONE_OPTIONS = [
  { value: 'Asia/Riyadh', label: 'Asia/Riyadh (UTC+3)' },
  { value: 'Asia/Dubai', label: 'Asia/Dubai (UTC+4)' },
  { value: 'Asia/Kuwait', label: 'Asia/Kuwait (UTC+3)' },
  { value: 'Asia/Qatar', label: 'Asia/Qatar (UTC+3)' },
  { value: 'UTC', label: 'UTC' },
];

// Currency options
export const CURRENCY_OPTIONS = [
  { value: 'SAR', label: 'SAR - Saudi Riyal' },
  { value: 'AED', label: 'AED - UAE Dirham' },
  { value: 'KWD', label: 'KWD - Kuwaiti Dinar' },
  { value: 'QAR', label: 'QAR - Qatari Riyal' },
  { value: 'USD', label: 'USD - US Dollar' },
];

// Default currency
export const DEFAULT_CURRENCY = 'SAR';
export const DEFAULT_TIMEZONE = 'Asia/Riyadh';

// Pagination defaults
export const DEFAULT_PAGE_SIZE = 20;
export const PAGE_SIZE_OPTIONS = [10, 20, 50, 100];

// Product types
export const PRODUCT_TYPES = {
  SIMPLE: 'simple',
  VARIABLE: 'variable',
};

export const PRODUCT_TYPE_COLORS = {
  simple: 'blue',
  variable: 'purple',
};

// Status colors for general use
export const STATUS_COLORS = {
  active: 'green',
  inactive: 'default',
  draft: 'orange',
  archived: 'red',
};

// Inventory status
export const INVENTORY_STATUS = {
  AVAILABLE: 'Available',
  SOLD: 'Sold',
  VENDOR_RETURNED: 'Returned Vendor',
  ORDER_RETURNED: 'Returned Order',
};

// Keyboard shortcuts
export const KEYBOARD_SHORTCUTS = {
  CHECKOUT: 'F9',
  NEW_ORDER: 'F2',
  SEARCH: 'F3',
  HOLD_ORDER: 'F4',
  CLEAR_CART: 'F5',
};

// API response status
export const API_STATUS = {
  SUCCESS: 'ok',
  ERROR: 'error',
};

// Date formats
export const DATE_FORMATS = {
  DISPLAY: 'MMM D, YYYY',
  DISPLAY_WITH_TIME: 'MMM D, YYYY h:mm A',
  API: 'YYYY-MM-DD',
  API_WITH_TIME: 'YYYY-MM-DD HH:mm:ss',
};

// Low stock threshold
export const DEFAULT_LOW_STOCK_THRESHOLD = 5;

/**
 * Format a value as currency
 * @param {number|string} value - The value to format
 * @param {string} currency - Currency code (defaults to DEFAULT_CURRENCY)
 * @returns {string} Formatted currency string
 */
export const formatCurrency = (value, currency = DEFAULT_CURRENCY) => {
  const num = parseFloat(value || 0);
  return `${num.toFixed(2)} ${currency}`;
};
