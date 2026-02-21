import axios from 'axios'

export const fetchRoleListing = async (params = {}) => {
  return await axios.post(route('role.listing'), params)
}

export const createRole = async (data) => {
  return await axios.post(route('role.store'), data)
}

export const getRoleData = async (id) => {
  return await axios.get(route('role.edit', id))
}

export const updateRole = async (id, data) => {
  return await axios.put(route('role.update', id), data)
}

export const deleteRole = async (id) => {
  return await axios.delete(route('role.delete', id))
}

export const cloneRole = async (id) => {
  return await axios.post(route('role.clone', id))
}

export const getPermissions = async () => {
  return await axios.get(route('role.getPermissions'))
}

export const storePermissions = async (permissions) => {
  return await axios.post(route('role.storePermissions'), { permissions })
}
