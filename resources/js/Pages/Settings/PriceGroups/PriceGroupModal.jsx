import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, Switch, message, Row, Col } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createPriceGroup, updatePriceGroup } from '@/Helpers/api/priceGroupService'

const { TextArea } = Input
const { Option } = Select

export default function PriceGroupModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const isEditing = !!record

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        description: record.description,
        price_calculation_type: record.price_calculation_type || 'fixed',
        price_calculation_amount: record.price_calculation_amount || 0,
        is_default: record.is_default ?? false,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
      })
    } else {
      form.resetFields()
      form.setFieldsValue({
        price_calculation_type: 'fixed',
        price_calculation_amount: 0,
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
        await updatePriceGroup(record.id, values)
        message.success('Price group updated successfully')
      } else {
        await createPriceGroup(values)
        message.success('Price group created successfully')
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
      title={isEditing ? 'Edit Price Group' : 'Create Price Group'}
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
              <Input placeholder="e.g., Wholesale, VIP, Retail" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="sort_order" label="Sort Order">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description of this price group" />
        </Form.Item>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item
              name="price_calculation_type"
              label="Calculation Type"
              rules={[{ required: true }]}
            >
              <Select>
                <Option value="fixed">Fixed Amount</Option>
                <Option value="percentage">Percentage</Option>
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="price_calculation_amount"
              label="Amount"
              rules={[{ required: true }]}
              extra="Positive = markup, Negative = discount"
            >
              <InputNumber style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={12}>
            <Form.Item name="is_default" label="Default" valuePropName="checked">
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
