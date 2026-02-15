/**
 * GlobalPageHeader Component (SparkCRM Pattern)
 *
 * A persistent page header with:
 * - Title and breadcrumbs
 * - Expandable search
 * - Action buttons with permissions
 * - Theme-aware styling
 * - Mobile responsive
 */

import { useState, useMemo } from 'react';
import { Breadcrumb, Flex, Input, Space, theme, Tooltip, Typography, Button } from 'antd';
import { Link, router } from '@inertiajs/react';
import { SearchOutlined, CloseOutlined } from '@ant-design/icons';
import { useTheme } from '@/Hooks/useTheme';

const { Title, Text } = Typography;

export default function GlobalPageHeader({
    title,
    parentPageTitle,
    parentPageRoute = 'dashboard',
    actionButtons = [],
    extraContent = null,
    breadcrumbItems = null,
    searchConfig = null, // { value, onChange, placeholder, resultText }
    style = {},
}) {
    const { token } = theme.useToken();
    const { isDark } = useTheme();
    const [searchExpanded, setSearchExpanded] = useState(false);

    // Build breadcrumb items
    const breadcrumbs = useMemo(() => {
        const defaultItems = [
            {
                title: (
                    <Link href={`/${parentPageRoute}`}>
                        {parentPageTitle}
                    </Link>
                ),
            },
            {
                title,
            },
        ];

        if (!breadcrumbItems) return defaultItems;

        return [
            {
                title: (
                    <Link href={`/${parentPageRoute}`}>
                        {parentPageTitle}
                    </Link>
                ),
            },
            ...breadcrumbItems.map((item, index) => {
                // If it's the last item or has no route, make it non-clickable
                if (!item.route || index === breadcrumbItems.length - 1) {
                    return { title: item.title };
                }
                // Otherwise make it clickable
                return {
                    title: (
                        <Link href={item.route}>
                            {item.title}
                        </Link>
                    ),
                };
            }),
        ];
    }, [title, parentPageTitle, parentPageRoute, breadcrumbItems]);

    // Determine if we're on mobile (simple check, could use a hook)
    const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;

    return (
        <Flex
            vertical={isMobile}
            justify="space-between"
            align={isMobile ? 'stretch' : 'center'}
            gap={isMobile ? 12 : 0}
            style={{
                padding: isMobile ? '12px' : '10px 20px',
                background: isDark ? '#191919' : '#f6f6f6',
                border: `1px solid ${token.colorBorderSecondary}`,
                margin: isMobile ? '10px' : '0 0 12px 0',
                borderRadius: '10px',
                ...style,
            }}
        >
            <Space direction="vertical" size={0} style={{ flex: isMobile ? 1 : 'auto' }}>
                <Title
                    level={isMobile ? 5 : 4}
                    style={{
                        padding: 0,
                        margin: 0,
                        fontSize: isMobile ? '16px' : undefined,
                        wordBreak: 'break-word',
                    }}
                >
                    {title}
                </Title>
                <Breadcrumb
                    style={{ padding: 0, fontSize: isMobile ? '12px' : undefined }}
                    items={breadcrumbs}
                />
            </Space>

            <Flex
                gap={8}
                wrap="wrap"
                justify={isMobile ? 'flex-start' : 'flex-end'}
                align="center"
                style={{ width: isMobile ? '100%' : 'auto' }}
            >
                {/* Expandable Search */}
                {searchConfig && (
                    <Flex align="center" gap={8}>
                        {searchExpanded ? (
                            <>
                                <Input
                                    placeholder={searchConfig.placeholder || 'Search...'}
                                    value={searchConfig.value}
                                    onChange={searchConfig.onChange}
                                    allowClear
                                    style={{ width: isMobile ? '100%' : 280 }}
                                    autoFocus
                                />
                                <Button
                                    icon={<CloseOutlined />}
                                    onClick={() => {
                                        setSearchExpanded(false);
                                        searchConfig.onChange?.({ target: { value: '' } });
                                    }}
                                    size="middle"
                                />
                            </>
                        ) : (
                            <Tooltip title="Search">
                                <Button
                                    icon={<SearchOutlined />}
                                    onClick={() => setSearchExpanded(true)}
                                    size="middle"
                                />
                            </Tooltip>
                        )}
                        {searchExpanded && searchConfig.resultText && (
                            <Text
                                type="secondary"
                                style={{ fontSize: '12px', whiteSpace: 'nowrap' }}
                            >
                                {searchConfig.resultText}
                            </Text>
                        )}
                    </Flex>
                )}

                {/* Extra Content */}
                {extraContent}

                {/* Action Buttons */}
                {actionButtons.map((button, index) => {
                    // Check permissions
                    const hasPermission = button.hasPermission !== false;
                    const showButton = button.showButton !== false;

                    if (!hasPermission || !showButton) return null;

                    // Custom button
                    if (button.customButton) {
                        return <span key={index}>{button.customButton}</span>;
                    }

                    const buttonContent = (
                        <Button
                            icon={button.icon}
                            onClick={button.link ? undefined : button.onClick}
                            type={button.type}
                            disabled={button.disabled}
                            danger={button.danger}
                            style={{
                                width: isMobile ? '100%' : 'auto',
                                minWidth: isMobile ? 'auto' : undefined,
                            }}
                            size="middle"
                        >
                            {isMobile && button.mobileTitle
                                ? button.mobileTitle
                                : button.title}
                        </Button>
                    );

                    const wrappedButton = button.link ? (
                        <Link
                            key={index}
                            href={button.link}
                            style={{ flex: isMobile ? '1 1 auto' : 'none' }}
                        >
                            {buttonContent}
                        </Link>
                    ) : (
                        <span key={index} style={{ flex: isMobile ? '1 1 auto' : 'none' }}>
                            {buttonContent}
                        </span>
                    );

                    return button.tooltipTitle ? (
                        <Tooltip key={index} title={button.tooltipTitle}>
                            {wrappedButton}
                        </Tooltip>
                    ) : (
                        wrappedButton
                    );
                })}
            </Flex>
        </Flex>
    );
}
