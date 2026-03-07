import { useRef, useState, useEffect } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Card, Row, Col, Statistic, Space, Modal, Form, InputNumber, Input, Select, message, Typography, Descriptions, Tag, DatePicker } from 'antd'
import {
  DollarOutlined,
  CreditCardOutlined,
  WalletOutlined,
  DownloadOutlined,
  EditOutlined,
  ArrowLeftOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import { router } from '@inertiajs/react'
import dayjs from 'dayjs'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import CustomModal from '@/Components/CustomModal'
import { formatCurrency, formatDateTime, getCurrency } from '@/Helpers/formatters'
import {
  fetchCustomerLedger,
  getCustomerStatement,
  collectPayment,
  adjustCustomerBalance,
} from '@/Helpers/api/customerLedgerService'

const { Text } = Typography
const { RangePicker } = DatePicker

export default function CustomerLedger() {
  const { customer } = usePage().props
  const gridRef = useRef()

  // Modal state
  const [collectModalOpen, setCollectModalOpen] = useState(false)
  const [adjustModalOpen, setAdjustModalOpen] = useState(false)
  const [dateRange, setDateRange] = useState(null)

  const [collectForm] = Form.useForm()
  const [adjustForm] = Form.useForm()

  // Collect payment mutation
  const collectMutation = useMutation({
    mutationFn: (data) => collectPayment(customer.id, data),
    onSuccess: () => {
      message.success('Payment collected successfully')
      setCollectModalOpen(false)
      collectForm.resetFields()
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to collect payment')
    },
  })

  // Adjust balance mutation
  const adjustMutation = useMutation({
    mutationFn: (data) => adjustCustomerBalance(customer.id, data),
    onSuccess: () => {
      message.success('Balance adjusted successfully')
      setAdjustModalOpen(false)
      adjustForm.resetFields()
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to adjust balance')
    },
  })

  const handleCollectPayment = async () => {
    try {
      const values = await collectForm.validateFields()
      collectMutation.mutate(values)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleAdjustBalance = async () => {
    try {
      const values = await adjustForm.validateFields()
      adjustMutation.mutate(values)
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleDownloadStatement = async () => {
    try {
      const params = {}
      if (dateRange) {
        params.start_date = dateRange[0].format('YYYY-MM-DD')
        params.end_date = dateRange[1].format('YYYY-MM-DD')
      }
      const response = await getCustomerStatement(customer.id, params)
      // Handle PDF download
      if (response.data.url) {
        window.open(response.data.url, '_blank')
      }
    } catch (error) {
      message.error('Failed to generate statement')
    }
  }

  const handleBack = () => {
    router.visit(route('pos.customers.index'))
  }

  // Calculate credit info
  const creditBalance = customer.current_balance || 0
  const creditLimit = customer.credit_limit || 0
  const availableCredit = Math.max(0, creditLimit - creditBalance)

  // Column definitions
  const columns = [
    {
      field: 'created_at',
      headerName: 'Date',
      flex: 1,
      minWidth: 160,
      cellRenderer: ({ value }) => formatDateTime(value),
    },
    {
      field: 'transaction_type',
      headerName: 'Type',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => {
        const colors = {
          sale: 'blue',
          payment: 'green',
          refund: 'orange',
          adjustment: 'purple',
          credit_sale: 'blue',
          credit_payment: 'green',
        }
        const labels = {
          sale: 'Sale',
          payment: 'Payment',
          refund: 'Refund',
          adjustment: 'Adjustment',
          credit_sale: 'Credit Sale',
          credit_payment: 'Credit Payment',
        }
        return <Tag color={colors[value] || 'default'}>{labels[value] || value}</Tag>
      },
    },
    {
      field: 'reference_type',
      headerName: 'Reference',
      flex: 1.2,
      minWidth: 140,
      cellRenderer: ({ data }) => data.reference_type ? `${data.reference_type} #${data.reference_id}` : '-',
    },
    {
      field: 'description',
      headerName: 'Description',
      flex: 2,
      minWidth: 200,
    },
    {
      field: 'debit',
      headerName: 'Debit',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <Text style={{ color: value > 0 ? '#ff4d4f' : 'inherit' }}>
          {value > 0 ? formatCurrency(value) : '-'}
        </Text>
      ),
    },
    {
      field: 'credit',
      headerName: 'Credit',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <Text style={{ color: value > 0 ? '#52c41a' : 'inherit' }}>
          {value > 0 ? formatCurrency(value) : '-'}
        </Text>
      ),
    },
    {
      field: 'balance_after',
      headerName: 'Balance',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
  ]

  return (
    <>
      <Head title={`Ledger - ${customer.full_name}`} />

      <GlobalPageHeader
        title={`${customer.full_name} - Ledger`}
        parentPageTitle="Customers"
        parentPageRoute="pos.customers.index"
        actionButtons={[
          {
            title: 'Back',
            icon: <ArrowLeftOutlined />,
            onClick: handleBack,
          },
          {
            title: 'Collect Payment',
            icon: <DollarOutlined />,
            onClick: () => setCollectModalOpen(true),
            type: 'primary',
            disabled: creditBalance <= 0,
          },
          {
            title: 'Adjust Balance',
            icon: <EditOutlined />,
            onClick: () => setAdjustModalOpen(true),
          },
          {
            title: 'Download Statement',
            icon: <DownloadOutlined />,
            onClick: handleDownloadStatement,
          },
        ]}
      />

      {/* Customer Info Card */}
      <Card style={{ marginBottom: 16 }}>
        <Descriptions column={{ xs: 1, sm: 2, md: 4 }}>
          <Descriptions.Item label="Customer">{customer.full_name}</Descriptions.Item>
          <Descriptions.Item label="Phone">{customer.phone || '-'}</Descriptions.Item>
          <Descriptions.Item label="Email">{customer.email || '-'}</Descriptions.Item>
          <Descriptions.Item label="Group">
            {customer.customer_group?.name || 'No Group'}
          </Descriptions.Item>
        </Descriptions>
      </Card>

      {/* Credit Summary Cards */}
      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col xs={24} sm={8}>
          <Card>
            <Statistic
              title="Credit Balance (Owed)"
              value={formatCurrency(creditBalance)}
              valueStyle={{ color: creditBalance > 0 ? '#cf1322' : '#3f8600' }}
              prefix={<DollarOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={8}>
          <Card>
            <Statistic
              title="Credit Limit"
              value={formatCurrency(creditLimit)}
              prefix={<CreditCardOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={8}>
          <Card>
            <Statistic
              title="Available Credit"
              value={formatCurrency(availableCredit)}
              valueStyle={{ color: availableCredit > 0 ? '#3f8600' : '#cf1322' }}
              prefix={<WalletOutlined />}
            />
          </Card>
        </Col>
      </Row>

      {/* Date Range Filter */}
      <Card style={{ marginBottom: 16 }}>
        <Space>
          <Text>Date Range:</Text>
          <RangePicker
            value={dateRange}
            onChange={setDateRange}
            allowClear
          />
          {dateRange && (
            <Button onClick={() => setDateRange(null)}>Clear</Button>
          )}
        </Space>
      </Card>

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.customer-ledger.listing"
        routeParams={{ customer: customer.id }}
        columns={columns}
        instanceId={`customer-ledger-${customer.id}`}
        pageSize={20}
        height="calc(100vh - 550px)"
        defaultFilter={dateRange ? {
          start_date: dateRange[0].format('YYYY-MM-DD'),
          end_date: dateRange[1].format('YYYY-MM-DD'),
        } : null}
      />

      {/* Collect Payment Modal */}
      <CustomModal
        title="Collect Payment"
        open={collectModalOpen}
        onCancel={() => {
          setCollectModalOpen(false)
          collectForm.resetFields()
        }}
        width={500}
        showSave
        saveText="Collect"
        loading={collectMutation.isPending}
        onSave={handleCollectPayment}
      >
        <div style={{ marginBottom: 16 }}>
          <Text>Outstanding Balance: </Text>
          <Text strong style={{ color: '#cf1322' }}>
            {formatCurrency(creditBalance)}
          </Text>
        </div>

        <Form form={collectForm} layout="vertical">
          <Form.Item
            name="amount"
            label={`Amount (${getCurrency()})`}
            rules={[
              { required: true, message: 'Please enter amount' },
              { type: 'number', min: 0.01, message: 'Amount must be greater than 0' },
              { type: 'number', max: creditBalance, message: 'Amount cannot exceed balance' },
            ]}
          >
            <InputNumber
              placeholder="0.00"
              min={0.01}
              max={creditBalance}
              precision={2}
              style={{ width: '100%' }}
            />
          </Form.Item>

          <Form.Item
            name="payment_method"
            label="Payment Method"
            rules={[{ required: true, message: 'Please select payment method' }]}
          >
            <Select placeholder="Select payment method">
              <Select.Option value="cash">Cash</Select.Option>
              <Select.Option value="card">Card</Select.Option>
              <Select.Option value="bank_transfer">Bank Transfer</Select.Option>
              <Select.Option value="cheque">Cheque</Select.Option>
            </Select>
          </Form.Item>

          <Form.Item name="reference" label="Reference">
            <Input placeholder="Payment reference (optional)" />
          </Form.Item>

          <Form.Item name="notes" label="Notes">
            <Input.TextArea rows={2} placeholder="Optional notes..." />
          </Form.Item>
        </Form>
      </CustomModal>

      {/* Adjust Balance Modal */}
      <CustomModal
        title="Adjust Balance"
        open={adjustModalOpen}
        onCancel={() => {
          setAdjustModalOpen(false)
          adjustForm.resetFields()
        }}
        width={500}
        showSave
        saveText="Adjust"
        loading={adjustMutation.isPending}
        onSave={handleAdjustBalance}
      >
        <div style={{ marginBottom: 16 }}>
          <Text>Current Balance: </Text>
          <Text strong>{formatCurrency(creditBalance)}</Text>
        </div>

        <Form form={adjustForm} layout="vertical">
          <Form.Item
            name="type"
            label="Adjustment Type"
            rules={[{ required: true, message: 'Please select type' }]}
          >
            <Select placeholder="Select type">
              <Select.Option value="debit">Increase Balance (Charge Customer)</Select.Option>
              <Select.Option value="credit">Decrease Balance (Credit Customer)</Select.Option>
            </Select>
          </Form.Item>

          <Form.Item
            name="amount"
            label={`Amount (${getCurrency()})`}
            rules={[
              { required: true, message: 'Please enter amount' },
              { type: 'number', min: 0.01, message: 'Amount must be greater than 0' },
            ]}
          >
            <InputNumber
              placeholder="0.00"
              min={0.01}
              precision={2}
              style={{ width: '100%' }}
            />
          </Form.Item>

          <Form.Item
            name="reason"
            label="Reason"
            rules={[{ required: true, message: 'Please enter reason' }]}
          >
            <Input.TextArea rows={3} placeholder="Reason for adjustment..." />
          </Form.Item>
        </Form>
      </CustomModal>
    </>
  )
}
