/**
 * Mixed API Helpers
 *
 * General purpose API utilities including dropdown options fetching.
 */

import axios from 'axios';
import { debounce } from 'lodash';

/**
 * Fetch dropdown options by type
 *
 * @param {string} type - The dropdown type (e.g., 'categories', 'vendors', 'customers')
 * @param {string} search - Search query
 * @param {Object} params - Additional parameters
 * @returns {Promise<Object>} API response with options data
 */
export const fetchDropdownOptions = async (type, search = '', params = {}) => {
    try {
        // Try to use the admin.dropdown route if it exists
        const routeName = 'admin.dropdown';
        let url;

        try {
            url = route(routeName, { type });
        } catch (e) {
            // Fallback to direct API endpoint
            url = `/api/dropdown/${type}`;
        }

        const response = await axios.post(url, {
            q: search,
            ...params,
        });

        return response.data;
    } catch (error) {
        // Fallback: try GET request to list endpoint
        try {
            const listUrl = `/api/${type}`;
            const response = await axios.get(listUrl, {
                params: {
                    search,
                    per_page: 50,
                    ...params,
                },
            });
            return response.data;
        } catch (fallbackError) {
            console.error(`Failed to fetch ${type} options:`, fallbackError);
            return { data: [] };
        }
    }
};

/**
 * Fetch options using a custom endpoint
 *
 * @param {string} endpoint - API endpoint
 * @param {Object} params - Query parameters
 * @returns {Promise<Object>} API response
 */
export const fetchOptions = async (endpoint, params = {}) => {
    try {
        const response = await axios.get(endpoint, { params });
        return response.data;
    } catch (error) {
        console.error(`Failed to fetch options from ${endpoint}:`, error);
        return { data: [] };
    }
};

/**
 * Search entities by query
 *
 * @param {string} entity - Entity type
 * @param {string} query - Search query
 * @param {Object} options - Additional options
 * @returns {Promise<Array>} Search results
 */
export const searchEntities = async (entity, query, options = {}) => {
    const { limit = 20, ...params } = options;

    try {
        const response = await axios.get(`/api/${entity}/search`, {
            params: {
                q: query,
                limit,
                ...params,
            },
        });
        return response.data.data || response.data || [];
    } catch (error) {
        console.error(`Search failed for ${entity}:`, error);
        return [];
    }
};

/**
 * Bulk action handler
 *
 * @param {string} endpoint - API endpoint
 * @param {string} action - Action to perform
 * @param {Array} ids - Array of entity IDs
 * @param {Object} data - Additional data
 * @returns {Promise<Object>} API response
 */
export const bulkAction = async (endpoint, action, ids, data = {}) => {
    try {
        const response = await axios.post(`${endpoint}/bulk`, {
            action,
            ids,
            ...data,
        });
        return response.data;
    } catch (error) {
        console.error(`Bulk action ${action} failed:`, error);
        throw error;
    }
};

/**
 * Export data to CSV
 *
 * @param {string} endpoint - API endpoint
 * @param {Object} params - Export parameters
 * @param {string} filename - Download filename
 * @returns {Promise<void>}
 */
export const exportToCsv = async (endpoint, params = {}, filename = 'export') => {
    try {
        const response = await axios.get(endpoint, {
            params: { ...params, export: 'csv' },
            responseType: 'blob',
        });

        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `${filename}-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Export failed:', error);
        throw error;
    }
};

/**
 * Upload file
 *
 * @param {string} endpoint - Upload endpoint
 * @param {File} file - File to upload
 * @param {Function} onProgress - Progress callback
 * @returns {Promise<Object>} Upload response
 */
export const uploadFile = async (endpoint, file, onProgress = null) => {
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await axios.post(endpoint, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (onProgress) {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    onProgress(percentCompleted);
                }
            },
        });
        return response.data;
    } catch (error) {
        console.error('Upload failed:', error);
        throw error;
    }
};

/**
 * Debounced version of fetchDropdownOptions for search inputs
 */
export const debouncedFetchOptions = debounce((type, search, params, callback) => {
    fetchDropdownOptions(type, search, params)
        .then(callback)
        .catch((error) => console.error(error));
}, 300);

export default {
    fetchDropdownOptions,
    fetchOptions,
    debouncedFetchOptions,
    searchEntities,
    bulkAction,
    exportToCsv,
    uploadFile,
};
