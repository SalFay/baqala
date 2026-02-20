import { useEffect, useState } from 'react'
import { Avatar, Button, Dropdown, Flex, Layout, theme, Typography } from 'antd'
import {
  MenuFoldOutlined,
  MenuOutlined,
  MenuUnfoldOutlined,
  MoonOutlined,
  SunOutlined,
  LogoutOutlined,
} from '@ant-design/icons'
import { router, usePage } from '@inertiajs/react'
import { useRecoilState, useSetRecoilState } from 'recoil'
import { themeAtom, userAtom, permissionsAtom, menuStateAtom } from '@/Helpers/atom'
import useMenuManagement from '@/Hooks/useMenuManagement'
import MenuSidebar from '@/Components/Layout/MenuSidebar'

const { Header, Sider, Content } = Layout
const { useToken } = theme
const { Text } = Typography

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const PersistentLayout = ({ children }) => {
  const { auth } = usePage().props
  const authUser = auth?.user
  const { token } = useToken()
  const [currentTheme, setTheme] = useRecoilState(themeAtom)
  const [menuState, setMenuState] = useRecoilState(menuStateAtom)
  const setUser = useSetRecoilState(userAtom)
  const setPermissions = useSetRecoilState(permissionsAtom)
  const [isMobile, setIsMobile] = useState(false)
  const [drawerVisible, setDrawerVisible] = useState(false)

  const {
    menuItems,
    selectedKeys,
    openKeys,
    handleMenuClick,
    handleOpenChange,
  } = useMenuManagement()

  useEffect(() => {
    if (authUser) {
      setUser(authUser)
      setPermissions(authUser.permissions || [])
    }
  }, [authUser, setUser, setPermissions])

  useEffect(() => {
    const handleResize = () => setIsMobile(window.innerWidth < 768)
    handleResize()
    window.addEventListener('resize', handleResize)
    return () => window.removeEventListener('resize', handleResize)
  }, [])

  const handleLogout = () => {
    router.post(route('logout'))
  }

  const handleThemeSwitch = () => {
    setTheme(currentTheme === 'dark' ? 'light' : 'dark')
  }

  const toggleCollapsed = () => {
    setMenuState(prev => ({ ...prev, collapsed: !prev.collapsed }))
  }

  const { collapsed } = menuState
  const mainBgColor = currentTheme === 'dark' ? '#141414' : '#ffffff'
  const siderBgColor = currentTheme === 'dark' ? '#1f1f1f' : '#fafafa'

  return (
    <Layout style={{ minHeight: '100vh' }}>
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

          <Dropdown
            trigger={['click']}
            menu={{
              items: [
                {
                  key: 'theme',
                  icon: currentTheme === 'dark' ? <SunOutlined /> : <MoonOutlined />,
                  label: currentTheme === 'dark' ? 'Light Mode' : 'Dark Mode',
                  onClick: handleThemeSwitch,
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
                  {getInitials(authUser?.name)}
                </Avatar>
                {!isMobile && <Text strong>{authUser?.name}</Text>}
              </Flex>
            </Button>
          </Dropdown>
        </Flex>
      </Header>

      <Layout style={{ marginTop: 56 }}>
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

        <Layout style={{ marginLeft: isMobile ? 0 : (collapsed ? 60 : 220) }}>
          <Content
            style={{
              padding: 16,
              minHeight: 'calc(100vh - 56px)',
              backgroundColor: token.colorBgContainer,
            }}
          >
            {children}
          </Content>
        </Layout>
      </Layout>
    </Layout>
  )
}

export default PersistentLayout
