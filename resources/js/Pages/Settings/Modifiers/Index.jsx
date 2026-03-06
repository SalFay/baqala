import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Space } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  AppstoreOutlined,
  CheckCircleOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import ModifierSetModal from './ModifierSetModal'
import { deleteModifierSet } from '@/Helpers/api/modifierSetService'

export default function ModifierSets() {
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
      await deleteModifierSet(rec.id)
      message.success('Modifier set deleted successfully')
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
      field: 'selection_type',
      width: 120,
      cellRenderer: ({ value }) => (
        <Tag color={value === 'single' ? 'blue' : 'green'}>
          {value === 'single' ? 'Single' : 'Multiple'}
        </Tag>
      ),
    },
    {
      headerName: 'Required',
      field: 'is_required',
      width: 100,
      cellRenderer: ({ value }) => (
        value ? <CheckCircleOutlined style={{ color: token.colorSuccess }} /> : '-'
      ),
    },
    {
      headerName: 'Options',
      field: 'modifiers_count',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag icon={<AppstoreOutlined />}>{value}</Tag>
      ),
    },
    {
      headerName: 'Modifiers',
      field: 'modifiers',
      flex: 1.5,
      minWidth: 200,
      cellRenderer: ({ value }) => (
        <Space size={4} wrap>
          {(value || []).slice(0, 5).map(m => (
            <Tag key={m.id} color={m.is_default ? 'blue' : 'default'}>
              {m.name}
              {m.price_adjustment > 0 && ` (+${m.price_adjustment})`}
            </Tag>
          ))}
          {(value || []).length > 5 && <Tag>+{value.length - 5} more</Tag>}
        </Space>
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
                    title="Delete modifier set?"
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
      <Head title="Modifier Sets" />

      <GlobalPageHeader
        title="Product Modifiers"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Modifier Set',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.modifier-sets.index"
        pageSize={20}
      />

      {visible && (
        <ModifierSetModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
