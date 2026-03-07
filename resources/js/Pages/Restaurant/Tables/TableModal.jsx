import { useEffect } from 'react'
import { Form, Input, InputNumber, Select, Switch, Row, Col, message } from 'antd'
import { useMutation } from '@tanstack/react-query'
import CustomModal from '@/Components/CustomModal'
import LocationSelector from '@/Components/LocationSelector'
import { createTable, updateTable } from '@/Helpers/api/restaurantService'

export default function TableModal({
  open,
  onClose,
  onSuccess,
  table,
}) {
  const [form] = Form.useForm()
  const isEditing = !!table

  useEffect(() => {
    if (open && table) {
      form.setFieldsValue({
        name: table.name,
        location_id: table.location_id,
        capacity: table.capacity,
        section: table.section,
        floor: table.floor,
        shape: table.shape || 'square',
        is_active: table.is_active !== false,
      })
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({
        is_active: true,
        capacity: 4,
        shape: 'square',
      })
    }
  }, [open, table, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createTable(data),
    onSuccess: () => {
      message.success('Table created successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create table')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateTable(table.id, data),
    onSuccess: () => {
      message.success('Table updated successfully')
      onSuccess()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update table')
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
      title={isEditing ? 'Edit Table' : 'Add Table'}
      open={open}
      onCancel={handleClose}
      width={500}
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
              label="Table Name"
              rules={[{ required: true, message: 'Please enter table name' }]}
            >
              <Input placeholder="e.g., Table 1, T-01" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="capacity"
              label="Capacity"
              rules={[{ required: true, message: 'Please enter capacity' }]}
            >
              <InputNumber
                min={1}
                max={50}
                placeholder="4"
                style={{ width: '100%' }}
                suffix="seats"
              />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="section" label="Section">
              <Select placeholder="Select section" allowClear>
                <Select.Option value="Indoor">Indoor</Select.Option>
                <Select.Option value="Outdoor">Outdoor</Select.Option>
                <Select.Option value="Terrace">Terrace</Select.Option>
                <Select.Option value="VIP">VIP</Select.Option>
                <Select.Option value="Private">Private</Select.Option>
              </Select>
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="floor" label="Floor">
              <Select placeholder="Select floor" allowClear>
                <Select.Option value="Ground Floor">Ground Floor</Select.Option>
                <Select.Option value="1st Floor">1st Floor</Select.Option>
                <Select.Option value="2nd Floor">2nd Floor</Select.Option>
                <Select.Option value="Rooftop">Rooftop</Select.Option>
              </Select>
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="shape" label="Shape">
              <Select>
                <Select.Option value="square">Square</Select.Option>
                <Select.Option value="rectangle">Rectangle</Select.Option>
                <Select.Option value="circle">Circle</Select.Option>
              </Select>
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="location_id" label="Location">
              <LocationSelector allowClear />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item
          name="is_active"
          label="Active"
          valuePropName="checked"
        >
          <Switch />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
