import { useEffect } from 'react'
import {
  Modal,
  Form,
  Input,
  InputNumber,
  Select,
  Switch,
  Upload,
  Row,
  Col,
  Divider,
  message,
} from 'antd'
import { PlusOutlined } from '@ant-design/icons'
import { getCurrency } from '@/Helpers/formatters'

const { TextArea } = Input
const { Option } = Select

export default function ProductFormModal({
  open,
  onClose,
  onSubmit,
  loading,
  product,
  categories,
}) {
  const [form] = Form.useForm()
  const isEditing = !!product

  useEffect(() => {
    if (open && product) {
      form.setFieldsValue({
        name: product.name,
        sku: product.sku,
        barcode: product.barcode,
        category_id: product.category_id,
        description: product.description,
        sale_price: product.price || product.sale_price,
        cost_price: product.cost || product.cost_price,
        is_active: product.is_active ?? true,
      })
    } else if (open) {
      form.resetFields()
      form.setFieldsValue({ is_active: true })
    }
  }, [open, product, form])

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
      title={isEditing ? 'Edit Product' : 'Add Product'}
      open={open}
      onOk={handleSubmit}
      onCancel={handleClose}
      okText={isEditing ? 'Update' : 'Create'}
      confirmLoading={loading}
      width={700}
      destroyOnClose
    >
      <Form
        form={form}
        layout="vertical"
        initialValues={{ is_active: true }}
      >
        <Row gutter={16}>
          <Col span={24}>
            <Form.Item
              name="name"
              label="Product Name"
              rules={[{ required: true, message: 'Please enter product name' }]}
            >
              <Input placeholder="Enter product name" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="sku"
              label="SKU"
              rules={[{ required: true, message: 'Please enter SKU' }]}
            >
              <Input placeholder="e.g., PRD-001" />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="barcode" label="Barcode">
              <Input placeholder="Enter barcode (optional)" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item name="category_id" label="Category">
              <Select placeholder="Select category" allowClear>
                {categories?.map((cat) => (
                  <Option key={cat.id} value={cat.id}>
                    {cat.name}
                  </Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name="is_active"
              label="Status"
              valuePropName="checked"
            >
              <Switch checkedChildren="Active" unCheckedChildren="Inactive" />
            </Form.Item>
          </Col>
        </Row>

        <Divider />

        <Row gutter={16}>
          <Col span={12}>
            <Form.Item
              name="sale_price"
              label={`Sale Price (${getCurrency()})`}
              rules={[{ required: true, message: 'Please enter sale price' }]}
            >
              <InputNumber
                placeholder="0.00"
                min={0}
                precision={2}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item name="cost_price" label={`Cost Price (${getCurrency()})`}>
              <InputNumber
                placeholder="0.00"
                min={0}
                precision={2}
                style={{ width: '100%' }}
              />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="description" label="Description">
          <TextArea
            rows={3}
            placeholder="Product description (optional)"
          />
        </Form.Item>
      </Form>
    </Modal>
  )
}
