import { memo } from 'react';
import { Drawer, Menu, Typography } from 'antd';
import { useThemeStore } from '@/Helpers/atom';

const { Text } = Typography;

// Memoized MenuSidebar - SparkCRM pattern
const MenuSidebar = memo(({
  menuItems,
  selectedKeys,
  openKeys,
  onOpenChange,
  onClick,
  collapsed,
  isMobile,
  drawerVisible,
  onDrawerClose,
}) => {
  const { theme } = useThemeStore();

  // Close drawer on mobile after menu click
  const handleMobileMenuClick = (e) => {
    onClick?.(e);
    if (e.key && e.key.startsWith('/')) {
      setTimeout(() => onDrawerClose?.(), 150);
    }
  };

  if (isMobile) {
    return (
      <Drawer
        placement="left"
        onClose={onDrawerClose}
        open={drawerVisible}
        width="70%"
        styles={{ body: { padding: 0 } }}
      >
        <Menu
          theme={theme}
          mode="inline"
          selectedKeys={selectedKeys}
          openKeys={openKeys}
          onOpenChange={onOpenChange}
          onClick={handleMobileMenuClick}
          items={menuItems}
        />
      </Drawer>
    );
  }

  return (
    <div style={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
      <div style={{ flex: 1, overflow: 'auto' }}>
        <Menu
          theme={theme}
          mode="inline"
          selectedKeys={selectedKeys}
          openKeys={openKeys}
          onOpenChange={onOpenChange}
          onClick={onClick}
          items={menuItems}
          inlineCollapsed={collapsed}
          style={{ border: 'none' }}
        />
      </div>
      {!collapsed && (
        <div style={{ padding: '10px', textAlign: 'center', borderTop: '1px solid #f0f0f0' }}>
          <Text type="secondary" style={{ fontSize: '12px' }}>
            Baqala POS v2.0
          </Text>
        </div>
      )}
    </div>
  );
}, (prevProps, nextProps) => {
  return (
    prevProps.selectedKeys === nextProps.selectedKeys &&
    prevProps.openKeys === nextProps.openKeys &&
    prevProps.menuItems === nextProps.menuItems &&
    prevProps.collapsed === nextProps.collapsed &&
    prevProps.isMobile === nextProps.isMobile &&
    prevProps.drawerVisible === nextProps.drawerVisible
  );
});

MenuSidebar.displayName = 'MenuSidebar';

export default MenuSidebar;
