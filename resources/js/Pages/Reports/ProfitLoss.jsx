import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Statistic, Table, Typography, Divider } from 'antd'
import {
  DollarOutlined,
  RiseOutlined,
  FallOutlined,
  ShoppingCartOutlined,
} from '@ant-design/icons'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency } from '@/Helpers/formatters'

const { Title, Text } = Typography

export default function ProfitLoss() {
  const { data, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.profit-loss'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const handleRefresh = () => {
    router.reload()
  }

  const profitMarginColor = (data?.gross_margin || 0) >= 20 ? '#52c41a' : (data?.gross_margin || 0) >= 10 ? '#faad14' : '#ff4d4f'

  return (
    <>
      <Head title="Profit & Loss Report" />

      <GlobalPageHeader
        title="Profit & Loss Report"
        parentPageTitle="Reports"
      />

      <ReportFilters
        dateRange={dateRange}
        onDateRangeChange={handleDateChange}
        onRefresh={handleRefresh}
        showLocation={false}
        showExport={false}
      />

      {/* Summary Cards */}
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Total Revenue"
              value={data?.total_revenue || 0}
              prefix={<DollarOutlined />}
              precision={2}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Cost of Goods"
              value={data?.total_cost || 0}
              precision={2}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Gross Profit"
              value={data?.gross_profit || 0}
              prefix={data?.gross_profit >= 0 ? <RiseOutlined /> : <FallOutlined />}
              precision={2}
              valueStyle={{ color: data?.gross_profit >= 0 ? '#52c41a' : '#ff4d4f' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Gross Margin"
              value={data?.gross_margin || 0}
              suffix="%"
              precision={1}
              valueStyle={{ color: profitMarginColor }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Total Orders"
              value={data?.total_orders || 0}
              prefix={<ShoppingCartOutlined />}
            />
          </Card>
        </Col>
      </Row>

      {/* P&L Statement */}
      <Card title="Profit & Loss Statement">
        <div style={{ maxWidth: 600 }}>
          <Row justify="space-between" style={{ padding: '12px 0', borderBottom: '1px solid #f0f0f0' }}>
            <Col>
              <Title level={5} style={{ margin: 0 }}>Revenue</Title>
            </Col>
            <Col>
              <Title level={5} style={{ margin: 0 }}>{formatCurrency(data?.total_revenue || 0)}</Title>
            </Col>
          </Row>

          <Row justify="space-between" style={{ padding: '12px 0', borderBottom: '1px solid #f0f0f0' }}>
            <Col>
              <Text>Cost of Goods Sold</Text>
            </Col>
            <Col>
              <Text type="danger">({formatCurrency(data?.total_cost || 0)})</Text>
            </Col>
          </Row>

          <Divider style={{ margin: '12px 0' }} />

          <Row justify="space-between" style={{ padding: '12px 0', backgroundColor: data?.gross_profit >= 0 ? '#f6ffed' : '#fff2f0' }}>
            <Col>
              <Title level={4} style={{ margin: 0 }}>Gross Profit</Title>
            </Col>
            <Col>
              <Title level={4} style={{ margin: 0, color: data?.gross_profit >= 0 ? '#52c41a' : '#ff4d4f' }}>
                {formatCurrency(data?.gross_profit || 0)}
              </Title>
            </Col>
          </Row>

          <Row justify="space-between" style={{ padding: '12px 0' }}>
            <Col>
              <Text type="secondary">Gross Margin</Text>
            </Col>
            <Col>
              <Text strong style={{ color: profitMarginColor }}>
                {(data?.gross_margin || 0).toFixed(1)}%
              </Text>
            </Col>
          </Row>
        </div>
      </Card>
    </>
  )
}
