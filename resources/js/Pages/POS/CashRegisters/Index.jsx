import { useRef, useState, useEffect } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Card, Row, Col, Statistic, Space, Tag } from 'antd'
import {
  MoreOutlined,
  PlusOutlined,
  DollarOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  EyeOutlined,
  BankOutlined,
  LoginOutlined,
  LogoutOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import StatusBadge from '@/Components/StatusBadge'
import OpenRegisterModal from './OpenRegisterModal'
import CloseRegisterModal from './CloseRegisterModal'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'
import {
  getCurrentRegister,
  getDailyReport,
  registerPayIn,
  registerPayOut,
} from '@/Helpers/api/cashRegisterService'

export default function CashRegisters() {
  const gridRef = useRef()

  // Modal state
  const [openModalVisible, setOpenModalVisible] = useState(false)
  const [closeModalVisible, setCloseModalVisible] = useState(false)
  const [selectedRegister, setSelectedRegister] = useState(null)
  const [payInOutModalVisible, setPayInOutModalVisible] = useState(false)
  const [payInOutType, setPayInOutType] = useState('in')

  // Fetch current register status
  const { data: currentRegister, refetch: refetchCurrentRegister } = useQuery({
    queryKey: ['currentRegister'],
    queryFn: () => getCurrentRegister().then(res => res.data.data),
  })

  // Fetch daily report
  const { data: dailyReport } = useQuery({
    queryKey: ['cashRegisterDailyReport'],
    queryFn: () => getDailyReport().then(res => res.data.data),
  })

  // Pay In/Out mutations
  const payInMutation = useMutation({
    mutationFn: ({ registerId, data }) => registerPayIn(registerId, data),
    onSuccess: () => {
      message.success('Pay-in recorded successfully')
      setPayInOutModalVisible(false)
      gridRef.current?.reloadData()
      refetchCurrentRegister()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to record pay-in')
    },
  })

  const payOutMutation = useMutation({
    mutationFn: ({ registerId, data }) => registerPayOut(registerId, data),
    onSuccess: () => {
      message.success('Pay-out recorded successfully')
      setPayInOutModalVisible(false)
      gridRef.current?.reloadData()
      refetchCurrentRegister()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to record pay-out')
    },
  })

  // Handlers
  const handleOpenRegister = () => {
    setOpenModalVisible(true)
  }

  const handleCloseRegister = (register) => {
    setSelectedRegister(register)
    setCloseModalVisible(true)
  }

  const handlePayIn = (register) => {
    setSelectedRegister(register)
    setPayInOutType('in')
    setPayInOutModalVisible(true)
  }

  const handlePayOut = (register) => {
    setSelectedRegister(register)
    setPayInOutType('out')
    setPayInOutModalVisible(true)
  }

  const handleSuccess = () => {
    setOpenModalVisible(false)
    setCloseModalVisible(false)
    setSelectedRegister(null)
    gridRef.current?.reloadData()
    refetchCurrentRegister()
  }

  const handlePayInOutSubmit = (values) => {
    if (payInOutType === 'in') {
      payInMutation.mutate({ registerId: selectedRegister.id, data: values })
    } else {
      payOutMutation.mutate({ registerId: selectedRegister.id, data: values })
    }
  }

  // Column definitions
  const columns = [
    {
      field: 'created_at',
      headerName: 'Opened At',
      flex: 1.2,
      minWidth: 160,
      cellRenderer: ({ value }) => formatDateTime(value),
    },
    {
      field: 'user',
      headerName: 'Cashier',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ data }) => data.user?.name || '-',
    },
    {
      field: 'opening_cash',
      headerName: 'Opening Cash',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'total_sales',
      headerName: 'Total Sales',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => formatCurrency(value || 0),
    },
    {
      field: 'closing_cash',
      headerName: 'Closing Cash',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value }) => value ? formatCurrency(value) : '-',
    },
    {
      field: 'cash_difference',
      headerName: 'Difference',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => {
        if (value === null || value === undefined) return '-'
        const color = value > 0 ? 'green' : value < 0 ? 'red' : 'inherit'
        const prefix = value > 0 ? '+' : ''
        return <span style={{ color }}>{prefix}{formatCurrency(value)}</span>
      },
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value === 'open' ? 'green' : 'default'}>
          {value === 'open' ? 'Open' : 'Closed'}
        </Tag>
      ),
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
              onClick: () => {},
            },
            ...(data.status === 'open' ? [
              { type: 'divider' },
              {
                key: 'payIn',
                label: 'Pay In',
                icon: <LoginOutlined />,
                onClick: () => handlePayIn(data),
              },
              {
                key: 'payOut',
                label: 'Pay Out',
                icon: <LogoutOutlined />,
                onClick: () => handlePayOut(data),
              },
              { type: 'divider' },
              {
                key: 'close',
                label: 'Close Register',
                icon: <CheckCircleOutlined />,
                onClick: () => handleCloseRegister(data),
              },
            ] : []),
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  return (
    <>
      <Head title="Cash Registers" />

      <GlobalPageHeader
        title="Cash Registers"
        parentPageTitle="POS"
        actionButtons={[
          {
            title: currentRegister ? 'Register Open' : 'Open Register',
            icon: <BankOutlined />,
            onClick: handleOpenRegister,
            type: 'primary',
            disabled: !!currentRegister,
          },
        ]}
      />

      {/* Summary Cards */}
      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Today's Sessions"
              value={dailyReport?.total_sessions || 0}
              prefix={<ClockCircleOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Total Cash Sales"
              value={formatCurrency(dailyReport?.total_cash_sales || 0)}
              prefix={<DollarOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Total Pay-In"
              value={formatCurrency(dailyReport?.total_pay_in || 0)}
              valueStyle={{ color: '#3f8600' }}
              prefix={<LoginOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Total Pay-Out"
              value={formatCurrency(dailyReport?.total_pay_out || 0)}
              valueStyle={{ color: '#cf1322' }}
              prefix={<LogoutOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.cash-registers.listing"
        columns={[...columns, actionsColumn]}
        instanceId="cash-registers"
        pageSize={20}
        height="calc(100vh - 400px)"
      />

      <OpenRegisterModal
        open={openModalVisible}
        onClose={() => setOpenModalVisible(false)}
        onSuccess={handleSuccess}
      />

      {selectedRegister && (
        <CloseRegisterModal
          open={closeModalVisible}
          onClose={() => {
            setCloseModalVisible(false)
            setSelectedRegister(null)
          }}
          onSuccess={handleSuccess}
          register={selectedRegister}
        />
      )}

      {/* Pay In/Out Modal */}
      <Modal
        title={payInOutType === 'in' ? 'Pay In' : 'Pay Out'}
        open={payInOutModalVisible}
        onCancel={() => setPayInOutModalVisible(false)}
        onOk={() => {
          const form = document.getElementById('payInOutForm')
          if (form) form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }))
        }}
        confirmLoading={payInMutation.isPending || payOutMutation.isPending}
      >
        <form
          id="payInOutForm"
          onSubmit={(e) => {
            e.preventDefault()
            const formData = new FormData(e.target)
            handlePayInOutSubmit({
              amount: parseFloat(formData.get('amount')),
              notes: formData.get('notes'),
            })
          }}
        >
          <div style={{ marginBottom: 16 }}>
            <label>Amount</label>
            <input
              name="amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              className="ant-input"
              style={{ width: '100%', padding: 8, marginTop: 4 }}
            />
          </div>
          <div>
            <label>Notes</label>
            <textarea
              name="notes"
              className="ant-input"
              style={{ width: '100%', padding: 8, marginTop: 4 }}
              rows={3}
            />
          </div>
        </form>
      </Modal>
    </>
  )
}
