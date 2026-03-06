import { useRef, useState, useEffect } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Card, Row, Col, Statistic, Space, Select, Progress } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  WarningOutlined,
  StopOutlined,
  ClockCircleOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import BatchModal from './BatchModal'
import { deleteBatch, fetchBatchStatistics, markBatchExpired, recallBatch, quarantineBatch } from '@/Helpers/api/productBatchService'

const { Option } = Select

const STATUS_OPTIONS = [
  { value: 'active', label: 'Active', color: 'green' },
  { value: 'low_stock', label: 'Low Stock', color: 'orange' },
  { value: 'out_of_stock', label: 'Out of Stock', color: 'default' },
  { value: 'expired', label: 'Expired', color: 'red' },
  { value: 'recalled', label: 'Recalled', color: 'red' },
  { value: 'quarantine', label: 'Quarantine', color: 'purple' },
]

const EXPIRY_OPTIONS = [
  { value: 'valid', label: 'Valid' },
  { value: 'expiring_soon', label: 'Expiring Soon' },
  { value: 'expired', label: 'Expired' },
]

export default function Batches() {
  const { token } = theme.useToken()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [record, setRecord] = useState(null)
  const [stats, setStats] = useState(null)
  const [statusFilter, setStatusFilter] = useState(null)
  const [expiryFilter, setExpiryFilter] = useState(null)

  useEffect(() => {
    loadStats()
  }, [])

  const loadStats = async () => {
    try {
      const response = await fetchBatchStatistics()
      setStats(response.data.data)
    } catch (error) {
      console.error('Failed to load statistics:', error)
    }
  }

  const handleRefresh = () => {
    gridRef.current?.reloadData()
    loadStats()
  }

  const handleCreate = () => {
    setRecord(null)
    setVisible(true)
  }

  const handleEdit = (rec) => {
    setRecord(rec)
    setVisible(true)
  }

  const handleDelete = async (rec) => {
    try {
      await deleteBatch(rec.id)
      message.success('Batch deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete')
    }
  }

  const handleMarkExpired = async (rec) => {
    try {
      await markBatchExpired(rec.id)
      message.success('Batch marked as expired')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to update')
    }
  }

  const handleRecall = async (rec) => {
    try {
      await recallBatch(rec.id)
      message.success('Batch recalled')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to update')
    }
  }

  const handleQuarantine = async (rec) => {
    try {
      await quarantineBatch(rec.id)
      message.success('Batch placed in quarantine')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to update')
    }
  }

  const columns = [
    {
      headerName: 'Batch #',
      field: 'batch_number',
      sortable: true,
      width: 140,
      cellRenderer: ({ value }) => (
        <span style={{ fontFamily: 'monospace' }}>{value}</span>
      ),
    },
    {
      headerName: 'Product',
      field: 'product',
      flex: 1,
      minWidth: 200,
      cellRenderer: ({ value }) => (
        value ? (
          <div>
            <div>{value.name}</div>
            <small style={{ color: token.colorTextSecondary }}>{value.sku}</small>
          </div>
        ) : '-'
      ),
    },
    {
      headerName: 'Expiry Date',
      field: 'expiry_date',
      width: 140,
      sortable: true,
      cellRenderer: ({ value, data }) => (
        <Space>
          <ClockCircleOutlined style={{ color: token[`color${data.expiry_status_color?.charAt(0).toUpperCase() + data.expiry_status_color?.slice(1)}`] || token.colorText }} />
          <span>{value}</span>
          {data.is_expired && <Tag color="red">Expired</Tag>}
          {!data.is_expired && data.days_until_expiry <= 30 && (
            <Tag color="orange">{data.days_until_expiry}d</Tag>
          )}
        </Space>
      ),
    },
    {
      headerName: 'Qty Available',
      field: 'quantity_available',
      width: 130,
      cellRenderer: ({ value, data }) => (
        <div>
          <span>{value}</span>
          <Progress
            percent={Math.round((value / data.quantity_purchased) * 100)}
            size="small"
            showInfo={false}
            status={value <= 0 ? 'exception' : undefined}
          />
        </div>
      ),
    },
    {
      headerName: 'Qty Sold',
      field: 'quantity_sold',
      width: 100,
    },
    {
      headerName: 'Status',
      field: 'status',
      width: 120,
      cellRenderer: ({ value, data }) => {
        const statusObj = STATUS_OPTIONS.find(s => s.value === value)
        return (
          <Tag color={statusObj?.color || data.status_color}>
            {statusObj?.label || value}
          </Tag>
        )
      },
    },
    {
      headerName: 'Shelf Life',
      field: 'shelf_life_percentage',
      width: 120,
      cellRenderer: ({ value }) => (
        value !== null ? (
          <Progress
            percent={Math.round(value)}
            size="small"
            status={value < 20 ? 'exception' : value < 50 ? 'active' : 'normal'}
          />
        ) : '-'
      ),
    },
    {
      headerName: 'Actions',
      field: 'actions',
      width: 80,
      pinned: 'right',
      sortable: false,
      filter: false,
      cellRenderer: ({ data }) => (
        <Dropdown
          menu={{
            items: [
              { key: 'edit', label: 'Edit', icon: <EditOutlined />, onClick: () => handleEdit(data) },
              { type: 'divider' },
              {
                key: 'expire',
                label: 'Mark as Expired',
                icon: <WarningOutlined />,
                disabled: data.status === 'expired',
                onClick: () => handleMarkExpired(data),
              },
              {
                key: 'recall',
                label: 'Recall',
                icon: <ExclamationCircleOutlined />,
                disabled: data.status === 'recalled',
                onClick: () => handleRecall(data),
              },
              {
                key: 'quarantine',
                label: 'Quarantine',
                icon: <StopOutlined />,
                disabled: data.status === 'quarantine',
                onClick: () => handleQuarantine(data),
              },
              { type: 'divider' },
              {
                key: 'delete',
                disabled: data.quantity_sold > 0,
                label: (
                  <Popconfirm
                    title="Delete batch?"
                    onConfirm={() => handleDelete(data)}
                    okText="Delete"
                    okButtonProps={{ danger: true }}
                    disabled={data.quantity_sold > 0}
                  >
                    <span style={{ color: data.quantity_sold > 0 ? token.colorTextDisabled : token.colorError }}>
                      Delete
                    </span>
                  </Popconfirm>
                ),
                icon: <DeleteOutlined style={{ color: data.quantity_sold > 0 ? token.colorTextDisabled : token.colorError }} />,
              },
            ],
          }}
          trigger={['hover']}
          placement="bottomRight"
        >
          <Button type="text" size="small" icon={<EllipsisOutlined style={{ fontSize: 18 }} />} />
        </Dropdown>
      ),
    },
  ]

  return (
    <>
      <Head title="Batch & Expiry Tracking" />

      <GlobalPageHeader
        title="Batch & Expiry Tracking"
        parentPageTitle="Inventory"
        parentPageRoute="inventory.page"
        actionButtons={[
          {
            title: 'Add Batch',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      {stats && (
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Total Batches" value={stats.total_batches} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Active" value={stats.active} valueStyle={{ color: token.colorSuccess }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Low Stock" value={stats.low_stock} valueStyle={{ color: token.colorWarning }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Expiring (30d)" value={stats.expiring_30_days} valueStyle={{ color: token.colorWarning }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Expired" value={stats.expired} valueStyle={{ color: token.colorError }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Recalled" value={stats.recalled} valueStyle={{ color: token.colorError }} />
            </Card>
          </Col>
        </Row>
      )}

      <Space style={{ marginBottom: 16 }}>
        <Select
          placeholder="Filter by status"
          allowClear
          style={{ width: 150 }}
          value={statusFilter}
          onChange={(value) => {
            setStatusFilter(value)
            gridRef.current?.reloadData({ status: value, expiry_status: expiryFilter })
          }}
        >
          {STATUS_OPTIONS.map(opt => (
            <Option key={opt.value} value={opt.value}>
              <Tag color={opt.color}>{opt.label}</Tag>
            </Option>
          ))}
        </Select>
        <Select
          placeholder="Filter by expiry"
          allowClear
          style={{ width: 150 }}
          value={expiryFilter}
          onChange={(value) => {
            setExpiryFilter(value)
            gridRef.current?.reloadData({ status: statusFilter, expiry_status: value })
          }}
        >
          {EXPIRY_OPTIONS.map(opt => (
            <Option key={opt.value} value={opt.value}>{opt.label}</Option>
          ))}
        </Select>
      </Space>

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.batches.index"
        pageSize={20}
        extraParams={{ status: statusFilter, expiry_status: expiryFilter }}
      />

      {visible && (
        <BatchModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
