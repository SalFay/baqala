import { useEffect, useState } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Spin } from 'antd';
import { useAuthStore } from './store/authStore';
import { authService } from './api/services/auth.service';
import MainLayout from './layouts/MainLayout';
import POSLayout from './layouts/POSLayout';
import DashboardPage from './pages/dashboard/DashboardPage';
import POSPage from './pages/pos/POSPage';
import ProductsPage from './pages/products/ProductsPage';
import ProductFormPage from './pages/products/ProductFormPage';
import CategoriesPage from './pages/categories/CategoriesPage';
import CustomersPage from './pages/customers/CustomersPage';
import CustomerFormPage from './pages/customers/CustomerFormPage';
import OrdersPage from './pages/orders/OrdersPage';
import OrderDetailPage from './pages/orders/OrderDetailPage';
import ReturnsPage from './pages/returns/ReturnsPage';
import InventoryPage from './pages/inventory/InventoryPage';
import ReportsPage from './pages/reports/ReportsPage';
import SettingsPage from './pages/settings/SettingsPage';
import VendorsPage from './pages/vendors/VendorsPage';
import UsersPage from './pages/users/UsersPage';
import RolesPage from './pages/roles/RolesPage';
import StoresPage from './pages/stores/StoresPage';
import PurchaseOrdersPage from './pages/purchase-orders/PurchaseOrdersPage';
import PurchaseOrderFormPage from './pages/purchase-orders/PurchaseOrderFormPage';
import PurchaseOrderDetailPage from './pages/purchase-orders/PurchaseOrderDetailPage';
import StockTransfersPage from './pages/stock-transfers/StockTransfersPage';
import StockTransferFormPage from './pages/stock-transfers/StockTransferFormPage';
import StockTransferDetailPage from './pages/stock-transfers/StockTransferDetailPage';
import ExpensesPage from './pages/expenses/ExpensesPage';
import StockTakesPage from './pages/stock-takes/StockTakesPage';
import StatementsPage from './pages/statements/StatementsPage';

function App() {
  const [loading, setLoading] = useState(true);
  const setAuth = useAuthStore((state) => state.setAuth);

  // Check auth status on app load - Laravel middleware ensures we're authenticated
  useEffect(() => {
    const checkAuth = async () => {
      try {
        const { user } = await authService.me();
        setAuth(user, null); // No token needed - using session auth
      } catch {
        // If 401, axios interceptor will redirect to login
      } finally {
        setLoading(false);
      }
    };
    checkAuth();
  }, [setAuth]);

  if (loading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <Routes>
      {/* POS route - full screen */}
      <Route
        path="/pos"
        element={
          <POSLayout>
            <POSPage />
          </POSLayout>
        }
      />

      {/* Main app routes */}
      <Route element={<MainLayout />}>
        <Route path="/" element={<Navigate to="/dashboard" replace />} />
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/products" element={<ProductsPage />} />
        <Route path="/products/new" element={<ProductFormPage />} />
        <Route path="/products/:id/edit" element={<ProductFormPage />} />
        <Route path="/categories" element={<CategoriesPage />} />
        <Route path="/customers" element={<CustomersPage />} />
        <Route path="/customers/new" element={<CustomerFormPage />} />
        <Route path="/customers/:id/edit" element={<CustomerFormPage />} />
        <Route path="/orders" element={<OrdersPage />} />
        <Route path="/orders/:id" element={<OrderDetailPage />} />
        <Route path="/returns" element={<ReturnsPage />} />
        <Route path="/inventory" element={<InventoryPage />} />
        <Route path="/reports" element={<ReportsPage />} />
        <Route path="/expenses" element={<ExpensesPage />} />
        <Route path="/settings" element={<SettingsPage />} />
        <Route path="/vendors" element={<VendorsPage />} />
        <Route path="/users" element={<UsersPage />} />
        <Route path="/roles" element={<RolesPage />} />
        <Route path="/stores" element={<StoresPage />} />
        <Route path="/purchase-orders" element={<PurchaseOrdersPage />} />
        <Route path="/purchase-orders/new" element={<PurchaseOrderFormPage />} />
        <Route path="/purchase-orders/:id" element={<PurchaseOrderDetailPage />} />
        <Route path="/purchase-orders/:id/edit" element={<PurchaseOrderFormPage />} />
        <Route path="/stock-transfers" element={<StockTransfersPage />} />
        <Route path="/stock-transfers/new" element={<StockTransferFormPage />} />
        <Route path="/stock-transfers/:id" element={<StockTransferDetailPage />} />
        <Route path="/stock-transfers/:id/edit" element={<StockTransferFormPage />} />
        <Route path="/stock-takes" element={<StockTakesPage />} />
        <Route path="/statements" element={<StatementsPage />} />
      </Route>

      {/* Fallback */}
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}

export default App;
