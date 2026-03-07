import axios from 'axios'

// Restaurant Tables

export const fetchTableListing = async (params = {}) => {
  return await axios.post(route('pos.restaurant-tables.listing'), params)
}

export const fetchAllTables = async (params = {}) => {
  return await axios.get(route('pos.restaurant-tables.all'), { params })
}

export const fetchFloorPlan = async (params = {}) => {
  return await axios.get(route('pos.restaurant-tables.floor-plan'), { params })
}

export const createTable = async (data) => {
  return await axios.post(route('pos.restaurant-tables.store'), data)
}

export const updateTable = async (id, data) => {
  return await axios.put(route('pos.restaurant-tables.update', id), data)
}

export const deleteTable = async (id) => {
  return await axios.delete(route('pos.restaurant-tables.destroy', id))
}

export const updateTableStatus = async (id, status) => {
  return await axios.post(route('pos.restaurant-tables.update-status', id), { status })
}

export const updateTablePosition = async (id, position) => {
  return await axios.post(route('pos.restaurant-tables.update-position', id), position)
}

// Reservations

export const fetchReservationListing = async (params = {}) => {
  return await axios.post(route('pos.reservations.listing'), params)
}

export const getTodaySummary = async () => {
  return await axios.get(route('pos.reservations.today-summary'))
}

export const createReservation = async (data) => {
  return await axios.post(route('pos.reservations.store'), data)
}

export const updateReservation = async (id, data) => {
  return await axios.put(route('pos.reservations.update', id), data)
}

export const deleteReservation = async (id) => {
  return await axios.delete(route('pos.reservations.destroy', id))
}

export const confirmReservation = async (id) => {
  return await axios.post(route('pos.reservations.confirm', id))
}

export const cancelReservation = async (id) => {
  return await axios.post(route('pos.reservations.cancel', id))
}

export const completeReservation = async (id) => {
  return await axios.post(route('pos.reservations.complete', id))
}

export const markNoShow = async (id) => {
  return await axios.post(route('pos.reservations.no-show', id))
}

// Kitchen

export const fetchKitchenOrders = async (params = {}) => {
  return await axios.get(route('pos.kitchen.orders'), { params })
}

export const fetchKitchenStatistics = async () => {
  return await axios.get(route('pos.kitchen.statistics'))
}

export const fetchOrdersByStation = async (station) => {
  return await axios.get(route('pos.kitchen.by-station'), { params: { station } })
}

export const updateKitchenOrderStatus = async (id, status) => {
  return await axios.post(route('pos.kitchen.update-status', id), { status })
}

export const startPreparing = async (id) => {
  return await axios.post(route('pos.kitchen.start', id))
}

export const markReady = async (id) => {
  return await axios.post(route('pos.kitchen.ready', id))
}

export const markServed = async (id) => {
  return await axios.post(route('pos.kitchen.served', id))
}

export const bumpOrder = async (orderId) => {
  return await axios.post(route('pos.kitchen.bump-order'), { order_id: orderId })
}
