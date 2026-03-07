import { useEffect, useState } from 'react'
import { Form, InputNumber, Input, Divider, Row, Col, Typography, Card, Statistic, Alert, message } from 'antd'
import { useMutation, useQuery } from '@tanstack/react-query'
import { ArrowUpOutlined, ArrowDownOutlined, MinusOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { closeRegister, getDenominations, getRegisterSummary } from '@/Helpers/api/cashRegisterService'
import { formatCurrency, getCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function CloseRegisterModal({
  open,
  onClose,
  onSuccess,
  register,
}) {
  const [form] = Form.useForm()
  const [useDenominations, setUseDenominations] = useState(false)
  const [calculatedTotal, setCalculatedTotal] = useState(0)
  const [closingCash, setClosingCash] = useState(0)

  // Fetch register summary
  const { data: summary } = useQuery({
    queryKey: ['registerSummary', register?.id],
    queryFn: () => getRegisterSummary(register.id).then(res => res.data.data),
    enabled: open && !!register?.id,
  })

  // Fetch denominations
  const { data: denominations = [] } = useQuery({
    queryKey: ['cashDenominations'],
    queryFn: () => getDenominations().then(res => res.data.data || []),
    enabled: open,
  })

  useEffect(() => {
    if (open) {
      form.resetFields()
      form.setFieldsValue({ closing_cash: 0 })
      setCalculatedTotal(0)
      setClosingCash(0)
    }
  }, [open, form])

  // Close register mutation
  const mutation = useMutation({
    mutationFn: (data) => closeRegister(register.id, data),
    onSuccess: () => {
      message.success('Register closed successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to close register')
    },
  })

  const expectedCash = summary?.expected_cash || 0
  const difference = closingCash - expectedCash

  const handleDenominationChange = () => {
    const values = form.getFieldsValue()
    let total = 0
    denominations.forEach(denom => {
      const count = values[`denom_${denom.value}`] || 0
      total += count * denom.value
    })
    setCalculatedTotal(total)
    setClosingCash(total)
    form.setFieldsValue({ closing_cash: total })
  }

  const handleClosingCashChange = (value) => {
    setClosingCash(value || 0)
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      const data = {
        closing_cash: values.closing_cash || 0,
        notes: values.notes,
      }

      // Include denomination breakdown if used
      if (useDenominations && denominations.length > 0) {
        const denominationBreakdown = {}
        denominations.forEach(denom => {
          const count = values[`denom_${denom.value}`] || 0
          if (count > 0) {
            denominationBreakdown[denom.value] = count
          }
        })
        data.denomination_breakdown = denominationBreakdown
      }

      mutation.mutate(data)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleClose = () => {
    form.resetFields()
    setUseDenominations(false)
    setCalculatedTotal(0)
    setClosingCash(0)
    onClose()
  }

  return (
    <CustomModal
      title="Close Cash Register"
      open={open}
      onCancel={handleClose}
      width={600}
      showSave
      saveText="Close Register"
      loading={mutation.isPending}
      onSave={handleSubmit}
    >
      {/* Register Summary */}
      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={8}>
          <Card size="small">
            <Statistic
              title="Opening Cash"
              value={formatCurrency(summary?.opening_cash || 0)}
              valueStyle={{ fontSize: 16 }}
            />
          </Card>
        </Col>
        <Col span={8}>
          <Card size="small">
            <Statistic
              title="Cash Sales"
              value={formatCurrency(summary?.cash_sales || 0)}
              valueStyle={{ fontSize: 16, color: '#3f8600' }}
            />
          </Card>
        </Col>
        <Col span={8}>
          <Card size="small">
            <Statistic
              title="Expected Cash"
              value={formatCurrency(expectedCash)}
              valueStyle={{ fontSize: 16, fontWeight: 'bold' }}
            />
          </Card>
        </Col>
      </Row>

      {summary && (
        <Row gutter={16} style={{ marginBottom: 24 }}>
          <Col span={8}>
            <Card size="small">
              <Statistic
                title="Pay-In"
                value={formatCurrency(summary.pay_in || 0)}
                valueStyle={{ fontSize: 14, color: '#3f8600' }}
              />
            </Card>
          </Col>
          <Col span={8}>
            <Card size="small">
              <Statistic
                title="Pay-Out"
                value={formatCurrency(summary.pay_out || 0)}
                valueStyle={{ fontSize: 14, color: '#cf1322' }}
              />
            </Card>
          </Col>
          <Col span={8}>
            <Card size="small">
              <Statistic
                title="Transactions"
                value={summary.transaction_count || 0}
                valueStyle={{ fontSize: 14 }}
              />
            </Card>
          </Col>
        </Row>
      )}

      <Form form={form} layout="vertical">
        <Form.Item
          name="closing_cash"
          label={`Closing Cash Amount (${getCurrency()})`}
          rules={[{ required: true, message: 'Please enter closing cash amount' }]}
        >
          <InputNumber
            placeholder="0.00"
            min={0}
            precision={2}
            style={{ width: '100%' }}
            size="large"
            disabled={useDenominations}
            onChange={handleClosingCashChange}
          />
        </Form.Item>

        {denominations.length > 0 && (
          <>
            <div style={{ marginBottom: 16 }}>
              <button
                type="button"
                onClick={() => setUseDenominations(!useDenominations)}
                style={{
                  background: 'none',
                  border: 'none',
                  color: '#1890ff',
                  cursor: 'pointer',
                  padding: 0,
                }}
              >
                {useDenominations ? 'Enter amount directly' : 'Count by denomination'}
              </button>
            </div>

            {useDenominations && (
              <>
                <Divider>Denomination Count</Divider>
                <Row gutter={[16, 8]}>
                  {denominations.map(denom => (
                    <Col span={12} key={denom.value}>
                      <Form.Item
                        name={`denom_${denom.value}`}
                        label={denom.label || formatCurrency(denom.value)}
                        style={{ marginBottom: 8 }}
                      >
                        <InputNumber
                          min={0}
                          placeholder="0"
                          style={{ width: '100%' }}
                          onChange={handleDenominationChange}
                        />
                      </Form.Item>
                    </Col>
                  ))}
                </Row>
                <div style={{ textAlign: 'right', marginTop: 16 }}>
                  <Text strong>
                    Total: {formatCurrency(calculatedTotal)}
                  </Text>
                </div>
              </>
            )}
          </>
        )}

        {/* Cash Difference Alert */}
        {closingCash > 0 && (
          <Alert
            type={difference === 0 ? 'success' : difference > 0 ? 'warning' : 'error'}
            message={
              <Row justify="space-between" align="middle">
                <Col>
                  <Text strong>Cash Difference:</Text>
                </Col>
                <Col>
                  <Text
                    strong
                    style={{
                      color: difference === 0 ? '#52c41a' : difference > 0 ? '#faad14' : '#ff4d4f',
                      fontSize: 16,
                    }}
                  >
                    {difference > 0 && <ArrowUpOutlined />}
                    {difference < 0 && <ArrowDownOutlined />}
                    {difference === 0 && <MinusOutlined />}
                    {' '}
                    {difference === 0 ? 'Balanced' : formatCurrency(Math.abs(difference))}
                    {difference > 0 && ' Over'}
                    {difference < 0 && ' Short'}
                  </Text>
                </Col>
              </Row>
            }
            style={{ marginTop: 16 }}
          />
        )}

        <Form.Item name="notes" label="Notes" style={{ marginTop: 16 }}>
          <Input.TextArea
            rows={3}
            placeholder="Optional notes..."
          />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
