import { useRef, useState, useCallback } from 'react'
import { Head } from '@inertiajs/react'
import { Typography, Button, Dropdown, Modal, message } from 'antd'
import { EyeOutlined, MoreOutlined, CheckCircleOutlined, CloseCircleOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Text } = Typography

export default function Returns() {
  const gridRef = useRef()
  const [detailModalOpen, setDetailModalOpen] = useState(false)
  const [selectedReturn, setSelectedReturn] = useState(null)

  const fetchReturns = useCallback(async (params) => {
    const response = await axios.get('/returns', {
      params: {
        page: params.page,
        per_page: params.per_page,
        search: params.search,
        status: params.filterTree?.status,
      },
    })
    return { data: response.data.data, total: response.data.meta?.total || 0 }
  }, [])

  const approveMutation = useMutation({
    mutationFn: (id) => axios.post(`/returns/${id}/approve`),
    onSuccess: () => {
      message.success('Return approved successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to approve return'),
  })

  const rejectMutation = useMutation({
    mutationFn: (id) => axios.post(`/returns/${id}/reject`),
    onSuccess: () => {
      message.success('Return rejected')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to reject return'),
  })

  const handleView = (returnItem) => {
    setSelectedReturn(returnItem)
    setDetailModalOpen(true)
  }

  const handleApprove = (returnItem) => {
    Modal.confirm({
      title: 'Approve Return',
      content: 'Are you sure you want to approve this return?',
      okText: 'Approve',
      onOk: () => approveMutation.mutate(returnItem.id),
    })
  }

  const handleReject = (returnItem) => {
    Modal.confirm({
      title: 'Reject Return',
      content: 'Are you sure you want to reject this return?',
      okText: 'Reject',
      okType: 'danger',
      onOk: () => rejectMutation.mutate(returnItem.id),
    })
  }

  const columns = [
    { field: 'return_number', headerName: 'Return #', flex: 1, minWidth: 120 },
    { field: 'order_number', headerName: 'Order #', flex: 1, minWidth: 120 },
    { field: 'customer_name', headerName: 'Customer', flex: 1.5, minWidth: 150 },
    {
      field: 'total',
      headerName: 'Amount',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    { field: 'reason', headerName: 'Reason', flex: 1.5, minWidth: 150 },
    {
      field: 'status',
      headerName: 'Status',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Pending', value: 'pending' },
        { label: 'Approved', value: 'approved' },
        { label: 'Rejected', value: 'rejected' },
        { label: 'Completed', value: 'completed' },
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
              disabled: data.status !== 'pending',
              onClick: () => handleApprove(data),
            },
            {
              key: 'reject',
              label: 'Reject',
              icon: <CloseCircleOutlined />,
              danger: true,
              disabled: data.status !== 'pending',
              onClick: () => handleReject(data),
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
      <Head title="Returns" />

      <GlobalPageHeader
        title="Returns"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchReturns}
        title="Returns"
        searchPlaceholder="Search returns..."
        actionsColumn={actionsColumn}
        instanceId="returns"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title={`Return Details - ${selectedReturn?.return_number}`}
        open={detailModalOpen}
        onCancel={() => {
          setDetailModalOpen(false)
          setSelectedReturn(null)
        }}
        footer={null}
        width={600}
      >
        {selectedReturn && (
          <div>
            <p><Text strong>Order:</Text> {selectedReturn.order_number}</p>
            <p><Text strong>Customer:</Text> {selectedReturn.customer_name}</p>
            <p><Text strong>Reason:</Text> {selectedReturn.reason}</p>
            <p><Text strong>Amount:</Text> {formatCurrency(selectedReturn.total)}</p>
            <p><Text strong>Status:</Text> <StatusBadge status={selectedReturn.status} /></p>
            <p><Text strong>Date:</Text> {formatDateTime(selectedReturn.created_at)}</p>
          </div>
        )}
      </Modal>
    </>
  )
}
