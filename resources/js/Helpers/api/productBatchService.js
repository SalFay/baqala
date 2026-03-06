import axios from 'axios'

export const fetchBatchListing = async (params = {}) => {
  return await axios.post(route('pos.batches.listing'), params)
}

export const fetchBatchesForProduct = async (productId) => {
  return await axios.get(route('pos.batches.for-product', productId))
}

export const fetchBatchStatistics = async (productId = null) => {
  return await axios.get(route('pos.batches.statistics'), {
    params: productId ? { product_id: productId } : {},
  })
}

export const fetchExpiryReport = async (days = 30) => {
  return await axios.get(route('pos.batches.expiry-report'), {
    params: { days },
  })
}

export const createBatch = async (data) => {
  return await axios.post(route('pos.batches.store'), data)
}

export const updateBatch = async (id, data) => {
  return await axios.put(route('pos.batches.update', id), data)
}

export const deleteBatch = async (id) => {
  return await axios.delete(route('pos.batches.destroy', id))
}

export const markBatchExpired = async (id) => {
  return await axios.post(route('pos.batches.expire', id))
}

export const recallBatch = async (id) => {
  return await axios.post(route('pos.batches.recall', id))
}

export const quarantineBatch = async (id) => {
  return await axios.post(route('pos.batches.quarantine', id))
}

export const adjustBatchQuantity = async (id, adjustment, reason, notes = null) => {
  return await axios.post(route('pos.batches.adjust', id), { adjustment, reason, notes })
}
