import { useRef, useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Typography, Button, Card, Table, Tag, Modal, Form, InputNumber, Select, message, Input, Space, Statistic, Row, Col } from 'antd'
import { SyncOutlined, WarningOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import axios from 'axios'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatNumber } from '@/Helpers/formatters'

const { Text } = Typography
const { Option } = Select
const { Search } = Input

export default function Inventory() {
  const { inventory: initialInventory, filters } = usePage().props
  const queryClient = useQueryClient()

  const [search, setSearch] = useState('')
  const [showLowStock, setShowLowStock] = useState(false)
  const [adjustModalOpen, setAdjustModalOpen] = useState(false)
  const [selectedItem, setSelectedItem] = useState(null)
  const [form] = Form.useForm()

  // Fetch inventory
  const { data: inventoryData, isLoading, refetch } = useQuery({
    queryKey: ['inventory', search, showLowStock],
    queryFn: async () => {
      const response = await axios.get('/inventory', {
        params: { search, low_stock: showLowStock || undefined },
      })
      return response.data.inventory?.data || []
    },
    initialData: initialInventory?.data || [],
  })

  // Adjust stock mutation
  const adjustMutation = useMutation({
    mutationFn: (data) => axios.post('/inventory/adjust', data),
    onSuccess: () => {
      message.success('Inventory adjusted successfully')
      setAdjustModalOpen(false)
      setSelectedItem(null)
      form.resetFields()
      refetch()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to adjust inventory'),
  })

  const handleAdjust = (item) => {
    setSelectedItem(item)
    form.setFieldsValue({
      product_id: item.product.id,
      store_id: 1,
      quantity: 0,
      type: 'add',
    })
    setAdjustModalOpen(true)
  }

  const handleSubmitAdjust = async () => {
    try {
      const values = await form.validateFields()
      adjustMutation.mutate(values)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const columns = [
    {
      title: 'Product',
      key: 'product',
      render: (_, record) => (
        <div>
          <Text strong>{record.product?.name}</Text>
          <br />
          <Text type="secondary" style={{ fontSize: 12 }}>{record.product?.sku}</Text>
        </div>
      ),
    },
    {
      title: 'Category',
      dataIndex: ['product', 'category'],
      key: 'category',
    },
    {
      title: 'Store',
      dataIndex: 'store',
      key: 'store',
    },
    {
      title: 'Quantity',
      dataIndex: 'quantity',
      key: 'quantity',
      render: (value, record) => (
        <span style={{ color: record.is_low_stock ? '#ff4d4f' : 'inherit', fontWeight: 'bold' }}>
          {formatNumber(value)}
        </span>
      ),
    },
    {
      title: 'Min Qty',
      dataIndex: 'min_quantity',
      key: 'min_quantity',
      render: formatNumber,
    },
    {
      title: 'Status',
      key: 'status',
      render: (_, record) => (
        record.is_low_stock ? (
          <Tag icon={<WarningOutlined />} color="error">Low Stock</Tag>
        ) : (
          <Tag color="success">In Stock</Tag>
        )
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_, record) => (
        <Button size="small" icon={<SyncOutlined />} onClick={() => handleAdjust(record)}>
          Adjust
        </Button>
      ),
    },
  ]

  const lowStockCount = inventoryData?.filter(i => i.is_low_stock).length || 0

  return (
    <>
      <Head title="Inventory" />

      <GlobalPageHeader
        title="Inventory"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col span={6}>
          <Card size="small">
            <Statistic title="Total Items" value={inventoryData?.length || 0} />
          </Card>
        </Col>
        <Col span={6}>
          <Card size="small">
            <Statistic
              title="Low Stock Items"
              value={lowStockCount}
              valueStyle={{ color: lowStockCount > 0 ? '#ff4d4f' : 'inherit' }}
            />
          </Card>
        </Col>
      </Row>

      <Card>
        <Space style={{ marginBottom: 16 }}>
          <Search
            placeholder="Search products..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onSearch={setSearch}
            style={{ width: 300 }}
            allowClear
          />
          <Button
            type={showLowStock ? 'primary' : 'default'}
            icon={<WarningOutlined />}
            onClick={() => setShowLowStock(!showLowStock)}
          >
            {showLowStock ? 'Showing Low Stock' : 'Show Low Stock'}
          </Button>
        </Space>

        <Table
          dataSource={inventoryData || []}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{ pageSize: 20 }}
        />
      </Card>

      <Modal
        title={`Adjust Stock - ${selectedItem?.product?.name}`}
        open={adjustModalOpen}
        onOk={handleSubmitAdjust}
        onCancel={() => {
          setAdjustModalOpen(false)
          setSelectedItem(null)
          form.resetFields()
        }}
        confirmLoading={adjustMutation.isPending}
      >
        <Form form={form} layout="vertical">
          <Form.Item name="product_id" hidden>
            <Input />
          </Form.Item>
          <Form.Item name="store_id" hidden>
            <Input />
          </Form.Item>

          <div style={{ marginBottom: 16, padding: 16, background: '#f5f5f5', borderRadius: 8 }}>
            <Text>Current Quantity: <Text strong>{selectedItem?.quantity || 0}</Text></Text>
          </div>

          <Form.Item name="type" label="Adjustment Type" rules={[{ required: true }]}>
            <Select>
              <Option value="add">Add to Stock</Option>
              <Option value="subtract">Subtract from Stock</Option>
              <Option value="set">Set Stock Level</Option>
            </Select>
          </Form.Item>

          <Form.Item name="quantity" label="Quantity" rules={[{ required: true, message: 'Please enter quantity' }]}>
            <InputNumber min={0} style={{ width: '100%' }} />
          </Form.Item>

          <Form.Item name="reason" label="Reason">
            <Input.TextArea rows={2} placeholder="Enter reason for adjustment" />
          </Form.Item>
        </Form>
      </Modal>
    </>
  )
}
