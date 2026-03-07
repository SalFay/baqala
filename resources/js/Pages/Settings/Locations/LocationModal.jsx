import { useEffect } from 'react'
import { Form, Input, Switch, Row, Col, message } from 'antd'
import { useMutation } from '@tanstack/react-query'
import CustomModal from '@/Components/CustomModal'
import { createLocation, updateLocation } from '@/Helpers/api/locationService'

export default function LocationModal({
  open,
  onClose,
  onSuccess,
  location,
}) {
  const [form] = Form.useForm()
  const isEditing = !!location

  useEffect(() => {
    if (open && location) {
      form.setFieldsValue({
        name: location.name,
        code: location.code,
        address: location.address,
        city: location.city,
        state: location.state,
        country: location.country,
        postal_code: location.postal_code,
        phone: location.phone,
        email: location.email,
        is_main: location.is_main,
        is_active: location.is_active !== false,
      })
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({ is_active: true, is_main: false })
    }
  }, [open, location, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createLocation(data),
    onSuccess: () => {
      message.success('Location created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create location')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateLocation(location.id, data),
    onSuccess: () => {
      message.success('Location updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update location')
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
      title={isEditing ? 'Edit Location' : 'Add Location'}
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
          <Col span={16}>
            <Form.Item
              name="name"
              label="Location Name"
              rules={[{ required: true, message: 'Please enter location name' }]}
            >
              <Input placeholder="Enter location name" />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item
              name="code"
              label="Code"
              rules={[{ required: true, message: 'Please enter code' }]}
            >
              <Input placeholder="e.g., LOC-001" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="address" label="Address">
          <Input.TextArea rows={2} placeholder="Enter address" />
        </Form.Item>

        <Row gutter={16}>
          <Col span={8}>
            <Form.Item name="city" label="City">
              <Input placeholder="Enter city" />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item name="state" label="State/Province">
              <Input placeholder="Enter state" />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item name="postal_code" label="Postal Code">
              <Input placeholder="Enter postal code" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="country" label="Country">
              <Input placeholder="Enter country" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="phone" label="Phone">
              <Input placeholder="Enter phone number" />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="email" label="Email">
          <Input placeholder="Enter email" type="email" />
        </Form.Item>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="is_main"
              label="Main Location"
              valuePropName="checked"
            >
              <Switch disabled={isEditing && location?.is_main} />
            </Form.Item>
          </Col>
          <Col span={12}>
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
