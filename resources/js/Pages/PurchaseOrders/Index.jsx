import { useRef, useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Typography, Button, Dropdown, Modal, message, Form, Input, Select, DatePicker, Row, Col } from 'antd'
import { EyeOutlined, MoreOutlined, PlusOutlined, CheckCircleOutlined, CloseCircleOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Text } = Typography
const { Option } = Select

export default function PurchaseOrders() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [detailModalOpen, setDetailModalOpen] = useState(false)
  const [selectedPO, setSelectedPO] = useState(null)
  const [form] = Form.useForm()

  const fetchPurchaseOrders = useCallback(async (params) => {
    const response = await axios.get('/purchase-orders', {
      params: {
        page: params.page,
        per_page: params.per_page,
        search: params.search,
        status: params.filterTree?.status,
      },
    })
    return { data: response.data.data, total: response.data.meta?.total || 0 }
  }, [])

  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/purchase-orders', data),
    onSuccess: () => {
      message.success('Purchase order created')
      setModalOpen(false)
      form.resetFields()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to create purchase order'),
  })

  const approveMutation = useMutation({
    mutationFn: (id) => axios.post(`/purchase-orders/${id}/approve`),
    onSuccess: () => {
      message.success('Purchase order approved')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to approve'),
  })

  const receiveMutation = useMutation({
    mutationFn: (id) => axios.post(`/purchase-orders/${id}/receive`),
    onSuccess: () => {
      message.success('Purchase order received')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to receive'),
  })

  const handleAdd = () => {
    form.resetFields()
    form.setFieldsValue({ expected_date: dayjs().add(7, 'day') })
    setModalOpen(true)
  }

  const handleView = (po) => {
    setSelectedPO(po)
    setDetailModalOpen(true)
  }

  const handleApprove = (po) => {
    Modal.confirm({
      title: 'Approve Purchase Order',
      content: 'Approve this purchase order?',
      onOk: () => approveMutation.mutate(po.id),
    })
  }

  const handleReceive = (po) => {
    Modal.confirm({
      title: 'Receive Purchase Order',
      content: 'Mark this purchase order as received?',
      onOk: () => receiveMutation.mutate(po.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      createMutation.mutate({
        ...values,
        expected_date: values.expected_date?.format('YYYY-MM-DD'),
      })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const columns = [
    { field: 'po_number', headerName: 'PO #', flex: 1, minWidth: 120 },
    { field: 'vendor_name', headerName: 'Vendor', flex: 1.5, minWidth: 150 },
    { field: 'items_count', headerName: 'Items', flex: 0.8, minWidth: 80 },
    {
      field: 'total',
      headerName: 'Total',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 1,
      minWidth: 110,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Draft', value: 'draft' },
        { label: 'Ordered', value: 'ordered' },
        { label: 'Partial', value: 'partial' },
        { label: 'Received', value: 'received' },
        { label: 'Cancelled', value: 'cancelled' },
      ],
    },
    {
      field: 'created_at',
      headerName: 'Date',
      flex: 1.2,
      minWidth: 150,
      cellRenderer: ({ value }) => formatDateTime(value),
    },
  ]

  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 100,
    sortable: false,
    cellRenderer: ({ data }) => (
      <Dropdown
        trigger={['click']}
        menu={{
          items: [
            { key: 'view', label: 'View Details', icon: <EyeOutlined />, onClick: () => handleView(data) },
            { type: 'divider' },
            {
              key: 'approve',
              label: 'Approve',
              icon: <CheckCircleOutlined />,
              disabled: data.status !== 'draft',
              onClick: () => handleApprove(data),
            },
            {
              key: 'receive',
              label: 'Receive',
              icon: <CheckCircleOutlined />,
              disabled: !['ordered', 'partial'].includes(data.status),
              onClick: () => handleReceive(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  return (
    <>
      <Head title="Purchase Orders" />

      <GlobalPageHeader
        title="Purchase Orders"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'New PO',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchPurchaseOrders}
        title="Purchase Orders"
        searchPlaceholder="Search purchase orders..."
        actionsColumn={actionsColumn}
        instanceId="purchase-orders"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title="New Purchase Order"
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={() => setModalOpen(false)}
        confirmLoading={createMutation.isPending}
        width={600}
      >
        <Form form={form} layout="vertical">
          <Form.Item name="vendor_id" label="Vendor" rules={[{ required: true }]}>
            <Select placeholder="Select vendor">
              <Option value={1}>Vendor A</Option>
              <Option value={2}>Vendor B</Option>
            </Select>
          </Form.Item>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="expected_date" label="Expected Date">
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="store_id" label="Destination Store" rules={[{ required: true }]}>
                <Select placeholder="Select store">
                  <Option value={1}>Main Store</Option>
                  <Option value={2}>Branch 1</Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>
          <Form.Item name="notes" label="Notes">
            <Input.TextArea rows={2} placeholder="PO notes (optional)" />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Purchase Order - ${selectedPO?.po_number}`}
        open={detailModalOpen}
        onCancel={() => {
          setDetailModalOpen(false)
          setSelectedPO(null)
        }}
        footer={null}
        width={600}
      >
        {selectedPO && (
          <div>
            <p><Text strong>Vendor:</Text> {selectedPO.vendor_name}</p>
            <p><Text strong>Total:</Text> {formatCurrency(selectedPO.total)}</p>
            <p><Text strong>Status:</Text> <StatusBadge status={selectedPO.status} /></p>
            <p><Text strong>Items:</Text> {selectedPO.items_count}</p>
            <p><Text strong>Date:</Text> {formatDateTime(selectedPO.created_at)}</p>
          </div>
        )}
      </Modal>
    </>
  )
}
