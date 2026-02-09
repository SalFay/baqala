import { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { Layout, Menu, Avatar, Dropdown, Button, theme } from 'antd';
import {
  DashboardOutlined,
  ShoppingCartOutlined,
  AppstoreOutlined,
  TagsOutlined,
  UserOutlined,
  FileTextOutlined,
  InboxOutlined,
  BarChartOutlined,
  SettingOutlined,
  LogoutOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  ShopOutlined,
  RollbackOutlined,
  TeamOutlined,
  SafetyCertificateOutlined,
  SwapOutlined,
  ShoppingOutlined,
  ContactsOutlined,
} from '@ant-design/icons';
import { useAuthStore } from '../store/authStore';

const { Header, Sider, Content } = Layout;

const menuItems = [
  { key: '/dashboard', icon: <DashboardOutlined />, label: 'Dashboard' },
  { key: '/pos', icon: <ShoppingCartOutlined />, label: 'POS Terminal' },
  {
    key: 'catalog',
    icon: <AppstoreOutlined />,
    label: 'Catalog',
    children: [
      { key: '/products', icon: <AppstoreOutlined />, label: 'Products' },
      { key: '/categories', icon: <TagsOutlined />, label: 'Categories' },
    ],
  },
  {
    key: 'sales',
    icon: <FileTextOutlined />,
    label: 'Sales',
    children: [
      { key: '/orders', icon: <FileTextOutlined />, label: 'Orders' },
      { key: '/returns', icon: <RollbackOutlined />, label: 'Returns' },
      { key: '/customers', icon: <UserOutlined />, label: 'Customers' },
    ],
  },
  {
    key: 'inventory-group',
    icon: <InboxOutlined />,
    label: 'Inventory',
    children: [
      { key: '/inventory', icon: <InboxOutlined />, label: 'Stock Levels' },
      { key: '/purchase-orders', icon: <ShoppingOutlined />, label: 'Purchase Orders' },
      { key: '/stock-transfers', icon: <SwapOutlined />, label: 'Stock Transfers' },
      { key: '/vendors', icon: <ContactsOutlined />, label: 'Vendors' },
    ],
  },
  { key: '/reports', icon: <BarChartOutlined />, label: 'Reports' },
  {
    key: 'admin',
    icon: <SettingOutlined />,
    label: 'Admin',
    children: [
      { key: '/settings', icon: <SettingOutlined />, label: 'Settings' },
      { key: '/users', icon: <TeamOutlined />, label: 'Users' },
      { key: '/roles', icon: <SafetyCertificateOutlined />, label: 'Roles' },
      { key: '/stores', icon: <ShopOutlined />, label: 'Stores' },
    ],
  },
];

export default function MainLayout() {
  const [collapsed, setCollapsed] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { user, logout } = useAuthStore();
  const { token } = theme.useToken();

  const handleMenuClick = ({ key }: { key: string }) => {
    if (key === '/pos') {
      window.location.href = '/pos';
    } else {
      navigate(key);
    }
  };

  const handleLogout = async () => {
    logout();
    navigate('/login');
  };

  const userMenuItems = [
    {
      key: 'profile',
      icon: <UserOutlined />,
      label: 'Profile',
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
        style={{
          borderRight: `1px solid ${token.colorBorderSecondary}`,
        }}
      >
        <div
          style={{
            height: 64,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            borderBottom: `1px solid ${token.colorBorderSecondary}`,
          }}
        >
          <ShopOutlined style={{ fontSize: 24, color: token.colorPrimary }} />
          {!collapsed && (
            <span
              style={{
                marginLeft: 8,
                fontSize: 18,
                fontWeight: 600,
                color: token.colorPrimary,
              }}
            >
              Baqala POS
            </span>
          )}
        </div>
        <Menu
          mode="inline"
          selectedKeys={[location.pathname]}
          items={menuItems}
          onClick={handleMenuClick}
          style={{ borderRight: 0 }}
        />
      </Sider>

      <Layout>
        <Header
          style={{
            padding: '0 24px',
            background: token.colorBgContainer,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            borderBottom: `1px solid ${token.colorBorderSecondary}`,
          }}
        >
          <Button
            type="text"
            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
            onClick={() => setCollapsed(!collapsed)}
            style={{ fontSize: 16 }}
          />

          <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
            <div style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 8 }}>
              <Avatar icon={<UserOutlined />} />
              <span>{user?.name}</span>
            </div>
          </Dropdown>
        </Header>

        <Content
          style={{
            margin: 24,
            padding: 24,
            background: token.colorBgContainer,
            borderRadius: token.borderRadius,
            overflow: 'auto',
          }}
        >
          <Outlet />
        </Content>
      </Layout>
    </Layout>
  );
}
