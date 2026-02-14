import { useState } from 'react';
import { Layout, Menu, Avatar, Dropdown, Space, Button } from 'antd';
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
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    UserOutlined,
    LogoutOutlined,
    InboxOutlined,
    ContactsOutlined,
} from '@ant-design/icons';
import { Link, usePage, router } from '@inertiajs/react';
import { useRecoilState } from 'recoil';
import { sidebarCollapsedAtom } from '@/Helpers/atoms/uiAtom';

const { Header, Sider, Content } = Layout;

const menuItems = [
    { key: 'dashboard', icon: <DashboardOutlined />, label: 'Dashboard', path: '/dashboard' },
    { key: 'pos', icon: <ShoppingCartOutlined />, label: 'POS', path: '/pos' },
    { key: 'products', icon: <AppstoreOutlined />, label: 'Products', path: '/products' },
    { key: 'orders', icon: <FileTextOutlined />, label: 'Orders', path: '/orders' },
    { key: 'customers', icon: <TeamOutlined />, label: 'Customers', path: '/customers' },
    { key: 'vendors', icon: <ContactsOutlined />, label: 'Vendors', path: '/vendors' },
    { key: 'inventory', icon: <InboxOutlined />, label: 'Inventory', path: '/inventory' },
    { key: 'purchase-orders', icon: <ShopOutlined />, label: 'Purchase Orders', path: '/purchase-orders' },
    { key: 'stock-transfers', icon: <SwapOutlined />, label: 'Stock Transfers', path: '/stock-transfers' },
    { key: 'reports', icon: <BarChartOutlined />, label: 'Reports', path: '/reports' },
    { key: 'settings', icon: <SettingOutlined />, label: 'Settings', path: '/settings' },
];

export default function MainLayout({ children }) {
    const [collapsed, setCollapsed] = useRecoilState(sidebarCollapsedAtom);
    const { auth } = usePage().props;

    const handleLogout = () => {
        router.post('/logout');
    };

    const userMenuItems = [
        {
            key: 'profile',
            icon: <UserOutlined />,
            label: 'Profile',
        },
        {
            type: 'divider',
        },
        {
            key: 'logout',
            icon: <LogoutOutlined />,
            label: 'Logout',
            onClick: handleLogout,
        },
    ];

    return (
        <Layout style={{ minHeight: '100vh' }}>
            <Sider
                trigger={null}
                collapsible
                collapsed={collapsed}
                theme="light"
                width={240}
                style={{
                    overflow: 'auto',
                    height: '100vh',
                    position: 'fixed',
                    left: 0,
                    top: 0,
                    bottom: 0,
                    borderRight: '1px solid #f0f0f0',
                }}
            >
                <div style={{
                    height: 64,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    borderBottom: '1px solid #f0f0f0',
                }}>
                    <h2 style={{ margin: 0, color: '#1890ff' }}>
                        {collapsed ? 'B' : 'Baqala POS'}
                    </h2>
                </div>
                <Menu
                    mode="inline"
                    defaultSelectedKeys={['dashboard']}
                    items={menuItems.map(item => ({
                        key: item.key,
                        icon: item.icon,
                        label: <Link href={item.path}>{item.label}</Link>,
                    }))}
                    style={{ borderRight: 0 }}
                />
            </Sider>
            <Layout style={{ marginLeft: collapsed ? 80 : 240, transition: 'margin-left 0.2s' }}>
                <Header
                    style={{
                        padding: '0 24px',
                        background: '#fff',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        borderBottom: '1px solid #f0f0f0',
                        position: 'sticky',
                        top: 0,
                        zIndex: 1,
                    }}
                >
                    <Button
                        type="text"
                        icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
                        onClick={() => setCollapsed(!collapsed)}
                        style={{ fontSize: 16 }}
                    />
                    <Space>
                        <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
                            <Space style={{ cursor: 'pointer' }}>
                                <Avatar icon={<UserOutlined />} />
                                <span>{auth?.user?.first_name || 'User'}</span>
                            </Space>
                        </Dropdown>
                    </Space>
                </Header>
                <Content
                    style={{
                        margin: 24,
                        padding: 24,
                        background: '#fff',
                        borderRadius: 8,
                        minHeight: 'calc(100vh - 112px)',
                    }}
                >
                    {children}
                </Content>
            </Layout>
        </Layout>
    );
}
