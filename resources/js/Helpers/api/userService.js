import axios from 'axios'

export const fetchUserListing = async (params = {}) => {
  return await axios.post(route('user.listing'), params)
}

export const createUser = async (data) => {
  return await axios.post(route('user.store'), data)
}

export const getUserData = async (id) => {
  return await axios.get(route('user.edit', id))
}

export const updateUser = async (id, data) => {
  return await axios.put(route('user.update', id), data)
}

export const deleteUser = async (id) => {
  return await axios.delete(route('user.delete', id))
}

export const updateUserPassword = async (id, data) => {
  return await axios.put(route('user.updatePassword', id), data)
}

export const restoreUser = async (id) => {
  return await axios.post(route('user.restore', id))
}
