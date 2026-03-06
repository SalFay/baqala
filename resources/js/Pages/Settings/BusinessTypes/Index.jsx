import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Switch, Popconfirm } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  EyeOutlined,
  PlusOutlined,
  ShopOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import BusinessTypeModal from './BusinessTypeModal'
import { deleteBusinessType } from '@/Helpers/api/businessTypeService'
import usePermissions from '@/Hooks/usePermissions'

export default function BusinessTypes() {
  const { token } = theme.useToken()
  const { hasPermission } = usePermissions()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [recordToUpdate, setRecordToUpdate] = useState(null)
  const [viewMode, setViewMode] = useState(false)

  const handleRefresh = () => {
    if (gridRef.current?.reloadData) {
      gridRef.current.reloadData()
    }
  }

  const handleCreate = () => {
    setRecordToUpdate(null)
    setViewMode(false)
    setVisible(true)
  }

  const handleEdit = (record) => {
    setRecordToUpdate(record)
    setViewMode(false)
    setVisible(true)
  }

  const handleView = (record) => {
    setRecordToUpdate(record)
    setViewMode(true)
    setVisible(true)
  }

  const handleDelete = async (record) => {
    try {
      await deleteBusinessType(record.id)
      message.success('Business type deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete business type')
    }
  }

  const handleModalClose = () => {
    setVisible(false)
    setRecordToUpdate(null)
  }

  const columns = [
    {
      headerName: 'Icon',
      field: 'icon',
      width: 70,
      cellRenderer: (params) => (
        <span style={{ fontSize: 20 }}>{params.value || <ShopOutlined />}</span>
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
      headerName: 'Name (Arabic)',
      field: 'name_ar',
      sortable: true,
      flex: 1,
      minWidth: 150,
    },
    {
      headerName: 'Slug',
      field: 'slug',
      sortable: true,
      width: 150,
      cellRenderer: (params) => (
        <Tag color="blue">{params.value}</Tag>
      ),
    },
    {
      headerName: 'Stores',
      field: 'stores_count',
      width: 100,
      cellRenderer: (params) => (
        <Tag icon={<ShopOutlined />}>{params.value || 0}</Tag>
      ),
    },
    {
      headerName: 'Has Seeder',
      field: 'has_seeder',
      width: 110,
      cellRenderer: (params) => (
        <Tag color={params.value ? 'green' : 'default'}>
          {params.value ? 'Yes' : 'No'}
        </Tag>
      ),
    },
    {
      headerName: 'Status',
      field: 'is_active',
      width: 100,
      cellRenderer: (params) => (
        <Tag color={params.value ? 'success' : 'error'}>
          {params.value ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      headerName: 'Order',
      field: 'sort_order',
      width: 80,
      sortable: true,
    },
    {
      headerName: 'Actions',
      field: 'actions',
      width: 80,
      pinned: 'right',
      sortable: false,
      filter: false,
      cellRenderer: (params) => {
        const record = params.data

        const menuItems = [
          {
            key: 'view',
            label: 'View',
            icon: <EyeOutlined />,
            onClick: () => handleView(record),
          },
          {
            key: 'edit',
            label: 'Edit',
            icon: <EditOutlined />,
            onClick: () => handleEdit(record),
          },
          {
            type: 'divider',
          },
          {
            key: 'delete',
            label: (
              <Popconfirm
                title="Delete business type?"
                description="This action cannot be undone."
                onConfirm={() => handleDelete(record)}
                okText="Delete"
                cancelText="Cancel"
                okButtonProps={{ danger: true }}
              >
                <span style={{ color: token.colorError }}>Delete</span>
              </Popconfirm>
            ),
            icon: <DeleteOutlined style={{ color: token.colorError }} />,
          },
        ]

        return (
          <Dropdown
            menu={{ items: menuItems }}
            trigger={['hover']}
            placement="bottomRight"
          >
            <Button type="text" size="small" icon={<EllipsisOutlined style={{ fontSize: 18 }} />} />
          </Dropdown>
        )
      },
    },
  ]

  return (
    <>
      <Head title="Business Types" />

      <GlobalPageHeader
        title="Business Types"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Business Type',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.business-types.index"
        pageSize={20}
      />

      {visible && (
        <BusinessTypeModal
          visible={visible}
          onCancel={handleModalClose}
          record={recordToUpdate}
          onUpdate={handleRefresh}
          viewMode={viewMode}
        />
      )}
    </>
  )
}
