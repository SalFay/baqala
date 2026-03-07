import axios from 'axios'

// Cheque listing
export const fetchChequeListing = async (params = {}) => {
  return await axios.post(route('pos.cheques.listing'), params)
}

// Get cheque summary
export const getChequeSummary = async () => {
  const response = await axios.get(route('pos.cheques.summary'))
  return { data: { data: response.data } }
}

// Create cheque
export const createCheque = async (data) => {
  return await axios.post(route('pos.cheques.store'), data)
}

// Update cheque
export const updateCheque = async (id, data) => {
  return await axios.put(route('pos.cheques.update', id), data)
}

// Delete cheque
export const deleteCheque = async (id) => {
  return await axios.delete(route('pos.cheques.destroy', id))
}

// Mark cheque as deposited
export const depositCheque = async (id) => {
  return await axios.post(route('pos.cheques.deposit', id))
}

// Mark cheque as cleared
export const clearCheque = async (id) => {
  return await axios.post(route('pos.cheques.clear', id))
}

// Mark cheque as bounced
export const bounceCheque = async (id, notes = null) => {
  return await axios.post(route('pos.cheques.bounce', id), { notes })
}

// Cancel cheque
export const cancelCheque = async (id, notes = null) => {
  return await axios.post(route('pos.cheques.cancel', id), { notes })
}
