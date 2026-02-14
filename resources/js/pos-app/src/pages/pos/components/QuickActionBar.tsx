import React from 'react';
import { Button, Space, Badge, Tooltip } from 'antd';
import {
  QuestionCircleOutlined,
  PauseCircleOutlined,
  RedoOutlined,
  PercentageOutlined,
  UserOutlined,
  RollbackOutlined,
  DeleteOutlined,
  CreditCardOutlined,
  CloseCircleOutlined,
  CloudSyncOutlined,
  WifiOutlined,
  DisconnectOutlined,
} from '@ant-design/icons';
import { useTheme } from '@/contexts/ThemeContext';
import { useOfflineStore } from '@/store/offlineStore';
import { useSyncStore } from '@/store/syncStore';

interface QuickAction {
  key: string;
  label: string;
  shortcut: string;
  icon: React.ReactNode;
  onClick: () => void;
  disabled?: boolean;
  badge?: number;
  danger?: boolean;
}

interface QuickActionBarProps {
  onHelp: () => void;
  onHold: () => void;
  onRecall: () => void;
  onDiscount: () => void;
  onCustomer: () => void;
  onReturn: () => void;
  onVoid: () => void;
  onCheckout: () => void;
  onCancel: () => void;
  onSync?: () => void;
  heldOrdersCount?: number;
  hasItems?: boolean;
  hasCustomer?: boolean;
}

export function QuickActionBar({
  onHelp,
  onHold,
  onRecall,
  onDiscount,
  onCustomer,
  onReturn,
  onVoid,
  onCheckout,
  onCancel,
  onSync,
  heldOrdersCount = 0,
  hasItems = false,
  hasCustomer = false,
}: QuickActionBarProps) {
  const { isDark, colors } = useTheme();
  const { isOnline, pendingOrderCount } = useOfflineStore();
  const { isSyncing } = useSyncStore();

  const actions: QuickAction[] = [
    {
      key: 'help',
      label: 'Help',
      shortcut: 'F1',
      icon: <QuestionCircleOutlined />,
      onClick: onHelp,
    },
    {
      key: 'hold',
      label: 'Hold',
      shortcut: 'F2',
      icon: <PauseCircleOutlined />,
      onClick: onHold,
      disabled: !hasItems,
    },
    {
      key: 'recall',
      label: 'Recall',
      shortcut: 'F3',
      icon: <RedoOutlined />,
      onClick: onRecall,
      badge: heldOrdersCount,
    },
    {
      key: 'discount',
      label: 'Discount',
      shortcut: 'F4',
      icon: <PercentageOutlined />,
      onClick: onDiscount,
      disabled: !hasItems,
    },
    {
      key: 'customer',
      label: hasCustomer ? 'Change Customer' : 'Customer',
      shortcut: 'F5',
      icon: <UserOutlined />,
      onClick: onCustomer,
    },
    {
      key: 'return',
      label: 'Return',
      shortcut: 'F7',
      icon: <RollbackOutlined />,
      onClick: onReturn,
    },
    {
      key: 'void',
      label: 'Void Last',
      shortcut: 'F8',
      icon: <DeleteOutlined />,
      onClick: onVoid,
      disabled: !hasItems,
      danger: true,
    },
    {
      key: 'checkout',
      label: 'Checkout',
      shortcut: 'F9',
      icon: <CreditCardOutlined />,
      onClick: onCheckout,
      disabled: !hasItems,
    },
    {
      key: 'cancel',
      label: 'Clear',
      shortcut: 'ESC',
      icon: <CloseCircleOutlined />,
      onClick: onCancel,
      disabled: !hasItems,
      danger: true,
    },
  ];

  return (
    <div
      style={{
        backgroundColor: isDark ? '#1f1f1f' : '#fff',
        borderTop: `1px solid ${isDark ? '#303030' : '#f0f0f0'}`,
        padding: '8px 16px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
      }}
    >
      {/* Left Actions */}
      <Space size={4}>
        {actions.map(action => (
          <Tooltip
            key={action.key}
            title={`${action.label} (${action.shortcut})`}
            placement="top"
          >
            <Badge count={action.badge} size="small" offset={[-5, 5]}>
              <Button
                type={action.key === 'checkout' ? 'primary' : 'default'}
                icon={action.icon}
                onClick={action.onClick}
                disabled={action.disabled}
                danger={action.danger}
                size="middle"
                style={{
                  minWidth: 80,
                  backgroundColor: action.key === 'checkout' && !action.disabled
                    ? colors.primaryColor
                    : undefined,
                }}
              >
                <span style={{ fontSize: 12 }}>{action.label}</span>
                <span
                  style={{
                    fontSize: 10,
                    opacity: 0.7,
                    marginLeft: 4,
                  }}
                >
                  {action.shortcut}
                </span>
              </Button>
            </Badge>
          </Tooltip>
        ))}
      </Space>

      {/* Right - Sync/Online Status */}
      <Space size={8}>
        {/* Pending Orders Badge */}
        {pendingOrderCount > 0 && (
          <Badge count={pendingOrderCount} title={`${pendingOrderCount} pending orders`}>
            <Button
              icon={<CloudSyncOutlined spin={isSyncing} />}
              onClick={onSync}
              loading={isSyncing}
              disabled={!isOnline}
            >
              Sync
            </Button>
          </Badge>
        )}

        {/* Online/Offline Indicator */}
        <Tooltip title={isOnline ? 'Online' : 'Offline Mode'}>
          <Button
            type="text"
            icon={isOnline ? <WifiOutlined /> : <DisconnectOutlined />}
            style={{
              color: isOnline ? '#52c41a' : '#faad14',
            }}
          >
            {isOnline ? 'Online' : 'Offline'}
          </Button>
        </Tooltip>
      </Space>
    </div>
  );
}

export default QuickActionBar;
