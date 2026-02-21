import { useState } from 'react'
import { Head } from '@inertiajs/react'
import { Typography, Card, Table, DatePicker, Select, Button, Row, Col, Statistic, Tag, Empty, Spin } from 'antd'
import { DownloadOutlined, DollarOutlined, ArrowUpOutlined, ArrowDownOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import axios from 'axios'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, formatDate } from '@/Helpers/formatters'

const { Text } = Typography
const { RangePicker } = DatePicker
const { Option } = Select

export default function Statements() {
  const [accountType, setAccountType] = useState('all')
  const [dateRange, setDateRange] = useState([dayjs().startOf('month'), dayjs().endOf('month')])

  const { data: statementData, isLoading, refetch } = useQuery({
    queryKey: ['statements', accountType, dateRange?.[0]?.format('YYYY-MM-DD'), dateRange?.[1]?.format('YYYY-MM-DD')],
    queryFn: async () => {
      const response = await axios.get('/statements', {
        params: {
          account_type: accountType !== 'all' ? accountType : undefined,
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
      `/statements/export?account_type=${accountType}&start_date=${dateRange?.[0]?.format('YYYY-MM-DD')}&end_date=${dateRange?.[1]?.format('YYYY-MM-DD')}`,
      '_blank'
    )
  }

  const columns = [
    {
      title: 'Date',
      dataIndex: 'date',
      key: 'date',
      render: formatDate,
    },
    {
      title: 'Description',
      dataIndex: 'description',
      key: 'description',
    },
    {
      title: 'Reference',
      dataIndex: 'reference',
      key: 'reference',
      render: (value) => <Text code>{value}</Text>,
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (value) => (
        <Tag color={value === 'credit' ? 'green' : 'red'}>
          {value === 'credit' ? <ArrowUpOutlined /> : <ArrowDownOutlined />}
          {' '}{value?.toUpperCase()}
        </Tag>
      ),
    },
    {
      title: 'Amount',
      dataIndex: 'amount',
      key: 'amount',
      align: 'right',
      render: (value, record) => (
        <Text style={{ color: record.type === 'credit' ? '#52c41a' : '#ff4d4f' }}>
          {record.type === 'credit' ? '+' : '-'}{formatCurrency(Math.abs(value))}
        </Text>
      ),
    },
    {
      title: 'Balance',
      dataIndex: 'balance',
      key: 'balance',
      align: 'right',
      render: formatCurrency,
    },
  ]

  const summary = statementData?.summary || {}

  return (
    <>
      <Head title="Statements" />

      <GlobalPageHeader
        title="Statements"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
      />

      {/* Filters */}
      <Card style={{ marginBottom: 16 }}>
        <Row gutter={16} align="middle">
          <Col>
            <Text strong>Account:</Text>
          </Col>
          <Col>
            <Select value={accountType} onChange={setAccountType} style={{ width: 180 }}>
              <Option value="all">All Accounts</Option>
              <Option value="cash">Cash</Option>
              <Option value="bank">Bank</Option>
              <Option value="receivable">Receivables</Option>
              <Option value="payable">Payables</Option>
            </Select>
          </Col>
          <Col>
            <Text strong>Period:</Text>
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
              Generate
            </Button>
          </Col>
          <Col>
            <Button icon={<DownloadOutlined />} onClick={handleExport}>
              Export PDF
            </Button>
          </Col>
        </Row>
      </Card>

      {/* Summary Stats */}
      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col span={6}>
          <Card>
            <Statistic
              title="Opening Balance"
              value={summary.opening_balance || 0}
              prefix={<DollarOutlined />}
              suffix="SAR"
              precision={2}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total Credits"
              value={summary.total_credits || 0}
              prefix={<ArrowUpOutlined />}
              suffix="SAR"
              precision={2}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total Debits"
              value={summary.total_debits || 0}
              prefix={<ArrowDownOutlined />}
              suffix="SAR"
              precision={2}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Closing Balance"
              value={summary.closing_balance || 0}
              prefix={<DollarOutlined />}
              suffix="SAR"
              precision={2}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
      </Row>

      {/* Transactions Table */}
      <Card title="Transactions">
        {isLoading ? (
          <div style={{ textAlign: 'center', padding: 60 }}>
            <Spin size="large" />
          </div>
        ) : statementData?.transactions?.length > 0 ? (
          <Table
            dataSource={statementData.transactions}
            columns={columns}
            rowKey={(record) => record.id || Math.random()}
            pagination={{ pageSize: 20 }}
          />
        ) : (
          <Empty description="No transactions found for the selected period" />
        )}
      </Card>
    </>
  )
}
