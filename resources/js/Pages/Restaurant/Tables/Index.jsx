import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space, Tabs } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  AppstoreOutlined,
  TableOutlined,
  CheckCircleOutlined,
  ToolOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import StatusBadge from '@/Components/StatusBadge'
import TableModal from './TableModal'
import FloorPlan from './FloorPlan'
import { deleteTable, updateTableStatus } from '@/Helpers/api/restaurantService'

const statusColors = {
  available: 'green',
  occupied: 'red',
  reserved: 'orange',
  maintenance: 'default',
}

const statusLabels = {
  available: 'Available',
  occupied: 'Occupied',
  reserved: 'Reserved',
  maintenance: 'Maintenance',
}

export default function Tables() {
  const gridRef = useRef()
  const [activeTab, setActiveTab] = useState('list')

  // Modal state
  const [modalOpen, setModalOpen] = useState(false)
  const [editingTable, setEditingTable] = useState(null)

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteTable(id),
    onSuccess: () => {
      message.success('Table deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete table')
    },
  })

  // Status mutation
  const statusMutation = useMutation({
    mutationFn: ({ id, status }) => updateTableStatus(id, status),
    onSuccess: () => {
      message.success('Status updated')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update status')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingTable(null)
    setModalOpen(true)
  }

  const handleEdit = (table) => {
    setEditingTable(table)
    setModalOpen(true)
  }

  const handleDelete = (table) => {
    if (table.status === 'occupied') {
      message.warning('Cannot delete an occupied table')
      return
    }
    Modal.confirm({
      title: 'Delete Table',
      content: `Are you sure you want to delete "${table.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(table.id),
    })
  }

  const handleStatusChange = (table, status) => {
    statusMutation.mutate({ id: table.id, status })
  }

  const handleSuccess = () => {
    setModalOpen(false)
    setEditingTable(null)
    gridRef.current?.reloadData()
  }

  // Column definitions
  const columns = [
    {
      field: 'name',
      headerName: 'Table',
      flex: 1,
      minWidth: 120,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <TableOutlined />
          <span>{data.name}</span>
        </Space>
      ),
    },
    {
      field: 'capacity',
      headerName: 'Capacity',
      flex: 0.7,
      minWidth: 80,
      cellRenderer: ({ value }) => `${value} seats`,
    },
    {
      field: 'section',
      headerName: 'Section',
      flex: 1,
      minWidth: 100,
    },
    {
      field: 'floor',
      headerName: 'Floor',
      flex: 0.8,
      minWidth: 80,
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <Tag color={statusColors[value] || 'default'}>
          {statusLabels[value] || value}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: Object.entries(statusLabels).map(([value, label]) => ({
        value,
        label,
      })),
    },
    {
      field: 'current_order',
      headerName: 'Current Order',
      flex: 1,
      minWidth: 120,
      cellRenderer: ({ data }) => (
        data.current_order
          ? <Tag color="blue">#{data.current_order.order_number}</Tag>
          : '-'
      ),
    },
    {
      field: 'is_active',
      headerName: 'Active',
      flex: 0.6,
      minWidth: 70,
      cellRenderer: ({ value }) => (
        <StatusBadge status={value ? 'active' : 'inactive'} />
      ),
    },
  ]

  // Actions column
  const actionsColumn = {
    field: 'actions',
    headerName: 'Actions',
    width: 120,
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
              key: 'available',
              label: 'Mark Available',
              icon: <CheckCircleOutlined />,
              disabled: data.status === 'available' || data.status === 'occupied',
              onClick: () => handleStatusChange(data, 'available'),
            },
            {
              key: 'maintenance',
              label: 'Set Maintenance',
              icon: <ToolOutlined />,
              disabled: data.status === 'maintenance' || data.status === 'occupied',
              onClick: () => handleStatusChange(data, 'maintenance'),
            },
            { type: 'divider' },
            {
              key: 'delete',
              label: 'Delete',
              icon: <DeleteOutlined />,
              danger: true,
              disabled: data.status === 'occupied',
              onClick: () => handleDelete(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  const tabItems = [
    {
      key: 'list',
      label: (
        <span>
          <TableOutlined /> List View
        </span>
      ),
      children: (
        <DataGridTable
          gridRef={gridRef}
          routeName="pos.restaurant-tables.listing"
          columns={[...columns, actionsColumn]}
          instanceId="restaurant-tables"
          pageSize={20}
          height="calc(100vh - 320px)"
        />
      ),
    },
    {
      key: 'floor',
      label: (
        <span>
          <AppstoreOutlined /> Floor Plan
        </span>
      ),
      children: <FloorPlan onEdit={handleEdit} />,
    },
  ]

  return (
    <>
      <Head title="Restaurant Tables" />

      <GlobalPageHeader
        title="Restaurant Tables"
        parentPageTitle="Restaurant"
        actionButtons={[
          {
            title: 'Add Table',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <Tabs
        activeKey={activeTab}
        onChange={setActiveTab}
        items={tabItems}
      />

      <TableModal
        open={modalOpen}
        onClose={() => {
          setModalOpen(false)
          setEditingTable(null)
        }}
        onSuccess={handleSuccess}
        table={editingTable}
      />
    </>
  )
}
