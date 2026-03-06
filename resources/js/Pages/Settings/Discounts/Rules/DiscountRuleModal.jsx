import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, Switch, message, Row, Col, DatePicker, Divider } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createDiscountRule, updateDiscountRule } from '@/Helpers/api/discountService'
import dayjs from 'dayjs'

const { TextArea } = Input
const { Option } = Select
const { RangePicker } = DatePicker

export default function DiscountRuleModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const isEditing = !!record
  const appliesTo = Form.useWatch('applies_to', form)

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        description: record.description,
        discount_type: record.discount_type || 'percentage',
        discount_amount: record.discount_amount || 0,
        applies_to: record.applies_to || 'all',
        applies_to_ids: record.applies_to_ids || [],
        conditions: record.conditions || {},
        priority: record.priority ?? 0,
        is_stackable: record.is_stackable ?? false,
        stop_further_rules: record.stop_further_rules ?? false,
        max_uses: record.max_uses,
        max_uses_per_customer: record.max_uses_per_customer,
        validity: record.starts_at && record.ends_at
          ? [dayjs(record.starts_at), dayjs(record.ends_at)]
          : null,
        is_active: record.is_active ?? true,
      })
    } else {
      form.resetFields()
      form.setFieldsValue({
        discount_type: 'percentage',
        discount_amount: 0,
        applies_to: 'all',
        priority: 0,
        is_stackable: false,
        stop_further_rules: false,
        is_active: true,
      })
    }
  }, [record, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      const payload = {
        ...values,
        starts_at: values.validity?.[0]?.format('YYYY-MM-DD HH:mm:ss'),
        ends_at: values.validity?.[1]?.format('YYYY-MM-DD HH:mm:ss'),
      }
      delete payload.validity

      if (isEditing) {
        await updateDiscountRule(record.id, payload)
        message.success('Discount rule updated successfully')
      } else {
        await createDiscountRule(payload)
        message.success('Discount rule created successfully')
      }

      onUpdate()
      onCancel()
    } catch (error) {
      if (error.response?.data?.message) {
        message.error(error.response.data.message)
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Discount Rule' : 'Create Discount Rule'}
      open={visible}
      onCancel={onCancel}
      width={700}
      showSave
      saveText={isEditing ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
    >
      <Form form={form} layout="vertical">
        <Row gutter={16}>
          <Col xs={24} md={16}>
            <Form.Item
              name="name"
              label="Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Summer Sale 20% Off" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="priority" label="Priority" extra="Higher = runs first">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description of this discount rule" />
        </Form.Item>

        <Divider orientation="left">Discount</Divider>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="discount_type"
              label="Discount Type"
              rules={[{ required: true }]}
            >
              <Select>
                <Option value="percentage">Percentage (%)</Option>
                <Option value="fixed">Fixed Amount</Option>
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="discount_amount"
              label="Discount Amount"
              rules={[{ required: true, message: 'Discount amount is required' }]}
            >
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Divider orientation="left">Applicability</Divider>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="applies_to"
              label="Applies To"
              rules={[{ required: true }]}
            >
              <Select>
                <Option value="all">All Products</Option>
                <Option value="category">Specific Categories</Option>
                <Option value="brand">Specific Brands</Option>
                <Option value="product">Specific Products</Option>
                <Option value="customer_group">Customer Groups</Option>
              </Select>
            </Form.Item>
          </Col>
          {appliesTo && appliesTo !== 'all' && (
            <Col xs={24} md={12}>
              <Form.Item
                name="applies_to_ids"
                label={`Select ${appliesTo === 'customer_group' ? 'Customer Groups' : appliesTo + 's'}`}
              >
                <Select mode="multiple" placeholder="Select items">
                  {/* TODO: Load options dynamically based on applies_to */}
                </Select>
              </Form.Item>
            </Col>
          )}
        </Row>

        <Divider orientation="left">Limits & Validity</Divider>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item name="max_uses" label="Max Total Uses" extra="Leave empty for unlimited">
              <InputNumber min={1} style={{ width: '100%' }} placeholder="Unlimited" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="max_uses_per_customer" label="Max Uses Per Customer">
              <InputNumber min={1} style={{ width: '100%' }} placeholder="Unlimited" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="validity" label="Validity Period">
          <RangePicker
            showTime
            style={{ width: '100%' }}
            placeholder={['Start Date', 'End Date']}
          />
        </Form.Item>

        <Divider orientation="left">Settings</Divider>

        <Row gutter={16}>
          <Col xs={8}>
            <Form.Item name="is_stackable" label="Stackable" valuePropName="checked" extra="Can combine with other rules">
              <Switch />
            </Form.Item>
          </Col>
          <Col xs={8}>
            <Form.Item name="stop_further_rules" label="Stop Further" valuePropName="checked" extra="Stops other rules">
              <Switch />
            </Form.Item>
          </Col>
          <Col xs={8}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
