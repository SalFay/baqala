import axios from 'axios'

export const fetchBusinessTypeListing = async (params = {}) => {
  return await axios.post(route('pos.business-types.listing'), params)
}

export const fetchAllBusinessTypes = async () => {
  return await axios.get(route('pos.business-types.all'))
}

export const createBusinessType = async (data) => {
  return await axios.post(route('pos.business-types.store'), data)
}

export const updateBusinessType = async (id, data) => {
  return await axios.put(route('pos.business-types.update', id), data)
}

export const deleteBusinessType = async (id) => {
  return await axios.delete(route('pos.business-types.destroy', id))
}

export const getCurrentBusinessType = async () => {
  return await axios.get(route('pos.business-types.current'))
}

export const previewBusinessType = async (id) => {
  return await axios.get(route('pos.business-types.preview', id))
}

export const applyBusinessType = async (id, options = {}) => {
  return await axios.post(route('pos.business-types.apply', id), options)
}

export const seedBusinessTypes = async () => {
  return await axios.post(route('pos.business-types.seed'))
}
