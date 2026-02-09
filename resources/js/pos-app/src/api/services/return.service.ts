import api from '../axios';
import type { OrderReturn, PaginatedResponse } from '../../types';

export interface ReturnFilters {
  search?: string;
  status?: string;
  type?: string;
  from_date?: string;
  to_date?: string;
  page?: number;
  per_page?: number;
}

export interface CreateReturnData {
  order_id: number;
  type: 'refund' | 'exchange' | 'store_credit';
  items: {
    order_item_id: number;
    quantity: number;
    condition: 'sellable' | 'damaged' | 'defective';
    restock: boolean;
    reason?: string;
  }[];
  reason?: string;
  notes?: string;
  restocking_fee?: number;
  refund_method?: 'cash' | 'card' | 'store_credit';
}

export const returnService = {
  getReturns: async (filters: ReturnFilters = {}): Promise<PaginatedResponse<OrderReturn>> => {
    const { data } = await api.get('/returns', { params: filters });
    return data;
  },

  getReturn: async (id: number): Promise<OrderReturn> => {
    const { data } = await api.get(`/returns/${id}`);
    return data.data;
  },

  getReturnableItems: async (orderId: number): Promise<any> => {
    const { data } = await api.get(`/returns/order/${orderId}`);
    return data.data;
  },

  createReturn: async (returnData: CreateReturnData): Promise<OrderReturn> => {
    const { data } = await api.post('/returns', returnData);
    return data.data;
  },

  approveReturn: async (id: number): Promise<OrderReturn> => {
    const { data } = await api.post(`/returns/${id}/approve`);
    return data.data;
  },

  rejectReturn: async (id: number, reason: string): Promise<OrderReturn> => {
    const { data } = await api.post(`/returns/${id}/reject`, { reason });
    return data.data;
  },

  processReturn: async (id: number): Promise<OrderReturn> => {
    const { data } = await api.post(`/returns/${id}/process`);
    return data.data;
  },

  getReturnReasons: async (): Promise<{ id: number; name: string }[]> => {
    const { data } = await api.get('/return-reasons');
    return data.data;
  },
};
