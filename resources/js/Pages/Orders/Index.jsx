import { useRef, useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Typography, Button, Dropdown, Modal, message, DatePicker } from 'antd'
import {
  EyeOutlined,
  CloseCircleOutlined,
  MoreOutlined,
  PrinterOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import OrderDetailModal from './Components/OrderDetailModal'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Text } = Typography
const { RangePicker } = DatePicker

export default function Orders() {
  const { filters } = usePage().props
  const gridRef = useRef()

  // Modal state
  const [detailModalOpen, setDetailModalOpen] = useState(false)
  const [selectedOrderId, setSelectedOrderId] = useState(null)
  const [cancelModalOpen, setCancelModalOpen] = useState(false)
  const [cancelReason, setCancelReason] = useState('')
  const [selectedOrder, setSelectedOrder] = useState(null)

  // Fetch orders
  const fetchOrders = useCallback(async (params) => {
    const response = await axios.get('/pos/orders', {
      params: {
        page: params.page,
        per_page: params.per_page,
        search: params.search,
        status: params.filterTree?.status,
        payment_status: params.filterTree?.payment_status,
        from_date: params.filterTree?.from_date,
        to_date: params.filterTree?.to_date,
      },
    })
    return {
      data: response.data.data,
      total: response.data.total,
    }
  }, [])

  // Cancel mutation
  const cancelMutation = useMutation({
    mutationFn: ({ id, reason }) => axios.post(`/pos/orders/${id}/cancel`, { reason }),
    onSuccess: () => {
      message.success('Order cancelled successfully')
      setCancelModalOpen(false)
      setCancelReason('')
      setSelectedOrder(null)
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to cancel order')
    },
  })

  // Handlers
  const handleViewDetail = (order) => {
    setSelectedOrderId(order.id)
    setDetailModalOpen(true)
  }

  const handlePrintReceipt = (order) => {
    window.open(`/pos/orders/${order.id}/receipt`, '_blank')
  }

  const handleCancelOrder = (order) => {
    setSelectedOrder(order)
    setCancelModalOpen(true)
  }

  const confirmCancelOrder = () => {
    if (!cancelReason.trim()) {
      message.warning('Please enter a cancellation reason')
      return
    }
    cancelMutation.mutate({ id: selectedOrder.id, reason: cancelReason })
  }

  // Column definitions
  const columns = [
    {
      field: 'order_number',
      headerName: 'Order #',
      flex: 1,
      minWidth: 120,
      filterType: 'text',
    },
    {
      field: 'customer_name',
      headerName: 'Customer',
      flex: 1.5,
      minWidth: 150,
      filterType: 'text',
    },
    {
      field: 'total',
      headerName: 'Total',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'current_status',
      headerName: 'Status',
      flex: 1,
      minWidth: 110,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Pending', value: 'pending' },
        { label: 'Completed', value: 'completed' },
        { label: 'Cancelled', value: 'cancelled' },
      ],
    },
    {
      field: 'payment_status',
      headerName: 'Payment',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Paid', value: 'paid' },
        { label: 'Unpaid', value: 'unpaid' },
        { label: 'Partial', value: 'partial' },
      ],
    },
    {
      field: 'created_at',
      headerName: 'Date',
      flex: 1.5,
      minWidth: 160,
      cellRenderer: ({ value }) => formatDateTime(value),
    },
  ]

  // Actions column
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
            {
              key: 'view',
              label: 'View Details',
              icon: <EyeOutlined />,
              onClick: () => handleViewDetail(data),
            },
            {
              key: 'print',
              label: 'Print Receipt',
              icon: <PrinterOutlined />,
              onClick: () => handlePrintReceipt(data),
            },
            {
              type: 'divider',
            },
            {
              key: 'cancel',
              label: 'Cancel Order',
              icon: <CloseCircleOutlined />,
              danger: true,
              disabled: data.current_status === 'cancelled',
              onClick: () => handleCancelOrder(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  // Filter fields
  const filterFields = [
    {
      field: 'status',
      label: 'Order Status',
      filterType: 'select',
      options: [
        { label: 'Pending', value: 'pending' },
        { label: 'Completed', value: 'completed' },
        { label: 'Cancelled', value: 'cancelled' },
      ],
    },
    {
      field: 'payment_status',
      label: 'Payment Status',
      filterType: 'select',
      options: [
        { label: 'Paid', value: 'paid' },
        { label: 'Unpaid', value: 'unpaid' },
        { label: 'Partial', value: 'partial' },
      ],
    },
  ]

  return (
    <>
      <Head title="Orders" />

      <GlobalPageHeader
        title="Orders"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchOrders}
        title="Orders"
        searchPlaceholder="Search orders..."
        actionsColumn={actionsColumn}
        filterFields={filterFields}
        instanceId="orders"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      {/* Order Detail Modal */}
      <OrderDetailModal
        open={detailModalOpen}
        onClose={() => {
          setDetailModalOpen(false)
          setSelectedOrderId(null)
        }}
        orderId={selectedOrderId}
      />

      {/* Cancel Order Modal */}
      <Modal
        title="Cancel Order"
        open={cancelModalOpen}
        onOk={confirmCancelOrder}
        onCancel={() => {
          setCancelModalOpen(false)
          setCancelReason('')
          setSelectedOrder(null)
        }}
        okText="Cancel Order"
        okType="danger"
        confirmLoading={cancelMutation.isPending}
      >
        <p>
          Are you sure you want to cancel order{' '}
          <Text strong>#{selectedOrder?.order_number}</Text>?
        </p>
        <p>Please enter a reason for cancellation:</p>
        <textarea
          value={cancelReason}
          onChange={(e) => setCancelReason(e.target.value)}
          placeholder="Enter cancellation reason..."
          style={{
            width: '100%',
            padding: 8,
            borderRadius: 6,
            border: '1px solid #d9d9d9',
            resize: 'vertical',
            minHeight: 80,
          }}
        />
      </Modal>
    </>
  )
}
