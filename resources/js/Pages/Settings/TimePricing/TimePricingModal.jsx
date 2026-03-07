import { useEffect, useState } from 'react'
import {
  Form,
  Input,
  InputNumber,
  Select,
  Switch,
  TimePicker,
  DatePicker,
  Checkbox,
  message,
  Row,
  Col,
  Divider,
} from 'antd'
import { useMutation, useQuery } from '@tanstack/react-query'
import { createTimePricing, updateTimePricing } from '@/Helpers/api/timePricingService'
import { fetchCategoriesAll } from '@/Helpers/api/categoryService'
import CustomModal from '@/Components/CustomModal'
import axios from 'axios'
import dayjs from 'dayjs'

const DISCOUNT_TYPES = [
  { value: 'percentage', label: 'Percentage Off' },
  { value: 'fixed', label: 'Fixed Amount Off' },
  { value: 'special_price', label: 'Special Price' },
]

const APPLIES_TO_OPTIONS = [
  { value: 'all', label: 'All Products' },
  { value: 'products', label: 'Specific Products' },
  { value: 'categories', label: 'Categories' },
  { value: 'brands', label: 'Brands' },
]

const DAYS_OF_WEEK = [
  { value: 1, label: 'Mon' },
  { value: 2, label: 'Tue' },
  { value: 3, label: 'Wed' },
  { value: 4, label: 'Thu' },
  { value: 5, label: 'Fri' },
  { value: 6, label: 'Sat' },
  { value: 7, label: 'Sun' },
]

export default function TimePricingModal({ open, onClose, onSuccess, timePricing }) {
  const [form] = Form.useForm()
  const isEditing = !!timePricing
  const [appliesTo, setAppliesTo] = useState('all')
  const [loading, setLoading] = useState(false)

  // Fetch categories
  const { data: categories = [] } = useQuery({
    queryKey: ['categories-all'],
    queryFn: () => fetchCategoriesAll().then(res => res.data?.data || []),
    enabled: appliesTo === 'categories',
  })

  // Fetch products (for dropdown)
  const { data: products = [] } = useQuery({
    queryKey: ['products-dropdown'],
    queryFn: () => axios.get(route('pos.products')).then(res => res.data?.data || []),
    enabled: appliesTo === 'products',
  })

  useEffect(() => {
    if (open && timePricing) {
      setAppliesTo(timePricing.applies_to || 'all')
      form.setFieldsValue({
        ...timePricing,
        time_range: timePricing.start_time && timePricing.end_time
          ? [dayjs(timePricing.start_time, 'HH:mm:ss'), dayjs(timePricing.end_time, 'HH:mm:ss')]
          : null,
        date_range: timePricing.starts_at && timePricing.ends_at
          ? [dayjs(timePricing.starts_at), dayjs(timePricing.ends_at)]
          : null,
      })
    } else if (open) {
      setAppliesTo('all')
      form.resetFields()
      form.setFieldsValue({
        discount_type: 'percentage',
        applies_to: 'all',
        is_active: true,
        priority: 0,
      })
    }
  }, [open, timePricing, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      const data = {
        ...values,
        start_time: values.time_range?.[0]?.format('HH:mm'),
        end_time: values.time_range?.[1]?.format('HH:mm'),
        starts_at: values.date_range?.[0]?.format('YYYY-MM-DD'),
        ends_at: values.date_range?.[1]?.format('YYYY-MM-DD'),
      }
      delete data.time_range
      delete data.date_range

      if (isEditing) {
        await updateTimePricing(timePricing.id, data)
        message.success('Time pricing updated')
      } else {
        await createTimePricing(data)
        message.success('Time pricing created')
      }
      onSuccess()
      form.resetFields()
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
      title={isEditing ? 'Edit Time-Based Pricing' : 'Add Time-Based Pricing'}
      open={open}
      onCancel={onClose}
      width={700}
      showSave
      saveText={isEditing ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
    >
      <Form
        form={form}
        layout="vertical"
        initialValues={{
          discount_type: 'percentage',
          applies_to: 'all',
          is_active: true,
          priority: 0,
        }}
      >
        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="name"
              label="Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Happy Hour, Weekend Special" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item name="description" label="Description">
              <Input placeholder="Describe this pricing rule" />
            </Form.Item>
          </Col>
        </Row>

        <Divider orientation="left" style={{ margin: '8px 0 16px' }}>Discount Settings</Divider>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="discount_type"
              label="Discount Type"
              rules={[{ required: true }]}
            >
              <Select options={DISCOUNT_TYPES} />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="discount_value"
              label="Discount Value"
              rules={[{ required: true, message: 'Value is required' }]}
            >
              <InputNumber
                style={{ width: '100%' }}
                min={0}
                precision={2}
                placeholder="Enter value"
              />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="applies_to"
              label="Applies To"
              rules={[{ required: true }]}
            >
              <Select
                options={APPLIES_TO_OPTIONS}
                onChange={setAppliesTo}
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="priority"
              label="Priority"
            >
              <InputNumber
                style={{ width: '100%' }}
                min={0}
                placeholder="Higher = more priority"
              />
            </Form.Item>
          </Col>
        </Row>

        {appliesTo === 'categories' && (
          <Row gutter={16}>
            <Col xs={24}>
              <Form.Item
                name="category_ids"
                label="Select Categories"
                rules={[{ required: true, message: 'Select at least one category' }]}
              >
                <Select
                  mode="multiple"
                  placeholder="Select categories"
                  options={categories.map(c => ({ value: c.id, label: c.name }))}
                />
              </Form.Item>
            </Col>
          </Row>
        )}

        {appliesTo === 'products' && (
          <Row gutter={16}>
            <Col xs={24}>
              <Form.Item
                name="product_ids"
                label="Select Products"
                rules={[{ required: true, message: 'Select at least one product' }]}
              >
                <Select
                  mode="multiple"
                  placeholder="Select products"
                  showSearch
                  optionFilterProp="label"
                  options={products.map(p => ({ value: p.id, label: p.name }))}
                />
              </Form.Item>
            </Col>
          </Row>
        )}

        <Divider orientation="left" style={{ margin: '8px 0 16px' }}>Schedule</Divider>

        <Row gutter={16}>
          <Col xs={24}>
            <Form.Item
              name="days_of_week"
              label="Active Days"
            >
              <Checkbox.Group style={{ width: '100%' }}>
                <Row>
                  {DAYS_OF_WEEK.map(day => (
                    <Col span={3} key={day.value}>
                      <Checkbox value={day.value}>{day.label}</Checkbox>
                    </Col>
                  ))}
                </Row>
              </Checkbox.Group>
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="time_range"
              label="Active Time Range"
            >
              <TimePicker.RangePicker
                format="HH:mm"
                style={{ width: '100%' }}
                placeholder={['Start Time', 'End Time']}
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="date_range"
              label="Valid Date Range (Optional)"
            >
              <DatePicker.RangePicker
                style={{ width: '100%' }}
                placeholder={['Start Date', 'End Date']}
              />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16} style={{ marginTop: 16 }}>
          <Col xs={24} md={12}>
            <Form.Item
              name="is_active"
              label="Status"
              valuePropName="checked"
            >
              <Switch checkedChildren="Active" unCheckedChildren="Inactive" />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
