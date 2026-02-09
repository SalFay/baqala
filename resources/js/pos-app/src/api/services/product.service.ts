import api from '../axios';
import type { Product, ProductVariant, PaginatedResponse } from '../../types';

interface ProductFilters {
  category_id?: number;
  search?: string;
  status?: string;
  store_id?: number;
  per_page?: number;
  page?: number;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}

export const productService = {
  async getProducts(filters: ProductFilters = {}): Promise<PaginatedResponse<Product>> {
    const response = await api.get('/products', { params: filters });
    return response.data;
  },

  async getProduct(id: number): Promise<Product> {
    const response = await api.get(`/products/${id}`);
    return response.data;
  },

  async createProduct(data: Partial<Product>): Promise<Product> {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value as string | Blob);
      }
    });
    const response = await api.post('/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async updateProduct(id: number, data: Partial<Product>): Promise<Product> {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        formData.append(key, value as string | Blob);
      }
    });
    formData.append('_method', 'PUT');
    const response = await api.post(`/products/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return response.data;
  },

  async deleteProduct(id: number): Promise<void> {
    await api.delete(`/products/${id}`);
  },

  async searchProducts(query: string): Promise<Product[]> {
    const response = await api.get('/products/search', { params: { q: query } });
    return response.data;
  },

  async findByBarcode(barcode: string): Promise<{ product: Product; variant: ProductVariant | null }> {
    const response = await api.get(`/products/barcode/${barcode}`);
    return response.data;
  },

  async createVariant(productId: number, data: Partial<ProductVariant> & { attributes: any[] }): Promise<ProductVariant> {
    const response = await api.post(`/products/${productId}/variants`, data);
    return response.data;
  },

  async updateVariant(productId: number, variantId: number, data: Partial<ProductVariant>): Promise<ProductVariant> {
    const response = await api.put(`/products/${productId}/variants/${variantId}`, data);
    return response.data;
  },
};
