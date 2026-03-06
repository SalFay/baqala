import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, message, Row, Col, DatePicker, Divider } from 'antd'
import dayjs from 'dayjs'
import CustomModal from '@/Components/CustomModal'
import { createBatch, updateBatch } from '@/Helpers/api/productBatchService'
import axios from 'axios'

const { Option } = Select
const { TextArea } = Input

const STATUS_OPTIONS = [
  { value: 'active', label: 'Active' },
  { value: 'low_stock', label: 'Low Stock' },
  { value: 'quarantine', label: 'Quarantine' },
]

export default function BatchModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [products, setProducts] = useState([])
  const [loadingProducts, setLoadingProducts] = useState(false)

  const isEditing = !!record

  useEffect(() => {
    loadProducts()
  }, [])

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        product_id: record.product?.id,
        batch_number: record.batch_number,
        lot_number: record.lot_number,
        manufacturing_date: record.manufacturing_date ? dayjs(record.manufacturing_date) : null,
        expiry_date: record.expiry_date ? dayjs(record.expiry_date) : null,
        purchase_price: record.purchase_price,
        received_date: record.received_date ? dayjs(record.received_date) : null,
        quantity_purchased: record.quantity_purchased,
        quantity_available: record.quantity_available,
        status: record.status,
        notes: record.notes,
      })
    } else {
      form.resetFields()
      form.setFieldsValue({
        status: 'active',
        received_date: dayjs(),
      })
    }
  }, [record, form])

  const loadProducts = async () => {
    setLoadingProducts(true)
    try {
      // Fetch products that have batch/expiry tracking enabled
      const response = await axios.get(route('pos.products'), {
        params: { enable_batch_tracking: true },
      })
      setProducts(response.data.products || [])
    } catch (error) {
      console.error('Failed to load products:', error)
    } finally {
      setLoadingProducts(false)
    }
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      // Format dates
      if (values.manufacturing_date) {
        values.manufacturing_date = values.manufacturing_date.format('YYYY-MM-DD')
      }
      if (values.expiry_date) {
        values.expiry_date = values.expiry_date.format('YYYY-MM-DD')
      }
      if (values.received_date) {
        values.received_date = values.received_date.format('YYYY-MM-DD')
      }

      setLoading(true)

      if (isEditing) {
        await updateBatch(record.id, values)
        message.success('Batch updated successfully')
      } else {
        await createBatch(values)
        message.success('Batch created successfully')
      }

      onUpdate()
      onCancel()
    } catch (error) {
      if (error.response?.data?.message) {
        message.error(error.response.data.message)
      } else if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        message.error(errors[0])
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Batch' : 'Add Batch'}
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
          <Col xs={24} md={12}>
            <Form.Item
              name="product_id"
              label="Product"
              rules={[{ required: true, message: 'Product is required' }]}
            >
              <Select
                placeholder="Select product"
                loading={loadingProducts}
                showSearch
                optionFilterProp="children"
                disabled={isEditing}
              >
                {products.map(product => (
                  <Option key={product.id} value={product.id}>
                    {product.name} ({product.sku})
                  </Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
          <Col xs={24} md={12}>
            <Form.Item
              name="batch_number"
              label="Batch Number"
              rules={[{ required: true, message: 'Batch number is required' }]}
            >
              <Input placeholder="Enter batch number" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={12}>
            <Form.Item name="lot_number" label="Lot Number">
              <Input placeholder="Enter lot number (optional)" />
            </Form.Item>
          </Col>
          {isEditing && (
            <Col xs={24} md={12}>
              <Form.Item name="status" label="Status">
                <Select>
                  {STATUS_OPTIONS.map(opt => (
                    <Option key={opt.value} value={opt.value}>{opt.label}</Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
          )}
        </Row>

        <Divider>Dates</Divider>

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item name="manufacturing_date" label="Manufacturing Date">
              <DatePicker
                style={{ width: '100%' }}
                disabledDate={(current) => current && current > dayjs().endOf('day')}
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item
              name="expiry_date"
              label="Expiry Date"
              rules={[{ required: true, message: 'Expiry date is required' }]}
            >
              <DatePicker
                style={{ width: '100%' }}
                disabledDate={(current) => current && current < dayjs().startOf('day')}
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="received_date" label="Received Date">
              <DatePicker style={{ width: '100%' }} />
            </Form.Item>
          </Col>
        </Row>

        <Divider>Quantity & Pricing</Divider>

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item
              name="quantity_purchased"
              label="Quantity Purchased"
              rules={[{ required: !isEditing, message: 'Quantity is required' }]}
            >
              <InputNumber
                min={0.0001}
                precision={4}
                style={{ width: '100%' }}
                disabled={isEditing}
              />
            </Form.Item>
          </Col>
          {isEditing && (
            <Col xs={24} md={8}>
              <Form.Item
                name="quantity_available"
                label="Quantity Available"
              >
                <InputNumber
                  min={0}
                  precision={4}
                  style={{ width: '100%' }}
                />
              </Form.Item>
            </Col>
          )}
          <Col xs={24} md={8}>
            <Form.Item name="purchase_price" label="Purchase Price (per unit)">
              <InputNumber
                min={0}
                precision={2}
                style={{ width: '100%' }}
                prefix="$"
              />
            </Form.Item>
          </Col>
        </Row>

        <Form.Item name="notes" label="Notes">
          <TextArea rows={2} placeholder="Additional notes" />
        </Form.Item>
      </Form>
    </CustomModal>
  )
}
