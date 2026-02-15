import api from '../axios';

const statementService = {
  // Customer statements
  getCustomerStatement: (customerId, params = {}) =>
    api.get(`/pos/customers/${customerId}/statement`, { params }),

  getCustomerStatementPdfUrl: (customerId, params = {}) => {
    const searchParams = new URLSearchParams(params);
    return `/pos/customers/${customerId}/statement/pdf?${searchParams}`;
  },

  getCustomerCredits: (customerId, params = {}) =>
    api.get(`/pos/customers/${customerId}/credits`, { params }),

  addCustomerCredit: (customerId, data) =>
    api.post(`/pos/customers/${customerId}/credits`, data),

  // Vendor statements
  getVendorStatement: (vendorId, params = {}) =>
    api.get(`/pos/vendors/${vendorId}/statement`, { params }),

  getVendorStatementPdfUrl: (vendorId, params = {}) => {
    const searchParams = new URLSearchParams(params);
    return `/pos/vendors/${vendorId}/statement/pdf?${searchParams}`;
  },

  getVendorCredits: (vendorId, params = {}) =>
    api.get(`/pos/vendors/${vendorId}/credits`, { params }),

  addVendorCredit: (vendorId, data) =>
    api.post(`/pos/vendors/${vendorId}/credits`, data),
};

export default statementService;
