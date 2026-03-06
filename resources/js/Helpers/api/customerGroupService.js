import axios from 'axios'

export const fetchCustomerGroupListing = async (params = {}) => {
  return await axios.post(route('pos.customer-groups.listing'), params)
}

export const fetchAllCustomerGroups = async () => {
  return await axios.get(route('pos.customer-groups.all'))
}

export const createCustomerGroup = async (data) => {
  return await axios.post(route('pos.customer-groups.store'), data)
}

export const updateCustomerGroup = async (id, data) => {
  return await axios.put(route('pos.customer-groups.update', id), data)
}

export const deleteCustomerGroup = async (id) => {
  return await axios.delete(route('pos.customer-groups.destroy', id))
}
