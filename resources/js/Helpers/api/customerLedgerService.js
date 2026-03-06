import axios from 'axios'

// Get customer ledger entries
export const fetchCustomerLedger = async (customerId, params = {}) => {
  return await axios.post(route('pos.customer-ledger.listing', customerId), params)
}

// Get customer statement
export const getCustomerStatement = async (customerId, params = {}) => {
  return await axios.get(route('pos.customer-ledger.statement', customerId), { params })
}

// Get customer aging report
export const getCustomerAging = async (customerId) => {
  return await axios.get(route('pos.customer-ledger.aging', customerId))
}

// Collect payment from customer
export const collectPayment = async (customerId, data) => {
  return await axios.post(route('pos.customer-ledger.collect-payment', customerId), data)
}

// Adjust customer balance
export const adjustCustomerBalance = async (customerId, data) => {
  return await axios.post(route('pos.customer-ledger.adjust', customerId), data)
}

// Get customers with outstanding balances
export const fetchOutstandingCustomers = async (params = {}) => {
  return await axios.get(route('pos.customer-ledger.outstanding'), { params })
}

// Recalculate customer balance
export const recalculateBalance = async (customerId) => {
  return await axios.post(route('pos.customer-ledger.recalculate', customerId))
}
