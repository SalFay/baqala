import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Tag, Typography, Avatar } from 'antd'
import { UserOutlined, DollarOutlined, WarningOutlined } from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function CustomerAging() {
  const { customers } = usePage().props

  const columns = [
    {
      title: 'Customer',
      key: 'customer',
      fixed: 'left',
      width: 200,
      render: (_, record) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <Avatar size="small" icon={<UserOutlined />} />
          <div>
            <Text strong>{record.name}</Text>
            {record.phone && (
              <div><Text type="secondary" style={{ fontSize: 12 }}>{record.phone}</Text></div>
            )}
          </div>
        </div>
      ),
    },
    {
      title: 'Credit Limit',
      dataIndex: 'credit_limit',
      key: 'credit_limit',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Current (0-30)',
      dataIndex: 'current',
      key: 'current',
      align: 'right',
      render: (value) => value > 0 ? formatCurrency(value) : '-',
    },
    {
      title: '31-60 Days',
      dataIndex: '1_30_days',
      key: '1_30_days',
      align: 'right',
      render: (value) => value > 0 ? (
        <Text type="warning">{formatCurrency(value)}</Text>
      ) : '-',
    },
    {
      title: '61-90 Days',
      dataIndex: '31_60_days',
      key: '31_60_days',
      align: 'right',
      render: (value) => value > 0 ? (
        <Text style={{ color: '#fa8c16' }}>{formatCurrency(value)}</Text>
      ) : '-',
    },
    {
      title: 'Over 90 Days',
      dataIndex: 'over_90_days',
      key: 'over_90_days',
      align: 'right',
      render: (value) => value > 0 ? (
        <Text type="danger">{formatCurrency(value)}</Text>
      ) : '-',
    },
    {
      title: 'Total Due',
      dataIndex: 'total_due',
      key: 'total_due',
      align: 'right',
      fixed: 'right',
      width: 120,
      render: (value) => (
        <Text strong style={{ color: value > 0 ? '#ff4d4f' : undefined }}>
          {formatCurrency(value)}
        </Text>
      ),
      sorter: (a, b) => a.total_due - b.total_due,
      defaultSortOrder: 'descend',
    },
  ]

  const customersList = customers?.toArray ? customers.toArray() : (Array.isArray(customers) ? customers : [])

  const totalDue = customersList.reduce((sum, c) => sum + (c.total_due || 0), 0)
  const current = customersList.reduce((sum, c) => sum + (c.current || 0), 0)
  const overdue = customersList.reduce((sum, c) =>
    sum + (c['1_30_days'] || 0) + (c['31_60_days'] || 0) + (c['61_90_days'] || 0) + (c.over_90_days || 0), 0)
  const over90 = customersList.reduce((sum, c) => sum + (c.over_90_days || 0), 0)

  return (
    <>
      <Head title="Customer Aging Report" />

      <GlobalPageHeader
        title="Customer Aging Report"
        parentPageTitle="Reports"
      />

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Customers with Balance"
              value={customersList.length}
              prefix={<UserOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Outstanding"
              value={totalDue}
              prefix={<DollarOutlined />}
              precision={2}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Current (0-30 days)"
              value={current}
              precision={2}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Over 90 Days"
              value={over90}
              prefix={<WarningOutlined />}
              precision={2}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Aging Summary">
        <Table
          dataSource={customersList}
          columns={columns}
          rowKey="id"
          pagination={{ pageSize: 50, showSizeChanger: true }}
          size="small"
          scroll={{ x: 1000 }}
          summary={(pageData) => {
            const totals = pageData.reduce((acc, curr) => ({
              current: acc.current + (curr.current || 0),
              days30: acc.days30 + (curr['1_30_days'] || 0),
              days60: acc.days60 + (curr['31_60_days'] || 0),
              days90: acc.days90 + (curr['61_90_days'] || 0),
              over90: acc.over90 + (curr.over_90_days || 0),
              total: acc.total + (curr.total_due || 0),
            }), { current: 0, days30: 0, days60: 0, days90: 0, over90: 0, total: 0 })

            return (
              <Table.Summary.Row style={{ fontWeight: 'bold', backgroundColor: '#fafafa' }}>
                <Table.Summary.Cell>Total</Table.Summary.Cell>
                <Table.Summary.Cell />
                <Table.Summary.Cell align="right">{formatCurrency(totals.current)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.days30)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.days60)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.over90)}</Table.Summary.Cell>
                <Table.Summary.Cell align="right">{formatCurrency(totals.total)}</Table.Summary.Cell>
              </Table.Summary.Row>
            )
          }}
        />
      </Card>
    </>
  )
}
