import { Row, Col, Typography, Button, InputNumber, Space } from 'antd'
import { DeleteOutlined, MinusOutlined, PlusOutlined } from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function CartItemRow({ item, onUpdateQuantity, onRemove, loading }) {
  const handleDecrease = () => {
    if (item.quantity > 1) {
      onUpdateQuantity(item.id, item.quantity - 1)
    } else {
      onRemove(item.id)
    }
  }

  const handleIncrease = () => {
    onUpdateQuantity(item.id, item.quantity + 1)
  }

  return (
    <div
      style={{
        padding: '12px 0',
        borderBottom: '1px solid #f0f0f0',
      }}
    >
      <Row align="middle" gutter={8}>
        <Col flex="1">
          <Text strong ellipsis style={{ display: 'block' }}>
            {item.product_name}
          </Text>
          {item.variant_name && (
            <Text type="secondary" style={{ fontSize: 12 }}>
              {item.variant_name}
            </Text>
          )}
          <Text type="secondary" style={{ display: 'block', fontSize: 12 }}>
            {formatCurrency(item.unit_price)} each
          </Text>
        </Col>
        <Col>
          <Space size={4}>
            <Button
              size="small"
              icon={<MinusOutlined />}
              onClick={handleDecrease}
              disabled={loading}
            />
            <InputNumber
              size="small"
              min={1}
              value={item.quantity}
              onChange={(val) => onUpdateQuantity(item.id, val)}
              style={{ width: 50 }}
              controls={false}
              disabled={loading}
            />
            <Button
              size="small"
              icon={<PlusOutlined />}
              onClick={handleIncrease}
              disabled={loading}
            />
          </Space>
        </Col>
        <Col style={{ width: 80, textAlign: 'right' }}>
          <Text strong>{formatCurrency(item.line_total)}</Text>
        </Col>
        <Col>
          <Button
            type="text"
            danger
            icon={<DeleteOutlined />}
            onClick={() => onRemove(item.id)}
            disabled={loading}
            size="small"
          />
        </Col>
      </Row>
    </div>
  )
}
