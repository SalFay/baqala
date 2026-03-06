import axios from 'axios'

export const fetchUnitListing = async (params = {}) => {
  return await axios.post(route('pos.units.listing'), params)
}

export const fetchAllUnits = async () => {
  return await axios.get(route('pos.units.all'))
}

export const fetchBaseUnits = async () => {
  return await axios.get(route('pos.units.base'))
}

export const createUnit = async (data) => {
  return await axios.post(route('pos.units.store'), data)
}

export const updateUnit = async (id, data) => {
  return await axios.put(route('pos.units.update', id), data)
}

export const deleteUnit = async (id) => {
  return await axios.delete(route('pos.units.destroy', id))
}
