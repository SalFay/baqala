import { useEffect, useState } from 'react'
import { Form, InputNumber, Input, Divider, Row, Col, Typography, message } from 'antd'
import { useMutation, useQuery } from '@tanstack/react-query'
import CustomModal from '@/Components/CustomModal'
import { openRegister, getDenominations } from '@/Helpers/api/cashRegisterService'
import { formatCurrency, getCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function OpenRegisterModal({
  open,
  onClose,
  onSuccess,
}) {
  const [form] = Form.useForm()
  const [useDenominations, setUseDenominations] = useState(false)
  const [calculatedTotal, setCalculatedTotal] = useState(0)

  // Fetch denominations
  const { data: denominations = [] } = useQuery({
    queryKey: ['cashDenominations'],
    queryFn: () => getDenominations().then(res => res.data.data || []),
    enabled: open,
  })

  useEffect(() => {
    if (open) {
      form.resetFields()
      form.setFieldsValue({ opening_cash: 0 })
      setCalculatedTotal(0)
    }
  }, [open, form])

  // Open register mutation
  const mutation = useMutation({
    mutationFn: (data) => openRegister(data),
    onSuccess: () => {
      message.success('Register opened successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to open register')
    },
  })

  const handleDenominationChange = () => {
    const values = form.getFieldsValue()
    let total = 0
    denominations.forEach(denom => {
      const count = values[`denom_${denom.value}`] || 0
      total += count * denom.value
    })
    setCalculatedTotal(total)
    form.setFieldsValue({ opening_cash: total })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      const data = {
        opening_cash: values.opening_cash || 0,
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
    onClose()
  }

  return (
    <CustomModal
      title="Open Cash Register"
      open={open}
      onCancel={handleClose}
      width={500}
      showSave
      saveText="Open Register"
      loading={mutation.isPending}
      onSave={handleSubmit}
    >
      <Form form={form} layout="vertical">
        <Form.Item
          name="opening_cash"
          label={`Opening Cash Amount (${getCurrency()})`}
          rules={[{ required: true, message: 'Please enter opening cash amount' }]}
        >
          <InputNumber
            placeholder="0.00"
            min={0}
            precision={2}
            style={{ width: '100%' }}
            size="large"
            disabled={useDenominations}
          />
        </Form.Item>

        {denominations.length > 0 && (
          <>
            <div style={{ marginBottom: 16 }}>
              <Button
                type="link"
                onClick={() => setUseDenominations(!useDenominations)}
                style={{ padding: 0 }}
              >
                {useDenominations ? 'Enter amount directly' : 'Count by denomination'}
              </Button>
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

// Minimal Button component for toggle
function Button({ type, onClick, style, children }) {
  return (
    <button
      type="button"
      onClick={onClick}
      style={{
        background: 'none',
        border: 'none',
        color: type === 'link' ? '#1890ff' : 'inherit',
        cursor: 'pointer',
        ...style,
      }}
    >
      {children}
    </button>
  )
}
