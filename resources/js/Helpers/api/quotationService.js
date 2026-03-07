import axios from 'axios'

// Quotations API

export const fetchQuotationListing = async (params = {}) => {
  return await axios.post(route('pos.quotations.listing'), params)
}

export const getQuotation = async (id) => {
  return await axios.get(route('pos.quotations.show', id))
}

export const createQuotation = async (data) => {
  return await axios.post(route('pos.quotations.store'), data)
}

export const updateQuotation = async (id, data) => {
  return await axios.put(route('pos.quotations.update', id), data)
}

export const deleteQuotation = async (id) => {
  return await axios.delete(route('pos.quotations.destroy', id))
}

export const markAsSent = async (id) => {
  return await axios.post(route('pos.quotations.send', id))
}

export const acceptQuotation = async (id) => {
  return await axios.post(route('pos.quotations.accept', id))
}

export const rejectQuotation = async (id) => {
  return await axios.post(route('pos.quotations.reject', id))
}

export const convertToOrder = async (id) => {
  return await axios.post(route('pos.quotations.convert', id))
}

export const duplicateQuotation = async (id) => {
  return await axios.post(route('pos.quotations.duplicate', id))
}

export const getQuotationStatistics = async () => {
  return await axios.get(route('pos.quotations.statistics'))
}
