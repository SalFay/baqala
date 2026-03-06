import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Space } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  SwapOutlined,
  CheckCircleOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import UnitModal from './UnitModal'
import { deleteUnit } from '@/Helpers/api/unitService'

export default function Units() {
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
      await deleteUnit(rec.id)
      message.success('Unit deleted successfully')
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
      headerName: 'Short Name',
      field: 'short_name',
      width: 120,
      cellRenderer: ({ value }) => (
        <Tag color="blue">{value}</Tag>
      ),
    },
    {
      headerName: 'Type',
      field: 'is_base_unit',
      width: 120,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'green' : 'orange'}>
          {value ? 'Base Unit' : 'Derived'}
        </Tag>
      ),
    },
    {
      headerName: 'Base Unit',
      field: 'base_unit',
      width: 140,
      cellRenderer: ({ value }) => (
        value ? (
          <Space>
            <SwapOutlined />
            {value.name} ({value.short_name})
          </Space>
        ) : '-'
      ),
    },
    {
      headerName: 'Conversion Rate',
      field: 'conversion_rate',
      width: 140,
      cellRenderer: ({ value, data }) => (
        data.is_base_unit ? '-' : (
          <Tag>{value}x</Tag>
        )
      ),
    },
    {
      headerName: 'Decimal',
      field: 'allow_decimal',
      width: 100,
      cellRenderer: ({ value }) => (
        value ? <CheckCircleOutlined style={{ color: token.colorSuccess }} /> : '-'
      ),
    },
    {
      headerName: 'Products',
      field: 'products_count',
      width: 100,
      cellRenderer: ({ value }) => <Tag>{value}</Tag>,
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
                    title="Delete unit?"
                    description={data.products_count > 0 ? 'This unit is used by products.' : undefined}
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
      <Head title="Units of Measure" />

      <GlobalPageHeader
        title="Units of Measure"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Unit',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.units.index"
        pageSize={20}
      />

      {visible && (
        <UnitModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
