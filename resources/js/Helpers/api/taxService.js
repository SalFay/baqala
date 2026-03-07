import axios from 'axios'

// Tax Rates

export const fetchTaxRateListing = async (params = {}) => {
  return await axios.post(route('pos.tax-rates.listing'), params)
}

export const fetchAllTaxRates = async () => {
  return await axios.get(route('pos.tax-rates.all'))
}

export const createTaxRate = async (data) => {
  return await axios.post(route('pos.tax-rates.store'), data)
}

export const updateTaxRate = async (id, data) => {
  return await axios.put(route('pos.tax-rates.update', id), data)
}

export const deleteTaxRate = async (id) => {
  return await axios.delete(route('pos.tax-rates.destroy', id))
}

// Tax Groups

export const fetchTaxGroupListing = async (params = {}) => {
  return await axios.post(route('pos.tax-groups.listing'), params)
}

export const fetchAllTaxGroups = async () => {
  return await axios.get(route('pos.tax-groups.all'))
}

export const createTaxGroup = async (data) => {
  return await axios.post(route('pos.tax-groups.store'), data)
}

export const updateTaxGroup = async (id, data) => {
  return await axios.put(route('pos.tax-groups.update', id), data)
}

export const deleteTaxGroup = async (id) => {
  return await axios.delete(route('pos.tax-groups.destroy', id))
}
