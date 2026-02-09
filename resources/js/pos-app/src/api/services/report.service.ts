import api from '../axios';

export interface SalesReportFilters {
  from_date: string;
  to_date: string;
  store_id?: number;
  group_by?: 'day' | 'week' | 'month';
}

export interface SalesReport {
  summary: {
    total_orders: number;
    total_sales: number;
    total_tax: number;
    total_discount: number;
    net_sales: number;
    average_order_value: number;
    total_items_sold: number;
  };
  daily_sales: {
    date: string;
    orders: number;
    sales: number;
    items: number;
  }[];
  payment_breakdown: {
    method: string;
    count: number;
    total: number;
  }[];
}

export interface ProductSalesReport {
  product_id: number;
  product_name: string;
  sku: string;
  quantity_sold: number;
  total_sales: number;
  total_profit: number;
  profit_margin: number;
}

export interface CategorySalesReport {
  category_id: number;
  category_name: string;
  quantity_sold: number;
  total_sales: number;
  percentage: number;
}

export interface InventoryReport {
  total_products: number;
  total_value: number;
  low_stock_count: number;
  out_of_stock_count: number;
  by_category: {
    category_name: string;
    product_count: number;
    total_quantity: number;
    total_value: number;
  }[];
}

export interface ProfitLossReport {
  period: {
    from: string;
    to: string;
  };
  revenue: {
    gross_sales: number;
    discounts: number;
    returns: number;
    net_sales: number;
  };
  cost_of_goods: number;
  gross_profit: number;
  gross_margin: number;
  expenses: {
    category: string;
    amount: number;
  }[];
  total_expenses: number;
  net_profit: number;
  net_margin: number;
}

export const reportService = {
  getSalesReport: async (filters: SalesReportFilters): Promise<SalesReport> => {
    const { data } = await api.get('/reports/sales', { params: filters });
    return data.data;
  },

  getSalesByProduct: async (filters: SalesReportFilters): Promise<ProductSalesReport[]> => {
    const { data } = await api.get('/reports/sales/by-product', { params: filters });
    return data.data;
  },

  getSalesByCategory: async (filters: SalesReportFilters): Promise<CategorySalesReport[]> => {
    const { data } = await api.get('/reports/sales/by-category', { params: filters });
    return data.data;
  },

  getInventoryReport: async (storeId?: number): Promise<InventoryReport> => {
    const { data } = await api.get('/reports/inventory', { params: { store_id: storeId } });
    return data.data;
  },

  getProfitLossReport: async (filters: { from_date: string; to_date: string }): Promise<ProfitLossReport> => {
    const { data } = await api.get('/reports/profit-loss', { params: filters });
    return data.data;
  },

  exportReport: async (type: string, format: 'pdf' | 'excel', filters: any): Promise<Blob> => {
    const { data } = await api.post(
      `/reports/export/${type}`,
      { format, ...filters },
      { responseType: 'blob' }
    );
    return data;
  },
};
