import { useState, useEffect } from 'react'
import { Modal, Tabs, InputNumber, Input, Button, Typography, Space, Divider, Alert } from 'antd'
import {
  DollarOutlined,
  CreditCardOutlined,
  MobileOutlined,
  BankOutlined,
} from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text, Title } = Typography

const PAYMENT_METHODS = [
  { key: 'cash', label: 'Cash', icon: <DollarOutlined /> },
  { key: 'card', label: 'Card', icon: <CreditCardOutlined /> },
  { key: 'mobile', label: 'Mobile', icon: <MobileOutlined /> },
  { key: 'bank_transfer', label: 'Bank', icon: <BankOutlined /> },
]

export default function CheckoutModal({
  open,
  onClose,
  onCheckout,
  total,
  loading,
}) {
  const [paymentMethod, setPaymentMethod] = useState('cash')
  const [cashReceived, setCashReceived] = useState(0)
  const [paymentReference, setPaymentReference] = useState('')

  useEffect(() => {
    if (open) {
      setCashReceived(Math.ceil(total))
      setPaymentReference('')
      setPaymentMethod('cash')
    }
  }, [open, total])

  const change = paymentMethod === 'cash' ? Math.max(0, cashReceived - total) : 0
  const canCheckout = paymentMethod === 'cash' ? cashReceived >= total : true

  const handleCheckout = () => {
    const data = {
      payment_method: paymentMethod,
    }

    if (paymentMethod === 'cash') {
      data.cash_received = cashReceived
    }

    if (paymentReference) {
      data.payment_reference = paymentReference
    }

    onCheckout(data)
  }

  const quickAmounts = [
    Math.ceil(total),
    Math.ceil(total / 10) * 10,
    Math.ceil(total / 50) * 50,
    Math.ceil(total / 100) * 100,
  ].filter((v, i, arr) => arr.indexOf(v) === i && v >= total)

  return (
    <Modal
      title="Checkout"
      open={open}
      onCancel={onClose}
      footer={null}
      width={500}
      destroyOnClose
    >
      <div style={{ marginBottom: 24 }}>
        <Title level={3} style={{ textAlign: 'center', margin: '16px 0' }}>
          Total: {formatCurrency(total)}
        </Title>
      </div>

      <Tabs
        activeKey={paymentMethod}
        onChange={setPaymentMethod}
        items={PAYMENT_METHODS.map((method) => ({
          key: method.key,
          label: (
            <span>
              {method.icon} {method.label}
            </span>
          ),
        }))}
      />

      <div style={{ padding: '16px 0' }}>
        {paymentMethod === 'cash' && (
          <>
            <Text strong style={{ display: 'block', marginBottom: 8 }}>
              Cash Received
            </Text>
            <InputNumber
              size="large"
              value={cashReceived}
              onChange={setCashReceived}
              min={0}
              style={{ width: '100%', marginBottom: 16 }}
              formatter={(value) => `SAR ${value}`}
              parser={(value) => value.replace(/SAR\s?/g, '')}
            />

            <Space wrap style={{ marginBottom: 16 }}>
              {quickAmounts.map((amount) => (
                <Button
                  key={amount}
                  onClick={() => setCashReceived(amount)}
                  type={cashReceived === amount ? 'primary' : 'default'}
                >
                  {formatCurrency(amount)}
                </Button>
              ))}
            </Space>

            <Divider />

            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
              }}
            >
              <Text strong style={{ fontSize: 18 }}>Change</Text>
              <Title level={3} style={{ margin: 0, color: '#52c41a' }}>
                {formatCurrency(change)}
              </Title>
            </div>
          </>
        )}

        {paymentMethod !== 'cash' && (
          <>
            <Text strong style={{ display: 'block', marginBottom: 8 }}>
              Reference Number (Optional)
            </Text>
            <Input
              size="large"
              value={paymentReference}
              onChange={(e) => setPaymentReference(e.target.value)}
              placeholder="Transaction ID or reference"
            />
          </>
        )}
      </div>

      {!canCheckout && (
        <Alert
          type="warning"
          message="Cash received must be at least equal to the total"
          style={{ marginBottom: 16 }}
        />
      )}

      <Button
        type="primary"
        size="large"
        block
        onClick={handleCheckout}
        loading={loading}
        disabled={!canCheckout}
      >
        Complete Sale
      </Button>
    </Modal>
  )
}
