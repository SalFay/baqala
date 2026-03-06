import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Space, Typography } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  CopyOutlined,
  BarChartOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import CouponModal from './CouponModal'
import CouponStatsModal from './CouponStatsModal'
import { deleteCoupon } from '@/Helpers/api/discountService'

const { Text } = Typography

export default function Coupons() {
  const { token } = theme.useToken()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [statsVisible, setStatsVisible] = useState(false)
  const [record, setRecord] = useState(null)

  const handleRefresh = () => gridRef.current?.reloadData()

  const handleCreate = () => {
    setRecord(null)
    setVisible(true)
  }

  const handleEdit = (rec) => {
    setRecord(rec)
    setVisible(true)
  }

  const handleStats = (rec) => {
    setRecord(rec)
    setStatsVisible(true)
  }

  const handleCopyCode = (code) => {
    navigator.clipboard.writeText(code)
    message.success('Coupon code copied!')
  }

  const handleDelete = async (rec) => {
    try {
      await deleteCoupon(rec.id)
      message.success('Coupon deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete')
    }
  }

  const columns = [
    {
      headerName: 'Code',
      field: 'code',
      sortable: true,
      width: 150,
      cellRenderer: ({ value }) => (
        <Space>
          <Text strong copyable={{ onCopy: () => handleCopyCode(value) }}>
            {value}
          </Text>
        </Space>
      ),
    },
    {
      headerName: 'Name',
      field: 'name',
      sortable: true,
      flex: 1,
      minWidth: 150,
    },
    {
      headerName: 'Discount',
      field: 'discount_display',
      width: 120,
      cellRenderer: ({ data }) => {
        if (data.discount_type === 'free_shipping') {
          return <Tag color="cyan">Free Shipping</Tag>
        }
        return (
          <Tag color="blue">
            {data.discount_type === 'percentage'
              ? `${data.discount_amount}%`
              : `${data.discount_amount}`}
          </Tag>
        )
      },
    },
    {
      headerName: 'Applies To',
      field: 'applies_to',
      width: 120,
      cellRenderer: ({ value }) => {
        const colors = {
          all: 'green',
          category: 'blue',
          brand: 'purple',
          product: 'orange',
        }
        return <Tag color={colors[value] || 'default'}>{value}</Tag>
      },
    },
    {
      headerName: 'Min Order',
      field: 'min_order_amount',
      width: 110,
      cellRenderer: ({ value }) => value ? `${value}` : '-',
    },
    {
      headerName: 'Uses',
      field: 'current_uses',
      width: 100,
      cellRenderer: ({ data }) => (
        <span>
          {data.current_uses || 0}
          {data.max_uses ? ` / ${data.max_uses}` : ''}
        </span>
      ),
    },
    {
      headerName: 'Validity',
      field: 'is_valid',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'success' : 'error'}>{value ? 'Valid' : 'Invalid'}</Tag>
      ),
    },
    {
      headerName: 'Status',
      field: 'is_active',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'success' : 'error'}>{value ? 'Active' : 'Inactive'}</Tag>
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
              { key: 'stats', label: 'Statistics', icon: <BarChartOutlined />, onClick: () => handleStats(data) },
              { type: 'divider' },
              {
                key: 'delete',
                label: (
                  <Popconfirm
                    title="Delete coupon?"
                    description={data.current_uses > 0 ? 'This coupon has been used. It will be archived.' : 'This action cannot be undone.'}
                    onConfirm={() => handleDelete(data)}
                    okText="Delete"
                    okButtonProps={{ danger: true }}
                  >
                    <span style={{ color: token.colorError }}>Delete</span>
                  </Popconfirm>
                ),
                icon: <DeleteOutlined style={{ color: token.colorError }} />,
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
      <Head title="Coupons" />

      <GlobalPageHeader
        title="Coupons"
        parentPageTitle="Marketing"
        actionButtons={[
          {
            title: 'Add Coupon',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.coupons.index"
        pageSize={20}
      />

      {visible && (
        <CouponModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}

      {statsVisible && (
        <CouponStatsModal
          visible={statsVisible}
          onCancel={() => setStatsVisible(false)}
          record={record}
        />
      )}
    </>
  )
}
