import { useRef, useState } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, message, Tag, theme, Popconfirm, Space } from 'antd'
import {
  DeleteOutlined,
  EditOutlined,
  EllipsisOutlined,
  PlusOutlined,
  PercentageOutlined,
} from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import DiscountRuleModal from './DiscountRuleModal'
import { deleteDiscountRule } from '@/Helpers/api/discountService'

export default function DiscountRules() {
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
      await deleteDiscountRule(rec.id)
      message.success('Discount rule deleted successfully')
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
      headerName: 'Discount',
      field: 'discount_display',
      width: 120,
      cellRenderer: ({ data }) => (
        <Tag color="blue">
          {data.discount_type === 'percentage'
            ? `${data.discount_amount}%`
            : `${data.discount_amount}`}
        </Tag>
      ),
    },
    {
      headerName: 'Applies To',
      field: 'applies_to',
      width: 130,
      cellRenderer: ({ value }) => {
        const colors = {
          all: 'green',
          category: 'blue',
          brand: 'purple',
          product: 'orange',
          customer_group: 'cyan',
        }
        return <Tag color={colors[value] || 'default'}>{value}</Tag>
      },
    },
    {
      headerName: 'Priority',
      field: 'priority',
      width: 90,
      cellRenderer: ({ value }) => <Tag>{value || 0}</Tag>,
    },
    {
      headerName: 'Stackable',
      field: 'is_stackable',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'green' : 'default'}>{value ? 'Yes' : 'No'}</Tag>
      ),
    },
    {
      headerName: 'Uses',
      field: 'current_uses',
      width: 100,
      cellRenderer: ({ data }) => (
        <span>
          {data.current_uses || 0}
          {data.max_uses ? ` / ${data.max_uses}` : ''}
        </span>
      ),
    },
    {
      headerName: 'Validity',
      field: 'is_valid',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'success' : 'error'}>{value ? 'Valid' : 'Invalid'}</Tag>
      ),
    },
    {
      headerName: 'Status',
      field: 'is_active',
      width: 100,
      cellRenderer: ({ value }) => (
        <Tag color={value ? 'success' : 'error'}>{value ? 'Active' : 'Inactive'}</Tag>
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
                    title="Delete discount rule?"
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
      <Head title="Discount Rules" />

      <GlobalPageHeader
        title="Discount Rules"
        parentPageTitle="Settings"
        parentPageRoute="settings.page"
        actionButtons={[
          {
            title: 'Add Discount Rule',
            icon: <PlusOutlined />,
            onClick: handleCreate,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        routeName="pos.discount-rules.index"
        pageSize={20}
      />

      {visible && (
        <DiscountRuleModal
          visible={visible}
          onCancel={() => setVisible(false)}
          record={record}
          onUpdate={handleRefresh}
        />
      )}
    </>
  )
}
