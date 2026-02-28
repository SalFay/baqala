// Global settings cache - updated by useInitSettings hook
let _appSettings = {
    currency: 'SAR',
    currency_symbol: '',
    currency_position: 'before',
}

/**
 * Update the global settings cache
 * Called by PersistentLayout when settings change
 */
export const updateFormatterSettings = (settings) => {
    if (settings) {
        _appSettings = { ..._appSettings, ...settings }
    }
}

/**
 * Get current settings
 */
export const getFormatterSettings = () => _appSettings

/**
 * Format currency value using app settings
 * Note: When used as Ant Design table render, second param may be record object
 */
export const formatCurrency = (value, currencyOverride = null) => {
    // If currencyOverride is not a string (e.g., when used as table render callback), ignore it
    const currency = typeof currencyOverride === 'string' ? currencyOverride : _appSettings.currency
    const symbol = _appSettings.currency_symbol
    const position = _appSettings.currency_position

    try {
        // Format number with Intl
        const formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
        const formattedNumber = formatter.format(value || 0)

        // If we have a custom symbol, use it; otherwise use Intl currency
        if (symbol) {
            return position === 'after'
                ? `${formattedNumber} ${symbol}`
                : `${symbol} ${formattedNumber}`
        }

        // Use Intl currency formatting
        const currencyFormatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
        return currencyFormatter.format(value || 0)
    } catch (e) {
        // Fallback if formatting fails
        return `${currency} ${(value || 0).toFixed(2)}`
    }
}

/**
 * Format currency value without symbol (just the number)
 */
export const formatAmount = (value) => {
    try {
        const formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
        return formatter.format(value || 0)
    } catch (e) {
        return (value || 0).toFixed(2)
    }
}

/**
 * Get currency code
 */
export const getCurrency = () => _appSettings.currency

/**
 * Format date
 */
export const formatDate = (date, options = {}) => {
    if (!date) return ''

    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...options,
    }

    return new Date(date).toLocaleDateString('en-US', defaultOptions)
}

/**
 * Format datetime
 */
export const formatDateTime = (date) => {
    if (!date) return ''

    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Format datetime for receipt (compact)
 */
export const formatReceiptDateTime = (date) => {
    if (!date) return ''

    return new Date(date).toLocaleString('en-US', {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    })
}

/**
 * Format relative time (e.g., "2 hours ago")
 */
export const formatRelativeTime = (date) => {
    if (!date) return ''

    const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' })
    const now = new Date()
    const then = new Date(date)
    const diffInSeconds = (then - now) / 1000

    if (Math.abs(diffInSeconds) < 60) {
        return rtf.format(Math.round(diffInSeconds), 'second')
    }
    if (Math.abs(diffInSeconds) < 3600) {
        return rtf.format(Math.round(diffInSeconds / 60), 'minute')
    }
    if (Math.abs(diffInSeconds) < 86400) {
        return rtf.format(Math.round(diffInSeconds / 3600), 'hour')
    }
    return rtf.format(Math.round(diffInSeconds / 86400), 'day')
}

/**
 * Format number with thousand separators
 * Note: When used as Ant Design table render, second param may be record object
 */
export const formatNumber = (value, decimals = 0) => {
    // If decimals is not a number (e.g., when used as table render callback), use default
    const decimalPlaces = typeof decimals === 'number' ? decimals : 0
    try {
        const formatter = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
        })
        return formatter.format(value || 0)
    } catch (e) {
        return String(value || 0)
    }
}

/**
 * Format percentage
 */
export const formatPercent = (value, decimals = 1) => {
    return `${formatNumber(value, decimals)}%`
}

/**
 * Truncate string
 */
export const truncate = (str, length = 30) => {
    if (!str) return ''
    if (str.length <= length) return str
    return str.substring(0, length) + '...'
}

/**
 * Format phone number
 */
export const formatPhone = (phone) => {
    if (!phone) return ''
    // Remove non-digits
    const digits = phone.replace(/\D/g, '')
    // Format based on length
    if (digits.length === 10) {
        return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`
    }
    if (digits.length === 12 && digits.startsWith('966')) {
        return `+${digits.slice(0, 3)} ${digits.slice(3, 5)} ${digits.slice(5, 8)} ${digits.slice(8)}`
    }
    return phone
}
