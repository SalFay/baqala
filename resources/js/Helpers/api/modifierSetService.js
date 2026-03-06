import axios from 'axios'

export const fetchModifierSetListing = async (params = {}) => {
  return await axios.post(route('pos.modifier-sets.listing'), params)
}

export const fetchAllModifierSets = async () => {
  return await axios.get(route('pos.modifier-sets.all'))
}

export const createModifierSet = async (data) => {
  return await axios.post(route('pos.modifier-sets.store'), data)
}

export const updateModifierSet = async (id, data) => {
  return await axios.put(route('pos.modifier-sets.update', id), data)
}

export const deleteModifierSet = async (id) => {
  return await axios.delete(route('pos.modifier-sets.destroy', id))
}
