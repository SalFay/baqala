import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space, Switch } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  ClockCircleOutlined,
  ScheduleOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import StatusBadge from '@/Components/StatusBadge'
import TimePricingModal from './TimePricingModal'
import { deleteTimePricing, toggleTimePricing } from '@/Helpers/api/timePricingService'

const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

export default function TimePricingIndex() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [editing, setEditing] = useState(null)

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteTimePricing(id),
    onSuccess: () => {
      message.success('Time pricing deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete')
    },
  })

  // Toggle mutation
  const toggleMutation = useMutation({
    mutationFn: (id) => toggleTimePricing(id),
    onSuccess: (res) => {
      const isActive = res.data?.data?.is_active
      message.success(isActive ? 'Time pricing activated' : 'Time pricing deactivated')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to toggle status')
    },
  })

  const handleAdd = () => {
    setEditing(null)
    setModalOpen(true)
  }

  const handleEdit = (record) => {
    setEditing(record)
    setModalOpen(true)
  }

  const handleDelete = (record) => {
    Modal.confirm({
      title: 'Delete Time Pricing',
      content: `Are you sure you want to delete "${record.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(record.id),
    })
  }

  const handleSuccess = () => {
    setModalOpen(false)
    setEditing(null)
    gridRef.current?.reloadData()
  }

  const columns = [
    {
      field: 'name',
      headerName: 'Name',
      flex: 1.5,
      minWidth: 180,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <ClockCircleOutlined />
          <span>{data.name}</span>
          {data.is_active_now && <Tag color="green">Active Now</Tag>}
        </Space>
      ),
    },
    {
      field: 'discount_description',
      headerName: 'Discount',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ data }) => {
        if (data.discount_type === 'percentage') {
          return <Tag color="blue">{data.discount_value}% off</Tag>
        }
        if (data.discount_type === 'fixed') {
          return <Tag color="green">{data.discount_value} off</Tag>
        }
        return <Tag color="purple">Special: {data.discount_value}</Tag>
      },
    },
    {
      field: 'applies_to',
      headerName: 'Applies To',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ value, data }) => {
        const labels = {
          all: 'All Products',
          products: 'Specific Products',
          categories: 'Categories',
          brands: 'Brands',
        }
        const count = data.product_ids?.length || data.category_ids?.length || data.brand_ids?.length || 0
        return (
          <span>
            {labels[value] || value}
            {count > 0 && ` (${count})`}
          </span>
        )
      },
    },
    {
      field: 'schedule_description',
      headerName: 'Schedule',
      flex: 1.5,
      minWidth: 200,
      cellRenderer: ({ data }) => (
        <Space direction="vertical" size={0}>
          {data.days_of_week?.length > 0 && (
            <Space size={2}>
              <ScheduleOutlined />
              {data.days_of_week.map(d => (
                <Tag key={d} size="small">{DAYS[d - 1]}</Tag>
              ))}
            </Space>
          )}
          {data.start_time && data.end_time && (
            <span style={{ color: '#888', fontSize: 12 }}>
              {data.start_time?.substring(0, 5)} - {data.end_time?.substring(0, 5)}
            </span>
          )}
        </Space>
      ),
    },
    {
      field: 'priority',
      headerName: 'Priority',
      flex: 0.5,
      minWidth: 80,
    },
    {
      field: 'is_active',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value, data }) => (
        <Switch
          size="small"
          checked={value}
          loading={toggleMutation.isLoading}
          onChange={() => toggleMutation.mutate(data.id)}
        />
      ),
    },
  ]

  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 100,
    sortable: false,
    cellRenderer: ({ data }) => (
      <Dropdown
        trigger={['click']}
        menu={{
          items: [
            {
              key: 'edit',
              label: 'Edit',
              icon: <EditOutlined />,
              onClick: () => handleEdit(data),
            },
            { type: 'divider' },
            {
              key: 'delete',
              label: 'Delete',
              icon: <DeleteOutlined />,
              danger: true,
              onClick: () => handleDelete(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  return (
    <>
      <Head title="Time-Based Pricing" />

      <GlobalPageHeader
        title="Time-Based Pricing"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Time Pricing',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.time-pricing.listing"
        columns={[...columns, actionsColumn]}
        instanceId="time-pricing"
        pageSize={20}
        height="calc(100vh - 280px)"
      />

      <TimePricingModal
        open={modalOpen}
        onClose={() => {
          setModalOpen(false)
          setEditing(null)
        }}
        onSuccess={handleSuccess}
        timePricing={editing}
      />
    </>
  )
}
