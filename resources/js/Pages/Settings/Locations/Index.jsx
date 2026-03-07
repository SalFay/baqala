import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  CheckCircleOutlined,
  EnvironmentOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import LocationModal from './LocationModal'
import StatusBadge from '@/Components/StatusBadge'
import { deleteLocation } from '@/Helpers/api/locationService'

export default function Locations() {
  const gridRef = useRef()

  // Modal state
  const [modalOpen, setModalOpen] = useState(false)
  const [editingLocation, setEditingLocation] = useState(null)

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => deleteLocation(id),
    onSuccess: () => {
      message.success('Location deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete location')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingLocation(null)
    setModalOpen(true)
  }

  const handleEdit = (location) => {
    setEditingLocation(location)
    setModalOpen(true)
  }

  const handleDelete = (location) => {
    if (location.is_main) {
      message.warning('Cannot delete the main location')
      return
    }
    Modal.confirm({
      title: 'Delete Location',
      content: `Are you sure you want to delete "${location.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(location.id),
    })
  }

  const handleSuccess = () => {
    setModalOpen(false)
    setEditingLocation(null)
    gridRef.current?.reloadData()
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingLocation(null)
  }

  // Column definitions
  const columns = [
    {
      field: 'name',
      headerName: 'Name',
      flex: 1.5,
      minWidth: 150,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <EnvironmentOutlined />
          <span>{data.name}</span>
          {data.is_main && <Tag color="gold">Main</Tag>}
        </Space>
      ),
    },
    {
      field: 'code',
      headerName: 'Code',
      flex: 0.8,
      minWidth: 80,
    },
    {
      field: 'address',
      headerName: 'Address',
      flex: 2,
      minWidth: 200,
    },
    {
      field: 'city',
      headerName: 'City',
      flex: 1,
      minWidth: 100,
    },
    {
      field: 'phone',
      headerName: 'Phone',
      flex: 1,
      minWidth: 120,
    },
    {
      field: 'is_active',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        <StatusBadge status={value ? 'active' : 'inactive'} />
      ),
    },
  ]

  // Actions column
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
            {
              type: 'divider',
            },
            {
              key: 'delete',
              label: 'Delete',
              icon: <DeleteOutlined />,
              danger: true,
              disabled: data.is_main,
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
      <Head title="Locations" />

      <GlobalPageHeader
        title="Locations"
        parentPageTitle="Settings"
        parentPageRoute="settings.index"
        actionButtons={[
          {
            title: 'Add Location',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        routeName="pos.locations.listing"
        columns={[...columns, actionsColumn]}
        instanceId="locations"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <LocationModal
        open={modalOpen}
        onClose={handleCloseModal}
        onSuccess={handleSuccess}
        location={editingLocation}
      />
    </>
  )
}
