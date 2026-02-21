import { Modal, List, Typography, Button, Empty, Space, Tag } from 'antd'
import { ShoppingCartOutlined, UserOutlined } from '@ant-design/icons'
import { formatCurrency, formatRelativeTime } from '@/Helpers/formatters'

const { Text } = Typography

export default function HeldCartsModal({
  open,
  onClose,
  heldCarts,
  onRestore,
  loading,
}) {
  return (
    <Modal
      title="Held Carts"
      open={open}
      onCancel={onClose}
      footer={null}
      width={500}
    >
      {!heldCarts || heldCarts.length === 0 ? (
        <Empty description="No held carts" style={{ padding: 40 }} />
      ) : (
        <List
          dataSource={heldCarts}
          renderItem={(cart) => (
            <List.Item
              actions={[
                <Button
                  key="restore"
                  type="primary"
                  onClick={() => {
                    onRestore(cart.id)
                    onClose()
                  }}
                  loading={loading}
                >
                  Restore
                </Button>,
              ]}
            >
              <List.Item.Meta
                avatar={
                  <div
                    style={{
                      width: 48,
                      height: 48,
                      borderRadius: 8,
                      background: '#f5f5f5',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                    }}
                  >
                    <ShoppingCartOutlined style={{ fontSize: 24, color: '#1890ff' }} />
                  </div>
                }
                title={
                  <Space>
                    <Text strong>{cart.name}</Text>
                    <Tag color="blue">{cart.items_count} items</Tag>
                  </Space>
                }
                description={
                  <Space direction="vertical" size={0}>
                    <Text type="success" strong>
                      {formatCurrency(cart.total)}
                    </Text>
                    {cart.customer && (
                      <Space size={4}>
                        <UserOutlined style={{ fontSize: 12 }} />
                        <Text type="secondary" style={{ fontSize: 12 }}>
                          {cart.customer.full_name}
                        </Text>
                      </Space>
                    )}
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      Held {formatRelativeTime(cart.held_at)}
                    </Text>
                  </Space>
                }
              />
            </List.Item>
          )}
        />
      )}
    </Modal>
  )
}
