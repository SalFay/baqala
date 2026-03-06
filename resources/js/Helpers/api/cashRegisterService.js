import axios from 'axios'

// Cash register listing
export const fetchCashRegisterListing = async (params = {}) => {
  return await axios.post(route('pos.cash-registers.listing'), params)
}

// Get current register status
export const getCurrentRegister = async () => {
  return await axios.get(route('pos.cash-registers.current'))
}

// Get denomination options
export const getDenominations = async () => {
  return await axios.get(route('pos.cash-registers.denominations'))
}

// Get daily report
export const getDailyReport = async (date = null) => {
  return await axios.get(route('pos.cash-registers.daily-report'), { params: { date } })
}

// Open a cash register
export const openRegister = async (data) => {
  return await axios.post(route('pos.cash-registers.open'), data)
}

// Close a cash register
export const closeRegister = async (registerId, data) => {
  return await axios.post(route('pos.cash-registers.close', registerId), data)
}

// Pay in (add cash)
export const registerPayIn = async (registerId, data) => {
  return await axios.post(route('pos.cash-registers.pay-in', registerId), data)
}

// Pay out (remove cash)
export const registerPayOut = async (registerId, data) => {
  return await axios.post(route('pos.cash-registers.pay-out', registerId), data)
}

// Get register summary
export const getRegisterSummary = async (registerId) => {
  return await axios.get(route('pos.cash-registers.summary', registerId))
}

// Get register transactions
export const getRegisterTransactions = async (registerId, params = {}) => {
  return await axios.get(route('pos.cash-registers.transactions', registerId), { params })
}
