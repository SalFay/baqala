import { useCallback, useMemo } from 'react';
import { Link, useLocation } from 'react-router-dom';
import {
  DashboardOutlined,
  ShoppingCartOutlined,
  AppstoreOutlined,
  UserOutlined,
  ShoppingOutlined,
  InboxOutlined,
  BarChartOutlined,
  SettingOutlined,
  SwapOutlined,
} from '@ant-design/icons';
import { useMenuStore } from '@/Helpers/atom';

// Helper to create menu items - SparkCRM pattern
const createMenuItem = (key, label, icon, children) => ({
  key,
  icon,
  label,
  children,
});

// Helper for route links
const routeLink = (path, label) => <Link to={path}>{label}</Link>;

const useMenuManagement = () => {
  const location = useLocation();
  const { openKeys, setOpenKeys, setSelectedKeys } = useMenuStore();

  const getMenuItems = useCallback(() => {
    const items = [];

    // Dashboard
    items.push(
      createMenuItem('/dashboard', routeLink('/dashboard', 'Dashboard'), <DashboardOutlined />)
    );

    // POS
    items.push(
      createMenuItem('/pos', routeLink('/pos', 'Point of Sale'), <ShoppingCartOutlined />)
    );

    // Products & Categories
    items.push(
      createMenuItem('products-group', 'Products', <AppstoreOutlined />, [
        createMenuItem('/products', routeLink('/products', 'All Products')),
        createMenuItem('/categories', routeLink('/categories', 'Categories')),
      ])
    );

    // Customers
    items.push(
      createMenuItem('/customers', routeLink('/customers', 'Customers'), <UserOutlined />)
    );

    // Orders
    items.push(
      createMenuItem('orders-group', 'Orders', <ShoppingOutlined />, [
        createMenuItem('/orders', routeLink('/orders', 'All Orders')),
        createMenuItem('/returns', routeLink('/returns', 'Returns')),
      ])
    );

    // Inventory
    items.push(
      createMenuItem('inventory-group', 'Inventory', <InboxOutlined />, [
        createMenuItem('/inventory', routeLink('/inventory', 'Stock Levels')),
        createMenuItem('/purchase-orders', routeLink('/purchase-orders', 'Purchase Orders')),
        createMenuItem('/stock-transfers', routeLink('/stock-transfers', 'Stock Transfers')),
      ])
    );

    // Vendors
    items.push(
      createMenuItem('/vendors', routeLink('/vendors', 'Vendors'), <SwapOutlined />)
    );

    // Reports
    items.push(
      createMenuItem('/reports', routeLink('/reports', 'Reports'), <BarChartOutlined />)
    );

    // Settings
    items.push(
      createMenuItem('settings-group', 'Settings', <SettingOutlined />, [
        createMenuItem('/settings', routeLink('/settings', 'General')),
        createMenuItem('/users', routeLink('/users', 'Users')),
        createMenuItem('/roles', routeLink('/roles', 'Roles')),
        createMenuItem('/stores', routeLink('/stores', 'Stores')),
      ])
    );

    return items;
  }, []);

  const handleMenuClick = useCallback((e) => {
    const { key } = e;
    setSelectedKeys([key]);

    const menuItems = getMenuItems();
    const parentKey = menuItems.find(item =>
      item.children?.some(child => child.key === key)
    )?.key;

    setOpenKeys(parentKey ? [parentKey] : []);
  }, [getMenuItems, setSelectedKeys, setOpenKeys]);

  const handleOpenChange = useCallback((keys) => {
    const latestOpenKey = keys.find(key => !openKeys.includes(key));
    setOpenKeys(latestOpenKey ? [latestOpenKey] : []);
  }, [openKeys, setOpenKeys]);

  const menuItems = useMemo(() => getMenuItems(), [getMenuItems]);

  return {
    menuItems,
    selectedKeys: [location.pathname],
    openKeys,
    handleMenuClick,
    handleOpenChange,
  };
};

export default useMenuManagement;
