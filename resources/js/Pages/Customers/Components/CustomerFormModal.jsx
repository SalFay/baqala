import { useEffect } from 'react'
import {
  Modal,
  Form,
  Input,
  InputNumber,
  Select,
  Row,
  Col,
  Divider,
} from 'antd'

const { Option } = Select

export default function CustomerFormModal({
  open,
  onClose,
  onSubmit,
  loading,
  customer,
}) {
  const [form] = Form.useForm()
  const isEditing = !!customer

  useEffect(() => {
    if (open && customer) {
      form.setFieldsValue({
        first_name: customer.first_name,
        last_name: customer.last_name,
        email: customer.email,
        phone: customer.phone,
        address: customer.address,
        city: customer.city,
        credit_limit: customer.credit_limit,
        status: customer.status || 'active',
      })
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({ status: 'active' })
    }
  }, [open, customer, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      onSubmit(values)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleClose = () => {
    form.resetFields()
    onClose()
  }

  return (
    <Modal
      title={isEditing ? 'Edit Customer' : 'Add Customer'}
      open={open}
      onOk={handleSubmit}
      onCancel={handleClose}
      okText={isEditing ? 'Update' : 'Create'}
      confirmLoading={loading}
      width={600}
      destroyOnClose
    >
      <Form
        form={form}
        layout="vertical"
        initialValues={{ status: 'active' }}
      >
        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="first_name"
              label="First Name"
              rules={[{ required: true, message: 'Please enter first name' }]}
            >
              <Input placeholder="Enter first name" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="last_name"
              label="Last Name"
              rules={[{ required: true, message: 'Please enter last name' }]}
            >
              <Input placeholder="Enter last name" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="email"
              label="Email"
              rules={[
                { type: 'email', message: 'Please enter valid email' },
              ]}
            >
              <Input placeholder="Enter email (optional)" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="phone"
              label="Phone"
              rules={[{ required: true, message: 'Please enter phone number' }]}
            >
              <Input placeholder="Enter phone number" />
            </Form.Item>
          </Col>
        </Row>

        <Divider />

        <Form.Item name="address" label="Address">
          <Input.TextArea
            rows={2}
            placeholder="Enter address (optional)"
          />
        </Form.Item>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="city" label="City">
              <Input placeholder="Enter city" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="status" label="Status">
              <Select>
                <Option value="active">Active</Option>
                <Option value="inactive">Inactive</Option>
              </Select>
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="credit_limit" label="Credit Limit (SAR)">
          <InputNumber
            placeholder="0.00"
            min={0}
            precision={2}
            style={{ width: '100%' }}
          />
        </Form.Item>
      </Form>
    </Modal>
  )
}
