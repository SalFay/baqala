import { useEffect, useState } from 'react'
import { Form, Input, Switch, InputNumber, Select, message, Row, Col } from 'antd'
import CustomModal from '@/Components/CustomModal'
import { createUnit, updateUnit, fetchBaseUnits } from '@/Helpers/api/unitService'

const { Option } = Select

export default function UnitModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [baseUnits, setBaseUnits] = useState([])
  const [loadingBaseUnits, setLoadingBaseUnits] = useState(false)
  const isBaseUnit = Form.useWatch('is_base_unit', form)

  const isEditing = !!record

  useEffect(() => {
    loadBaseUnits()
  }, [])

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        short_name: record.short_name,
        is_base_unit: record.is_base_unit ?? true,
        base_unit_id: record.base_unit_id,
        conversion_rate: record.conversion_rate ?? 1,
        allow_decimal: record.allow_decimal ?? true,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
      })
    } else {
      form.resetFields()
      form.setFieldsValue({
        is_base_unit: true,
        conversion_rate: 1,
        allow_decimal: true,
        is_active: true,
        sort_order: 0,
      })
    }
  }, [record, form])

  const loadBaseUnits = async () => {
    setLoadingBaseUnits(true)
    try {
      const response = await fetchBaseUnits()
      setBaseUnits(response.data.data || [])
    } catch (error) {
      console.error('Failed to load base units:', error)
    } finally {
      setLoadingBaseUnits(false)
    }
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      // If it's a base unit, clear conversion fields
      if (values.is_base_unit) {
        values.base_unit_id = null
        values.conversion_rate = 1
      }

      setLoading(true)

      if (isEditing) {
        await updateUnit(record.id, values)
        message.success('Unit updated successfully')
      } else {
        await createUnit(values)
        message.success('Unit created successfully')
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

  // Filter out current unit from base units (can't be its own base)
  const availableBaseUnits = baseUnits.filter(u => !record || u.id !== record.id)

  return (
    <CustomModal
      title={isEditing ? 'Edit Unit' : 'Create Unit'}
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
          <Col xs={24} md={12}>
            <Form.Item
              name="name"
              label="Unit Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Kilogram, Piece, Box" />
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="short_name"
              label="Short Name"
              rules={[{ required: true, message: 'Short name is required' }]}
            >
              <Input placeholder="e.g., kg, pc, box" maxLength={20} />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item name="is_base_unit" label="Base Unit" valuePropName="checked">
              <Switch checkedChildren="Yes" unCheckedChildren="No" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="allow_decimal" label="Allow Decimal" valuePropName="checked">
              <Switch checkedChildren="Yes" unCheckedChildren="No" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>

        {!isBaseUnit && (
          <Row gutter={16}>
            <Col xs={24} md={12}>
              <Form.Item
                name="base_unit_id"
                label="Base Unit"
                rules={[{ required: !isBaseUnit, message: 'Select a base unit' }]}
              >
                <Select
                  placeholder="Select base unit"
                  loading={loadingBaseUnits}
                  allowClear
                >
                  {availableBaseUnits.map(unit => (
                    <Option key={unit.id} value={unit.id}>
                      {unit.name} ({unit.short_name})
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item
                name="conversion_rate"
                label="Conversion Rate"
                tooltip="How many base units equal 1 of this unit"
                rules={[{ required: !isBaseUnit, message: 'Conversion rate is required' }]}
              >
                <InputNumber
                  min={0.0001}
                  max={9999999999}
                  step={0.01}
                  precision={4}
                  style={{ width: '100%' }}
                  placeholder="e.g., 12 (1 box = 12 pieces)"
                />
              </Form.Item>
            </Col>
          </Row>
        )}

        <Form.Item name="sort_order" label="Sort Order">
          <InputNumber min={0} style={{ width: '100%' }} />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
