import { useRef, useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Button, Dropdown, message, Spin, Tag, theme } from 'antd'
import {
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  EyeOutlined,
  PlusOutlined,
  SecurityScanOutlined,
  TeamOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import RoleModal from './Components/RoleModal'
import { cloneRole, deleteRole, getRoleData } from '@/Helpers/api/roleService'
import usePermissions from '@/Hooks/usePermissions'

export default function Roles() {
  const { roles, permissions: allPermissions } = usePage().props
  const { token } = theme.useToken()
  const { hasPermission } = usePermissions()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [recordToUpdate, setRecordToUpdate] = useState(null)
  const [permissionType, setPermissionType] = useState('edit')
  const [recordLoading, setRecordLoading] = useState(false)

  const handleRefresh = () => {
    if (gridRef.current?.reloadData) {
      gridRef.current.reloadData()
    }
  }

  const handleCreate = () => {
    setRecordToUpdate(null)
    setPermissionType('edit')
    setVisible(true)
  }

  const handleUpdate = async (record, type = 'edit') => {
    setPermissionType(type)
    try {
      setRecordLoading(true)
      const response = await getRoleData(record.id)
      setRecordToUpdate(response.data.data)
      setVisible(true)
    } catch (error) {
      message.error('Failed to load role data')
    } finally {
      setRecordLoading(false)
    }
  }

  const handleDelete = async (record) => {
    try {
      await deleteRole(record.id)
      message.success('Role deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete role')
    }
  }

  const handleClone = async (record) => {
    try {
      await cloneRole(record.id)
      message.success('Role cloned successfully')
      handleRefresh()
    } catch (error) {
      message.error('Failed to clone role')
    }
  }

  const handleModalClose = () => {
    setVisible(false)
    setRecordToUpdate(null)
  }

  const columns = [
    {
      headerName: 'Name',
      field: 'name',
      sortable: true,
      flex: 1,
      minWidth: 150,
    },
    {
      headerName: 'Color',
      field: 'color',
      width: 120,
      cellRenderer: (params) => {
        const color = params.value || '#cdceca'
        return (
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <div
              style={{
                width: 20,
                height: 20,
                borderRadius: 4,
                backgroundColor: color,
                border: '1px solid rgba(0,0,0,0.1)',
              }}
            />
            <span style={{ fontSize: 12, color: '#666' }}>{color}</span>
          </div>
        )
      },
    },
    {
      headerName: 'Users',
      field: 'users_count',
      width: 100,
      cellRenderer: (params) => (
        <Tag icon={<TeamOutlined />}>{params.value || 0}</Tag>
      ),
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
            onClick: () => handleUpdate(record, 'view'),
          },
          {
            key: 'edit',
            label: 'Edit',
            icon: <EditOutlined />,
            onClick: () => handleUpdate(record, 'edit'),
          },
          {
            key: 'clone',
            label: 'Clone',
            icon: <CopyOutlined />,
            onClick: () => handleClone(record),
          },
          {
            key: 'delete',
            label: <span style={{ color: token.colorError }}>Delete</span>,
            icon: <DeleteOutlined style={{ color: token.colorError }} />,
            onClick: () => handleDelete(record),
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
      <Head title="Roles" />

      <Spin spinning={recordLoading}>
        <GlobalPageHeader
          title="Manage Roles"
          parentPageTitle="Dashboard"
          parentPageRoute="dashboard"
          actionButtons={[
            {
              title: 'Permissions',
              icon: <SecurityScanOutlined />,
              onClick: () => router.visit(route('role.permissions')),
            },
            {
              title: 'Add Role',
              icon: <PlusOutlined />,
              onClick: handleCreate,
              type: 'primary',
            },
          ]}
        />

        <DataGridTable
          gridRef={gridRef}
          columns={columns}
          routeName="role.listing"
          pageSize={20}
        />

        {visible && (
          <RoleModal
            visible={visible}
            onCancel={handleModalClose}
            record={recordToUpdate}
            onUpdate={handleRefresh}
            permissions={allPermissions}
            permissionType={permissionType}
            roles={roles}
          />
        )}
      </Spin>
    </>
  )
}
