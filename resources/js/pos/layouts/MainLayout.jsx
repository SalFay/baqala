import { useEffect } from 'react';
import { Outlet } from 'react-router-dom';
import { Avatar, Button, Dropdown, Flex, Layout, theme, Typography } from 'antd';
import {
  MenuFoldOutlined,
  MenuOutlined,
  MenuUnfoldOutlined,
  MoonOutlined,
  SunOutlined,
  LogoutOutlined,
} from '@ant-design/icons';
import { useThemeStore, useMenuStore, useUIStore } from '@pos/Helpers/atom';
import { useAuthStore } from '@pos/store/authStore';
import { authService } from '@pos/api/services/auth.service';
import useMenuManagement from '@pos/Hooks/useMenuManagement.jsx';
import MenuSidebar from '@pos/Components/Layout/MenuSidebar';

const { Header, Sider, Content } = Layout;
const { useToken } = theme;
const { Text } = Typography;

// Get initials - SparkCRM pattern
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

export default function MainLayout() {
  const { token } = useToken();
  const { theme: currentTheme, toggleTheme } = useThemeStore();
  const { collapsed, toggleCollapsed } = useMenuStore();
  const { isMobile, drawerVisible, setDrawerVisible, setIsMobile } = useUIStore();
  const user = useAuthStore((state) => state.user);
  const logout = useAuthStore((state) => state.logout);

  const {
    menuItems,
    selectedKeys,
    openKeys,
    handleMenuClick,
    handleOpenChange,
  } = useMenuManagement();

  // Handle window resize - SparkCRM pattern
  useEffect(() => {
    const handleResize = () => setIsMobile(window.innerWidth < 768);
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [setIsMobile]);

  const handleLogout = async () => {
    try {
      await authService.logout();
    } catch {
      logout();
      window.location.href = '/login';
    }
  };

  const mainBgColor = currentTheme === 'dark' ? '#141414' : '#ffffff';
  const siderBgColor = currentTheme === 'dark' ? '#1f1f1f' : '#fafafa';

  return (
    <Layout style={{ minHeight: '100vh' }}>
      {/* Fixed Header - SparkCRM pattern */}
      <Header
        style={{
          background: mainBgColor,
          borderBottom: `1px solid ${token.colorBorder}`,
          padding: 0,
          position: 'fixed',
          top: 0,
          width: '100%',
          zIndex: 999,
          height: 56,
        }}
      >
        <Flex
          align="center"
          justify="space-between"
          style={{ height: '100%', padding: '0 16px' }}
        >
          {/* Left - Logo & Toggle */}
          <Flex align="center" gap={16}>
            {isMobile ? (
              <Button
                type="text"
                icon={<MenuOutlined />}
                onClick={() => setDrawerVisible(true)}
              />
            ) : (
              <>
                <Text strong style={{ fontSize: 18, color: token.colorPrimary }}>
                  Baqala POS
                </Text>
                <Button
                  type="text"
                  icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
                  onClick={toggleCollapsed}
                />
              </>
            )}
          </Flex>

          {/* Right - User Menu */}
          <Dropdown
            trigger={['click']}
            menu={{
              items: [
                {
                  key: 'theme',
                  icon: currentTheme === 'dark' ? <SunOutlined /> : <MoonOutlined />,
                  label: currentTheme === 'dark' ? 'Light Mode' : 'Dark Mode',
                  onClick: toggleTheme,
                },
                { type: 'divider' },
                {
                  key: 'logout',
                  icon: <LogoutOutlined />,
                  label: 'Logout',
                  danger: true,
                  onClick: handleLogout,
                },
              ],
            }}
          >
            <Button type="text" style={{ padding: '4px 8px' }}>
              <Flex align="center" gap={8}>
                <Avatar size="small" style={{ backgroundColor: token.colorPrimary }}>
                  {getInitials(user?.name)}
                </Avatar>
                {!isMobile && <Text strong>{user?.name}</Text>}
              </Flex>
            </Button>
          </Dropdown>
        </Flex>
      </Header>

      {/* Main Layout */}
      <Layout style={{ marginTop: 56 }}>
        {/* Desktop Sidebar */}
        {!isMobile && (
          <Sider
            width={220}
            collapsedWidth={60}
            collapsed={collapsed}
            style={{
              position: 'fixed',
              height: 'calc(100vh - 56px)',
              left: 0,
              top: 56,
              backgroundColor: siderBgColor,
              borderRight: `1px solid ${token.colorBorder}`,
              overflow: 'auto',
            }}
          >
            <MenuSidebar
              menuItems={menuItems}
              selectedKeys={selectedKeys}
              openKeys={openKeys}
              onOpenChange={handleOpenChange}
              onClick={handleMenuClick}
              collapsed={collapsed}
              isMobile={false}
              drawerVisible={false}
              onDrawerClose={() => {}}
            />
          </Sider>
        )}

        {/* Mobile Drawer */}
        {isMobile && (
          <MenuSidebar
            menuItems={menuItems}
            selectedKeys={selectedKeys}
            openKeys={openKeys}
            onOpenChange={handleOpenChange}
            onClick={handleMenuClick}
            collapsed={false}
            isMobile={true}
            drawerVisible={drawerVisible}
            onDrawerClose={() => setDrawerVisible(false)}
          />
        )}

        {/* Content */}
        <Layout style={{ marginLeft: isMobile ? 0 : (collapsed ? 60 : 220) }}>
          <Content
            style={{
              padding: 16,
              minHeight: 'calc(100vh - 56px)',
              backgroundColor: token.colorBgContainer,
            }}
          >
            <Outlet />
          </Content>
        </Layout>
      </Layout>
    </Layout>
  );
}
