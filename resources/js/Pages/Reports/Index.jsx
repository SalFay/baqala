import { useState } from 'react'
import { Head } from '@inertiajs/react'
import { Typography, Card, Row, Col, DatePicker, Button, Select, Table, Statistic, Spin, Empty } from 'antd'
import { DownloadOutlined, BarChartOutlined, DollarOutlined, ShoppingCartOutlined, UserOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import { Column } from '@ant-design/charts'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, formatDate } from '@/Helpers/formatters'

const { Text } = Typography
const { RangePicker } = DatePicker
const { Option } = Select

export default function Reports() {
  const [reportType, setReportType] = useState('sales')
  const [dateRange, setDateRange] = useState([dayjs().startOf('month'), dayjs().endOf('month')])

  const { data: reportData, isLoading, refetch } = useQuery({
    queryKey: ['reports', reportType, dateRange?.[0]?.format('YYYY-MM-DD'), dateRange?.[1]?.format('YYYY-MM-DD')],
    queryFn: async () => {
      const response = await axios.get('/reports', {
        params: {
          type: reportType,
          start_date: dateRange?.[0]?.format('YYYY-MM-DD'),
          end_date: dateRange?.[1]?.format('YYYY-MM-DD'),
        },
      })
      return response.data
    },
    enabled: !!dateRange?.[0] && !!dateRange?.[1],
  })

  const handleExport = () => {
    window.open(
      `/reports/export?type=${reportType}&start_date=${dateRange?.[0]?.format('YYYY-MM-DD')}&end_date=${dateRange?.[1]?.format('YYYY-MM-DD')}`,
      '_blank'
    )
  }

  const salesColumns = [
    { title: 'Date', dataIndex: 'date', key: 'date', render: formatDate },
    { title: 'Orders', dataIndex: 'order_count', key: 'order_count' },
    { title: 'Total Sales', dataIndex: 'total', key: 'total', render: formatCurrency },
    { title: 'Avg Order', dataIndex: 'average', key: 'average', render: formatCurrency },
  ]

  const productColumns = [
    { title: 'Product', dataIndex: 'name', key: 'name' },
    { title: 'SKU', dataIndex: 'sku', key: 'sku' },
    { title: 'Qty Sold', dataIndex: 'quantity', key: 'quantity' },
    { title: 'Revenue', dataIndex: 'revenue', key: 'revenue', render: formatCurrency },
  ]

  const chartConfig = {
    data: reportData?.chart_data || [],
    xField: 'date',
    yField: 'total',
    style: { fill: '#1890ff', radiusTopLeft: 4, radiusTopRight: 4 },
    axis: { y: { labelFormatter: (v) => `${v} SAR` } },
  }

  return (
    <>
      <Head title="Reports" />

      <GlobalPageHeader
        title="Reports"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      {/* Filters */}
      <Card style={{ marginBottom: 16 }}>
        <Row gutter={16} align="middle">
          <Col>
            <Text strong>Report Type:</Text>
          </Col>
          <Col>
            <Select value={reportType} onChange={setReportType} style={{ width: 200 }}>
              <Option value="sales">Sales Report</Option>
              <Option value="products">Products Report</Option>
              <Option value="customers">Customers Report</Option>
              <Option value="inventory">Inventory Report</Option>
            </Select>
          </Col>
          <Col>
            <Text strong>Date Range:</Text>
          </Col>
          <Col>
            <RangePicker
              value={dateRange}
              onChange={setDateRange}
              allowClear={false}
            />
          </Col>
          <Col>
            <Button type="primary" onClick={() => refetch()}>
              Generate Report
            </Button>
          </Col>
          <Col>
            <Button icon={<DownloadOutlined />} onClick={handleExport}>
              Export
            </Button>
          </Col>
        </Row>
      </Card>

      {/* Summary Stats */}
      {reportData?.summary && (
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col span={6}>
            <Card>
              <Statistic
                title="Total Sales"
                value={reportData.summary.total_sales || 0}
                prefix={<DollarOutlined />}
                suffix="SAR"
                precision={2}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title="Total Orders"
                value={reportData.summary.total_orders || 0}
                prefix={<ShoppingCartOutlined />}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title="Avg Order Value"
                value={reportData.summary.avg_order_value || 0}
                prefix={<BarChartOutlined />}
                suffix="SAR"
                precision={2}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title="Unique Customers"
                value={reportData.summary.unique_customers || 0}
                prefix={<UserOutlined />}
              />
            </Card>
          </Col>
        </Row>
      )}

      {/* Chart */}
      {reportData?.chart_data?.length > 0 && (
        <Card title="Sales Trend" style={{ marginBottom: 16 }}>
          <Column {...chartConfig} height={300} />
        </Card>
      )}

      {/* Data Table */}
      <Card title="Report Data">
        {isLoading ? (
          <div style={{ textAlign: 'center', padding: 60 }}>
            <Spin size="large" />
          </div>
        ) : reportData?.data?.length > 0 ? (
          <Table
            dataSource={reportData.data}
            columns={reportType === 'products' ? productColumns : salesColumns}
            rowKey={(record) => record.id || record.date || Math.random()}
            pagination={{ pageSize: 20 }}
          />
        ) : (
          <Empty description="No data available for the selected period" />
        )}
      </Card>
    </>
  )
}
