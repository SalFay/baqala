import { useEffect } from 'react';
import { Layout, theme } from 'antd';
import { Toaster } from 'react-hot-toast';
import { useRecoilState, useRecoilValue } from 'recoil';
import {
    sidebarCollapsedAtom,
    isMobileAtom,
    drawerVisibleAtom,
    themeAtom,
} from '@/Helpers/atoms/uiAtom';
import { useTheme } from '@/Hooks/useTheme';
import GlobalHeader from '@/Components/Layout/GlobalHeader';
import MenuSidebar from '@/Components/Layout/MenuSidebar';
import useMenuManagement from '@/Hooks/useMenuManagement';

const { Header, Sider, Content } = Layout;
const { useToken } = theme;

/**
 * PersistentLayout - SparkCRM Pattern
 * Global wrapper that persists across page navigations
 */
export default function PersistentLayout({ children }) {
    const { token } = useToken();
    const { colors } = useTheme();
    const currentTheme = useRecoilValue(themeAtom);
    const [collapsed, setCollapsed] = useRecoilState(sidebarCollapsedAtom);
    const [isMobile, setIsMobile] = useRecoilState(isMobileAtom);
    const [drawerVisible, setDrawerVisible] = useRecoilState(drawerVisibleAtom);

    const {
        menuItems,
        selectedKeys,
        openKeys,
        handleMenuClick,
        handleOpenChange,
    } = useMenuManagement();

    // Handle window resize
    useEffect(() => {
        const handleResize = () => setIsMobile(window.innerWidth < 768);
        window.addEventListener('resize', handleResize);
        handleResize(); // Initial check
        return () => window.removeEventListener('resize', handleResize);
    }, [setIsMobile]);

    const mainBgColor = currentTheme === 'dark' ? '#141414' : '#ffffff';
    const siderBgColor = currentTheme === 'dark' ? '#1f1f1f' : '#fafafa';

    return (
        <>
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
                    <GlobalHeader />
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
                                siderColor={siderBgColor}
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
                            siderColor={siderBgColor}
                        />
                    )}

                    {/* Content */}
                    <Layout style={{ marginLeft: isMobile ? 0 : (collapsed ? 60 : 220) }}>
                        <Content
                            style={{
                                padding: 16,
                                minHeight: 'calc(100vh - 56px)',
                                backgroundColor: colors.bgSecondary,
                            }}
                        >
                            {children}
                        </Content>
                    </Layout>
                </Layout>
            </Layout>

            <Toaster position="top-right" />
        </>
    );
}
