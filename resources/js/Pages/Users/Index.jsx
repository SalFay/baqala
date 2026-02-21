import { useRef, useState } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Dropdown, message, Spin, Tag, theme, Typography } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  KeyOutlined,
  PlusOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import UserModal from './Components/UserModal'
import UpdatePasswordModal from './Components/UpdatePasswordModal'
import { deleteUser, getUserData } from '@/Helpers/api/userService'

const { Text } = Typography

export default function Users() {
  const { roles } = usePage().props
  const { token } = theme.useToken()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
  const [passwordModalVisible, setPasswordModalVisible] = useState(false)
  const [recordToUpdate, setRecordToUpdate] = useState(null)
  const [recordLoading, setRecordLoading] = useState(false)

  const handleRefresh = () => {
    if (gridRef.current?.reloadData) {
      gridRef.current.reloadData()
    }
  }

  const handleCreate = () => {
    setRecordToUpdate(null)
    setVisible(true)
  }

  const handleEdit = async (record) => {
    try {
      setRecordLoading(true)
      const response = await getUserData(record.id)
      setRecordToUpdate(response.data.data)
      setVisible(true)
    } catch (error) {
      message.error('Failed to load user data')
    } finally {
      setRecordLoading(false)
    }
  }

  const handleDelete = async (record) => {
    try {
      await deleteUser(record.id)
      message.success('User deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete user')
    }
  }

  const handlePasswordUpdate = (record) => {
    setRecordToUpdate(record)
    setPasswordModalVisible(true)
  }

  const handleModalClose = () => {
    setVisible(false)
    setRecordToUpdate(null)
  }

  const columns = [
    {
      headerName: 'Name',
      field: 'full_name',
      sortable: true,
      flex: 1,
      minWidth: 150,
    },
    {
      headerName: 'Email',
      field: 'email',
      sortable: true,
      flex: 1,
      minWidth: 200,
      cellRenderer: (params) => (
        <Text copyable={{ text: params.value }}>{params.value}</Text>
      ),
    },
    {
      headerName: 'Role',
      field: 'role',
      width: 150,
      cellRenderer: (params) => {
        const role = params.value
        if (!role) return <Tag>No Role</Tag>
        return (
          <Tag color={role.color || 'default'}>
            {role.name}
          </Tag>
        )
      },
    },
    {
      headerName: 'Status',
      field: 'status',
      width: 100,
      cellRenderer: (params) => (
        <Tag color={params.value === 'active' ? 'green' : 'red'}>
          {params.value?.toUpperCase() || 'UNKNOWN'}
        </Tag>
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
            key: 'edit',
            label: 'Edit',
            icon: <EditOutlined />,
            onClick: () => handleEdit(record),
          },
          {
            key: 'password',
            label: 'Update Password',
            icon: <KeyOutlined />,
            onClick: () => handlePasswordUpdate(record),
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
      <Head title="Users" />

      <Spin spinning={recordLoading}>
        <GlobalPageHeader
          title="Manage Users"
          parentPageTitle="Dashboard"
          parentPageRoute="dashboard"
          actionButtons={[
            {
              title: 'Add User',
              icon: <PlusOutlined />,
              onClick: handleCreate,
              type: 'primary',
            },
          ]}
        />

        <DataGridTable
          gridRef={gridRef}
          columns={columns}
          routeName="user.listing"
          pageSize={20}
        />

        {visible && (
          <UserModal
            visible={visible}
            onCancel={handleModalClose}
            record={recordToUpdate}
            onUpdate={handleRefresh}
            roles={roles}
          />
        )}

        {passwordModalVisible && (
          <UpdatePasswordModal
            visible={passwordModalVisible}
            onCancel={() => {
              setPasswordModalVisible(false)
              setRecordToUpdate(null)
            }}
            record={recordToUpdate}
          />
        )}
      </Spin>
    </>
  )
}
