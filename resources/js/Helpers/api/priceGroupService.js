import axios from 'axios'

export const fetchPriceGroupListing = async (params = {}) => {
  return await axios.post(route('pos.price-groups.listing'), params)
}

export const fetchAllPriceGroups = async () => {
  return await axios.get(route('pos.price-groups.all'))
}

export const createPriceGroup = async (data) => {
  return await axios.post(route('pos.price-groups.store'), data)
}

export const updatePriceGroup = async (id, data) => {
  return await axios.put(route('pos.price-groups.update', id), data)
}

export const deletePriceGroup = async (id) => {
  return await axios.delete(route('pos.price-groups.destroy', id))
}
