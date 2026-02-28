import { useState } from 'react'
import {
  Input,
  Table,
  Button,
  Space,
  Typography,
  Tag,
  Select,
  InputNumber,
  Form,
  Divider,
  Empty,
  Spin,
  Alert,
  Radio,
  Checkbox,
  theme,
  message,
} from 'antd'
import { SearchOutlined, RollbackOutlined, SwapOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import posService from '@/Helpers/api/posService'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Text, Title } = Typography

const RETURN_REASONS = [
  { value: 'defective', label: 'Defective/Damaged' },
  { value: 'wrong_item', label: 'Wrong Item' },
  { value: 'customer_changed_mind', label: 'Changed Mind' },
  { value: 'expired', label: 'Expired' },
  { value: 'other', label: 'Other' },
]

const getMaxReturnable = (item) => item.quantity - (item.returned_quantity || 0)

export default function ReturnModal({ open, onClose, onReturnComplete }) {
  const { token } = theme.useToken()
  const queryClient = useQueryClient()
  const [form] = Form.useForm()
  const [searchQuery, setSearchQuery] = useState('')
  const [selectedOrder, setSelectedOrder] = useState(null)
  const [selectedItems, setSelectedItems] = useState({}) // { itemId: quantity }

  const { data: searchResults, isLoading: isSearching, refetch: searchOrders } = useQuery({
    queryKey: ['return-order-search', searchQuery],
    queryFn: () => posService.searchOrdersForReturn(searchQuery).then(r => r.data.data || []),
    enabled: false,
  })

  const { data: orderDetail, isLoading: isLoadingOrder } = useQuery({
    queryKey: ['return-order-detail', selectedOrder?.id],
    queryFn: () => posService.getOrderDetail(selectedOrder.id).then(r => r.data),
    enabled: !!selectedOrder?.id,
  })

  const returnMutation = useMutation({
    mutationFn: (data) => posService.processReturn(selectedOrder.id, data),
    onSuccess: (response) => {
      message.success('Return processed')
      queryClient.invalidateQueries(['recent-orders'])
      onReturnComplete?.(response.data)
      handleClose()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed'),
  })

  const handleSearch = (value) => {
    setSearchQuery(value)
    if (value.trim()) searchOrders()
  }

  const handleSelectOrder = (order) => {
    setSelectedOrder(order)
    setSelectedItems({})
  }

  const handleItemToggle = (item, checked) => {
    if (checked) {
      setSelectedItems({ ...selectedItems, [item.id]: getMaxReturnable(item) })
    } else {
      const { [item.id]: _, ...rest } = selectedItems
      setSelectedItems(rest)
    }
  }

  const handleQuantityChange = (itemId, qty) => {
    setSelectedItems({ ...selectedItems, [itemId]: qty })
  }

  const refundAmount = Object.entries(selectedItems).reduce((sum, [id, qty]) => {
    const item = orderDetail?.items?.find(i => i.id === Number(id))
    return sum + (item?.unit_price || 0) * qty
  }, 0)

  const handleSubmit = async () => {
    const values = await form.validateFields()
    if (!Object.keys(selectedItems).length) {
      return message.error('Select items to return')
    }
    returnMutation.mutate({
      items: Object.entries(selectedItems).map(([id, qty]) => ({ order_item_id: Number(id), quantity: qty })),
      reason: values.reason,
      reason_notes: values.notes,
      refund_method: values.refund_method,
      return_mode: values.return_mode,
      restock: values.restock,
    })
  }

  const handleClose = () => {
    setSearchQuery('')
    setSelectedOrder(null)
    setSelectedItems({})
    form.resetFields()
    onClose()
  }

  const itemColumns = [
    {
      title: '', width: 40,
      render: (_, item) => (
        <Checkbox
          checked={item.id in selectedItems}
          onChange={(e) => handleItemToggle(item, e.target.checked)}
          disabled={getMaxReturnable(item) <= 0}
        />
      ),
    },
    {
      title: 'Product', dataIndex: 'product_name',
      render: (name, r) => <Text strong>{name || r.display_name}</Text>,
    },
    { title: 'Qty', dataIndex: 'quantity', width: 60, align: 'center' },
    { title: 'Returned', dataIndex: 'returned_quantity', width: 70, align: 'center', render: (v) => v || 0 },
    {
      title: 'Return', width: 80,
      render: (_, item) => (
        <InputNumber
          min={1}
          max={getMaxReturnable(item)}
          value={selectedItems[item.id] || 0}
          onChange={(v) => handleQuantityChange(item.id, v)}
          disabled={!(item.id in selectedItems)}
          size="small"
          style={{ width: '100%' }}
        />
      ),
    },
    { title: 'Price', dataIndex: 'unit_price', width: 90, align: 'right', render: formatCurrency },
    {
      title: 'Refund', width: 90, align: 'right',
      render: (_, item) => formatCurrency((selectedItems[item.id] || 0) * item.unit_price),
    },
  ]

  const searchColumns = [
    { title: 'Order #', dataIndex: 'order_number', render: (n) => <Text strong>#{n}</Text> },
    { title: 'Date', dataIndex: 'created_at', render: formatDateTime },
    { title: 'Customer', dataIndex: 'customer_name', render: (n) => n || 'Walk-in' },
    { title: 'Total', dataIndex: 'total', render: formatCurrency },
    {
      title: 'Status', dataIndex: 'status',
      render: (s) => <Tag color={s === 'completed' ? 'green' : 'orange'}>{s}</Tag>,
    },
    {
      title: '', width: 80,
      render: (_, r) => <Button type="primary" size="small" onClick={() => handleSelectOrder(r)}>Select</Button>,
    },
  ]

  return (
    <CustomModal
      title={<><RollbackOutlined /> Returns</>}
      open={open}
      onCancel={handleClose}
      width={800}
      footer={selectedOrder ? (
        <Space>
          <Button onClick={() => setSelectedOrder(null)}>Back</Button>
          <Button
            type="primary"
            onClick={handleSubmit}
            loading={returnMutation.isPending}
            disabled={!Object.keys(selectedItems).length}
          >
            Process ({formatCurrency(refundAmount)})
          </Button>
        </Space>
      ) : null}
    >
      {!selectedOrder ? (
        <div>
          <Input.Search
            placeholder="Search order #, customer, phone..."
            onSearch={handleSearch}
            enterButton
            size="large"
            loading={isSearching}
            style={{ marginBottom: 16 }}
          />
          {isSearching ? (
            <Spin style={{ display: 'block', margin: '40px auto' }} />
          ) : searchResults?.length ? (
            <Table dataSource={searchResults} columns={searchColumns} rowKey="id" pagination={false} size="small" />
          ) : searchQuery ? (
            <Empty description="No orders found" />
          ) : (
            <Text type="secondary" style={{ display: 'block', textAlign: 'center', padding: 40 }}>
              Search for an order to process return
            </Text>
          )}
        </div>
      ) : isLoadingOrder ? (
        <Spin style={{ display: 'block', margin: '40px auto' }} />
      ) : orderDetail ? (
        <div>
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16, padding: 12, background: token.colorBgLayout, borderRadius: 8 }}>
            <div>
              <Title level={5} style={{ margin: 0 }}>Order #{orderDetail.order_number}</Title>
              <Text type="secondary">{formatDateTime(orderDetail.created_at)}</Text>
            </div>
            <Text strong style={{ fontSize: 18 }}>{formatCurrency(orderDetail.total)}</Text>
          </div>

          <Table
            dataSource={orderDetail.items}
            columns={itemColumns}
            rowKey="id"
            pagination={false}
            size="small"
            style={{ marginBottom: 16 }}
          />

          {Object.keys(selectedItems).length > 0 && (
            <Alert
              type="info"
              message={<><Text strong>{Object.keys(selectedItems).length}</Text> item(s) - Refund: <Text strong>{formatCurrency(refundAmount)}</Text></>}
              style={{ marginBottom: 16 }}
            />
          )}

          <Divider />

          <Form form={form} layout="vertical" initialValues={{ return_mode: 'refund', refund_method: 'original', restock: true }}>
            <Form.Item name="return_mode" label="Type">
              <Radio.Group optionType="button" buttonStyle="solid">
                <Radio.Button value="refund"><RollbackOutlined /> Refund</Radio.Button>
                <Radio.Button value="exchange"><SwapOutlined /> Exchange</Radio.Button>
              </Radio.Group>
            </Form.Item>
            <Form.Item name="reason" label="Reason" rules={[{ required: true }]}>
              <Select options={RETURN_REASONS} placeholder="Select reason" />
            </Form.Item>
            <Form.Item name="notes" label="Notes">
              <Input.TextArea rows={2} />
            </Form.Item>
            <Form.Item noStyle shouldUpdate={(prev, curr) => prev.return_mode !== curr.return_mode}>
              {({ getFieldValue }) => getFieldValue('return_mode') === 'refund' && (
                <Form.Item name="refund_method" label="Refund Method">
                  <Radio.Group>
                    <Radio value="original">Original Payment</Radio>
                    <Radio value="cash">Cash</Radio>
                    <Radio value="store_credit">Store Credit</Radio>
                  </Radio.Group>
                </Form.Item>
              )}
            </Form.Item>
            <Form.Item name="restock" valuePropName="checked">
              <Checkbox>Restock items</Checkbox>
            </Form.Item>
          </Form>
        </div>
      ) : null}
    </CustomModal>
  )
}
