import api from '../axios';

export interface ProductAttributeValue {
  id: number;
  product_attribute_id: number;
  value: string;
  value_ar: string | null;
  slug: string;
  color_code: string | null;
  sort_order: number;
}

export interface ProductAttribute {
  id: number;
  name: string;
  name_ar: string | null;
  slug: string;
  type: 'select' | 'color' | 'text' | 'size';
  is_visible: boolean;
  is_variation: boolean;
  sort_order: number;
  values?: ProductAttributeValue[];
}

interface CreateAttributeData {
  name: string;
  name_ar?: string;
  slug?: string;
  type: 'select' | 'color' | 'text' | 'size';
  is_visible?: boolean;
  is_variation?: boolean;
  values?: {
    value: string;
    value_ar?: string;
    color_code?: string;
  }[];
}

export const productAttributeService = {
  async getAttributes(params: { search?: string; is_variation?: boolean } = {}): Promise<ProductAttribute[]> {
    const response = await api.get('/product-attributes', { params });
    return response.data.data;
  },

  async getAttribute(id: number): Promise<ProductAttribute> {
    const response = await api.get(`/product-attributes/${id}`);
    return response.data.data;
  },

  async createAttribute(data: CreateAttributeData): Promise<ProductAttribute> {
    const response = await api.post('/product-attributes', data);
    return response.data.data;
  },

  async updateAttribute(id: number, data: Partial<CreateAttributeData>): Promise<ProductAttribute> {
    const response = await api.put(`/product-attributes/${id}`, data);
    return response.data.data;
  },

  async deleteAttribute(id: number): Promise<void> {
    await api.delete(`/product-attributes/${id}`);
  },

  async reorderAttributes(order: number[]): Promise<void> {
    await api.post('/product-attributes/reorder', { order });
  },

  async createValue(attributeId: number, data: { value: string; value_ar?: string; color_code?: string }): Promise<ProductAttributeValue> {
    const response = await api.post(`/product-attributes/${attributeId}/values`, data);
    return response.data.data;
  },

  async updateValue(valueId: number, data: { value?: string; value_ar?: string; color_code?: string }): Promise<ProductAttributeValue> {
    const response = await api.put(`/product-attributes/values/${valueId}`, data);
    return response.data.data;
  },

  async deleteValue(valueId: number): Promise<void> {
    await api.delete(`/product-attributes/values/${valueId}`);
  },

  async reorderValues(attributeId: number, order: number[]): Promise<void> {
    await api.post(`/product-attributes/${attributeId}/values/reorder`, { order });
  },
};
