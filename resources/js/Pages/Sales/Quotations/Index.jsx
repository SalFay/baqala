import { useRef, useState } from 'react'
import { Head, router } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space, Card, Statistic, Row, Col } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  SendOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  CopyOutlined,
  ShoppingCartOutlined,
  PrinterOutlined,
  EyeOutlined,
  WarningOutlined,
  FileTextOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import {
  deleteQuotation,
  markAsSent,
  acceptQuotation,
  rejectQuotation,
  convertToOrder,
  duplicateQuotation,
  getQuotationStatistics,
} from '@/Helpers/api/quotationService'

const statusColors = {
  draft: 'default',
  sent: 'processing',
  accepted: 'success',
  rejected: 'error',
  expired: 'warning',
  converted: 'purple',
}

const statusLabels = {
  draft: 'Draft',
  sent: 'Sent',
  accepted: 'Accepted',
  rejected: 'Rejected',
  expired: 'Expired',
  converted: 'Converted',
}

export default function Quotations() {
  const gridRef = useRef()

  // Fetch statistics
  const { data: stats, refetch: refetchStats } = useQuery({
    queryKey: ['quotationStats'],
    queryFn: () => getQuotationStatistics().then(res => res.data),
    refetchInterval: 60000,
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteQuotation(id),
    onSuccess: () => {
      message.success('Quotation deleted')
      gridRef.current?.reloadData()
      refetchStats()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to delete')
    },
  })

  // Send mutation
  const sendMutation = useMutation({
    mutationFn: (id) => markAsSent(id),
    onSuccess: () => {
      message.success('Quotation marked as sent')
      gridRef.current?.reloadData()
      refetchStats()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to send')
    },
  })

  // Accept mutation
  const acceptMutation = useMutation({
    mutationFn: (id) => acceptQuotation(id),
    onSuccess: () => {
      message.success('Quotation accepted')
      gridRef.current?.reloadData()
      refetchStats()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to accept')
    },
  })

  // Reject mutation
  const rejectMutation = useMutation({
    mutationFn: (id) => rejectQuotation(id),
    onSuccess: () => {
      message.success('Quotation rejected')
      gridRef.current?.reloadData()
      refetchStats()
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to reject')
    },
  })

  // Convert mutation
  const convertMutation = useMutation({
    mutationFn: (id) => convertToOrder(id),
    onSuccess: (response) => {
      message.success('Quotation converted to order')
      gridRef.current?.reloadData()
      refetchStats()
      // Navigate to order if needed
      if (response.data?.data?.order?.id) {
        router.visit(route('pos.orders.show', response.data.data.order.id))
      }
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to convert')
    },
  })

  // Duplicate mutation
  const duplicateMutation = useMutation({
    mutationFn: (id) => duplicateQuotation(id),
    onSuccess: (response) => {
      message.success('Quotation duplicated')
      gridRef.current?.reloadData()
      refetchStats()
      // Navigate to edit the duplicate
      if (response.data?.data?.id) {
        router.visit(route('pos.quotations.edit', response.data.data.id))
      }
    },
    onError: (error) => {
      message.error(error.response?.data?.notifications?.[0]?.message || 'Failed to duplicate')
    },
  })

  // Handlers
  const handleCreate = () => {
    router.visit(route('pos.quotations.create'))
  }

  const handleEdit = (quotation) => {
    router.visit(route('pos.quotations.edit', quotation.id))
  }

  const handleView = (quotation) => {
    router.visit(route('pos.quotations.print', quotation.id))
  }

  const handleDelete = (quotation) => {
    Modal.confirm({
      title: 'Delete Quotation',
      content: `Are you sure you want to delete quotation ${quotation.quotation_number}?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(quotation.id),
    })
  }

  const handleConvert = (quotation) => {
    Modal.confirm({
      title: 'Convert to Order',
      content: `Convert quotation ${quotation.quotation_number} to a sales order?`,
      okText: 'Convert',
      onOk: () => convertMutation.mutate(quotation.id),
    })
  }

  // Column definitions
  const columns = [
    {
      field: 'quotation_number',
      headerName: 'Quotation #',
      flex: 1,
      minWidth: 130,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <FileTextOutlined />
          <a onClick={() => handleView(data)}>{data.quotation_number}</a>
        </Space>
      ),
    },
    {
      field: 'customer_name',
      headerName: 'Customer',
      flex: 1.2,
      minWidth: 150,
      filterType: 'text',
    },
    {
      field: 'items_count',
      headerName: 'Items',
      flex: 0.5,
      minWidth: 70,
    },
    {
      field: 'total',
      headerName: 'Total',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <span style={{ fontWeight: 500 }}>
          ${parseFloat(value || 0).toFixed(2)}
        </span>
      ),
    },
    {
      field: 'valid_until',
      headerName: 'Valid Until',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ data }) => {
        if (!data.valid_until) return '-'
        const isExpired = data.is_expired
        return (
          <Space>
            {isExpired && <WarningOutlined style={{ color: '#ff4d4f' }} />}
            <span style={{ color: isExpired ? '#ff4d4f' : undefined }}>
              {dayjs(data.valid_until).format('DD MMM YYYY')}
            </span>
          </Space>
        )
      },
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
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
      field: 'created_by',
      headerName: 'Created By',
      flex: 0.8,
      minWidth: 100,
    },
    {
      field: 'created_at',
      headerName: 'Created',
      flex: 0.8,
      minWidth: 120,
      cellRenderer: ({ value }) => dayjs(value).format('DD MMM YYYY'),
    },
  ]

  // Actions column
  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 120,
    sortable: false,
    cellRenderer: ({ data }) => {
      const isDraft = data.status === 'draft'
      const isSent = data.status === 'sent'
      const isAccepted = data.status === 'accepted'
      const isEditable = isDraft || isSent
      const canConvert = isAccepted || isSent

      return (
        <Dropdown
          trigger={['click']}
          menu={{
            items: [
              {
                key: 'view',
                label: 'View / Print',
                icon: <EyeOutlined />,
                onClick: () => handleView(data),
              },
              {
                key: 'edit',
                label: 'Edit',
                icon: <EditOutlined />,
                disabled: !isEditable,
                onClick: () => handleEdit(data),
              },
              {
                key: 'duplicate',
                label: 'Duplicate',
                icon: <CopyOutlined />,
                onClick: () => duplicateMutation.mutate(data.id),
              },
              { type: 'divider' },
              {
                key: 'send',
                label: 'Mark as Sent',
                icon: <SendOutlined />,
                disabled: !isDraft,
                onClick: () => sendMutation.mutate(data.id),
              },
              {
                key: 'accept',
                label: 'Accept',
                icon: <CheckCircleOutlined style={{ color: '#52c41a' }} />,
                disabled: !isEditable,
                onClick: () => acceptMutation.mutate(data.id),
              },
              {
                key: 'reject',
                label: 'Reject',
                icon: <CloseCircleOutlined style={{ color: '#ff4d4f' }} />,
                disabled: !isEditable,
                onClick: () => rejectMutation.mutate(data.id),
              },
              { type: 'divider' },
              {
                key: 'convert',
                label: 'Convert to Order',
                icon: <ShoppingCartOutlined style={{ color: '#722ed1' }} />,
                disabled: !canConvert || data.converted_order_id,
                onClick: () => handleConvert(data),
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

  return (
    <>
      <Head title="Quotations" />

      <GlobalPageHeader
        title="Quotations"
        parentPageTitle="Sales"
        actionButtons={[
          {
            title: 'New Quotation',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      {/* Statistics */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Total"
              value={stats?.total || 0}
              prefix={<FileTextOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Draft"
              value={stats?.draft || 0}
              valueStyle={{ color: '#8c8c8c' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Sent"
              value={stats?.sent || 0}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Accepted"
              value={stats?.accepted || 0}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Expiring Soon"
              value={stats?.expiring_soon || 0}
              valueStyle={{ color: '#fa8c16' }}
              prefix={<WarningOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} md={6} lg={4}>
          <Card size="small">
            <Statistic
              title="Conversion Rate"
              value={stats?.conversion_rate || 0}
              suffix="%"
              valueStyle={{ color: '#722ed1' }}
            />
          </Card>
        </Col>
      </Row>

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.quotations.listing"
        columns={[...columns, actionsColumn]}
        instanceId="quotations"
        pageSize={20}
        height="calc(100vh - 370px)"
      />
    </>
  )
}
