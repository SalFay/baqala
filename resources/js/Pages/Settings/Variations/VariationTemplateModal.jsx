import { useEffect, useState } from 'react'
import { Form, Input, Switch, InputNumber, message, Row, Col, Button, Space, Card, Tag } from 'antd'
import { PlusOutlined, DeleteOutlined, CloseOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { createVariationTemplate, updateVariationTemplate } from '@/Helpers/api/variationTemplateService'

const { TextArea } = Input

export default function VariationTemplateModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [attributes, setAttributes] = useState([])

  const isEditing = !!record

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        name: record.name,
        description: record.description,
        is_active: record.is_active ?? true,
        sort_order: record.sort_order ?? 0,
      })
      setAttributes(record.attributes || [])
    } else {
      form.resetFields()
      form.setFieldsValue({
        is_active: true,
        sort_order: 0,
      })
      setAttributes([{ name: '', values: [''] }])
    }
  }, [record, form])

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      // Validate attributes
      const validAttributes = attributes.filter(attr =>
        attr.name.trim() && attr.values.some(v => v.trim())
      ).map(attr => ({
        name: attr.name.trim(),
        values: attr.values.filter(v => v.trim())
      }))

      if (validAttributes.length === 0) {
        message.error('At least one attribute with values is required')
        return
      }

      setLoading(true)

      const data = {
        ...values,
        attributes: validAttributes,
      }

      if (isEditing) {
        await updateVariationTemplate(record.id, data)
        message.success('Variation template updated successfully')
      } else {
        await createVariationTemplate(data)
        message.success('Variation template created successfully')
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

  const addAttribute = () => {
    setAttributes([...attributes, { name: '', values: [''] }])
  }

  const removeAttribute = (index) => {
    const newAttributes = [...attributes]
    newAttributes.splice(index, 1)
    setAttributes(newAttributes)
  }

  const updateAttributeName = (index, name) => {
    const newAttributes = [...attributes]
    newAttributes[index].name = name
    setAttributes(newAttributes)
  }

  const addValue = (attrIndex) => {
    const newAttributes = [...attributes]
    newAttributes[attrIndex].values.push('')
    setAttributes(newAttributes)
  }

  const updateValue = (attrIndex, valueIndex, value) => {
    const newAttributes = [...attributes]
    newAttributes[attrIndex].values[valueIndex] = value
    setAttributes(newAttributes)
  }

  const removeValue = (attrIndex, valueIndex) => {
    const newAttributes = [...attributes]
    newAttributes[attrIndex].values.splice(valueIndex, 1)
    if (newAttributes[attrIndex].values.length === 0) {
      newAttributes[attrIndex].values = ['']
    }
    setAttributes(newAttributes)
  }

  // Calculate possible combinations
  const getCombinationsCount = () => {
    const validAttributes = attributes.filter(attr =>
      attr.name.trim() && attr.values.some(v => v.trim())
    )
    if (validAttributes.length === 0) return 0
    return validAttributes.reduce((total, attr) => {
      const validValues = attr.values.filter(v => v.trim()).length
      return total * (validValues || 1)
    }, 1)
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Variation Template' : 'Create Variation Template'}
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
          <Col xs={24} md={16}>
            <Form.Item
              name="name"
              label="Template Name"
              rules={[{ required: true, message: 'Name is required' }]}
            >
              <Input placeholder="e.g., Clothing Sizes, Mobile Colors" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="sort_order" label="Sort Order">
              <InputNumber min={0} style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea rows={2} placeholder="Description of this variation template" />
        </Form.Item>

        <div style={{ marginBottom: 16 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
            <span style={{ fontWeight: 500 }}>Attributes</span>
            <Tag color="blue">{getCombinationsCount()} possible combinations</Tag>
          </div>

          {attributes.map((attr, attrIndex) => (
            <Card
              key={attrIndex}
              size="small"
              style={{ marginBottom: 12 }}
              title={
                <Input
                  placeholder="Attribute name (e.g., Size, Color)"
                  value={attr.name}
                  onChange={(e) => updateAttributeName(attrIndex, e.target.value)}
                  style={{ width: '100%' }}
                  bordered={false}
                />
              }
              extra={
                attributes.length > 1 && (
                  <Button
                    type="text"
                    danger
                    size="small"
                    icon={<DeleteOutlined />}
                    onClick={() => removeAttribute(attrIndex)}
                  />
                )
              }
            >
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, alignItems: 'center' }}>
                {attr.values.map((value, valueIndex) => (
                  <Space.Compact key={valueIndex}>
                    <Input
                      placeholder="Value"
                      value={value}
                      onChange={(e) => updateValue(attrIndex, valueIndex, e.target.value)}
                      style={{ width: 120 }}
                    />
                    {attr.values.length > 1 && (
                      <Button
                        icon={<CloseOutlined />}
                        onClick={() => removeValue(attrIndex, valueIndex)}
                      />
                    )}
                  </Space.Compact>
                ))}
                <Button
                  type="dashed"
                  size="small"
                  icon={<PlusOutlined />}
                  onClick={() => addValue(attrIndex)}
                >
                  Value
                </Button>
              </div>
            </Card>
          ))}

          <Button
            type="dashed"
            block
            icon={<PlusOutlined />}
            onClick={addAttribute}
          >
            Add Attribute
          </Button>
        </div>

        <Form.Item name="is_active" label="Active" valuePropName="checked">
          <Switch />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
