import { useEffect, useState } from 'react'
import { Form, Input, Switch, InputNumber, Select, message, Row, Col, Button, Space, Card, Checkbox } from 'antd'
import { PlusOutlined, DeleteOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { createModifierSet, updateModifierSet } from '@/Helpers/api/modifierSetService'

const { TextArea } = Input
const { Option } = Select

export default function ModifierSetModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [modifiers, setModifiers] = useState([])
  const selectionType = Form.useWatch('selection_type', form)

  const isEditing = !!record

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        description: record.description,
        selection_type: record.selection_type || 'multiple',
        is_required: record.is_required ?? false,
        min_selections: record.min_selections ?? 0,
        max_selections: record.max_selections,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
      })
      setModifiers(record.modifiers || [])
    } else {
      form.resetFields()
      form.setFieldsValue({
        selection_type: 'multiple',
        is_required: false,
        min_selections: 0,
        is_active: true,
        sort_order: 0,
      })
      setModifiers([{ name: '', price_adjustment: 0, price_type: 'fixed', is_default: false, is_active: true }])
    }
  }, [record, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      // Validate modifiers
      const validModifiers = modifiers.filter(m => m.name.trim())

      if (validModifiers.length === 0) {
        message.error('At least one modifier option is required')
        return
      }

      // For single selection, only one can be default
      if (values.selection_type === 'single') {
        const defaultCount = validModifiers.filter(m => m.is_default).length
        if (defaultCount > 1) {
          message.error('Only one modifier can be default for single selection')
          return
        }
      }

      setLoading(true)

      const data = {
        ...values,
        modifiers: validModifiers,
      }

      if (isEditing) {
        await updateModifierSet(record.id, data)
        message.success('Modifier set updated successfully')
      } else {
        await createModifierSet(data)
        message.success('Modifier set created successfully')
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

  const addModifier = () => {
    setModifiers([...modifiers, {
      name: '',
      price_adjustment: 0,
      price_type: 'fixed',
      is_default: false,
      is_active: true,
      sort_order: modifiers.length,
    }])
  }

  const removeModifier = (index) => {
    const newModifiers = [...modifiers]
    newModifiers.splice(index, 1)
    setModifiers(newModifiers)
  }

  const updateModifier = (index, field, value) => {
    const newModifiers = [...modifiers]
    newModifiers[index][field] = value

    // For single selection, ensure only one default
    if (field === 'is_default' && value && selectionType === 'single') {
      newModifiers.forEach((m, i) => {
        if (i !== index) m.is_default = false
      })
    }

    setModifiers(newModifiers)
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Modifier Set' : 'Create Modifier Set'}
      open={visible}
      onCancel={onCancel}
      width={800}
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
              label="Set Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Extra Toppings, Sauce Selection" />
            </Form.Item>
          </Col>
          <Col xs={24} md={6}>
            <Form.Item
              name="selection_type"
              label="Selection Type"
              rules={[{ required: true }]}
            >
              <Select>
                <Option value="single">Single (pick one)</Option>
                <Option value="multiple">Multiple (pick many)</Option>
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={6}>
            <Form.Item name="sort_order" label="Sort Order">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description of this modifier set" />
        </Form.Item>

        <Row gutter={16}>
          <Col xs={24} md={6}>
            <Form.Item name="is_required" label="Required" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
          {selectionType === 'multiple' && (
            <>
              <Col xs={24} md={6}>
                <Form.Item name="min_selections" label="Min Selections">
                  <InputNumber min={0} style={{ width: '100%' }} />
                </Form.Item>
              </Col>
              <Col xs={24} md={6}>
                <Form.Item name="max_selections" label="Max Selections">
                  <InputNumber min={1} style={{ width: '100%' }} placeholder="Unlimited" />
                </Form.Item>
              </Col>
            </>
          )}
          <Col xs={24} md={6}>
            <Form.Item name="is_active" label="Active" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Col>
        </Row>

        <div style={{ marginBottom: 16 }}>
          <div style={{ fontWeight: 500, marginBottom: 8 }}>Modifier Options</div>

          {modifiers.map((modifier, index) => (
            <Card key={index} size="small" style={{ marginBottom: 8 }}>
              <Row gutter={8} align="middle">
                <Col flex="1">
                  <Input
                    placeholder="Option name"
                    value={modifier.name}
                    onChange={(e) => updateModifier(index, 'name', e.target.value)}
                  />
                </Col>
                <Col>
                  <Space.Compact>
                    <InputNumber
                      placeholder="Price"
                      value={modifier.price_adjustment}
                      onChange={(v) => updateModifier(index, 'price_adjustment', v || 0)}
                      style={{ width: 100 }}
                    />
                    <Select
                      value={modifier.price_type}
                      onChange={(v) => updateModifier(index, 'price_type', v)}
                      style={{ width: 80 }}
                    >
                      <Option value="fixed">Fixed</Option>
                      <Option value="percentage">%</Option>
                    </Select>
                  </Space.Compact>
                </Col>
                <Col>
                  <Checkbox
                    checked={modifier.is_default}
                    onChange={(e) => updateModifier(index, 'is_default', e.target.checked)}
                  >
                    Default
                  </Checkbox>
                </Col>
                <Col>
                  <Checkbox
                    checked={modifier.is_active}
                    onChange={(e) => updateModifier(index, 'is_active', e.target.checked)}
                  >
                    Active
                  </Checkbox>
                </Col>
                <Col>
                  {modifiers.length > 1 && (
                    <Button
                      type="text"
                      danger
                      icon={<DeleteOutlined />}
                      onClick={() => removeModifier(index)}
                    />
                  )}
                </Col>
              </Row>
            </Card>
          ))}

          <Button
            type="dashed"
            block
            icon={<PlusOutlined />}
            onClick={addModifier}
          >
            Add Option
          </Button>
        </div>
      </Form>
    </CustomModal>
  )
}
