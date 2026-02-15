import { useCallback, useMemo } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    DashboardOutlined,
    ShoppingCartOutlined,
    AppstoreOutlined,
    TeamOutlined,
    ShopOutlined,
    SwapOutlined,
    FileTextOutlined,
    BarChartOutlined,
    SettingOutlined,
    InboxOutlined,
    ContactsOutlined,
    SafetyCertificateOutlined,
    UserOutlined,
} from '@ant-design/icons';
import { useRecoilState } from 'recoil';
import { menuOpenKeysAtom, menuSelectedKeysAtom } from '@/Helpers/atoms/uiAtom';

// Helper to create menu items - SparkCRM pattern
const createMenuItem = (key, label, icon, children) => ({
    key,
    icon,
    label,
    children,
});

// Helper for route links
const safeRouteLink = (path, label) => <Link href={path}>{label}</Link>;

const useMenuManagement = () => {
    const { url } = usePage();
    const [openKeys, setOpenKeys] = useRecoilState(menuOpenKeysAtom);
    const [selectedKeys, setSelectedKeys] = useRecoilState(menuSelectedKeysAtom);

    const getMenuItems = useCallback(() => {
        const items = [];

        // Dashboard
        items.push(
            createMenuItem('/dashboard', safeRouteLink('/dashboard', 'Dashboard'), <DashboardOutlined />)
        );

        // POS
        items.push(
            createMenuItem('/pos', safeRouteLink('/pos', 'Point of Sale'), <ShoppingCartOutlined />)
        );

        // Catalog
        items.push(
            createMenuItem('catalog', 'Catalog', <AppstoreOutlined />, [
                createMenuItem('/products', safeRouteLink('/products', 'Products')),
                createMenuItem('/categories', safeRouteLink('/categories', 'Categories')),
            ])
        );

        // Sales
        items.push(
            createMenuItem('sales', 'Sales', <FileTextOutlined />, [
                createMenuItem('/orders', safeRouteLink('/orders', 'Orders')),
                createMenuItem('/customers', safeRouteLink('/customers', 'Customers')),
            ])
        );

        // Inventory
        items.push(
            createMenuItem('inventory', 'Inventory', <InboxOutlined />, [
                createMenuItem('/inventory', safeRouteLink('/inventory', 'Stock Levels')),
                createMenuItem('/purchase-orders', safeRouteLink('/purchase-orders', 'Purchase Orders')),
                createMenuItem('/stock-transfers', safeRouteLink('/stock-transfers', 'Stock Transfers')),
                createMenuItem('/vendors', safeRouteLink('/vendors', 'Vendors')),
            ])
        );

        // Reports
        items.push(
            createMenuItem('/reports', safeRouteLink('/reports', 'Reports'), <BarChartOutlined />)
        );

        // Settings
        items.push(
            createMenuItem('settings', 'Settings', <SettingOutlined />, [
                createMenuItem('/settings', safeRouteLink('/settings', 'General')),
                createMenuItem('/users', safeRouteLink('/users', 'Users')),
                createMenuItem('/roles', safeRouteLink('/roles', 'Roles')),
                createMenuItem('/stores', safeRouteLink('/stores', 'Stores')),
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
        selectedKeys: [url],
        openKeys,
        handleMenuClick,
        handleOpenChange,
    };
};

export default useMenuManagement;
