import api from '../axios';

const statementService = {
  // Customer statements
  getCustomerStatement: (customerId, params = {}) =>
    api.get(route('pos.statements.customer', { customer: customerId }), { params }),

  getCustomerStatementPdfUrl: (customerId, params = {}) => {
    const searchParams = new URLSearchParams(params);
    return route('pos.statements.customer.pdf', { customer: customerId }) + `?${searchParams}`;
  },

  getCustomerCredits: (customerId, params = {}) =>
    api.get(route('pos.statements.customer.credits', { customer: customerId }), { params }),

  addCustomerCredit: (customerId, data) =>
    api.post(route('pos.statements.customer.credits.add', { customer: customerId }), data),

  // Vendor statements
  getVendorStatement: (vendorId, params = {}) =>
    api.get(route('pos.statements.vendor', { vendor: vendorId }), { params }),

  getVendorStatementPdfUrl: (vendorId, params = {}) => {
    const searchParams = new URLSearchParams(params);
    return route('pos.statements.vendor.pdf', { vendor: vendorId }) + `?${searchParams}`;
  },

  getVendorCredits: (vendorId, params = {}) =>
    api.get(route('pos.statements.vendor.credits', { vendor: vendorId }), { params }),

  addVendorCredit: (vendorId, data) =>
    api.post(route('pos.statements.vendor.credits.add', { vendor: vendorId }), data),
};

export default statementService;
