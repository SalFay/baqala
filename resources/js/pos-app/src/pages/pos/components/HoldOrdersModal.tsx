import { useEffect } from 'react';
import { Modal, List, Button, Empty, Typography, Tag } from 'antd';
import { PlayCircleOutlined } from '@ant-design/icons';
import { useCartStore } from '../../../store/cartStore';
import dayjs from 'dayjs';

const { Text } = Typography;

interface HoldOrdersModalProps {
  open: boolean;
  onClose: () => void;
}

export default function HoldOrdersModal({ open, onClose }: HoldOrdersModalProps) {
  const { heldCarts, fetchHeldCarts, restoreHeldCart, isLoading } = useCartStore();

  useEffect(() => {
    if (open) {
      fetchHeldCarts();
    }
  }, [open, fetchHeldCarts]);

  const handleRestore = async (cartId: number) => {
    await restoreHeldCart(cartId);
    onClose();
  };

  return (
    <Modal
      title="Held Orders"
      open={open}
      onCancel={onClose}
      footer={null}
      width={600}
    >
      {heldCarts.length === 0 ? (
        <Empty description="No held orders" />
      ) : (
        <List
          dataSource={heldCarts}
          loading={isLoading}
          renderItem={(cart) => (
            <List.Item
              actions={[
                <Button
                  type="primary"
                  icon={<PlayCircleOutlined />}
                  onClick={() => handleRestore(cart.id)}
                >
                  Restore
                </Button>,
              ]}
            >
              <List.Item.Meta
                title={
                  <span>
                    {cart.hold_name || 'Unnamed'}
                    <Tag color="blue" style={{ marginLeft: 8 }}>
                      {cart.items?.length || 0} items
                    </Tag>
                  </span>
                }
                description={
                  <div>
                    <Text type="secondary">
                      {cart.customer?.full_name || 'Walk-in Customer'}
                    </Text>
                    <br />
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      {cart.held_at && dayjs(cart.held_at).format('MMM D, h:mm A')}
                    </Text>
                  </div>
                }
              />
              <Text strong style={{ fontSize: 16 }}>
                {(cart.total || 0).toFixed(2)} SAR
              </Text>
            </List.Item>
          )}
        />
      )}
    </Modal>
  );
}
