import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Tag, Typography } from 'antd'
import { DollarOutlined, UserOutlined, ClockCircleOutlined, WarningOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency, formatDate } from '@/Helpers/formatters'

const { Text } = Typography

const statusColors = {
  open: 'green',
  closed: 'default',
}

export default function CashRegisterReport() {
  const { registers, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.cash-registers'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const columns = [
    {
      title: 'User',
      dataIndex: 'user',
      key: 'user',
      render: (value) => (
        <span><UserOutlined /> {value}</span>
      ),
    },
    {
      title: 'Location',
      dataIndex: 'location',
      key: 'location',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (value) => (
        <Tag color={statusColors[value] || 'default'}>
          {value?.toUpperCase()}
        </Tag>
      ),
    },
    {
      title: 'Opened At',
      dataIndex: 'opened_at',
      key: 'opened_at',
      render: (value) => value || '-',
    },
    {
      title: 'Closed At',
      dataIndex: 'closed_at',
      key: 'closed_at',
      render: (value) => value || '-',
    },
    {
      title: 'Opening Cash',
      dataIndex: 'opening_cash',
      key: 'opening_cash',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Closing Cash',
      dataIndex: 'closing_cash',
      key: 'closing_cash',
      align: 'right',
      render: (value) => value ? formatCurrency(value) : '-',
    },
    {
      title: 'Expected',
      dataIndex: 'expected_cash',
      key: 'expected_cash',
      align: 'right',
      render: (value) => value ? formatCurrency(value) : '-',
    },
    {
      title: 'Difference',
      dataIndex: 'difference',
      key: 'difference',
      align: 'right',
      render: (value) => {
        if (value === null || value === undefined) return '-'
        const color = value === 0 ? '#52c41a' : value > 0 ? '#1890ff' : '#ff4d4f'
        const prefix = value > 0 ? '+' : ''
        return (
          <Text style={{ color }}>
            {prefix}{formatCurrency(value)}
          </Text>
        )
      },
    },
    {
      title: 'Total Sales',
      dataIndex: 'total_sales',
      key: 'total_sales',
      align: 'right',
      render: (value) => value ? formatCurrency(value) : '-',
    },
  ]

  const registersList = registers?.toArray ? registers.toArray() : (Array.isArray(registers) ? registers : [])

  const totalSales = registersList.reduce((sum, r) => sum + (parseFloat(r.total_sales) || 0), 0)
  const totalDifference = registersList.filter(r => r.status === 'closed')
    .reduce((sum, r) => sum + (r.difference || 0), 0)
  const sessionsCount = registersList.length
  const openSessions = registersList.filter(r => r.status === 'open').length

  return (
    <>
      <Head title="Cash Register Report" />

      <GlobalPageHeader
        title="Cash Register Report"
        parentPageTitle="Reports"
      />

      <ReportFilters
        dateRange={dateRange}
        onDateRangeChange={handleDateChange}
        onRefresh={() => router.reload()}
        showLocation={false}
        showExport={false}
      />

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Sessions"
              value={sessionsCount}
              prefix={<ClockCircleOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Currently Open"
              value={openSessions}
              valueStyle={{ color: openSessions > 0 ? '#52c41a' : undefined }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Sales"
              value={totalSales}
              prefix={<DollarOutlined />}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Cash Difference"
              value={totalDifference}
              precision={2}
              prefix={totalDifference !== 0 ? <WarningOutlined /> : null}
              valueStyle={{
                color: totalDifference === 0 ? '#52c41a' : totalDifference > 0 ? '#1890ff' : '#ff4d4f'
              }}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Register Sessions">
        <Table
          dataSource={registersList}
          columns={columns}
          rowKey="id"
          pagination={{ pageSize: 20, showSizeChanger: true }}
          size="small"
          scroll={{ x: 1200 }}
        />
      </Card>
    </>
  )
}
