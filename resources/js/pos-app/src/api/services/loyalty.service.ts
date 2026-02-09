import api from '../axios';
import type { PaginatedResponse, Customer } from '../../types';

export interface LoyaltyTier {
  id: number;
  name: string;
  name_ar: string | null;
  min_points: number;
  points_multiplier: number;
  discount_percentage: number;
  benefits: string[] | null;
  badge_color: string | null;
  badge_icon: string | null;
  is_active: boolean;
  customer_loyalties_count?: number;
}

export interface CustomerLoyalty {
  id: number;
  customer_id: number;
  loyalty_tier_id: number | null;
  card_number: string | null;
  points_balance: number;
  points_earned_total: number;
  points_redeemed_total: number;
  points_expired_total: number;
  lifetime_spend: number;
  last_activity_at: string | null;
  tier_expires_at: string | null;
  is_active: boolean;
  tier?: LoyaltyTier;
  customer?: Customer;
}

export interface LoyaltyTransaction {
  id: number;
  customer_loyalty_id: number;
  store_id: number | null;
  type: 'earn' | 'redeem' | 'expire' | 'adjust' | 'bonus' | 'refund';
  points: number;
  points_balance_after: number;
  reference_type: string | null;
  reference_id: number | null;
  description: string | null;
  created_by: number | null;
  created_at: string;
  customer_loyalty?: CustomerLoyalty;
}

interface LoyaltyOverview {
  total_members: number;
  total_points_issued: number;
  total_points_redeemed: number;
  total_points_balance: number;
  tiers: LoyaltyTier[];
}

interface TransactionFilters {
  customer_id?: number;
  type?: string;
  from_date?: string;
  to_date?: string;
  per_page?: number;
  page?: number;
}

export const loyaltyService = {
  async getOverview(): Promise<LoyaltyOverview> {
    const response = await api.get('/loyalty/overview');
    return response.data.data;
  },

  async getTiers(): Promise<LoyaltyTier[]> {
    const response = await api.get('/loyalty/tiers');
    return response.data.data;
  },

  async createTier(data: Partial<LoyaltyTier>): Promise<LoyaltyTier> {
    const response = await api.post('/loyalty/tiers', data);
    return response.data.data;
  },

  async updateTier(id: number, data: Partial<LoyaltyTier>): Promise<LoyaltyTier> {
    const response = await api.put(`/loyalty/tiers/${id}`, data);
    return response.data.data;
  },

  async deleteTier(id: number): Promise<void> {
    await api.delete(`/loyalty/tiers/${id}`);
  },

  async getCustomerLoyalty(customerId: number): Promise<{ enrolled: boolean; loyalty?: CustomerLoyalty; recent_transactions?: LoyaltyTransaction[] }> {
    const response = await api.get(`/loyalty/customer/${customerId}`);
    return response.data.data;
  },

  async enrollCustomer(customerId: number, cardNumber?: string): Promise<CustomerLoyalty> {
    const response = await api.post(`/loyalty/customer/${customerId}/enroll`, { card_number: cardNumber });
    return response.data.data;
  },

  async awardPoints(customerId: number, points: number, description?: string): Promise<{ transaction: LoyaltyTransaction; new_balance: number }> {
    const response = await api.post(`/loyalty/customer/${customerId}/award`, { points, description });
    return response.data.data;
  },

  async redeemPoints(customerId: number, points: number, description?: string): Promise<{ transaction: LoyaltyTransaction; new_balance: number }> {
    const response = await api.post(`/loyalty/customer/${customerId}/redeem`, { points, description });
    return response.data.data;
  },

  async calculatePoints(amount: number, customerId?: number): Promise<{ amount: number; points: number; multiplier: number }> {
    const response = await api.post('/loyalty/calculate', { amount, customer_id: customerId });
    return response.data.data;
  },

  async getTransactions(filters: TransactionFilters = {}): Promise<PaginatedResponse<LoyaltyTransaction>> {
    const response = await api.get('/loyalty/transactions', { params: filters });
    return response.data;
  },

  async getPointsValue(points: number): Promise<{ points: number; value: number; currency: string }> {
    const response = await api.post('/loyalty/points-value', { points });
    return response.data.data;
  },
};
