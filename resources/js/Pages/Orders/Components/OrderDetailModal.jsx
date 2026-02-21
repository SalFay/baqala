import { useState, useEffect } from 'react'
import {
  Modal,
  Descriptions,
  Table,
  Typography,
  Tag,
  Divider,
  Space,
  Button,
  Spin,
  Alert,
} from 'antd'
import { PrinterOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import axios from 'axios'
import StatusBadge from '@/Components/StatusBadge'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Title, Text } = Typography

export default function OrderDetailModal({
  open,
  onClose,
  orderId,
}) {
  const { data: order, isLoading, error } = useQuery({
    queryKey: ['order-detail', orderId],
    queryFn: async () => {
      const response = await axios.get(`/pos/orders/${orderId}`)
      return response.data.data
    },
    enabled: !!orderId && open,
  })

  const itemColumns = [
    {
      title: 'Product',
      dataIndex: 'product_name',
      key: 'product_name',
    },
    {
      title: 'SKU',
      dataIndex: 'sku',
      key: 'sku',
      width: 100,
    },
    {
      title: 'Qty',
      dataIndex: 'quantity',
      key: 'quantity',
      width: 60,
      align: 'center',
    },
    {
      title: 'Price',
      dataIndex: 'unit_price',
      key: 'unit_price',
      width: 100,
      align: 'right',
      render: formatCurrency,
    },
    {
      title: 'Total',
      dataIndex: 'line_total',
      key: 'line_total',
      width: 100,
      align: 'right',
      render: formatCurrency,
    },
  ]

  const handlePrint = () => {
    window.open(`/pos/orders/${orderId}/receipt`, '_blank')
  }

  return (
    <Modal
      title={
        <Space>
          <span>Order Details</span>
          {order && <Text code>#{order.order_number}</Text>}
        </Space>
      }
      open={open}
      onCancel={onClose}
      width={700}
      footer={[
        <Button key="print" icon={<PrinterOutlined />} onClick={handlePrint} disabled={!order}>
          Print Receipt
        </Button>,
        <Button key="close" type="primary" onClick={onClose}>
          Close
        </Button>,
      ]}
    >
      {isLoading ? (
        <div style={{ textAlign: 'center', padding: 60 }}>
          <Spin size="large" />
        </div>
      ) : error ? (
        <Alert type="error" message="Failed to load order details" />
      ) : order ? (
        <>
          <Descriptions column={2} size="small" bordered>
            <Descriptions.Item label="Order Number">{order.order_number}</Descriptions.Item>
            <Descriptions.Item label="Invoice No">{order.invoice_no || '-'}</Descriptions.Item>
            <Descriptions.Item label="Customer">
              {order.customer?.full_name || order.customer_name || 'Walk-in'}
            </Descriptions.Item>
            <Descriptions.Item label="Cashier">
              {order.user?.first_name || order.cashier_name || '-'}
            </Descriptions.Item>
            <Descriptions.Item label="Status">
              <StatusBadge status={order.current_status} />
            </Descriptions.Item>
            <Descriptions.Item label="Payment">
              <StatusBadge status={order.payment_status} />
            </Descriptions.Item>
            <Descriptions.Item label="Created At" span={2}>
              {formatDateTime(order.created_at)}
            </Descriptions.Item>
          </Descriptions>

          <Divider />

          <Title level={5}>Order Items</Title>
          <Table
            dataSource={order.items || []}
            columns={itemColumns}
            rowKey="id"
            pagination={false}
            size="small"
          />

          <Divider />

          <div style={{ textAlign: 'right' }}>
            <Space direction="vertical" size={4} style={{ minWidth: 200 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Text type="secondary">Subtotal:</Text>
                <Text>{formatCurrency(order.subtotal)}</Text>
              </div>
              {order.discount > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text type="secondary">Discount:</Text>
                  <Text type="success">-{formatCurrency(order.discount)}</Text>
                </div>
              )}
              {order.tax_amount > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text type="secondary">Tax:</Text>
                  <Text>{formatCurrency(order.tax_amount)}</Text>
                </div>
              )}
              <Divider style={{ margin: '8px 0' }} />
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Title level={4} style={{ margin: 0 }}>Total:</Title>
                <Title level={4} style={{ margin: 0, color: '#1890ff' }}>
                  {formatCurrency(order.total)}
                </Title>
              </div>
            </Space>
          </div>

          {order.payments?.length > 0 && (
            <>
              <Divider />
              <Title level={5}>Payments</Title>
              <Space direction="vertical" size={4}>
                {order.payments.map((payment) => (
                  <div key={payment.id} style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text>{payment.method}</Text>
                    <Text>{formatCurrency(payment.amount)}</Text>
                  </div>
                ))}
              </Space>
            </>
          )}
        </>
      ) : null}
    </Modal>
  )
}
