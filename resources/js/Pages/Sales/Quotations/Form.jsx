import { useState, useEffect } from 'react'
import { Head, router, usePage } from '@inertiajs/react'
import {
  Card,
  Form,
  Input,
  InputNumber,
  DatePicker,
  Button,
  Space,
  Row,
  Col,
  Table,
  Typography,
  Divider,
  message,
  Popconfirm,
} from 'antd'
import {
  PlusOutlined,
  DeleteOutlined,
  SaveOutlined,
  ArrowLeftOutlined,
  SearchOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import CustomerSelector from '@/Components/CustomerSelector'
import LocationSelector from '@/Components/LocationSelector'
import ProductSearch from '@/Components/ProductSearch'
import { createQuotation, updateQuotation } from '@/Helpers/api/quotationService'

const { Text, Title } = Typography
const { TextArea } = Input

export default function QuotationForm() {
  const { quotation } = usePage().props
  const [form] = Form.useForm()
  const isEditing = !!quotation?.id

  const [items, setItems] = useState([])
  const [selectedCustomer, setSelectedCustomer] = useState(null)
  const [totals, setTotals] = useState({
    subtotal: 0,
    tax: 0,
    discount: 0,
    total: 0,
  })

  // Load existing quotation data
  useEffect(() => {
    if (quotation) {
      form.setFieldsValue({
        customer_id: quotation.customer_id,
        customer_name: quotation.customer_name || quotation.customer?.name,
        customer_email: quotation.customer_email || quotation.customer?.email,
        customer_phone: quotation.customer_phone || quotation.customer?.phone,
        location_id: quotation.location_id,
        valid_until: quotation.valid_until ? dayjs(quotation.valid_until) : null,
        discount_percent: quotation.discount_percent,
        notes: quotation.notes,
        terms_and_conditions: quotation.terms_and_conditions,
      })

      if (quotation.items) {
        setItems(quotation.items.map((item, index) => ({
          key: index,
          product_id: item.product_id,
          product_name: item.product_name,
          product_sku: item.product_sku,
          quantity: item.quantity,
          unit_price: parseFloat(item.unit_price),
          discount: parseFloat(item.discount || 0),
          tax_rate: parseFloat(item.tax_rate || 0),
          notes: item.notes,
        })))
      }

      if (quotation.customer) {
        setSelectedCustomer(quotation.customer)
      }
    }
  }, [quotation, form])

  // Recalculate totals when items or discount change
  useEffect(() => {
    const discountPercent = form.getFieldValue('discount_percent') || 0

    const subtotal = items.reduce((sum, item) => {
      return sum + (item.quantity * item.unit_price - (item.discount || 0))
    }, 0)

    const tax = items.reduce((sum, item) => {
      const itemSubtotal = item.quantity * item.unit_price - (item.discount || 0)
      return sum + (itemSubtotal * (item.tax_rate || 0) / 100)
    }, 0)

    const discount = subtotal * (discountPercent / 100)
    const total = subtotal + tax - discount

    setTotals({
      subtotal: subtotal.toFixed(2),
      tax: tax.toFixed(2),
      discount: discount.toFixed(2),
      total: total.toFixed(2),
    })
  }, [items, form])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => createQuotation(data),
    onSuccess: () => {
      message.success('Quotation created successfully')
      router.visit(route('pos.quotations.index'))
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to create quotation')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: (data) => updateQuotation(quotation.id, data),
    onSuccess: () => {
      message.success('Quotation updated successfully')
      router.visit(route('pos.quotations.index'))
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to update quotation')
    },
  })

  // Add product to items
  const handleAddProduct = (product) => {
    // Check if already exists
    const existingIndex = items.findIndex(item => item.product_id === product.id)

    if (existingIndex >= 0) {
      // Increment quantity
      const newItems = [...items]
      newItems[existingIndex].quantity += 1
      setItems(newItems)
    } else {
      // Add new item
      setItems([...items, {
        key: Date.now(),
        product_id: product.id,
        product_name: product.name,
        product_sku: product.sku,
        quantity: 1,
        unit_price: parseFloat(product.selling_price || 0),
        discount: 0,
        tax_rate: parseFloat(product.tax_rate || 0),
        notes: '',
      }])
    }
  }

  // Update item
  const handleUpdateItem = (key, field, value) => {
    setItems(items.map(item =>
      item.key === key ? { ...item, [field]: value } : item
    ))
  }

  // Remove item
  const handleRemoveItem = (key) => {
    setItems(items.filter(item => item.key !== key))
  }

  // Handle customer select
  const handleCustomerSelect = (customerId, option) => {
    if (option?.customer) {
      setSelectedCustomer(option.customer)
      form.setFieldsValue({
        customer_name: option.customer.name,
        customer_email: option.customer.email,
        customer_phone: option.customer.phone,
      })
    }
  }

  // Submit form
  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()

      if (items.length === 0) {
        message.error('Please add at least one item')
        return
      }

      const data = {
        ...values,
        valid_until: values.valid_until?.format('YYYY-MM-DD'),
        items: items.map(item => ({
          product_id: item.product_id,
          product_name: item.product_name,
          product_sku: item.product_sku,
          quantity: item.quantity,
          unit_price: item.unit_price,
          discount: item.discount,
          tax_rate: item.tax_rate,
          notes: item.notes,
        })),
      }

      if (isEditing) {
        updateMutation.mutate(data)
      } else {
        createMutation.mutate(data)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  // Item columns
  const itemColumns = [
    {
      title: 'Product',
      dataIndex: 'product_name',
      key: 'product_name',
      render: (text, record) => (
        <div>
          <Text strong>{text}</Text>
          {record.product_sku && (
            <div>
              <Text type="secondary" style={{ fontSize: 12 }}>
                SKU: {record.product_sku}
              </Text>
            </div>
          )}
        </div>
      ),
    },
    {
      title: 'Qty',
      dataIndex: 'quantity',
      key: 'quantity',
      width: 80,
      render: (value, record) => (
        <InputNumber
          min={1}
          value={value}
          onChange={(v) => handleUpdateItem(record.key, 'quantity', v)}
          size="small"
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Unit Price',
      dataIndex: 'unit_price',
      key: 'unit_price',
      width: 120,
      render: (value, record) => (
        <InputNumber
          min={0}
          step={0.01}
          value={value}
          onChange={(v) => handleUpdateItem(record.key, 'unit_price', v)}
          size="small"
          prefix="$"
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Discount',
      dataIndex: 'discount',
      key: 'discount',
      width: 100,
      render: (value, record) => (
        <InputNumber
          min={0}
          step={0.01}
          value={value}
          onChange={(v) => handleUpdateItem(record.key, 'discount', v)}
          size="small"
          prefix="$"
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Tax %',
      dataIndex: 'tax_rate',
      key: 'tax_rate',
      width: 80,
      render: (value, record) => (
        <InputNumber
          min={0}
          max={100}
          value={value}
          onChange={(v) => handleUpdateItem(record.key, 'tax_rate', v)}
          size="small"
          suffix="%"
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Line Total',
      key: 'line_total',
      width: 100,
      render: (_, record) => {
        const subtotal = record.quantity * record.unit_price - (record.discount || 0)
        const tax = subtotal * (record.tax_rate || 0) / 100
        return <Text strong>${(subtotal + tax).toFixed(2)}</Text>
      },
    },
    {
      title: '',
      key: 'actions',
      width: 50,
      render: (_, record) => (
        <Popconfirm
          title="Remove this item?"
          onConfirm={() => handleRemoveItem(record.key)}
          okText="Yes"
          cancelText="No"
        >
          <Button type="text" danger icon={<DeleteOutlined />} size="small" />
        </Popconfirm>
      ),
    },
  ]

  const isLoading = createMutation.isPending || updateMutation.isPending

  return (
    <>
      <Head title={isEditing ? `Edit Quotation ${quotation.quotation_number}` : 'New Quotation'} />

      <GlobalPageHeader
        title={isEditing ? `Edit Quotation ${quotation.quotation_number}` : 'New Quotation'}
        parentPageTitle="Quotations"
        actionButtons={[
          {
            title: 'Back',
            icon: <ArrowLeftOutlined />,
            onClick: () => router.visit(route('pos.quotations.index')),
          },
          {
            title: isEditing ? 'Update' : 'Save',
            icon: <SaveOutlined />,
            onClick: handleSubmit,
            type: 'primary',
            loading: isLoading,
          },
        ]}
      />

      <Row gutter={16}>
        {/* Main Content */}
        <Col xs={24} lg={16}>
          <Card title="Products" style={{ marginBottom: 16 }}>
            <ProductSearch
              onSelect={handleAddProduct}
              placeholder="Search products to add..."
              style={{ marginBottom: 16 }}
            />

            <Table
              dataSource={items}
              columns={itemColumns}
              pagination={false}
              size="small"
              rowKey="key"
              locale={{ emptyText: 'No items added. Search and add products above.' }}
            />
          </Card>

          <Card title="Notes">
            <Form form={form} layout="vertical">
              <Form.Item name="notes" label="Internal Notes">
                <TextArea rows={3} placeholder="Notes for internal use" />
              </Form.Item>

              <Form.Item name="terms_and_conditions" label="Terms & Conditions">
                <TextArea rows={3} placeholder="Terms and conditions to show on quotation" />
              </Form.Item>
            </Form>
          </Card>
        </Col>

        {/* Sidebar */}
        <Col xs={24} lg={8}>
          <Card title="Customer Details" style={{ marginBottom: 16 }}>
            <Form form={form} layout="vertical">
              <Form.Item name="customer_id" label="Customer">
                <CustomerSelector
                  allowClear
                  placeholder="Select or search customer"
                  onChange={handleCustomerSelect}
                />
              </Form.Item>

              <Form.Item
                name="customer_name"
                label="Customer Name"
                rules={[{ required: true, message: 'Enter customer name' }]}
              >
                <Input placeholder="Customer name" />
              </Form.Item>

              <Row gutter={8}>
                <Col span={12}>
                  <Form.Item name="customer_email" label="Email">
                    <Input type="email" placeholder="Email" />
                  </Form.Item>
                </Col>
                <Col span={12}>
                  <Form.Item name="customer_phone" label="Phone">
                    <Input placeholder="Phone" />
                  </Form.Item>
                </Col>
              </Row>
            </Form>
          </Card>

          <Card title="Quotation Details" style={{ marginBottom: 16 }}>
            <Form form={form} layout="vertical">
              <Form.Item name="location_id" label="Location">
                <LocationSelector allowClear />
              </Form.Item>

              <Form.Item name="valid_until" label="Valid Until">
                <DatePicker
                  style={{ width: '100%' }}
                  disabledDate={(current) => current && current < dayjs().endOf('day')}
                />
              </Form.Item>

              <Form.Item name="discount_percent" label="Overall Discount (%)">
                <InputNumber
                  min={0}
                  max={100}
                  style={{ width: '100%' }}
                  onChange={() => setTotals({ ...totals })} // Trigger recalc
                />
              </Form.Item>
            </Form>
          </Card>

          <Card title="Summary">
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Text>Subtotal:</Text>
                <Text>${totals.subtotal}</Text>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Text>Tax:</Text>
                <Text>${totals.tax}</Text>
              </div>
              {parseFloat(totals.discount) > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text>Discount:</Text>
                  <Text type="danger">-${totals.discount}</Text>
                </div>
              )}
              <Divider style={{ margin: '8px 0' }} />
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Title level={4} style={{ margin: 0 }}>Total:</Title>
                <Title level={4} style={{ margin: 0 }}>${totals.total}</Title>
              </div>
            </div>

            <Button
              type="primary"
              block
              size="large"
              icon={<SaveOutlined />}
              onClick={handleSubmit}
              loading={isLoading}
              style={{ marginTop: 16 }}
            >
              {isEditing ? 'Update Quotation' : 'Save Quotation'}
            </Button>
          </Card>
        </Col>
      </Row>
    </>
  )
}
