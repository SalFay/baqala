import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Tag } from 'antd'
import { DollarOutlined, CreditCardOutlined, BankOutlined, WalletOutlined } from '@ant-design/icons'
import { Pie, Column } from '@ant-design/charts'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency } from '@/Helpers/formatters'

const paymentIcons = {
  cash: <WalletOutlined />,
  card: <CreditCardOutlined />,
  credit_card: <CreditCardOutlined />,
  bank_transfer: <BankOutlined />,
  credit: <DollarOutlined />,
}

const paymentColors = {
  cash: 'green',
  card: 'blue',
  credit_card: 'blue',
  bank_transfer: 'purple',
  credit: 'orange',
}

export default function PaymentMethodReport() {
  const { payments, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.payment-methods'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const columns = [
    {
      title: 'Payment Method',
      dataIndex: 'payment_method',
      key: 'payment_method',
      render: (value) => (
        <Tag icon={paymentIcons[value]} color={paymentColors[value] || 'default'}>
          {value?.replace('_', ' ').toUpperCase() || 'UNKNOWN'}
        </Tag>
      ),
    },
    {
      title: 'Transactions',
      dataIndex: 'transaction_count',
      key: 'transaction_count',
      align: 'right',
    },
    {
      title: 'Total Amount',
      dataIndex: 'total_amount',
      key: 'total_amount',
      align: 'right',
      render: (value) => formatCurrency(value),
      sorter: (a, b) => a.total_amount - b.total_amount,
      defaultSortOrder: 'descend',
    },
    {
      title: '% of Total',
      key: 'percentage',
      align: 'right',
      render: (_, record) => {
        const total = (payments || []).reduce((sum, p) => sum + parseFloat(p.total_amount || 0), 0)
        const pct = total > 0 ? (parseFloat(record.total_amount) / total * 100) : 0
        return `${pct.toFixed(1)}%`
      },
    },
  ]

  const totalAmount = (payments || []).reduce((sum, p) => sum + parseFloat(p.total_amount || 0), 0)
  const totalTransactions = (payments || []).reduce((sum, p) => sum + (p.transaction_count || 0), 0)

  const chartData = (payments || []).map(p => ({
    method: p.payment_method?.replace('_', ' ').toUpperCase() || 'UNKNOWN',
    value: parseFloat(p.total_amount || 0),
  }))

  const pieConfig = {
    data: chartData,
    angleField: 'value',
    colorField: 'method',
    radius: 0.8,
    innerRadius: 0.6,
    label: {
      text: 'method',
      position: 'outside',
    },
    legend: {
      position: 'bottom',
    },
    height: 300,
  }

  return (
    <>
      <Head title="Payment Methods Report" />

      <GlobalPageHeader
        title="Payment Methods Report"
        parentPageTitle="Reports"
      />

      <ReportFilters
        dateRange={dateRange}
        onDateRangeChange={handleDateChange}
        onRefresh={() => router.reload()}
        showLocation={false}
      />

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Total Amount"
              value={totalAmount}
              prefix={<DollarOutlined />}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Total Transactions"
              value={totalTransactions}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Payment Methods"
              value={(payments || []).length}
            />
          </Card>
        </Col>
      </Row>

      <Row gutter={16}>
        <Col xs={24} lg={12}>
          <Card title="Payment Distribution">
            {chartData.length > 0 ? (
              <Pie {...pieConfig} />
            ) : (
              <div style={{ textAlign: 'center', padding: 60, color: '#999' }}>
                No data available
              </div>
            )}
          </Card>
        </Col>
        <Col xs={24} lg={12}>
          <Card title="Payment Breakdown">
            <Table
              dataSource={payments || []}
              columns={columns}
              rowKey="payment_method"
              pagination={false}
              size="small"
            />
          </Card>
        </Col>
      </Row>
    </>
  )
}
