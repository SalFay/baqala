import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, Switch, message, Row, Col } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createCustomerGroup, updateCustomerGroup } from '@/Helpers/api/customerGroupService'
import { fetchAllPriceGroups } from '@/Helpers/api/priceGroupService'

const { TextArea } = Input
const { Option } = Select

export default function CustomerGroupModal({ visible, onCancel, record, onUpdate, priceGroups: initialPriceGroups = [] }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [priceGroups, setPriceGroups] = useState(initialPriceGroups)

  const isEditing = !!record

  useEffect(() => {
    // Fetch price groups if not provided
    if (initialPriceGroups.length === 0) {
      fetchAllPriceGroups().then(res => setPriceGroups(res.data.data || []))
    }
  }, [initialPriceGroups])

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        description: record.description,
        selling_price_group_id: record.selling_price_group_id,
        discount_percent: record.discount_percent || 0,
        is_default: record.is_default ?? false,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
      })
    } else {
      form.resetFields()
      form.setFieldsValue({
        discount_percent: 0,
        is_active: true,
        is_default: false,
        sort_order: 0,
      })
    }
  }, [record, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      setLoading(true)

      if (isEditing) {
        await updateCustomerGroup(record.id, values)
        message.success('Customer group updated successfully')
      } else {
        await createCustomerGroup(values)
        message.success('Customer group created successfully')
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
      title={isEditing ? 'Edit Customer Group' : 'Create Customer Group'}
      open={visible}
      onCancel={onCancel}
      width={600}
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
              <Input placeholder="e.g., VIP, Wholesale, Regular" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="sort_order" label="Sort Order">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description of this customer group" />
        </Form.Item>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="selling_price_group_id"
              label="Price Group"
              extra="Customers in this group will use this price group"
            >
              <Select allowClear placeholder="Select price group">
                {priceGroups.map(pg => (
                  <Option key={pg.id} value={pg.id}>{pg.name}</Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="discount_percent"
              label="Additional Discount %"
              extra="Extra discount on top of price group"
            >
              <InputNumber min={0} max={100} style={{ width: '100%' }} addonAfter="%" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={12}>
            <Form.Item name="is_default" label="Default Group" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
          <Col xs={12}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
