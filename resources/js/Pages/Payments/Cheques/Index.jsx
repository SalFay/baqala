import { useRef, useState, useEffect } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Card, Row, Col, Statistic, Tag, Space } from 'antd'
import {
  MoreOutlined,
  PlusOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  ExclamationCircleOutlined,
  BankOutlined,
  EditOutlined,
  DeleteOutlined,
  WarningOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ChequeModal from './ChequeModal'
import { formatCurrency, formatDate } from '@/Helpers/formatters'
import {
  getChequeSummary,
  deleteCheque,
  depositCheque,
  clearCheque,
  bounceCheque,
  cancelCheque,
} from '@/Helpers/api/chequeService'

const statusColors = {
  pending: 'orange',
  deposited: 'blue',
  cleared: 'green',
  bounced: 'red',
  cancelled: 'default',
}

const statusLabels = {
  pending: 'Pending',
  deposited: 'Deposited',
  cleared: 'Cleared',
  bounced: 'Bounced',
  cancelled: 'Cancelled',
}

export default function Cheques() {
  const gridRef = useRef()

  // Modal state
  const [modalOpen, setModalOpen] = useState(false)
  const [editingCheque, setEditingCheque] = useState(null)

  // Fetch summary
  const { data: summary, refetch: refetchSummary } = useQuery({
    queryKey: ['chequeSummary'],
    queryFn: () => getChequeSummary().then(res => res.data.data),
  })

  // Mutations
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteCheque(id),
    onSuccess: () => {
      message.success('Cheque deleted successfully')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete cheque')
    },
  })

  const depositMutation = useMutation({
    mutationFn: (id) => depositCheque(id),
    onSuccess: () => {
      message.success('Cheque marked as deposited')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to deposit cheque')
    },
  })

  const clearMutation = useMutation({
    mutationFn: (id) => clearCheque(id),
    onSuccess: () => {
      message.success('Cheque marked as cleared')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to clear cheque')
    },
  })

  const bounceMutation = useMutation({
    mutationFn: ({ id, notes }) => bounceCheque(id, notes),
    onSuccess: () => {
      message.success('Cheque marked as bounced')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to mark cheque as bounced')
    },
  })

  const cancelMutation = useMutation({
    mutationFn: ({ id, notes }) => cancelCheque(id, notes),
    onSuccess: () => {
      message.success('Cheque cancelled')
      gridRef.current?.reloadData()
      refetchSummary()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to cancel cheque')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingCheque(null)
    setModalOpen(true)
  }

  const handleEdit = (cheque) => {
    setEditingCheque(cheque)
    setModalOpen(true)
  }

  const handleDelete = (cheque) => {
    Modal.confirm({
      title: 'Delete Cheque',
      content: `Are you sure you want to delete cheque #${cheque.cheque_number}?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(cheque.id),
    })
  }

  const handleDeposit = (cheque) => {
    Modal.confirm({
      title: 'Deposit Cheque',
      content: `Mark cheque #${cheque.cheque_number} as deposited?`,
      onOk: () => depositMutation.mutate(cheque.id),
    })
  }

  const handleClear = (cheque) => {
    Modal.confirm({
      title: 'Clear Cheque',
      content: `Mark cheque #${cheque.cheque_number} as cleared?`,
      onOk: () => clearMutation.mutate(cheque.id),
    })
  }

  const handleBounce = (cheque) => {
    Modal.confirm({
      title: 'Bounce Cheque',
      content: `Mark cheque #${cheque.cheque_number} as bounced? This will affect the customer's credit.`,
      okType: 'danger',
      onOk: () => bounceMutation.mutate({ id: cheque.id }),
    })
  }

  const handleCancel = (cheque) => {
    Modal.confirm({
      title: 'Cancel Cheque',
      content: `Cancel cheque #${cheque.cheque_number}?`,
      onOk: () => cancelMutation.mutate({ id: cheque.id }),
    })
  }

  const handleSuccess = () => {
    setModalOpen(false)
    setEditingCheque(null)
    gridRef.current?.reloadData()
    refetchSummary()
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingCheque(null)
  }

  // Get action items based on status
  const getActionItems = (cheque) => {
    const items = []

    if (cheque.status === 'pending') {
      items.push({
        key: 'edit',
        label: 'Edit',
        icon: <EditOutlined />,
        onClick: () => handleEdit(cheque),
      })
      items.push({
        key: 'deposit',
        label: 'Mark as Deposited',
        icon: <BankOutlined />,
        onClick: () => handleDeposit(cheque),
      })
      items.push({ type: 'divider' })
      items.push({
        key: 'cancel',
        label: 'Cancel',
        icon: <CloseCircleOutlined />,
        onClick: () => handleCancel(cheque),
      })
    } else if (cheque.status === 'deposited') {
      items.push({
        key: 'clear',
        label: 'Mark as Cleared',
        icon: <CheckCircleOutlined />,
        onClick: () => handleClear(cheque),
      })
      items.push({
        key: 'bounce',
        label: 'Mark as Bounced',
        icon: <ExclamationCircleOutlined />,
        danger: true,
        onClick: () => handleBounce(cheque),
      })
    }

    if (cheque.status === 'pending') {
      items.push({ type: 'divider' })
      items.push({
        key: 'delete',
        label: 'Delete',
        icon: <DeleteOutlined />,
        danger: true,
        onClick: () => handleDelete(cheque),
      })
    }

    return items
  }

  // Column definitions
  const columns = [
    {
      field: 'cheque_number',
      headerName: 'Cheque #',
      flex: 1,
      minWidth: 120,
      filterType: 'text',
    },
    {
      field: 'customer',
      headerName: 'Customer',
      flex: 1.5,
      minWidth: 150,
      cellRenderer: ({ data }) => data.customer?.name || '-',
    },
    {
      field: 'bank_name',
      headerName: 'Bank',
      flex: 1,
      minWidth: 120,
    },
    {
      field: 'amount',
      headerName: 'Amount',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'cheque_date',
      headerName: 'Cheque Date',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => formatDate(value),
    },
    {
      field: 'due_date',
      headerName: 'Due Date',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value, data }) => {
        const date = formatDate(value)
        const isOverdue = data.status === 'pending' && new Date(value) < new Date()
        return (
          <Space>
            {date}
            {isOverdue && <WarningOutlined style={{ color: '#ff4d4f' }} />}
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
  ]

  // Actions column
  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 100,
    sortable: false,
    cellRenderer: ({ data }) => {
      const items = getActionItems(data)
      if (items.length === 0) return null
      return (
        <Dropdown trigger={['click']} menu={{ items }}>
          <Button type="text" icon={<MoreOutlined />} />
        </Dropdown>
      )
    },
  }

  return (
    <>
      <Head title="Cheques" />

      <GlobalPageHeader
        title="Cheques"
        parentPageTitle="Payments"
        actionButtons={[
          {
            title: 'Add Cheque',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      {/* Summary Cards */}
      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Pending"
              value={summary?.pending?.count || 0}
              suffix={`(${formatCurrency(summary?.pending?.amount || 0)})`}
              valueStyle={{ color: '#fa8c16' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Deposited"
              value={summary?.deposited?.count || 0}
              suffix={`(${formatCurrency(summary?.deposited?.amount || 0)})`}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Overdue"
              value={summary?.overdue?.count || 0}
              suffix={`(${formatCurrency(summary?.overdue?.amount || 0)})`}
              valueStyle={{ color: '#ff4d4f' }}
              prefix={<WarningOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Due Soon (7 days)"
              value={summary?.due_soon?.count || 0}
              suffix={`(${formatCurrency(summary?.due_soon?.amount || 0)})`}
              valueStyle={{ color: '#faad14' }}
            />
          </Card>
        </Col>
      </Row>

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.cheques.listing"
        columns={[...columns, actionsColumn]}
        instanceId="cheques"
        pageSize={20}
        height="calc(100vh - 400px)"
      />

      <ChequeModal
        open={modalOpen}
        onClose={handleCloseModal}
        onSuccess={handleSuccess}
        cheque={editingCheque}
      />
    </>
  )
}
