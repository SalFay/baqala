import { useEffect, useState } from 'react'
import { Form, Input, InputNumber, Select, message, Row, Col, DatePicker, Divider, Button, Space, Card } from 'antd'
import { PlusOutlined, DeleteOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import CustomModal from '@/Components/CustomModal'
import { createSerial, updateSerial, bulkCreateSerials } from '@/Helpers/api/productSerialService'
import axios from 'axios'

const { Option } = Select
const { TextArea } = Input

const STATUS_OPTIONS = [
  { value: 'available', label: 'Available' },
  { value: 'reserved', label: 'Reserved' },
  { value: 'damaged', label: 'Damaged' },
  { value: 'lost', label: 'Lost' },
]

export default function SerialModal({ visible, onCancel, record, onUpdate }) {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)
  const [products, setProducts] = useState([])
  const [loadingProducts, setLoadingProducts] = useState(false)
  const [bulkMode, setBulkMode] = useState(false)
  const [bulkSerials, setBulkSerials] = useState([{ serial_number: '', imei: '', color: '', storage_capacity: '' }])

  const isEditing = !!record

  useEffect(() => {
    loadProducts()
  }, [])

  useEffect(() => {
    if (record) {
      form.setFieldsValue({
        product_id: record.product?.id,
        serial_number: record.serial_number,
        imei: record.imei,
        imei2: record.imei2,
        purchase_price: record.purchase_price,
        purchase_date: record.purchase_date ? dayjs(record.purchase_date) : null,
        warranty_start_date: record.warranty_start_date ? dayjs(record.warranty_start_date) : null,
        warranty_end_date: record.warranty_end_date ? dayjs(record.warranty_end_date) : null,
        color: record.color,
        storage_capacity: record.storage_capacity,
        status: record.status,
        notes: record.notes,
      })
      setBulkMode(false)
    } else {
      form.resetFields()
      form.setFieldsValue({
        status: 'available',
      })
      setBulkSerials([{ serial_number: '', imei: '', color: '', storage_capacity: '' }])
    }
  }, [record, form])

  const loadProducts = async () => {
    setLoadingProducts(true)
    try {
      // Fetch products that have serial tracking enabled
      const response = await axios.get(route('pos.products'), {
        params: { enable_serial_tracking: true },
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
      if (values.purchase_date) {
        values.purchase_date = values.purchase_date.format('YYYY-MM-DD')
      }
      if (values.warranty_start_date) {
        values.warranty_start_date = values.warranty_start_date.format('YYYY-MM-DD')
      }
      if (values.warranty_end_date) {
        values.warranty_end_date = values.warranty_end_date.format('YYYY-MM-DD')
      }

      setLoading(true)

      if (isEditing) {
        await updateSerial(record.id, values)
        message.success('Serial updated successfully')
      } else if (bulkMode) {
        const validSerials = bulkSerials.filter(s => s.serial_number.trim())
        if (validSerials.length === 0) {
          message.error('At least one serial number is required')
          return
        }

        await bulkCreateSerials({
          ...values,
          serials: validSerials,
        })
        message.success(`${validSerials.length} serial(s) created successfully`)
      } else {
        await createSerial(values)
        message.success('Serial created successfully')
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

  const addBulkSerial = () => {
    setBulkSerials([...bulkSerials, { serial_number: '', imei: '', color: '', storage_capacity: '' }])
  }

  const removeBulkSerial = (index) => {
    const newSerials = [...bulkSerials]
    newSerials.splice(index, 1)
    setBulkSerials(newSerials)
  }

  const updateBulkSerial = (index, field, value) => {
    const newSerials = [...bulkSerials]
    newSerials[index][field] = value
    setBulkSerials(newSerials)
  }

  return (
    <CustomModal
      title={isEditing ? 'Edit Serial' : (bulkMode ? 'Add Multiple Serials' : 'Add Serial')}
      open={visible}
      onCancel={onCancel}
      width={bulkMode ? 900 : 700}
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
          {!isEditing && (
            <Col xs={24} md={12}>
              <Form.Item label=" ">
                <Button
                  type={bulkMode ? 'primary' : 'default'}
                  onClick={() => setBulkMode(!bulkMode)}
                >
                  {bulkMode ? 'Single Entry' : 'Bulk Entry'}
                </Button>
              </Form.Item>
            </Col>
          )}
        </Row>

        {!bulkMode && (
          <>
            <Row gutter={16}>
              <Col xs={24} md={12}>
                <Form.Item
                  name="serial_number"
                  label="Serial Number"
                  rules={[{ required: true, message: 'Serial number is required' }]}
                >
                  <Input placeholder="Enter serial number" />
                </Form.Item>
              </Col>
              <Col xs={24} md={12}>
                <Form.Item name="imei" label="IMEI">
                  <Input placeholder="Enter IMEI" maxLength={20} />
                </Form.Item>
              </Col>
            </Row>

            <Row gutter={16}>
              <Col xs={24} md={12}>
                <Form.Item name="imei2" label="IMEI 2 (Dual-SIM)">
                  <Input placeholder="Enter second IMEI" maxLength={20} />
                </Form.Item>
              </Col>
              {isEditing && (
                <Col xs={24} md={12}>
                  <Form.Item name="status" label="Status">
                    <Select disabled={record?.status === 'sold'}>
                      {STATUS_OPTIONS.map(opt => (
                        <Option key={opt.value} value={opt.value}>{opt.label}</Option>
                      ))}
                    </Select>
                  </Form.Item>
                </Col>
              )}
            </Row>
          </>
        )}

        {bulkMode && (
          <div style={{ marginBottom: 16 }}>
            <div style={{ fontWeight: 500, marginBottom: 8 }}>Serial Numbers</div>
            {bulkSerials.map((serial, index) => (
              <Card key={index} size="small" style={{ marginBottom: 8 }}>
                <Row gutter={8} align="middle">
                  <Col flex="1">
                    <Input
                      placeholder="Serial Number *"
                      value={serial.serial_number}
                      onChange={(e) => updateBulkSerial(index, 'serial_number', e.target.value)}
                    />
                  </Col>
                  <Col flex="1">
                    <Input
                      placeholder="IMEI"
                      value={serial.imei}
                      onChange={(e) => updateBulkSerial(index, 'imei', e.target.value)}
                    />
                  </Col>
                  <Col>
                    <Input
                      placeholder="Color"
                      value={serial.color}
                      onChange={(e) => updateBulkSerial(index, 'color', e.target.value)}
                      style={{ width: 100 }}
                    />
                  </Col>
                  <Col>
                    <Input
                      placeholder="Storage"
                      value={serial.storage_capacity}
                      onChange={(e) => updateBulkSerial(index, 'storage_capacity', e.target.value)}
                      style={{ width: 100 }}
                    />
                  </Col>
                  <Col>
                    {bulkSerials.length > 1 && (
                      <Button
                        type="text"
                        danger
                        icon={<DeleteOutlined />}
                        onClick={() => removeBulkSerial(index)}
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
              onClick={addBulkSerial}
            >
              Add Another
            </Button>
          </div>
        )}

        <Divider />

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item name="purchase_price" label="Purchase Price">
              <InputNumber
                min={0}
                precision={2}
                style={{ width: '100%' }}
                prefix="$"
              />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="purchase_date" label="Purchase Date">
              <DatePicker style={{ width: '100%' }} />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="color" label="Color">
              <Input placeholder="e.g., Black, Silver" />
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col xs={24} md={8}>
            <Form.Item name="storage_capacity" label="Storage">
              <Input placeholder="e.g., 128GB, 256GB" />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="warranty_start_date" label="Warranty Start">
              <DatePicker style={{ width: '100%' }} />
            </Form.Item>
          </Col>
          <Col xs={24} md={8}>
            <Form.Item name="warranty_end_date" label="Warranty End">
              <DatePicker style={{ width: '100%' }} />
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
