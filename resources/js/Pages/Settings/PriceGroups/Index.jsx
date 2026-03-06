import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  DollarOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import PriceGroupModal from './PriceGroupModal'
import { deletePriceGroup } from '@/Helpers/api/priceGroupService'

export default function PriceGroups() {
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
      await deletePriceGroup(rec.id)
      message.success('Price group deleted successfully')
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
      headerName: 'Type',
      field: 'price_calculation_type',
      width: 120,
      cellRenderer: ({ value }) => (
        <Tag color={value === 'percentage' ? 'blue' : 'green'}>
          {value === 'percentage' ? 'Percentage' : 'Fixed'}
        </Tag>
      ),
    },
    {
      headerName: 'Amount',
      field: 'price_calculation_amount',
      width: 120,
      cellRenderer: ({ data }) => (
        <span>
          {data.price_calculation_type === 'percentage'
            ? `${data.price_calculation_amount}%`
            : `${data.price_calculation_amount}`}
        </span>
      ),
    },
    {
      headerName: 'Customer Groups',
      field: 'customer_groups_count',
      width: 140,
      cellRenderer: ({ value }) => <Tag>{value || 0}</Tag>,
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
                    title="Delete price group?"
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
      <Head title="Price Groups" />

      <GlobalPageHeader
        title="Selling Price Groups"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Price Group',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.price-groups.index"
        pageSize={20}
      />

      {visible && (
        <PriceGroupModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
