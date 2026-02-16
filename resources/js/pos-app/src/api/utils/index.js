/**
 * API utility functions for consistent response handling
 */

/**
 * Unwrap API response to handle various formats
 * Normalizes response.data vs response.data.data inconsistencies
 *
 * @param {Object} response - Axios response object
 * @returns {*} - Unwrapped data
 */
export function unwrapResponse(response) {
  const data = response?.data;

  // If response has data.data (paginated or wrapped response)
  if (data && typeof data === 'object' && 'data' in data) {
    // Return the full pagination object if it looks like Laravel pagination
    if ('current_page' in data || 'total' in data) {
      return data;
    }
    // Otherwise just return the nested data
    return data.data;
  }

  return data;
}

/**
 * Extract error message from API error response
 *
 * @param {Error} error - Error object (typically from axios)
 * @returns {string} - Human readable error message
 */
export function extractErrorMessage(error) {
  // Axios error with response
  if (error.response?.data) {
    const data = error.response.data;

    // Laravel validation errors
    if (data.errors) {
      const firstError = Object.values(data.errors)[0];
      return Array.isArray(firstError) ? firstError[0] : firstError;
    }

    // Standard message field
    if (data.message) {
      return data.message;
    }

    // Error field
    if (data.error) {
      return data.error;
    }
  }

  // Network or other errors
  if (error.message) {
    return error.message;
  }

  return 'An unexpected error occurred';
}

/**
 * Build query string from params object
 * Handles null/undefined values and arrays
 *
 * @param {Object} params - Query parameters
 * @returns {string} - Query string (without leading ?)
 */
export function buildQueryString(params) {
  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value === null || value === undefined || value === '') {
      return;
    }

    if (Array.isArray(value)) {
      value.forEach((item) => searchParams.append(`${key}[]`, item));
    } else {
      searchParams.append(key, value);
    }
  });

  return searchParams.toString();
}

/**
 * Create a standardized API response
 *
 * @param {boolean} success - Whether the operation was successful
 * @param {*} data - Response data
 * @param {string} message - Optional message
 * @returns {Object} - Standardized response object
 */
export function createResponse(success, data = null, message = null) {
  return {
    success,
    data,
    message,
    timestamp: new Date().toISOString(),
  };
}

/**
 * Check if error is a network error
 *
 * @param {Error} error - Error object
 * @returns {boolean}
 */
export function isNetworkError(error) {
  return !error.response && error.message === 'Network Error';
}

/**
 * Check if error is an authentication error
 *
 * @param {Error} error - Error object
 * @returns {boolean}
 */
export function isAuthError(error) {
  return error.response?.status === 401;
}

/**
 * Check if error is a validation error
 *
 * @param {Error} error - Error object
 * @returns {boolean}
 */
export function isValidationError(error) {
  return error.response?.status === 422;
}

/**
 * Get validation errors from API response
 *
 * @param {Error} error - Error object
 * @returns {Object} - Validation errors keyed by field name
 */
export function getValidationErrors(error) {
  if (!isValidationError(error)) {
    return {};
  }

  return error.response?.data?.errors || {};
}
