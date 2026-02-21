import { useRef, useState, useCallback } from 'react'
import { Head } from '@inertiajs/react'
import { Typography, Button, Dropdown, Modal, message, Form, Input, Select, Table } from 'antd'
import { EyeOutlined, MoreOutlined, PlusOutlined, CheckCircleOutlined, SendOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatDateTime } from '@/Helpers/formatters'

const { Text } = Typography
const { Option } = Select

export default function StockTransfers() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [detailModalOpen, setDetailModalOpen] = useState(false)
  const [selectedTransfer, setSelectedTransfer] = useState(null)
  const [form] = Form.useForm()

  const fetchTransfers = useCallback(async (params) => {
    const response = await axios.get('/stock-transfers', {
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
    mutationFn: (data) => axios.post('/stock-transfers', data),
    onSuccess: () => {
      message.success('Transfer created successfully')
      setModalOpen(false)
      form.resetFields()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to create transfer'),
  })

  const shipMutation = useMutation({
    mutationFn: (id) => axios.post(`/stock-transfers/${id}/ship`),
    onSuccess: () => {
      message.success('Transfer shipped')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to ship transfer'),
  })

  const receiveMutation = useMutation({
    mutationFn: (id) => axios.post(`/stock-transfers/${id}/receive`),
    onSuccess: () => {
      message.success('Transfer received')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to receive transfer'),
  })

  const handleAdd = () => {
    form.resetFields()
    setModalOpen(true)
  }

  const handleView = (transfer) => {
    setSelectedTransfer(transfer)
    setDetailModalOpen(true)
  }

  const handleShip = (transfer) => {
    Modal.confirm({
      title: 'Ship Transfer',
      content: 'Mark this transfer as shipped?',
      onOk: () => shipMutation.mutate(transfer.id),
    })
  }

  const handleReceive = (transfer) => {
    Modal.confirm({
      title: 'Receive Transfer',
      content: 'Mark this transfer as received?',
      onOk: () => receiveMutation.mutate(transfer.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      createMutation.mutate(values)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const columns = [
    { field: 'transfer_number', headerName: 'Transfer #', flex: 1, minWidth: 120 },
    { field: 'from_store', headerName: 'From Store', flex: 1.2, minWidth: 130 },
    { field: 'to_store', headerName: 'To Store', flex: 1.2, minWidth: 130 },
    { field: 'items_count', headerName: 'Items', flex: 0.8, minWidth: 80 },
    {
      field: 'status',
      headerName: 'Status',
      flex: 1,
      minWidth: 110,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Draft', value: 'draft' },
        { label: 'Pending', value: 'pending' },
        { label: 'Shipped', value: 'shipped' },
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
              key: 'ship',
              label: 'Ship',
              icon: <SendOutlined />,
              disabled: data.status !== 'pending',
              onClick: () => handleShip(data),
            },
            {
              key: 'receive',
              label: 'Receive',
              icon: <CheckCircleOutlined />,
              disabled: data.status !== 'shipped',
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
      <Head title="Stock Transfers" />

      <GlobalPageHeader
        title="Stock Transfers"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'New Transfer',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchTransfers}
        title="Stock Transfers"
        searchPlaceholder="Search transfers..."
        actionsColumn={actionsColumn}
        instanceId="stock-transfers"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title="New Stock Transfer"
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={() => setModalOpen(false)}
        confirmLoading={createMutation.isPending}
        width={600}
      >
        <Form form={form} layout="vertical">
          <Form.Item name="from_store_id" label="From Store" rules={[{ required: true }]}>
            <Select placeholder="Select source store">
              <Option value={1}>Main Store</Option>
              <Option value={2}>Branch 1</Option>
            </Select>
          </Form.Item>
          <Form.Item name="to_store_id" label="To Store" rules={[{ required: true }]}>
            <Select placeholder="Select destination store">
              <Option value={1}>Main Store</Option>
              <Option value={2}>Branch 1</Option>
            </Select>
          </Form.Item>
          <Form.Item name="notes" label="Notes">
            <Input.TextArea rows={2} placeholder="Transfer notes (optional)" />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Transfer Details - ${selectedTransfer?.transfer_number}`}
        open={detailModalOpen}
        onCancel={() => {
          setDetailModalOpen(false)
          setSelectedTransfer(null)
        }}
        footer={null}
        width={600}
      >
        {selectedTransfer && (
          <div>
            <p><Text strong>From:</Text> {selectedTransfer.from_store}</p>
            <p><Text strong>To:</Text> {selectedTransfer.to_store}</p>
            <p><Text strong>Status:</Text> <StatusBadge status={selectedTransfer.status} /></p>
            <p><Text strong>Items:</Text> {selectedTransfer.items_count}</p>
            <p><Text strong>Date:</Text> {formatDateTime(selectedTransfer.created_at)}</p>
          </div>
        )}
      </Modal>
    </>
  )
}
