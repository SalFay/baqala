import axios from 'axios'

// Warranty Templates
export const fetchWarrantyListing = async (params = {}) => {
  return await axios.post(route('pos.warranties.listing'), params)
}

export const fetchAllWarranties = async () => {
  return await axios.get(route('pos.warranties.all'))
}

export const createWarranty = async (data) => {
  return await axios.post(route('pos.warranties.store'), data)
}

export const updateWarranty = async (id, data) => {
  return await axios.put(route('pos.warranties.update', id), data)
}

export const deleteWarranty = async (id) => {
  return await axios.delete(route('pos.warranties.destroy', id))
}

// Warranty Claims
export const fetchClaimListing = async (params = {}) => {
  return await axios.post(route('pos.warranty-claims.listing'), params)
}

export const fetchClaimStatistics = async () => {
  return await axios.get(route('pos.warranty-claims.statistics'))
}

export const getClaim = async (id) => {
  return await axios.get(route('pos.warranty-claims.show', id))
}

export const createClaim = async (data) => {
  return await axios.post(route('pos.warranty-claims.store'), data)
}

export const updateClaim = async (id, data) => {
  return await axios.put(route('pos.warranty-claims.update', id), data)
}

export const deleteClaim = async (id) => {
  return await axios.delete(route('pos.warranty-claims.destroy', id))
}
