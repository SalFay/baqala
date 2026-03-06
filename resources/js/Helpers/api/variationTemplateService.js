import axios from 'axios'

export const fetchVariationTemplateListing = async (params = {}) => {
  return await axios.post(route('pos.variation-templates.listing'), params)
}

export const fetchAllVariationTemplates = async () => {
  return await axios.get(route('pos.variation-templates.all'))
}

export const createVariationTemplate = async (data) => {
  return await axios.post(route('pos.variation-templates.store'), data)
}

export const updateVariationTemplate = async (id, data) => {
  return await axios.put(route('pos.variation-templates.update', id), data)
}

export const deleteVariationTemplate = async (id) => {
  return await axios.delete(route('pos.variation-templates.destroy', id))
}

export const generateCombinations = async (id) => {
  return await axios.get(route('pos.variation-templates.combinations', id))
}
