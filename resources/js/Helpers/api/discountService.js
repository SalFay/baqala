import axios from 'axios'

// Discount Rules
export const fetchDiscountRuleListing = async (params = {}) => {
  return await axios.post(route('pos.discount-rules.listing'), params)
}

export const createDiscountRule = async (data) => {
  return await axios.post(route('pos.discount-rules.store'), data)
}

export const updateDiscountRule = async (id, data) => {
  return await axios.put(route('pos.discount-rules.update', id), data)
}

export const deleteDiscountRule = async (id) => {
  return await axios.delete(route('pos.discount-rules.destroy', id))
}

// Coupons
export const fetchCouponListing = async (params = {}) => {
  return await axios.post(route('pos.coupons.listing'), params)
}

export const generateCouponCode = async () => {
  return await axios.get(route('pos.coupons.generate-code'))
}

export const validateCoupon = async (code, items, customerId = null) => {
  return await axios.post(route('pos.coupons.validate'), {
    code,
    items,
    customer_id: customerId,
  })
}

export const createCoupon = async (data) => {
  return await axios.post(route('pos.coupons.store'), data)
}

export const updateCoupon = async (id, data) => {
  return await axios.put(route('pos.coupons.update', id), data)
}

export const deleteCoupon = async (id) => {
  return await axios.delete(route('pos.coupons.destroy', id))
}

export const getCouponStatistics = async (id) => {
  return await axios.get(route('pos.coupons.statistics', id))
}
