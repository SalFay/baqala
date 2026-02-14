/**
 * Format currency value
 * Note: When used as Ant Design table render, second param may be record object
 */
export const formatCurrency = (value, currency = 'SAR') => {
    // If currency is not a string (e.g., when used as table render callback), use default
    const currencyCode = typeof currency === 'string' ? currency : 'SAR';
    try {
        const formatter = new Intl.NumberFormat('en-SA', {
            style: 'currency',
            currency: currencyCode,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
        return formatter.format(value || 0);
    } catch (e) {
        // Fallback if formatting fails
        return `SAR ${(value || 0).toFixed(2)}`;
    }
};

/**
 * Format date
 */
export const formatDate = (date, options = {}) => {
    if (!date) return '';

    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...options,
    };

    return new Date(date).toLocaleDateString('en-US', defaultOptions);
};

/**
 * Format datetime
 */
export const formatDateTime = (date) => {
    if (!date) return '';

    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

/**
 * Format relative time (e.g., "2 hours ago")
 */
export const formatRelativeTime = (date) => {
    if (!date) return '';

    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
    const now = new Date();
    const then = new Date(date);
    const diffInSeconds = (then - now) / 1000;

    if (Math.abs(diffInSeconds) < 60) {
        return rtf.format(Math.round(diffInSeconds), 'second');
    }
    if (Math.abs(diffInSeconds) < 3600) {
        return rtf.format(Math.round(diffInSeconds / 60), 'minute');
    }
    if (Math.abs(diffInSeconds) < 86400) {
        return rtf.format(Math.round(diffInSeconds / 3600), 'hour');
    }
    return rtf.format(Math.round(diffInSeconds / 86400), 'day');
};

/**
 * Format number with thousand separators
 * Note: When used as Ant Design table render, second param may be record object
 */
export const formatNumber = (value, decimals = 0) => {
    // If decimals is not a number (e.g., when used as table render callback), use default
    const decimalPlaces = typeof decimals === 'number' ? decimals : 0;
    try {
        const formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
        });
        return formatter.format(value || 0);
    } catch (e) {
        return String(value || 0);
    }
};

/**
 * Format percentage
 */
export const formatPercent = (value, decimals = 1) => {
    return `${formatNumber(value, decimals)}%`;
};

/**
 * Truncate string
 */
export const truncate = (str, length = 30) => {
    if (!str) return '';
    if (str.length <= length) return str;
    return str.substring(0, length) + '...';
};
