// Product types
export interface Product {
  id: number;
  store_id: number | null;
  name: string;
  arabic_name: string | null;
  sku: string | null;
  barcode: string | null;
  type: 'simple' | 'variable';
  category_id: number;
  category: Category;
  purchase_price: number;
  sale_price: number;
  taxable: boolean;
  status: string;
  track_inventory: boolean;
  low_stock_threshold: number;
  product_image: string | null;
  image: string;
  variants: ProductVariant[];
  created_at: string;
  updated_at: string;
}

export interface ProductVariant {
  id: number;
  product_id: number;
  sku: string | null;
  barcode: string | null;
  name: string | null;
  purchase_price: number;
  sale_price: number;
  compare_price: number | null;
  is_active: boolean;
  image: string | null;
}

export interface Category {
  id: number;
  name: string;
  code: string | null;
  description: string | null;
  products_count?: number;
}

// Customer types
export interface Customer {
  id: number;
  first_name: string;
  last_name: string | null;
  business_name: string | null;
  email: string | null;
  phone_mobile: string | null;
  address: string | null;
  full_name: string;
  loyalty_points: number;
  credit_balance: number;
  date_of_birth?: string | null;
  loyalty?: CustomerLoyalty;
}

export interface CustomerLoyalty {
  id: number;
  customer_id: number;
  card_number: string;
  points_balance: number;
  points_earned_total: number;
  points_redeemed_total: number;
  tier?: LoyaltyTier;
}

export interface LoyaltyTier {
  id: number;
  name: string;
  min_points: number;
  discount_percentage: number;
  points_multiplier: number;
  badge_color: string | null;
}

// Order types
export interface Order {
  id: number;
  order_number: string;
  store_id: number | null;
  customer_id: number | null;
  customer: Customer | null;
  user_id: number | null;
  user: User | null;
  cashier_name?: string | null;
  payment_type: string;
  status: OrderStatus;
  payment_status: PaymentStatus;
  sub_total: number;
  tax_amount: number;
  discount: number;
  total: number;
  loyalty_points_earned: number;
  loyalty_points_redeemed: number;
  loyalty_discount: number;
  items: OrderItem[];
  created_at: string;
}

export interface OrderItem {
  id: number;
  order_id: number;
  product_id: number;
  product: Product;
  product_variant_id: number | null;
  variant: ProductVariant | null;
  sku: string | null;
  product_name: string;
  variant_name: string | null;
  purchase_price: number;
  sale_price: number;
  stock: number;
  discount: number;
  tax_rate: number;
  tax_amount: number;
  line_total: number;
}

export type OrderStatus = 'pending' | 'processing' | 'completed' | 'cancelled' | 'refunded';
export type PaymentStatus = 'pending' | 'paid' | 'partially_paid' | 'failed' | 'refunded';

// Cart types
export interface Cart {
  id: number;
  store_id: number | null;
  user_id: number | null;
  customer_id: number | null;
  customer: Customer | null;
  status: 'active' | 'held' | 'converted';
  hold_name: string | null;
  held_at?: string | null;
  subtotal: number;
  tax_amount: number;
  discount: number;
  discount_type: 'fixed' | 'percentage' | null;
  total: number;
  loyalty_points_to_redeem: number;
  loyalty_discount: number;
  items: CartItem[];
}

export interface CartItem {
  id: number;
  cart_id: number;
  product_id: number;
  product: Product;
  product_variant_id: number | null;
  variant: ProductVariant | null;
  sku: string | null;
  product_name: string;
  variant_name: string | null;
  quantity: number;
  unit_price: number;
  purchase_price: number;
  discount: number;
  tax_rate: number;
  tax_amount: number;
  line_total: number;
}

export interface CartSummary {
  items_count: number;
  subtotal: number;
  tax_amount: number;
  discount: number;
  discount_type: string | null;
  loyalty_discount: number;
  total: number;
  customer: Customer | null;
}

// Return types
export interface OrderReturn {
  id: number;
  return_number: string;
  order_id: number;
  order: Order;
  customer_id: number | null;
  customer: Customer | null;
  type: 'refund' | 'exchange' | 'store_credit';
  status: 'pending' | 'approved' | 'processing' | 'completed' | 'rejected';
  reason: string | null;
  subtotal: number;
  tax_amount: number;
  total_amount: number;
  refund_amount: number;
  refund_method: string | null;
  items: OrderReturnItem[];
  created_at: string;
}

export interface OrderReturnItem {
  id: number;
  order_return_id: number;
  order_item_id: number;
  product_id: number;
  product_name: string;
  variant_name: string | null;
  quantity: number;
  unit_price: number;
  tax_amount: number;
  total: number;
  condition: 'sellable' | 'damaged' | 'defective';
  restock: boolean;
  reason: string | null;
}

// User types
export interface User {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  status: 'active' | 'inactive';
  role_id: number;
  role: Role;
  stores?: Store[];
  permissions?: string[];
  is_super_user?: boolean;
  created_at: string;
}

export interface Role {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  permissions: string[];
  color: string | null;
  sort_order: number;
  users_count?: number;
}

export interface Store {
  id: number;
  code: string;
  name: string;
  name_ar: string | null;
  address: string | null;
  city: string | null;
  phone: string | null;
  email: string | null;
  manager_name: string | null;
  timezone: string;
  currency_code: string;
  is_active: boolean;
  is_warehouse: boolean;
  operating_hours: Record<string, any> | null;
  users_count?: number;
  orders_count?: number;
  products_count?: number;
}

// API Response types
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

// Dashboard types
export interface DashboardStats {
  today: SalesStats;
  yesterday: SalesStats;
  month: SalesStats;
  inventory: InventoryStats;
  sales_growth: number;
}

export interface SalesStats {
  total_orders: number;
  total_sales: number;
  total_tax: number;
  total_discount: number;
  average_order_value: number;
}

export interface InventoryStats {
  total_products: number;
  total_quantity: number;
  total_value: number;
  low_stock_count: number;
  out_of_stock_count: number;
}

// Settings types
export interface Settings {
  shop_name: string;
  shop_phone: string;
  shop_email: string;
  shop_address: string;
  shop_vat_number: string;
  shop_cr_number: string;
  shop_logo: string;
  default_tax_rate: number;
  currency_code: string;
  currency_symbol: string;
  loyalty_enabled: boolean;
  [key: string]: string | number | boolean;
}
