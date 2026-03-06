import axios from 'axios'

// Location listing
export const fetchLocationListing = async (params = {}) => {
  return await axios.post(route('pos.locations.listing'), params)
}

// Get all active locations
export const fetchAllLocations = async () => {
  return await axios.get(route('pos.locations.all'))
}

// Create location
export const createLocation = async (data) => {
  return await axios.post(route('pos.locations.store'), data)
}

// Update location
export const updateLocation = async (id, data) => {
  return await axios.put(route('pos.locations.update', id), data)
}

// Delete location
export const deleteLocation = async (id) => {
  return await axios.delete(route('pos.locations.destroy', id))
}

// Get location stock summary
export const getLocationStockSummary = async (id) => {
  return await axios.get(route('pos.locations.stock-summary', id))
}
