import { useRef, useState, useEffect } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Card, Row, Col, Statistic, Space, Select } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  MobileOutlined,
  SafetyCertificateOutlined,
  WarningOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import SerialModal from './SerialModal'
import { deleteSerial, fetchSerialStatistics } from '@/Helpers/api/productSerialService'

const { Option } = Select

const STATUS_OPTIONS = [
  { value: 'available', label: 'Available', color: 'green' },
  { value: 'reserved', label: 'Reserved', color: 'blue' },
  { value: 'sold', label: 'Sold', color: 'default' },
  { value: 'returned', label: 'Returned', color: 'orange' },
  { value: 'damaged', label: 'Damaged', color: 'red' },
  { value: 'lost', label: 'Lost', color: 'red' },
]

export default function Serials() {
  const { token } = theme.useToken()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [record, setRecord] = useState(null)
  const [stats, setStats] = useState(null)
  const [statusFilter, setStatusFilter] = useState(null)

  useEffect(() => {
    loadStats()
  }, [])

  const loadStats = async () => {
    try {
      const response = await fetchSerialStatistics()
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
      await deleteSerial(rec.id)
      message.success('Serial deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete')
    }
  }

  const columns = [
    {
      headerName: 'Serial Number',
      field: 'serial_number',
      sortable: true,
      flex: 1,
      minWidth: 180,
      cellRenderer: ({ value, data }) => (
        <Space>
          <MobileOutlined />
          <span style={{ fontFamily: 'monospace' }}>{value}</span>
        </Space>
      ),
    },
    {
      headerName: 'IMEI',
      field: 'imei',
      width: 180,
      cellRenderer: ({ value }) => (
        value ? <span style={{ fontFamily: 'monospace' }}>{value}</span> : '-'
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
      headerName: 'Purchase Price',
      field: 'purchase_price',
      width: 120,
      cellRenderer: ({ value }) => value ? `$${value}` : '-',
    },
    {
      headerName: 'Sale Price',
      field: 'sale_price',
      width: 120,
      cellRenderer: ({ value }) => value ? `$${value}` : '-',
    },
    {
      headerName: 'Warranty',
      field: 'warranty_end_date',
      width: 140,
      cellRenderer: ({ value, data }) => {
        if (!value) return '-'
        return (
          <Space>
            <SafetyCertificateOutlined style={{ color: data.warranty_active ? token.colorSuccess : token.colorError }} />
            {value}
            {data.warranty_remaining_days !== null && data.warranty_remaining_days <= 30 && (
              <WarningOutlined style={{ color: token.colorWarning }} />
            )}
          </Space>
        )
      },
    },
    {
      headerName: 'Sold At',
      field: 'sold_at',
      width: 150,
      cellRenderer: ({ value }) => value || '-',
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
                key: 'delete',
                disabled: data.status === 'sold',
                label: (
                  <Popconfirm
                    title="Delete serial?"
                    onConfirm={() => handleDelete(data)}
                    okText="Delete"
                    okButtonProps={{ danger: true }}
                    disabled={data.status === 'sold'}
                  >
                    <span style={{ color: data.status === 'sold' ? token.colorTextDisabled : token.colorError }}>
                      Delete
                    </span>
                  </Popconfirm>
                ),
                icon: <DeleteOutlined style={{ color: data.status === 'sold' ? token.colorTextDisabled : token.colorError }} />,
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
      <Head title="Serial/IMEI Tracking" />

      <GlobalPageHeader
        title="Serial/IMEI Tracking"
        parentPageTitle="Inventory"
        parentPageRoute="inventory.page"
        actionButtons={[
          {
            title: 'Add Serial',
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
              <Statistic title="Total" value={stats.total} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Available" value={stats.available} valueStyle={{ color: token.colorSuccess }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Sold" value={stats.sold} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Reserved" value={stats.reserved} valueStyle={{ color: token.colorInfo }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Damaged/Lost" value={(stats.damaged || 0) + (stats.lost || 0)} valueStyle={{ color: token.colorError }} />
            </Card>
          </Col>
          <Col xs={12} sm={8} md={4}>
            <Card size="small">
              <Statistic title="Warranty Expiring" value={stats.warranty_expiring_soon} valueStyle={{ color: token.colorWarning }} />
            </Card>
          </Col>
        </Row>
      )}

      <div style={{ marginBottom: 16 }}>
        <Select
          placeholder="Filter by status"
          allowClear
          style={{ width: 200 }}
          value={statusFilter}
          onChange={(value) => {
            setStatusFilter(value)
            gridRef.current?.reloadData({ status: value })
          }}
        >
          {STATUS_OPTIONS.map(opt => (
            <Option key={opt.value} value={opt.value}>
              <Tag color={opt.color}>{opt.label}</Tag>
            </Option>
          ))}
        </Select>
      </div>

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.serials.index"
        pageSize={20}
        extraParams={{ status: statusFilter }}
      />

      {visible && (
        <SerialModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
