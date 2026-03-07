import { useEffect, useState } from 'react'
import { Form, Input, Switch, Select, Row, Col, message, Typography, Card, Space, Tag } from 'antd'
import { useMutation } from '@tanstack/react-query'
import { DragOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { createTaxGroup, updateTaxGroup } from '@/Helpers/api/taxService'

const { Text } = Typography

export default function TaxGroupModal({
  open,
  onClose,
  onSuccess,
  taxGroup,
  taxRates = [],
}) {
  const [form] = Form.useForm()
  const [selectedRates, setSelectedRates] = useState([])
  const isEditing = !!taxGroup

  useEffect(() => {
    if (open && taxGroup) {
      const rateIds = taxGroup.tax_rates?.map(r => r.id) || []
      form.setFieldsValue({
        name: taxGroup.name,
        name_ar: taxGroup.name_ar,
        description: taxGroup.description,
        is_active: taxGroup.is_active !== false,
        tax_rate_ids: rateIds,
      })
      setSelectedRates(rateIds)
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({ is_active: true })
      setSelectedRates([])
    }
  }, [open, taxGroup, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createTaxGroup(data),
    onSuccess: () => {
      message.success('Tax group created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create tax group')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateTaxGroup(taxGroup.id, data),
    onSuccess: () => {
      message.success('Tax group updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update tax group')
    },
  })

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      if (isEditing) {
        updateMutation.mutate(values)
      } else {
        createMutation.mutate(values)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleClose = () => {
    form.resetFields()
    setSelectedRates([])
    onClose()
  }

  const handleRateChange = (ids) => {
    setSelectedRates(ids)
    form.setFieldsValue({ tax_rate_ids: ids })
  }

  // Calculate total rate
  const totalRate = selectedRates.reduce((sum, id) => {
    const rate = taxRates.find(r => r.id === id)
    return sum + (rate?.rate || 0)
  }, 0)

  // Get selected rate details
  const selectedRateDetails = selectedRates.map(id => {
    const rate = taxRates.find(r => r.id === id)
    return rate
  }).filter(Boolean)

  return (
    <CustomModal
      title={isEditing ? 'Edit Tax Group' : 'Add Tax Group'}
      open={open}
      onCancel={handleClose}
      width={600}
      showSave
      saveText={isEditing ? 'Update' : 'Create'}
      loading={createMutation.isPending || updateMutation.isPending}
      onSave={handleSubmit}
    >
      <Form form={form} layout="vertical">
        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="name"
              label="Group Name"
              rules={[{ required: true, message: 'Please enter group name' }]}
            >
              <Input placeholder="e.g., Standard VAT + Municipal Tax" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="name_ar" label="Name (Arabic)">
              <Input placeholder="Arabic name (optional)" dir="rtl" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item
          name="tax_rate_ids"
          label="Tax Rates"
          rules={[{ required: true, message: 'Please select at least one tax rate' }]}
          tooltip="The order matters for compound taxes. First non-compound, then compound."
        >
          <Select
            mode="multiple"
            placeholder="Select tax rates..."
            onChange={handleRateChange}
            options={taxRates.map(rate => ({
              value: rate.id,
              label: `${rate.name} (${rate.rate}%)`,
            }))}
          />
        </Form.Item>

        {selectedRateDetails.length > 0 && (
          <Card size="small" style={{ marginBottom: 16 }}>
            <Space direction="vertical" style={{ width: '100%' }}>
              <Text strong>Selected Tax Rates (in order of application):</Text>
              {selectedRateDetails.map((rate, index) => (
                <div key={rate.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <Space>
                    <DragOutlined style={{ color: '#999' }} />
                    <span>{index + 1}. {rate.name}</span>
                    {rate.is_compound && <Tag color="blue">Compound</Tag>}
                  </Space>
                  <Text>{rate.rate}%</Text>
                </div>
              ))}
              <div style={{ borderTop: '1px solid #f0f0f0', paddingTop: 8, marginTop: 8 }}>
                <Space style={{ width: '100%', justifyContent: 'space-between' }}>
                  <Text strong>Total Rate (simple sum):</Text>
                  <Text strong>{totalRate.toFixed(2)}%</Text>
                </Space>
              </div>
            </Space>
          </Card>
        )}

        <Form.Item name="description" label="Description">
          <Input.TextArea rows={2} placeholder="Optional description..." />
        </Form.Item>

        <Form.Item
          name="is_active"
          label="Active"
          valuePropName="checked"
        >
          <Switch />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
