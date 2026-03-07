import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Statistic, Table, Typography } from 'antd'
import { DollarOutlined, ShoppingCartOutlined, PercentageOutlined } from '@ant-design/icons'
import { Column } from '@ant-design/charts'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency, formatDate } from '@/Helpers/formatters'

const { Text } = Typography

export default function DailySummary() {
  const { daily, summary, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.daily-summary'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const columns = [
    {
      title: 'Date',
      dataIndex: 'date',
      key: 'date',
      render: (value) => formatDate(value),
    },
    {
      title: 'Orders',
      dataIndex: 'order_count',
      key: 'order_count',
      align: 'right',
    },
    {
      title: 'Sales',
      dataIndex: 'total_sales',
      key: 'total_sales',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Tax',
      dataIndex: 'total_tax',
      key: 'total_tax',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Avg Order',
      key: 'average',
      align: 'right',
      render: (_, record) => formatCurrency(record.order_count > 0 ? record.total_sales / record.order_count : 0),
    },
  ]

  const chartData = (daily || []).map(d => ({
    date: dayjs(d.date).format('MMM DD'),
    total: parseFloat(d.total_sales || 0),
  }))

  const chartConfig = {
    data: chartData,
    xField: 'date',
    yField: 'total',
    style: { fill: '#1890ff', radiusTopLeft: 4, radiusTopRight: 4 },
    height: 300,
  }

  return (
    <>
      <Head title="Daily Sales Summary" />

      <GlobalPageHeader
        title="Daily Sales Summary"
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
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Sales"
              value={summary?.total_sales || 0}
              prefix={<DollarOutlined />}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Orders"
              value={summary?.total_orders || 0}
              prefix={<ShoppingCartOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Avg Order Value"
              value={summary?.average_order_value || 0}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Tax"
              value={summary?.total_tax || 0}
              prefix={<PercentageOutlined />}
              precision={2}
            />
          </Card>
        </Col>
      </Row>

      {/* Chart */}
      {chartData.length > 0 && (
        <Card title="Sales Trend" style={{ marginBottom: 16 }}>
          <Column {...chartConfig} />
        </Card>
      )}

      {/* Data Table */}
      <Card title="Daily Breakdown">
        <Table
          dataSource={daily || []}
          columns={columns}
          rowKey="date"
          pagination={{ pageSize: 31 }}
          size="small"
          summary={(pageData) => {
            const totals = pageData.reduce((acc, curr) => ({
              orders: acc.orders + (curr.order_count || 0),
              sales: acc.sales + parseFloat(curr.total_sales || 0),
              tax: acc.tax + parseFloat(curr.total_tax || 0),
            }), { orders: 0, sales: 0, tax: 0 })

            return (
              <Table.Summary.Row style={{ fontWeight: 'bold', backgroundColor: '#fafafa' }}>
                <Table.Summary.Cell>Total</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{totals.orders}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.sales)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.tax)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.orders > 0 ? totals.sales / totals.orders : 0)}</Table.Summary.Cell>
              </Table.Summary.Row>
            )
          }}
        />
      </Card>
    </>
  )
}
