import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, Switch, message, Row, Col, DatePicker, Divider, Button, Space } from 'antd'
import { ReloadOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { createCoupon, updateCoupon, generateCouponCode } from '@/Helpers/api/discountService'
import dayjs from 'dayjs'

const { TextArea } = Input
const { Option } = Select
const { RangePicker } = DatePicker

export default function CouponModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [generatingCode, setGeneratingCode] = useState(false)

  const isEditing = !!record
  const discountType = Form.useWatch('discount_type', form)
  const appliesTo = Form.useWatch('applies_to', form)

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        code: record.code,
        name: record.name,
        description: record.description,
        discount_type: record.discount_type || 'percentage',
        discount_amount: record.discount_amount || 0,
        applies_to: record.applies_to || 'all',
        applies_to_ids: record.applies_to_ids || [],
        min_order_amount: record.min_order_amount,
        max_discount_amount: record.max_discount_amount,
        customer_ids: record.customer_ids || [],
        customer_group_ids: record.customer_group_ids || [],
        first_order_only: record.first_order_only ?? false,
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
        first_order_only: false,
        is_active: true,
      })
      handleGenerateCode()
    }
  }, [record, form])

  const handleGenerateCode = async () => {
    setGeneratingCode(true)
    try {
      const response = await generateCouponCode()
      form.setFieldValue('code', response.data.code)
    } catch (error) {
      message.error('Failed to generate code')
    } finally {
      setGeneratingCode(false)
    }
  }

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

      // Remove discount_amount for free shipping
      if (payload.discount_type === 'free_shipping') {
        payload.discount_amount = null
      }

      if (isEditing) {
        await updateCoupon(record.id, payload)
        message.success('Coupon updated successfully')
      } else {
        await createCoupon(payload)
        message.success('Coupon created successfully')
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
      title={isEditing ? 'Edit Coupon' : 'Create Coupon'}
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
          <Col xs={24} md={12}>
            <Form.Item
              name="code"
              label="Coupon Code"
              rules={[{ required: true, message: 'Code is required' }]}
            >
              <Input
                placeholder="e.g., SUMMER20"
                style={{ textTransform: 'uppercase' }}
                suffix={
                  <Button
                    type="text"
                    size="small"
                    icon={<ReloadOutlined spin={generatingCode} />}
                    onClick={handleGenerateCode}
                    disabled={generatingCode}
                  />
                }
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="name" label="Display Name">
              <Input placeholder="e.g., Summer Sale 20% Off" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description (visible to staff)" />
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
                <Option value="free_shipping">Free Shipping</Option>
              </Select>
            </Form.Item>
          </Col>
          {discountType !== 'free_shipping' && (
            <Col xs={24} md={12}>
              <Form.Item
                name="discount_amount"
                label="Discount Amount"
                rules={[{ required: discountType !== 'free_shipping', message: 'Amount required' }]}
              >
                <InputNumber min={0} style={{ width: '100%' }} />
              </Form.Item>
            </Col>
          )}
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
              </Select>
            </Form.Item>
          </Col>
          {appliesTo && appliesTo !== 'all' && (
            <Col xs={24} md={12}>
              <Form.Item
                name="applies_to_ids"
                label={`Select ${appliesTo}s`}
              >
                <Select mode="multiple" placeholder="Select items">
                  {/* TODO: Load options dynamically */}
                </Select>
              </Form.Item>
            </Col>
          )}
        </Row>

        <Divider orientation="left">Restrictions</Divider>

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item name="min_order_amount" label="Min Order Amount">
              <InputNumber min={0} style={{ width: '100%' }} placeholder="No minimum" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="max_discount_amount" label="Max Discount" extra="For % discounts">
              <InputNumber min={0} style={{ width: '100%' }} placeholder="No limit" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="first_order_only" label="First Order Only" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item name="customer_group_ids" label="Limit to Customer Groups">
              <Select mode="multiple" placeholder="All customers" allowClear>
                {/* TODO: Load customer groups */}
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="customer_ids" label="Limit to Specific Customers">
              <Select mode="multiple" placeholder="All customers" allowClear>
                {/* TODO: Load customers */}
              </Select>
            </Form.Item>
          </Col>
        </Row>

        <Divider orientation="left">Limits & Validity</Divider>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item name="max_uses" label="Max Total Uses">
              <InputNumber min={1} style={{ width: '100%' }} placeholder="Unlimited" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="max_uses_per_customer" label="Max Uses Per Customer">
              <InputNumber min={1} style={{ width: '100%' }} placeholder="Unlimited" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={16}>
            <Form.Item name="validity" label="Validity Period">
              <RangePicker
                showTime
                style={{ width: '100%' }}
                placeholder={['Start Date', 'End Date']}
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
