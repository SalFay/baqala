import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Switch, message, Row, Col, Tabs } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createBusinessType, updateBusinessType } from '@/Helpers/api/businessTypeService'

const { TextArea } = Input

export default function BusinessTypeModal({
  visible,
  onCancel,
  record,
  onUpdate,
  viewMode = false,
}) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const isEditing = !!record

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        name_ar: record.name_ar,
        icon: record.icon,
        description: record.description,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
        // JSON fields as stringified for display
        settings: record.settings ? JSON.stringify(record.settings, null, 2) : '',
        tax_config: record.tax_config ? JSON.stringify(record.tax_config, null, 2) : '',
        receipt_config: record.receipt_config ? JSON.stringify(record.receipt_config, null, 2) : '',
        default_attributes: record.default_attributes ? JSON.stringify(record.default_attributes, null, 2) : '',
      })
    } else {
      form.resetFields()
      form.setFieldsValue({ is_active: true, sort_order: 0 })
    }
  }, [record, form])

  const parseJsonField = (value) => {
    if (!value || value.trim() === '') return null
    try {
      return JSON.parse(value)
    } catch {
      return null
    }
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      const data = {
        name: values.name,
        name_ar: values.name_ar,
        icon: values.icon,
        description: values.description,
        is_active: values.is_active,
        sort_order: values.sort_order,
        settings: parseJsonField(values.settings),
        tax_config: parseJsonField(values.tax_config),
        receipt_config: parseJsonField(values.receipt_config),
        default_attributes: parseJsonField(values.default_attributes),
      }

      if (isEditing) {
        await updateBusinessType(record.id, data)
        message.success('Business type updated successfully')
      } else {
        await createBusinessType(data)
        message.success('Business type created successfully')
      }

      onUpdate()
      onCancel()
    } catch (error) {
      if (error.response?.data?.message) {
        message.error(error.response.data.message)
      } else if (error.errorFields) {
        // Form validation error
      } else {
        message.error('An error occurred')
      }
    } finally {
      setLoading(false)
    }
  }

  const validateJson = (_, value) => {
    if (!value || value.trim() === '') return Promise.resolve()
    try {
      JSON.parse(value)
      return Promise.resolve()
    } catch {
      return Promise.reject(new Error('Invalid JSON format'))
    }
  }

  const tabItems = [
    {
      key: 'basic',
      label: 'Basic Info',
      children: (
        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="name"
              label="Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Retail, Restaurant" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="name_ar" label="Name (Arabic)">
              <Input placeholder="e.g., متجر تجزئة" dir="rtl" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="icon" label="Icon (Emoji)">
              <Input placeholder="e.g., 🛒, 🍽️, 💊" />
            </Form.Item>
          </Col>
          <Col xs={24} md={6}>
            <Form.Item name="sort_order" label="Sort Order">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
          <Col xs={24} md={6}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
          <Col xs={24}>
            <Form.Item name="description" label="Description">
              <TextArea rows={3} placeholder="Brief description of this business type" />
            </Form.Item>
          </Col>
        </Row>
      ),
    },
    {
      key: 'settings',
      label: 'Settings (JSON)',
      children: (
        <Form.Item
          name="settings"
          label="Settings"
          rules={[{ validator: validateJson }]}
          extra="JSON configuration for this business type"
        >
          <TextArea
            rows={10}
            placeholder='{"key": "value"}'
            style={{ fontFamily: 'monospace' }}
          />
        </Form.Item>
      ),
    },
    {
      key: 'tax',
      label: 'Tax Config',
      children: (
        <Form.Item
          name="tax_config"
          label="Tax Configuration"
          rules={[{ validator: validateJson }]}
          extra="Tax settings specific to this business type"
        >
          <TextArea
            rows={10}
            placeholder='{"default_tax_rate": 15, "tax_inclusive": true}'
            style={{ fontFamily: 'monospace' }}
          />
        </Form.Item>
      ),
    },
    {
      key: 'receipt',
      label: 'Receipt Config',
      children: (
        <Form.Item
          name="receipt_config"
          label="Receipt Configuration"
          rules={[{ validator: validateJson }]}
          extra="Receipt layout and content settings"
        >
          <TextArea
            rows={10}
            placeholder='{"show_logo": true, "paper_size": "80mm"}'
            style={{ fontFamily: 'monospace' }}
          />
        </Form.Item>
      ),
    },
    {
      key: 'attributes',
      label: 'Default Attributes',
      children: (
        <Form.Item
          name="default_attributes"
          label="Default Attributes"
          rules={[{ validator: validateJson }]}
          extra="Product attributes and categories for this business type"
        >
          <TextArea
            rows={10}
            placeholder='{"product_attributes": [], "categories": []}'
            style={{ fontFamily: 'monospace' }}
          />
        </Form.Item>
      ),
    },
  ]

  return (
    <CustomModal
      title={isEditing ? (viewMode ? 'View Business Type' : 'Edit Business Type') : 'Create Business Type'}
      open={visible}
      onCancel={onCancel}
      width={700}
      showSave={!viewMode}
      saveText={isEditing ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
    >
      <Form form={form} layout="vertical" disabled={viewMode}>
        <Tabs items={tabItems} />
      </Form>
    </CustomModal>
  )
}
