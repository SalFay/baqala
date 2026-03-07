import { useCallback, useMemo } from 'react'
import { Link, usePage } from '@inertiajs/react'
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
  WalletOutlined,
  FileTextOutlined,
} from '@ant-design/icons'
import { useRecoilState } from 'recoil'
import { menuStateAtom } from '@/Helpers/atom'

// Helper to create menu items - SparkCRM pattern
const createMenuItem = (key, label, icon, children) => ({
  key,
  icon,
  label,
  children,
})

// Helper for route links
const routeLink = (path, label) => <Link href={path}>{label}</Link>

const useMenuManagement = () => {
  const { url } = usePage()
  const [menuState, setMenuState] = useRecoilState(menuStateAtom)
  const { openKeys } = menuState

  const getMenuItems = useCallback(() => {
    const items = []

    // Dashboard
    items.push(
      createMenuItem('/dashboard', routeLink('/dashboard', 'Dashboard'), <DashboardOutlined />)
    )

    // POS
    items.push(
      createMenuItem('/pos', routeLink('/pos', 'Point of Sale'), <ShoppingCartOutlined />)
    )

    // Products & Categories
    items.push(
      createMenuItem('products-group', 'Products', <AppstoreOutlined />, [
        createMenuItem('/products', routeLink('/products', 'All Products')),
        createMenuItem('/categories', routeLink('/categories', 'Categories')),
      ])
    )

    // Customers
    items.push(
      createMenuItem('/customers', routeLink('/customers', 'Customers'), <UserOutlined />)
    )

    // Orders
    items.push(
      createMenuItem('orders-group', 'Orders', <ShoppingOutlined />, [
        createMenuItem('/orders', routeLink('/orders', 'All Orders')),
        createMenuItem('/returns', routeLink('/returns', 'Returns')),
      ])
    )

    // Inventory
    items.push(
      createMenuItem('inventory-group', 'Inventory', <InboxOutlined />, [
        createMenuItem('/inventory', routeLink('/inventory', 'Stock Levels')),
        createMenuItem('/stock-takes', routeLink('/stock-takes', 'Stock Takes')),
        createMenuItem('/purchase-orders', routeLink('/purchase-orders', 'Purchase Orders')),
        createMenuItem('/stock-transfers', routeLink('/stock-transfers', 'Stock Transfers')),
      ])
    )

    // Vendors
    items.push(
      createMenuItem('/vendors', routeLink('/vendors', 'Vendors'), <SwapOutlined />)
    )

    // Reports
    items.push(
      createMenuItem('/reports', routeLink('/reports', 'Reports'), <BarChartOutlined />)
    )

    // Expenses
    items.push(
      createMenuItem('/expenses', routeLink('/expenses', 'Expenses'), <WalletOutlined />)
    )

    // Statements
    items.push(
      createMenuItem('/statements', routeLink('/statements', 'Statements'), <FileTextOutlined />)
    )

    // Settings
    items.push(
      createMenuItem('settings-group', 'Settings', <SettingOutlined />, [
        createMenuItem('/settings', routeLink('/settings', 'General')),
        createMenuItem('/users', routeLink('/users', 'Users')),
        createMenuItem('/roles', routeLink('/roles', 'Roles')),
        createMenuItem('/stores', routeLink('/stores', 'Stores')),
        createMenuItem('/settings/time-pricing', routeLink('/settings/time-pricing', 'Time Pricing')),
        createMenuItem('/coupons', routeLink('/coupons', 'Coupons')),
      ])
    )

    return items
  }, [])

  const handleMenuClick = useCallback((e) => {
    const { key } = e
    setMenuState(prev => ({ ...prev, selectedKeys: [key] }))

    const menuItems = getMenuItems()
    const parentKey = menuItems.find(item =>
      item.children?.some(child => child.key === key)
    )?.key

    setMenuState(prev => ({ ...prev, openKeys: parentKey ? [parentKey] : [] }))
  }, [getMenuItems, setMenuState])

  const handleOpenChange = useCallback((keys) => {
    const latestOpenKey = keys.find(key => !openKeys.includes(key))
    setMenuState(prev => ({ ...prev, openKeys: latestOpenKey ? [latestOpenKey] : [] }))
  }, [openKeys, setMenuState])

  const menuItems = useMemo(() => getMenuItems(), [getMenuItems])

  // Get current path from URL
  const currentPath = '/' + (url?.split('?')[0] || '').replace(/^\//, '')

  return {
    menuItems,
    selectedKeys: [currentPath],
    openKeys,
    handleMenuClick,
    handleOpenChange,
  }
}

export default useMenuManagement
