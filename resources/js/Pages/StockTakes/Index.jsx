import { useRef, useState, useCallback } from 'react'
import { Head } from '@inertiajs/react'
import { Typography, Button, Dropdown, Modal, message, Form, Input, Select, DatePicker } from 'antd'
import { EyeOutlined, MoreOutlined, PlusOutlined, CheckCircleOutlined, PlayCircleOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatDateTime } from '@/Helpers/formatters'

const { Text } = Typography
const { Option } = Select

export default function StockTakes() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [detailModalOpen, setDetailModalOpen] = useState(false)
  const [selectedStockTake, setSelectedStockTake] = useState(null)
  const [form] = Form.useForm()

  const fetchStockTakes = useCallback(async (params) => {
    const response = await axios.get('/stock-takes', {
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
    mutationFn: (data) => axios.post('/stock-takes', data),
    onSuccess: () => {
      message.success('Stock take created')
      setModalOpen(false)
      form.resetFields()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to create stock take'),
  })

  const startMutation = useMutation({
    mutationFn: (id) => axios.post(`/stock-takes/${id}/start`),
    onSuccess: () => {
      message.success('Stock take started')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to start stock take'),
  })

  const completeMutation = useMutation({
    mutationFn: (id) => axios.post(`/stock-takes/${id}/complete`),
    onSuccess: () => {
      message.success('Stock take completed')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to complete stock take'),
  })

  const handleAdd = () => {
    form.resetFields()
    form.setFieldsValue({ scheduled_date: dayjs() })
    setModalOpen(true)
  }

  const handleView = (stockTake) => {
    setSelectedStockTake(stockTake)
    setDetailModalOpen(true)
  }

  const handleStart = (stockTake) => {
    Modal.confirm({
      title: 'Start Stock Take',
      content: 'Begin counting for this stock take?',
      onOk: () => startMutation.mutate(stockTake.id),
    })
  }

  const handleComplete = (stockTake) => {
    Modal.confirm({
      title: 'Complete Stock Take',
      content: 'Mark this stock take as complete? This will apply the adjustments.',
      onOk: () => completeMutation.mutate(stockTake.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      createMutation.mutate({
        ...values,
        scheduled_date: values.scheduled_date?.format('YYYY-MM-DD'),
      })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const columns = [
    { field: 'reference', headerName: 'Reference', flex: 1, minWidth: 120 },
    { field: 'store_name', headerName: 'Store', flex: 1.2, minWidth: 130 },
    { field: 'items_count', headerName: 'Items', flex: 0.8, minWidth: 80 },
    { field: 'variance_count', headerName: 'Variances', flex: 0.8, minWidth: 90 },
    {
      field: 'status',
      headerName: 'Status',
      flex: 1,
      minWidth: 110,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Draft', value: 'draft' },
        { label: 'In Progress', value: 'in_progress' },
        { label: 'Completed', value: 'completed' },
        { label: 'Cancelled', value: 'cancelled' },
      ],
    },
    {
      field: 'scheduled_date',
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
              key: 'start',
              label: 'Start',
              icon: <PlayCircleOutlined />,
              disabled: data.status !== 'draft',
              onClick: () => handleStart(data),
            },
            {
              key: 'complete',
              label: 'Complete',
              icon: <CheckCircleOutlined />,
              disabled: data.status !== 'in_progress',
              onClick: () => handleComplete(data),
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
      <Head title="Stock Takes" />

      <GlobalPageHeader
        title="Stock Takes"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'New Stock Take',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchStockTakes}
        title="Stock Takes"
        searchPlaceholder="Search stock takes..."
        actionsColumn={actionsColumn}
        instanceId="stock-takes"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title="New Stock Take"
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={() => setModalOpen(false)}
        confirmLoading={createMutation.isPending}
        width={500}
      >
        <Form form={form} layout="vertical">
          <Form.Item name="store_id" label="Store" rules={[{ required: true }]}>
            <Select placeholder="Select store">
              <Option value={1}>Main Store</Option>
              <Option value={2}>Branch 1</Option>
            </Select>
          </Form.Item>
          <Form.Item name="scheduled_date" label="Scheduled Date">
            <DatePicker style={{ width: '100%' }} />
          </Form.Item>
          <Form.Item name="notes" label="Notes">
            <Input.TextArea rows={2} placeholder="Notes (optional)" />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Stock Take - ${selectedStockTake?.reference}`}
        open={detailModalOpen}
        onCancel={() => {
          setDetailModalOpen(false)
          setSelectedStockTake(null)
        }}
        footer={null}
        width={600}
      >
        {selectedStockTake && (
          <div>
            <p><Text strong>Store:</Text> {selectedStockTake.store_name}</p>
            <p><Text strong>Status:</Text> <StatusBadge status={selectedStockTake.status} /></p>
            <p><Text strong>Items Counted:</Text> {selectedStockTake.items_count}</p>
            <p><Text strong>Variances:</Text> {selectedStockTake.variance_count}</p>
            <p><Text strong>Date:</Text> {formatDateTime(selectedStockTake.scheduled_date)}</p>
          </div>
        )}
      </Modal>
    </>
  )
}
