import { Modal, Typography, Divider, Table, Button, Space } from 'antd'
import { PrinterOutlined, CheckCircleOutlined } from '@ant-design/icons'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Title, Text } = Typography

export default function ReceiptModal({
  open,
  onClose,
  order,
  receipt,
}) {
  if (!order || !receipt) return null

  const handlePrint = () => {
    window.print()
  }

  const itemColumns = [
    {
      title: 'Item',
      dataIndex: 'name',
      key: 'name',
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
      dataIndex: 'price',
      key: 'price',
      width: 100,
      align: 'right',
      render: formatCurrency,
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      width: 100,
      align: 'right',
      render: formatCurrency,
    },
  ]

  return (
    <Modal
      title={
        <Space>
          <CheckCircleOutlined style={{ color: '#52c41a', fontSize: 24 }} />
          <span>Order Completed!</span>
        </Space>
      }
      open={open}
      onCancel={onClose}
      width={450}
      footer={[
        <Button key="print" icon={<PrinterOutlined />} onClick={handlePrint}>
          Print Receipt
        </Button>,
        <Button key="done" type="primary" onClick={onClose}>
          Done
        </Button>,
      ]}
    >
      <div className="receipt-content" style={{ padding: '16px 0' }}>
        {/* Header */}
        <div style={{ textAlign: 'center', marginBottom: 16 }}>
          <Title level={4} style={{ margin: 0 }}>
            {receipt.store?.name || 'Baqala POS'}
          </Title>
          {receipt.store?.address && (
            <Text type="secondary">{receipt.store.address}</Text>
          )}
        </div>

        <Divider style={{ margin: '12px 0' }} />

        {/* Order Info */}
        <div style={{ marginBottom: 16 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Text type="secondary">Order #</Text>
            <Text strong>{order?.order_number || receipt.order?.order_number}</Text>
          </div>
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Text type="secondary">Date</Text>
            <Text>{formatDateTime(receipt.date)}</Text>
          </div>
          {receipt.customer && (
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <Text type="secondary">Customer</Text>
              <Text>{typeof receipt.customer === 'string' ? receipt.customer : receipt.customer.full_name}</Text>
            </div>
          )}
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Text type="secondary">Payment</Text>
            <Text style={{ textTransform: 'capitalize' }}>{receipt.payment_type}</Text>
          </div>
        </div>

        <Divider style={{ margin: '12px 0' }} />

        {/* Items */}
        <Table
          dataSource={receipt.items || []}
          columns={itemColumns}
          rowKey={(record, index) => record.id || index}
          pagination={false}
          size="small"
        />

        <Divider style={{ margin: '12px 0' }} />

        {/* Totals */}
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
            <Text type="secondary">Subtotal</Text>
            <Text>{formatCurrency(receipt.subtotal)}</Text>
          </div>
          {receipt.tax > 0 && (
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
              <Text type="secondary">Tax</Text>
              <Text>{formatCurrency(receipt.tax)}</Text>
            </div>
          )}
          {receipt.discount > 0 && (
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
              <Text type="secondary">Discount</Text>
              <Text type="success">-{formatCurrency(receipt.discount)}</Text>
            </div>
          )}
          <Divider style={{ margin: '8px 0' }} />
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Title level={4} style={{ margin: 0 }}>Total</Title>
            <Title level={4} style={{ margin: 0, color: '#1890ff' }}>
              {formatCurrency(receipt.total)}
            </Title>
          </div>
          {receipt.cash_received && (
            <>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 8 }}>
                <Text type="secondary">Cash Received</Text>
                <Text>{formatCurrency(receipt.cash_received)}</Text>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Text type="secondary">Change</Text>
                <Text type="success">{formatCurrency(receipt.change)}</Text>
              </div>
            </>
          )}
        </div>

        {/* Footer */}
        <Divider style={{ margin: '12px 0' }} />
        <div style={{ textAlign: 'center' }}>
          <Text type="secondary">Thank you for your purchase!</Text>
        </div>
      </div>
    </Modal>
  )
}
