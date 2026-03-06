import { useRef, useState } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  TeamOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import CustomerGroupModal from './CustomerGroupModal'
import { deleteCustomerGroup } from '@/Helpers/api/customerGroupService'

export default function CustomerGroups() {
  const { priceGroups = [] } = usePage().props
  const { token } = theme.useToken()
  const gridRef = useRef(null)

  const [visible, setVisible] = useState(false)
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

  const handleDelete = async (rec) => {
    try {
      await deleteCustomerGroup(rec.id)
      message.success('Customer group deleted successfully')
      handleRefresh()
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to delete')
    }
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
      headerName: 'Price Group',
      field: 'selling_price_group',
      width: 150,
      cellRenderer: ({ value }) => (
        value ? <Tag color="blue">{value}</Tag> : <Tag>Default</Tag>
      ),
    },
    {
      headerName: 'Discount %',
      field: 'discount_percent',
      width: 120,
      cellRenderer: ({ value }) => (
        value > 0 ? <Tag color="green">{value}%</Tag> : <span>-</span>
      ),
    },
    {
      headerName: 'Customers',
      field: 'customers_count',
      width: 120,
      cellRenderer: ({ value }) => (
        <Tag icon={<TeamOutlined />}>{value || 0}</Tag>
      ),
    },
    {
      headerName: 'Default',
      field: 'is_default',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'gold' : 'default'}>
          {value ? 'Default' : 'No'}
        </Tag>
      ),
    },
    {
      headerName: 'Status',
      field: 'is_active',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'success' : 'error'}>
          {value ? 'Active' : 'Inactive'}
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
      cellRenderer: ({ data }) => (
        <Dropdown
          menu={{
            items: [
              { key: 'edit', label: 'Edit', icon: <EditOutlined />, onClick: () => handleEdit(data) },
              { type: 'divider' },
              {
                key: 'delete',
                label: (
                  <Popconfirm
                    title="Delete customer group?"
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
      <Head title="Customer Groups" />

      <GlobalPageHeader
        title="Customer Groups"
        parentPageTitle="Customers"
        parentPageRoute="pos.customers.index"
        actionButtons={[
          {
            title: 'Add Group',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.customer-groups.index"
        pageSize={20}
      />

      {visible && (
        <CustomerGroupModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
          priceGroups={priceGroups}
        />
      )}
    </>
  )
}
