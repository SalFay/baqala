import axios from 'axios'

export const fetchSerialListing = async (params = {}) => {
  return await axios.post(route('pos.serials.listing'), params)
}

export const lookupSerial = async (query) => {
  return await axios.get(route('pos.serials.lookup'), { params: { query } })
}

export const fetchSerialsForProduct = async (productId) => {
  return await axios.get(route('pos.serials.for-product', productId))
}

export const fetchSerialStatistics = async (productId = null) => {
  return await axios.get(route('pos.serials.statistics'), {
    params: productId ? { product_id: productId } : {},
  })
}

export const createSerial = async (data) => {
  return await axios.post(route('pos.serials.store'), data)
}

export const bulkCreateSerials = async (data) => {
  return await axios.post(route('pos.serials.bulk-store'), data)
}

export const updateSerial = async (id, data) => {
  return await axios.put(route('pos.serials.update', id), data)
}

export const updateSerialStatus = async (id, status) => {
  return await axios.put(route('pos.serials.status', id), { status })
}

export const deleteSerial = async (id) => {
  return await axios.delete(route('pos.serials.destroy', id))
}
