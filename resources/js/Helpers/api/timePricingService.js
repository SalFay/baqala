import axios from 'axios';

/**
 * Time-Based Pricing API Service
 */

export const fetchTimePricingListing = (params) =>
    axios.post(route('pos.time-pricing.listing'), params);

export const fetchActiveTimePricing = () =>
    axios.get(route('pos.time-pricing.active'));

export const getTimePricing = (id) =>
    axios.get(route('pos.time-pricing.show', id));

export const createTimePricing = (data) =>
    axios.post(route('pos.time-pricing.store'), data);

export const updateTimePricing = (id, data) =>
    axios.put(route('pos.time-pricing.update', id), data);

export const deleteTimePricing = (id) =>
    axios.delete(route('pos.time-pricing.destroy', id));

export const toggleTimePricing = (id) =>
    axios.post(route('pos.time-pricing.toggle', id));

export const previewTimePricing = (data) =>
    axios.post(route('pos.time-pricing.preview'), data);

export default {
    fetchTimePricingListing,
    fetchActiveTimePricing,
    getTimePricing,
    createTimePricing,
    updateTimePricing,
    deleteTimePricing,
    toggleTimePricing,
    previewTimePricing,
};
