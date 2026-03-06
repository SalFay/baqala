import { useEffect } from 'react'
import {
  Form,
  Input,
  InputNumber,
  Select,
  Row,
  Col,
  Divider,
} from 'antd'
import CustomModal from '@/Components/CustomModal'
import { getCurrency } from '@/Helpers/formatters'

const { Option } = Select

export default function CustomerFormModal({
  open,
  onClose,
  onSubmit,
  loading,
  customer,
  customerGroups = [],
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
        customer_group_id: customer.customer_group_id,
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
    <CustomModal
      title={isEditing ? 'Edit Customer' : 'Add Customer'}
      open={open}
      onCancel={handleClose}
      width={600}
      showSave
      saveText={isEditing ? 'Update' : 'Create'}
      loading={loading}
      onSave={handleSubmit}
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

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="customer_group_id" label="Customer Group">
              <Select allowClear placeholder="Select customer group">
                {customerGroups.map(group => (
                  <Option key={group.id} value={group.id}>{group.name}</Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="credit_limit" label={`Credit Limit (${getCurrency()})`}>
              <InputNumber
                placeholder="0.00"
                min={0}
                precision={2}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </CustomModal>
  )
}
