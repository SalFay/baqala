import { useState, useEffect } from 'react'
import { Input, Button, Typography, Space, Divider, Alert, Row, Col, theme, Tabs, InputNumber } from 'antd'
import {
  DollarOutlined,
  CreditCardOutlined,
  MobileOutlined,
  BankOutlined,
  CheckCircleOutlined,
  DeleteOutlined,
  EditOutlined,
} from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { formatCurrency, getCurrency, getFormatterSettings } from '@/Helpers/formatters'

const { Text, Title } = Typography
const { TextArea } = Input

const PAYMENT_METHODS = [
  { key: 'cash', label: 'Cash', icon: <DollarOutlined style={{ fontSize: 24 }} />, color: '#52c41a' },
  { key: 'card', label: 'Card', icon: <CreditCardOutlined style={{ fontSize: 24 }} />, color: '#1890ff' },
  { key: 'mobile', label: 'Mobile', icon: <MobileOutlined style={{ fontSize: 24 }} />, color: '#722ed1' },
  { key: 'bank_transfer', label: 'Bank', icon: <BankOutlined style={{ fontSize: 24 }} />, color: '#fa8c16' },
]

// Numeric Keypad Component
function NumericKeypad({ value, onChange, onClear }) {
  const { token } = theme.useToken()

  const handleKeyPress = (key) => {
    if (key === 'C') {
      onClear()
    } else if (key === 'DEL') {
      onChange(Math.floor(value / 10))
    } else {
      const newValue = value * 10 + parseInt(key)
      if (newValue <= 999999) {
        onChange(newValue)
      }
    }
  }

  const keys = ['7', '8', '9', '4', '5', '6', '1', '2', '3', 'C', '0', 'DEL']

  return (
    <div
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(3, 1fr)',
        gap: 8,
        marginTop: 16,
      }}
    >
      {keys.map((key) => (
        <Button
          key={key}
          size="large"
          onClick={() => handleKeyPress(key)}
          style={{
            height: 48,
            fontSize: key === 'DEL' || key === 'C' ? 14 : 18,
            fontWeight: 'bold',
            borderRadius: 8,
            background: key === 'C' ? token.colorErrorBg : key === 'DEL' ? token.colorWarningBg : token.colorBgLayout,
            color: key === 'C' ? token.colorError : key === 'DEL' ? token.colorWarning : token.colorText,
          }}
          icon={key === 'DEL' ? <DeleteOutlined /> : null}
        >
          {key === 'DEL' ? '' : key}
        </Button>
      ))}
    </div>
  )
}

// Split Payment Item
function SplitPaymentItem({ payment, index, onUpdate, onRemove, remaining }) {
  const { token } = theme.useToken()
  const method = PAYMENT_METHODS.find(m => m.key === payment.method)
  const requiresReference = payment.method !== 'cash'
  const currency = getCurrency()

  return (
    <div
      style={{
        padding: 12,
        background: token.colorBgLayout,
        borderRadius: 8,
        marginBottom: 8,
        border: `1px solid ${token.colorBorder}`,
      }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
        <div
          style={{
            width: 40,
            height: 40,
            borderRadius: 8,
            background: method?.color + '20',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: method?.color,
          }}
        >
          {method?.icon}
        </div>
        <div style={{ flex: 1 }}>
          <Text strong>{method?.label}</Text>
          <InputNumber
            size="small"
            value={payment.amount}
            onChange={(val) => onUpdate(index, { ...payment, amount: val || 0 })}
            min={0}
            max={remaining + payment.amount}
            formatter={(value) => `${currency} ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
            parser={(value) => value.replace(new RegExp(`${currency}\\s?|(,*)`, 'g'), '')}
            style={{ width: 140, marginLeft: 8 }}
          />
        </div>
        <Button
          type="text"
          danger
          icon={<DeleteOutlined />}
          onClick={() => onRemove(index)}
          size="small"
        />
      </div>
      {requiresReference && (
        <Input
          size="small"
          value={payment.reference || ''}
          onChange={(e) => onUpdate(index, { ...payment, reference: e.target.value })}
          placeholder="Reference number (required)"
          style={{ marginTop: 8 }}
          status={!payment.reference ? 'warning' : ''}
        />
      )}
    </div>
  )
}

export default function CheckoutModal({
  open,
  onClose,
  onCheckout,
  total,
  loading,
}) {
  const { token } = theme.useToken()
  const [mode, setMode] = useState('single') // 'single' or 'split'
  const [paymentMethod, setPaymentMethod] = useState('cash')
  const [cashReceived, setCashReceived] = useState(0)
  const [paymentReference, setPaymentReference] = useState('')
  const [orderNotes, setOrderNotes] = useState('')
  const [showNotes, setShowNotes] = useState(false)
  const [splitPayments, setSplitPayments] = useState([])

  useEffect(() => {
    if (open) {
      setCashReceived(Math.ceil(total))
      setPaymentReference('')
      setPaymentMethod('cash')
      setOrderNotes('')
      setShowNotes(false)
      setMode('single')
      setSplitPayments([])
    }
  }, [open, total])

  // Single payment validation
  const change = paymentMethod === 'cash' ? Math.max(0, cashReceived - total) : 0
  const isReferenceRequired = paymentMethod !== 'cash'
  const isReferenceValid = !isReferenceRequired || (paymentReference && paymentReference.trim().length >= 3)
  const canCheckoutSingle = paymentMethod === 'cash' ? cashReceived >= total : isReferenceValid

  // Split payment validation
  const splitTotal = splitPayments.reduce((sum, p) => sum + (p.amount || 0), 0)
  const splitRemaining = total - splitTotal
  const allSplitReferencesValid = splitPayments.every(p =>
    p.method === 'cash' || (p.reference && p.reference.trim().length >= 3)
  )
  const canCheckoutSplit = Math.abs(splitRemaining) < 0.01 && allSplitReferencesValid && splitPayments.length > 0

  const canCheckout = mode === 'single' ? canCheckoutSingle : canCheckoutSplit

  const handleCheckout = () => {
    if (mode === 'split') {
      onCheckout({
        payments: splitPayments,
        notes: orderNotes || undefined,
      })
    } else {
      const data = {
        payment_method: paymentMethod,
        notes: orderNotes || undefined,
      }

      if (paymentMethod === 'cash') {
        data.cash_received = cashReceived
      }

      if (paymentReference) {
        data.payment_reference = paymentReference
      }

      onCheckout(data)
    }
  }

  const addSplitPayment = (method) => {
    const amount = splitRemaining > 0 ? Math.min(splitRemaining, total) : 0
    setSplitPayments([...splitPayments, { method, amount, reference: '' }])
  }

  const updateSplitPayment = (index, payment) => {
    const updated = [...splitPayments]
    updated[index] = payment
    setSplitPayments(updated)
  }

  const removeSplitPayment = (index) => {
    setSplitPayments(splitPayments.filter((_, i) => i !== index))
  }

  // Quick amounts for cash
  const quickAmounts = [
    Math.ceil(total),
    Math.ceil(total / 10) * 10,
    Math.ceil(total / 50) * 50,
    Math.ceil(total / 100) * 100,
    200,
    500,
  ].filter((v, i, arr) => arr.indexOf(v) === i && v >= total).slice(0, 4)

  return (
    <CustomModal
      title="Checkout"
      open={open}
      onCancel={onClose}
      footer={null}
      width={560}
    >
      {/* Total Display */}
      <div
        style={{
          textAlign: 'center',
          padding: '16px 0',
          background: token.colorPrimaryBg,
          borderRadius: 12,
          marginBottom: 20,
        }}
      >
        <Text type="secondary">Amount Due</Text>
        <Title level={2} style={{ margin: '4px 0 0', color: token.colorPrimary }}>
          {formatCurrency(total)}
        </Title>
      </div>

      {/* Payment Mode Tabs */}
      <Tabs
        activeKey={mode}
        onChange={setMode}
        centered
        items={[
          { key: 'single', label: 'Single Payment' },
          { key: 'split', label: 'Split Payment' },
        ]}
        style={{ marginBottom: 16 }}
      />

      {mode === 'single' ? (
        <>
          {/* Payment Method Selection */}
          <Text strong style={{ display: 'block', marginBottom: 8 }}>Payment Method</Text>
          <Row gutter={8} style={{ marginBottom: 16 }}>
            {PAYMENT_METHODS.map((method) => (
              <Col span={6} key={method.key}>
                <div
                  onClick={() => setPaymentMethod(method.key)}
                  style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    padding: '12px 4px',
                    borderRadius: 10,
                    cursor: 'pointer',
                    transition: 'all 0.2s ease',
                    border: `2px solid ${paymentMethod === method.key ? method.color : token.colorBorder}`,
                    background: paymentMethod === method.key ? `${method.color}10` : 'transparent',
                  }}
                >
                  <div style={{ color: paymentMethod === method.key ? method.color : token.colorTextSecondary, fontSize: 20 }}>
                    {method.icon}
                  </div>
                  <Text
                    strong={paymentMethod === method.key}
                    style={{
                      marginTop: 4,
                      fontSize: 11,
                      color: paymentMethod === method.key ? method.color : token.colorTextSecondary,
                    }}
                  >
                    {method.label}
                  </Text>
                </div>
              </Col>
            ))}
          </Row>

          {/* Payment Details */}
          <div style={{ minHeight: 240 }}>
            {paymentMethod === 'cash' && (
              <>
                <Text strong style={{ display: 'block', marginBottom: 8 }}>Cash Received</Text>
                <div
                  style={{
                    padding: '12px 16px',
                    background: token.colorBgLayout,
                    borderRadius: 10,
                    textAlign: 'center',
                    marginBottom: 8,
                  }}
                >
                  <Title level={3} style={{ margin: 0 }}>
                    {formatCurrency(cashReceived)}
                  </Title>
                </div>

                <Space wrap style={{ marginBottom: 12, width: '100%', justifyContent: 'center' }}>
                  {quickAmounts.map((amount) => (
                    <Button
                      key={amount}
                      onClick={() => setCashReceived(amount)}
                      type={cashReceived === amount ? 'primary' : 'default'}
                      size="middle"
                      style={{ minWidth: 70, borderRadius: 8 }}
                    >
                      {formatCurrency(amount)}
                    </Button>
                  ))}
                </Space>

                <NumericKeypad
                  value={cashReceived}
                  onChange={setCashReceived}
                  onClear={() => setCashReceived(0)}
                />

                <Divider style={{ margin: '16px 0' }} />

                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    padding: '12px 16px',
                    background: change > 0 ? token.colorSuccessBg : token.colorBgLayout,
                    borderRadius: 10,
                    border: change > 0 ? `1px solid ${token.colorSuccess}` : 'none',
                  }}
                >
                  <Text strong style={{ fontSize: 16 }}>Change</Text>
                  <Title level={3} style={{ margin: 0, color: token.colorSuccess }}>
                    {formatCurrency(change)}
                  </Title>
                </div>
              </>
            )}

            {paymentMethod !== 'cash' && (
              <div style={{ textAlign: 'center', padding: '30px 0' }}>
                <div
                  style={{
                    width: 70,
                    height: 70,
                    borderRadius: '50%',
                    background: PAYMENT_METHODS.find(m => m.key === paymentMethod)?.color + '20',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    margin: '0 auto 16px',
                    fontSize: 28,
                    color: PAYMENT_METHODS.find(m => m.key === paymentMethod)?.color,
                  }}
                >
                  {PAYMENT_METHODS.find(m => m.key === paymentMethod)?.icon}
                </div>
                <Text type="secondary" style={{ display: 'block', marginBottom: 20 }}>
                  Process payment with {PAYMENT_METHODS.find(m => m.key === paymentMethod)?.label}
                </Text>

                <Input
                  size="large"
                  value={paymentReference}
                  onChange={(e) => setPaymentReference(e.target.value)}
                  placeholder="Reference number (required)"
                  status={isReferenceRequired && paymentReference.length > 0 && paymentReference.length < 3 ? 'error' : ''}
                  style={{ borderRadius: 8 }}
                />
                {isReferenceRequired && !isReferenceValid && (
                  <Text type="danger" style={{ fontSize: 12, marginTop: 8, display: 'block' }}>
                    Reference number is required (minimum 3 characters)
                  </Text>
                )}
              </div>
            )}
          </div>
        </>
      ) : (
        /* Split Payment Mode */
        <div style={{ minHeight: 280 }}>
          <Text strong style={{ display: 'block', marginBottom: 8 }}>Add Payments</Text>
          <Row gutter={8} style={{ marginBottom: 16 }}>
            {PAYMENT_METHODS.map((method) => (
              <Col span={6} key={method.key}>
                <Button
                  icon={method.icon}
                  onClick={() => addSplitPayment(method.key)}
                  style={{
                    width: '100%',
                    height: 50,
                    borderRadius: 8,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: 10,
                  }}
                >
                  <span style={{ fontSize: 10, marginTop: 2 }}>{method.label}</span>
                </Button>
              </Col>
            ))}
          </Row>

          {splitPayments.length === 0 ? (
            <div style={{ textAlign: 'center', padding: 40, color: token.colorTextSecondary }}>
              <Text type="secondary">Click a payment method to add a split payment</Text>
            </div>
          ) : (
            <div style={{ maxHeight: 200, overflow: 'auto' }}>
              {splitPayments.map((payment, index) => (
                <SplitPaymentItem
                  key={index}
                  payment={payment}
                  index={index}
                  onUpdate={updateSplitPayment}
                  onRemove={removeSplitPayment}
                  remaining={splitRemaining}
                />
              ))}
            </div>
          )}

          <Divider style={{ margin: '16px 0' }} />

          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Text>Total Paid:</Text>
            <Text strong style={{ fontSize: 16 }}>{formatCurrency(splitTotal)}</Text>
          </div>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
            <Text>Remaining:</Text>
            <Text
              strong
              style={{
                fontSize: 16,
                color: splitRemaining > 0 ? token.colorError : token.colorSuccess
              }}
            >
              {formatCurrency(splitRemaining)}
            </Text>
          </div>
        </div>
      )}

      {/* Order Notes */}
      <div style={{ marginTop: 16 }}>
        {showNotes ? (
          <>
            <Text strong style={{ display: 'block', marginBottom: 8 }}>Order Notes</Text>
            <TextArea
              value={orderNotes}
              onChange={(e) => setOrderNotes(e.target.value)}
              placeholder="Add notes for this order..."
              rows={2}
              maxLength={500}
              showCount
              style={{ borderRadius: 8 }}
            />
          </>
        ) : (
          <Button
            type="text"
            icon={<EditOutlined />}
            onClick={() => setShowNotes(true)}
            style={{ padding: 0 }}
          >
            Add order notes
          </Button>
        )}
      </div>

      {/* Warning */}
      {!canCheckout && mode === 'single' && paymentMethod === 'cash' && (
        <Alert
          type="warning"
          message="Cash received must be at least equal to the total"
          style={{ marginTop: 16, borderRadius: 8 }}
          showIcon
        />
      )}

      {!canCheckout && mode === 'split' && splitPayments.length > 0 && (
        <Alert
          type="warning"
          message={
            Math.abs(splitRemaining) >= 0.01
              ? `Remaining amount: ${formatCurrency(splitRemaining)}`
              : 'Please provide reference numbers for non-cash payments'
          }
          style={{ marginTop: 16, borderRadius: 8 }}
          showIcon
        />
      )}

      {/* Complete Sale Button */}
      <Button
        type="primary"
        size="large"
        block
        onClick={handleCheckout}
        loading={loading}
        disabled={!canCheckout}
        icon={<CheckCircleOutlined />}
        style={{
          height: 52,
          fontSize: 17,
          borderRadius: 12,
          marginTop: 16,
        }}
      >
        Complete Sale
      </Button>
    </CustomModal>
  )
}
