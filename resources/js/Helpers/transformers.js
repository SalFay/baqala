/**
 * Data Transformers and Utilities
 *
 * Helper functions for transforming data between different formats.
 */

/**
 * Convert hex color to RGBA (SparkCRM Pattern)
 * @param {string|null} input - Color value (hex, rgb, rgba, hsl, css var)
 * @param {number} alpha - Alpha value (0-1)
 * @returns {string} RGBA color string
 */
export const hexToRgba = (input, alpha = 1) => {
    if (input == null) return `rgba(0,0,0,${alpha})`;

    let v = typeof input === 'string' ? input.trim() : String(input);

    // Already a color format we shouldn't transform
    if (
        v.startsWith('rgba(') ||
        v.startsWith('rgb(') ||
        v.startsWith('hsl(') ||
        v.startsWith('var(')
    ) {
        return v;
    }

    // Normalize hex
    let hex = v.replace(/^#/, '');
    if (![3, 4, 6, 8].includes(hex.length)) {
        // Probably a named color or invalid; return as-is
        return v;
    }

    if (hex.length === 3 || hex.length === 4) {
        hex = hex.split('').map((c) => c + c).join('');
    }

    const r = parseInt(hex.slice(0, 2), 16);
    const g = parseInt(hex.slice(2, 4), 16);
    const b = parseInt(hex.slice(4, 6), 16);

    if (![r, g, b].every(Number.isFinite)) {
        return `rgba(0,0,0,${alpha})`;
    }

    let a = alpha;
    if (hex.length === 8) {
        const ah = parseInt(hex.slice(6, 8), 16);
        const fromHex = Number.isFinite(ah) ? +(ah / 255).toFixed(3) : 1;
        // If caller passed alpha, prefer it; otherwise use embedded alpha
        a = alpha ?? fromHex;
    }

    return `rgba(${r}, ${g}, ${b}, ${a})`;
};

/**
 * Convert RGB color to Hex
 * @param {number} r - Red value (0-255)
 * @param {number} g - Green value (0-255)
 * @param {number} b - Blue value (0-255)
 * @returns {string} Hex color code
 */
export const rgbToHex = (r, g, b) => {
    const toHex = (n) => {
        const hex = Math.max(0, Math.min(255, n)).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    };
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
};

/**
 * Transform API response data to select options format
 * @param {Object} response - API response with data array
 * @returns {Array} Array of options with value and label
 */
export const dataToOptions = (response) => {
    const data = response?.data || response || [];

    if (!Array.isArray(data)) {
        return [];
    }

    return data.map((item) => ({
        value: item.id,
        label: item.name || item.title || item.label || `Item ${item.id}`,
        // Include all original data for extended use
        ...item,
    }));
};

/**
 * Transform options back to simple value array
 * @param {Array} options - Array of option objects
 * @returns {Array} Array of values
 */
export const optionsToValues = (options) => {
    if (!Array.isArray(options)) return [];
    return options.map((opt) => (typeof opt === 'object' ? opt.value : opt));
};

/**
 * Group items by a key
 * @param {Array} items - Array of items to group
 * @param {string|Function} key - Key to group by or function returning key
 * @returns {Object} Grouped items
 */
export const groupBy = (items, key) => {
    return items.reduce((result, item) => {
        const groupKey = typeof key === 'function' ? key(item) : item[key];
        (result[groupKey] = result[groupKey] || []).push(item);
        return result;
    }, {});
};

/**
 * Sort items by a key
 * @param {Array} items - Array of items to sort
 * @param {string} key - Key to sort by
 * @param {string} order - 'asc' or 'desc'
 * @returns {Array} Sorted array
 */
export const sortBy = (items, key, order = 'asc') => {
    return [...items].sort((a, b) => {
        const aVal = a[key];
        const bVal = b[key];

        if (aVal < bVal) return order === 'asc' ? -1 : 1;
        if (aVal > bVal) return order === 'asc' ? 1 : -1;
        return 0;
    });
};

/**
 * Filter items by search text across multiple fields
 * @param {Array} items - Array of items to filter
 * @param {string} searchText - Text to search for
 * @param {Array} fields - Fields to search in
 * @returns {Array} Filtered items
 */
export const filterBySearch = (items, searchText, fields = ['name', 'title']) => {
    if (!searchText) return items;

    const lowerSearch = searchText.toLowerCase();
    return items.filter((item) =>
        fields.some((field) => {
            const value = item[field];
            return value && String(value).toLowerCase().includes(lowerSearch);
        })
    );
};

/**
 * Build a tree structure from flat data
 * @param {Array} items - Flat array with parent references
 * @param {string} idKey - Key for item ID
 * @param {string} parentKey - Key for parent ID
 * @param {string} childrenKey - Key for children array
 * @returns {Array} Tree structure
 */
export const buildTree = (
    items,
    idKey = 'id',
    parentKey = 'parent_id',
    childrenKey = 'children'
) => {
    const map = new Map();
    const roots = [];

    // Create map of items
    items.forEach((item) => {
        map.set(item[idKey], { ...item, [childrenKey]: [] });
    });

    // Build tree
    items.forEach((item) => {
        const node = map.get(item[idKey]);
        const parentId = item[parentKey];

        if (parentId && map.has(parentId)) {
            map.get(parentId)[childrenKey].push(node);
        } else {
            roots.push(node);
        }
    });

    return roots;
};

/**
 * Flatten a tree structure to flat array
 * @param {Array} tree - Tree structure
 * @param {string} childrenKey - Key for children array
 * @returns {Array} Flat array
 */
export const flattenTree = (tree, childrenKey = 'children') => {
    const result = [];

    const flatten = (nodes, depth = 0) => {
        nodes.forEach((node) => {
            const { [childrenKey]: children, ...rest } = node;
            result.push({ ...rest, depth });
            if (children?.length) {
                flatten(children, depth + 1);
            }
        });
    };

    flatten(tree);
    return result;
};

/**
 * Deep clone an object
 * @param {*} obj - Object to clone
 * @returns {*} Cloned object
 */
export const deepClone = (obj) => {
    if (obj === null || typeof obj !== 'object') return obj;
    if (obj instanceof Date) return new Date(obj);
    if (obj instanceof Array) return obj.map((item) => deepClone(item));
    if (obj instanceof Object) {
        return Object.fromEntries(
            Object.entries(obj).map(([key, value]) => [key, deepClone(value)])
        );
    }
    return obj;
};

/**
 * Compare two objects for equality (shallow)
 * @param {Object} obj1 - First object
 * @param {Object} obj2 - Second object
 * @returns {boolean} Whether objects are equal
 */
export const shallowEqual = (obj1, obj2) => {
    const keys1 = Object.keys(obj1);
    const keys2 = Object.keys(obj2);

    if (keys1.length !== keys2.length) return false;

    return keys1.every((key) => obj1[key] === obj2[key]);
};

/**
 * Debounce a function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @returns {Function} Debounced function
 */
export const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Throttle a function
 * @param {Function} func - Function to throttle
 * @param {number} limit - Limit in ms
 * @returns {Function} Throttled function
 */
export const throttle = (func, limit) => {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
};

/**
 * Generate a unique ID
 * @param {string} prefix - Optional prefix
 * @returns {string} Unique ID
 */
export const uniqueId = (prefix = '') => {
    return `${prefix}${Date.now().toString(36)}-${Math.random().toString(36).substr(2, 9)}`;
};

/**
 * Parse query string to object
 * @param {string} queryString - Query string
 * @returns {Object} Parsed object
 */
export const parseQueryString = (queryString) => {
    const params = new URLSearchParams(queryString);
    const result = {};
    params.forEach((value, key) => {
        result[key] = value;
    });
    return result;
};

/**
 * Build query string from object
 * @param {Object} obj - Object to stringify
 * @returns {string} Query string
 */
export const buildQueryString = (obj) => {
    return Object.entries(obj)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');
};

export default {
    hexToRgba,
    rgbToHex,
    dataToOptions,
    optionsToValues,
    groupBy,
    sortBy,
    filterBySearch,
    buildTree,
    flattenTree,
    deepClone,
    shallowEqual,
    debounce,
    throttle,
    uniqueId,
    parseQueryString,
    buildQueryString,
};
