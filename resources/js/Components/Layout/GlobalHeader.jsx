import { Avatar, Button, Dropdown, Flex, Typography } from 'antd';
import {
    MenuFoldOutlined,
    MenuOutlined,
    MenuUnfoldOutlined,
    LogoutOutlined,
    UserOutlined,
} from '@ant-design/icons';
import { router, usePage } from '@inertiajs/react';
import { useRecoilState, useRecoilValue } from 'recoil';
import { sidebarCollapsedAtom, isMobileAtom, drawerVisibleAtom } from '@/Helpers/atoms/uiAtom';
import { useTheme } from '@/Hooks/useTheme';
import ThemeToggle from '@/Components/ThemeToggle';

const { Text } = Typography;

// Get initials - SparkCRM pattern
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

export default function GlobalHeader() {
    const { auth } = usePage().props;
    const { colors } = useTheme();
    const [collapsed, setCollapsed] = useRecoilState(sidebarCollapsedAtom);
    const isMobile = useRecoilValue(isMobileAtom);
    const [drawerVisible, setDrawerVisible] = useRecoilState(drawerVisibleAtom);

    const handleLogout = () => {
        router.post('/logout');
    };

    const userMenuItems = [
        {
            key: 'profile',
            icon: <UserOutlined />,
            label: 'Profile',
        },
        { type: 'divider' },
        {
            key: 'logout',
            icon: <LogoutOutlined />,
            label: 'Logout',
            danger: true,
            onClick: handleLogout,
        },
    ];

    return (
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
                        style={{ color: colors.textPrimary }}
                    />
                ) : (
                    <>
                        <Text strong style={{ fontSize: 18, color: colors.primary }}>
                            Baqala POS
                        </Text>
                        <Button
                            type="text"
                            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
                            onClick={() => setCollapsed(!collapsed)}
                            style={{ color: colors.textPrimary }}
                        />
                    </>
                )}
            </Flex>

            {/* Right - Actions & User */}
            <Flex align="center" gap={12}>
                <ThemeToggle />

                <Dropdown
                    trigger={['click']}
                    menu={{ items: userMenuItems }}
                    placement="bottomRight"
                >
                    <Button type="text" style={{ padding: '4px 8px' }}>
                        <Flex align="center" gap={8}>
                            <Avatar
                                size="small"
                                style={{ backgroundColor: colors.primary }}
                            >
                                {getInitials(auth?.user?.name || auth?.user?.first_name)}
                            </Avatar>
                            {!isMobile && (
                                <Text strong style={{ color: colors.textPrimary }}>
                                    {auth?.user?.name || auth?.user?.first_name || 'User'}
                                </Text>
                            )}
                        </Flex>
                    </Button>
                </Dropdown>
            </Flex>
        </Flex>
    );
}
