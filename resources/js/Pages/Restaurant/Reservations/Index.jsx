import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space, Card, Statistic, Row, Col, DatePicker } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  ExclamationCircleOutlined,
  CalendarOutlined,
  UserOutlined,
  TeamOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReservationModal from './ReservationModal'
import {
  deleteReservation,
  confirmReservation,
  cancelReservation,
  completeReservation,
  markNoShow,
  getTodaySummary,
} from '@/Helpers/api/restaurantService'

const statusColors = {
  pending: 'gold',
  confirmed: 'blue',
  cancelled: 'default',
  completed: 'green',
  no_show: 'red',
}

const statusLabels = {
  pending: 'Pending',
  confirmed: 'Confirmed',
  cancelled: 'Cancelled',
  completed: 'Completed',
  no_show: 'No Show',
}

export default function Reservations() {
  const gridRef = useRef()
  const [selectedDate, setSelectedDate] = useState(null)

  // Modal state
  const [modalOpen, setModalOpen] = useState(false)
  const [editingReservation, setEditingReservation] = useState(null)

  // Fetch today's summary
  const { data: summary, refetch: refetchSummary } = useQuery({
    queryKey: ['reservationSummary'],
    queryFn: () => getTodaySummary().then(res => res.data),
    refetchInterval: 60000,
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteReservation(id),
    onSuccess: () => {
      message.success('Reservation deleted')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete reservation')
    },
  })

  // Status mutations
  const confirmMutation = useMutation({
    mutationFn: (id) => confirmReservation(id),
    onSuccess: () => {
      message.success('Reservation confirmed')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to confirm')
    },
  })

  const cancelMutation = useMutation({
    mutationFn: (id) => cancelReservation(id),
    onSuccess: () => {
      message.success('Reservation cancelled')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to cancel')
    },
  })

  const completeMutation = useMutation({
    mutationFn: (id) => completeReservation(id),
    onSuccess: () => {
      message.success('Reservation completed')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to complete')
    },
  })

  const noShowMutation = useMutation({
    mutationFn: (id) => markNoShow(id),
    onSuccess: () => {
      message.warning('Marked as no-show')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to mark as no-show')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingReservation(null)
    setModalOpen(true)
  }

  const handleEdit = (reservation) => {
    setEditingReservation(reservation)
    setModalOpen(true)
  }

  const handleDelete = (reservation) => {
    Modal.confirm({
      title: 'Delete Reservation',
      content: `Are you sure you want to delete this reservation for "${reservation.guest_name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(reservation.id),
    })
  }

  const handleSuccess = () => {
    setModalOpen(false)
    setEditingReservation(null)
    gridRef.current?.reloadData()
    refetchSummary()
  }

  // Column definitions
  const columns = [
    {
      field: 'reservation_date',
      headerName: 'Date',
      flex: 0.8,
      minWidth: 100,
      filterType: 'date',
      cellRenderer: ({ value }) => (
        <Space>
          <CalendarOutlined />
          <span>{dayjs(value).format('DD MMM')}</span>
        </Space>
      ),
    },
    {
      field: 'start_time',
      headerName: 'Time',
      flex: 0.7,
      minWidth: 80,
      cellRenderer: ({ data }) => (
        <span>{data.start_time}{data.end_time ? ` - ${data.end_time}` : ''}</span>
      ),
    },
    {
      field: 'guest_name',
      headerName: 'Guest',
      flex: 1,
      minWidth: 120,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <UserOutlined />
          <span>{data.guest_name}</span>
        </Space>
      ),
    },
    {
      field: 'customer_phone',
      headerName: 'Phone',
      flex: 0.8,
      minWidth: 100,
    },
    {
      field: 'party_size',
      headerName: 'Party',
      flex: 0.5,
      minWidth: 60,
      cellRenderer: ({ value }) => (
        <Space>
          <TeamOutlined />
          <span>{value}</span>
        </Space>
      ),
    },
    {
      field: 'table.name',
      headerName: 'Table',
      flex: 0.7,
      minWidth: 80,
      valueGetter: ({ data }) => data.table?.name || '-',
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 0.7,
      minWidth: 90,
      cellRenderer: ({ value }) => (
        <Tag color={statusColors[value] || 'default'}>
          {statusLabels[value] || value}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: Object.entries(statusLabels).map(([value, label]) => ({
        value,
        label,
      })),
    },
    {
      field: 'special_requests',
      headerName: 'Notes',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        value ? <span style={{ fontSize: 12 }}>{value.substring(0, 30)}...</span> : '-'
      ),
    },
  ]

  // Actions column
  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 120,
    sortable: false,
    cellRenderer: ({ data }) => {
      const isPending = data.status === 'pending'
      const isConfirmed = data.status === 'confirmed'
      const isActive = isPending || isConfirmed

      return (
        <Dropdown
          trigger={['click']}
          menu={{
            items: [
              {
                key: 'edit',
                label: 'Edit',
                icon: <EditOutlined />,
                onClick: () => handleEdit(data),
                disabled: !isActive,
              },
              { type: 'divider' },
              {
                key: 'confirm',
                label: 'Confirm',
                icon: <CheckCircleOutlined />,
                disabled: !isPending,
                onClick: () => confirmMutation.mutate(data.id),
              },
              {
                key: 'complete',
                label: 'Complete',
                icon: <CheckCircleOutlined style={{ color: '#52c41a' }} />,
                disabled: !isConfirmed,
                onClick: () => completeMutation.mutate(data.id),
              },
              {
                key: 'no_show',
                label: 'Mark No-Show',
                icon: <ExclamationCircleOutlined style={{ color: '#ff4d4f' }} />,
                disabled: !isConfirmed,
                onClick: () => noShowMutation.mutate(data.id),
              },
              {
                key: 'cancel',
                label: 'Cancel',
                icon: <CloseCircleOutlined />,
                disabled: !isActive,
                onClick: () => cancelMutation.mutate(data.id),
              },
              { type: 'divider' },
              {
                key: 'delete',
                label: 'Delete',
                icon: <DeleteOutlined />,
                danger: true,
                onClick: () => handleDelete(data),
              },
            ],
          }}
        >
          <Button type="text" icon={<MoreOutlined />} />
        </Dropdown>
      )
    },
  }

  // Filter parameters
  const filterParams = selectedDate
    ? { date: selectedDate.format('YYYY-MM-DD') }
    : {}

  return (
    <>
      <Head title="Reservations" />

      <GlobalPageHeader
        title="Reservations"
        parentPageTitle="Restaurant"
        actionButtons={[
          {
            title: 'Add Reservation',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      {/* Summary Cards */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Today's Total"
              value={summary?.total || 0}
              prefix={<CalendarOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Pending"
              value={summary?.pending || 0}
              valueStyle={{ color: '#faad14' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Confirmed"
              value={summary?.confirmed || 0}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Completed"
              value={summary?.completed || 0}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="No Shows"
              value={summary?.no_show || 0}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
      </Row>

      {/* Filters */}
      <Card size="small" style={{ marginBottom: 16 }}>
        <Space>
          <span>Filter by date:</span>
          <DatePicker
            value={selectedDate}
            onChange={setSelectedDate}
            allowClear
            placeholder="All dates"
          />
        </Space>
      </Card>

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.reservations.listing"
        columns={[...columns, actionsColumn]}
        instanceId="reservations"
        pageSize={20}
        height="calc(100vh - 420px)"
        additionalParams={filterParams}
      />

      <ReservationModal
        open={modalOpen}
        onClose={() => {
          setModalOpen(false)
          setEditingReservation(null)
        }}
        onSuccess={handleSuccess}
        reservation={editingReservation}
      />
    </>
  )
}
