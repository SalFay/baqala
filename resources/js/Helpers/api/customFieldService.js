import axios from 'axios'

export const fetchCustomFieldListing = async (params = {}) => {
  return await axios.post(route('pos.custom-fields.listing'), params)
}

export const fetchCustomFieldMetadata = async () => {
  return await axios.get(route('pos.custom-fields.metadata'))
}

export const fetchCustomFieldsForEntity = async (entityType) => {
  return await axios.get(route('pos.custom-fields.for-entity', entityType))
}

export const createCustomField = async (data) => {
  return await axios.post(route('pos.custom-fields.store'), data)
}

export const updateCustomField = async (id, data) => {
  return await axios.put(route('pos.custom-fields.update', id), data)
}

export const deleteCustomField = async (id) => {
  return await axios.delete(route('pos.custom-fields.destroy', id))
}
