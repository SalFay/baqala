import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Typography } from 'antd'
import { Pie } from '@ant-design/charts'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function SalesByCategory() {
  const { categories, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.sales-by-category'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const columns = [
    {
      title: 'Category',
      dataIndex: 'category_name',
      key: 'category_name',
    },
    {
      title: 'Qty Sold',
      dataIndex: 'total_quantity',
      key: 'total_quantity',
      align: 'right',
    },
    {
      title: 'Revenue',
      dataIndex: 'total_revenue',
      key: 'total_revenue',
      align: 'right',
      render: (value) => formatCurrency(value),
      sorter: (a, b) => a.total_revenue - b.total_revenue,
    },
    {
      title: '% of Total',
      key: 'percentage',
      align: 'right',
      render: (_, record) => {
        const total = (categories || []).reduce((sum, c) => sum + parseFloat(c.total_revenue || 0), 0)
        const pct = total > 0 ? (parseFloat(record.total_revenue) / total * 100) : 0
        return `${pct.toFixed(1)}%`
      },
    },
  ]

  const totalRevenue = (categories || []).reduce((sum, c) => sum + parseFloat(c.total_revenue || 0), 0)

  const chartData = (categories || []).map(c => ({
    category: c.category_name,
    value: parseFloat(c.total_revenue || 0),
  }))

  const chartConfig = {
    data: chartData,
    angleField: 'value',
    colorField: 'category',
    radius: 0.8,
    innerRadius: 0.6,
    label: {
      text: 'category',
      position: 'outside',
    },
    legend: {
      position: 'right',
    },
    height: 350,
  }

  return (
    <>
      <Head title="Sales by Category" />

      <GlobalPageHeader
        title="Sales by Category"
        parentPageTitle="Reports"
      />

      <ReportFilters
        dateRange={dateRange}
        onDateRangeChange={handleDateChange}
        onRefresh={() => router.reload()}
        showLocation={false}
      />

      <Row gutter={16}>
        <Col xs={24} lg={12}>
          <Card title="Category Revenue Distribution">
            {chartData.length > 0 ? (
              <Pie {...chartConfig} />
            ) : (
              <div style={{ textAlign: 'center', padding: 60, color: '#999' }}>
                No data available
              </div>
            )}
          </Card>
        </Col>
        <Col xs={24} lg={12}>
          <Card
            title="Category Breakdown"
            extra={<Text type="secondary">Total: {formatCurrency(totalRevenue)}</Text>}
          >
            <Table
              dataSource={categories || []}
              columns={columns}
              rowKey="category_id"
              pagination={false}
              size="small"
            />
          </Card>
        </Col>
      </Row>
    </>
  )
}
