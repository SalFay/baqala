import { useEffect } from 'react'
import { Form, Input, InputNumber, Switch, Row, Col, message } from 'antd'
import { useMutation } from '@tanstack/react-query'
import CustomModal from '@/Components/CustomModal'
import { createTaxRate, updateTaxRate } from '@/Helpers/api/taxService'

export default function TaxRateModal({
  open,
  onClose,
  onSuccess,
  taxRate,
}) {
  const [form] = Form.useForm()
  const isEditing = !!taxRate

  useEffect(() => {
    if (open && taxRate) {
      form.setFieldsValue({
        name: taxRate.name,
        name_ar: taxRate.name_ar,
        rate: taxRate.rate,
        tax_number: taxRate.tax_number,
        is_default: taxRate.is_default,
        is_compound: taxRate.is_compound,
        is_recoverable: taxRate.is_recoverable,
        is_active: taxRate.is_active !== false,
        description: taxRate.description,
      })
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({
        is_active: true,
        is_recoverable: true,
        is_compound: false,
        is_default: false,
        rate: 15, // Default VAT in Saudi Arabia
      })
    }
  }, [open, taxRate, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createTaxRate(data),
    onSuccess: () => {
      message.success('Tax rate created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create tax rate')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateTaxRate(taxRate.id, data),
    onSuccess: () => {
      message.success('Tax rate updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update tax rate')
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
    onClose()
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Tax Rate' : 'Add Tax Rate'}
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
              label="Name"
              rules={[{ required: true, message: 'Please enter tax rate name' }]}
            >
              <Input placeholder="e.g., VAT, GST" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="name_ar" label="Name (Arabic)">
              <Input placeholder="Arabic name (optional)" dir="rtl" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="rate"
              label="Rate (%)"
              rules={[
                { required: true, message: 'Please enter rate' },
                { type: 'number', min: 0, max: 100, message: 'Rate must be between 0 and 100' },
              ]}
            >
              <InputNumber
                placeholder="15"
                min={0}
                max={100}
                precision={2}
                style={{ width: '100%' }}
                suffix="%"
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="tax_number" label="Tax Number">
              <Input placeholder="Tax registration number" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <Input.TextArea rows={2} placeholder="Optional description..." />
        </Form.Item>

        <Row gutter={16}>
          <Col span={6}>
            <Form.Item
              name="is_default"
              label="Default"
              valuePropName="checked"
              tooltip="This rate will be applied by default to products without specific tax settings"
            >
              <Switch />
            </Form.Item>
          </Col>
          <Col span={6}>
            <Form.Item
              name="is_compound"
              label="Compound"
              valuePropName="checked"
              tooltip="Compound tax is calculated on amount plus previous taxes"
            >
              <Switch />
            </Form.Item>
          </Col>
          <Col span={6}>
            <Form.Item
              name="is_recoverable"
              label="Recoverable"
              valuePropName="checked"
              tooltip="Tax can be recovered/claimed as input credit"
            >
              <Switch />
            </Form.Item>
          </Col>
          <Col span={6}>
            <Form.Item
              name="is_active"
              label="Active"
              valuePropName="checked"
            >
              <Switch />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
