import { useRef, useState, useEffect } from 'react'
import { Head } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Tag, Space, Tabs } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
  CheckCircleOutlined,
  PercentageOutlined,
  GroupOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery } from '@tanstack/react-query'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import StatusBadge from '@/Components/StatusBadge'
import TaxRateModal from './TaxRateModal'
import TaxGroupModal from './TaxGroupModal'
import { deleteTaxRate, deleteTaxGroup, fetchAllTaxRates } from '@/Helpers/api/taxService'

export default function TaxRates() {
  const gridRef = useRef()
  const groupGridRef = useRef()
  const [activeTab, setActiveTab] = useState('rates')

  // Modal state
  const [rateModalOpen, setRateModalOpen] = useState(false)
  const [groupModalOpen, setGroupModalOpen] = useState(false)
  const [editingRate, setEditingRate] = useState(null)
  const [editingGroup, setEditingGroup] = useState(null)

  // Fetch tax rates for group modal
  const { data: allTaxRates = [] } = useQuery({
    queryKey: ['allTaxRates'],
    queryFn: () => fetchAllTaxRates().then(res => res.data.data || []),
  })

  // Delete mutations
  const deleteRateMutation = useMutation({
    mutationFn: (id) => deleteTaxRate(id),
    onSuccess: () => {
      message.success('Tax rate deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete tax rate')
    },
  })

  const deleteGroupMutation = useMutation({
    mutationFn: (id) => deleteTaxGroup(id),
    onSuccess: () => {
      message.success('Tax group deleted successfully')
      groupGridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete tax group')
    },
  })

  // Rate handlers
  const handleAddRate = () => {
    setEditingRate(null)
    setRateModalOpen(true)
  }

  const handleEditRate = (rate) => {
    setEditingRate(rate)
    setRateModalOpen(true)
  }

  const handleDeleteRate = (rate) => {
    if (rate.is_default) {
      message.warning('Cannot delete the default tax rate')
      return
    }
    Modal.confirm({
      title: 'Delete Tax Rate',
      content: `Are you sure you want to delete "${rate.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteRateMutation.mutate(rate.id),
    })
  }

  const handleRateSuccess = () => {
    setRateModalOpen(false)
    setEditingRate(null)
    gridRef.current?.reloadData()
  }

  // Group handlers
  const handleAddGroup = () => {
    setEditingGroup(null)
    setGroupModalOpen(true)
  }

  const handleEditGroup = (group) => {
    setEditingGroup(group)
    setGroupModalOpen(true)
  }

  const handleDeleteGroup = (group) => {
    Modal.confirm({
      title: 'Delete Tax Group',
      content: `Are you sure you want to delete "${group.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteGroupMutation.mutate(group.id),
    })
  }

  const handleGroupSuccess = () => {
    setGroupModalOpen(false)
    setEditingGroup(null)
    groupGridRef.current?.reloadData()
  }

  // Tax Rate columns
  const rateColumns = [
    {
      field: 'name',
      headerName: 'Name',
      flex: 1.5,
      minWidth: 150,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <PercentageOutlined />
          <span>{data.name}</span>
          {data.is_default && <Tag color="gold">Default</Tag>}
        </Space>
      ),
    },
    {
      field: 'rate',
      headerName: 'Rate (%)',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => `${value}%`,
    },
    {
      field: 'tax_number',
      headerName: 'Tax Number',
      flex: 1,
      minWidth: 120,
    },
    {
      field: 'is_compound',
      headerName: 'Compound',
      flex: 0.7,
      minWidth: 90,
      cellRenderer: ({ value }) => (
        value ? <Tag color="blue">Yes</Tag> : <Tag>No</Tag>
      ),
    },
    {
      field: 'is_recoverable',
      headerName: 'Recoverable',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => (
        value ? <Tag color="green">Yes</Tag> : <Tag>No</Tag>
      ),
    },
    {
      field: 'is_active',
      headerName: 'Status',
      flex: 0.7,
      minWidth: 90,
      cellRenderer: ({ value }) => (
        <StatusBadge status={value ? 'active' : 'inactive'} />
      ),
    },
  ]

  const rateActionsColumn = {
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
              onClick: () => handleEditRate(data),
            },
            { type: 'divider' },
            {
              key: 'delete',
              label: 'Delete',
              icon: <DeleteOutlined />,
              danger: true,
              disabled: data.is_default,
              onClick: () => handleDeleteRate(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  // Tax Group columns
  const groupColumns = [
    {
      field: 'name',
      headerName: 'Name',
      flex: 1.5,
      minWidth: 150,
      filterType: 'text',
      cellRenderer: ({ data }) => (
        <Space>
          <GroupOutlined />
          <span>{data.name}</span>
        </Space>
      ),
    },
    {
      field: 'total_rate',
      headerName: 'Total Rate (%)',
      flex: 0.8,
      minWidth: 120,
      cellRenderer: ({ value }) => `${value || 0}%`,
    },
    {
      field: 'tax_rates',
      headerName: 'Tax Rates',
      flex: 2,
      minWidth: 200,
      cellRenderer: ({ value }) => (
        <Space wrap>
          {value?.map(rate => (
            <Tag key={rate.id}>{rate.name} ({rate.rate}%)</Tag>
          ))}
        </Space>
      ),
    },
    {
      field: 'is_active',
      headerName: 'Status',
      flex: 0.7,
      minWidth: 90,
      cellRenderer: ({ value }) => (
        <StatusBadge status={value ? 'active' : 'inactive'} />
      ),
    },
  ]

  const groupActionsColumn = {
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
              onClick: () => handleEditGroup(data),
            },
            { type: 'divider' },
            {
              key: 'delete',
              label: 'Delete',
              icon: <DeleteOutlined />,
              danger: true,
              onClick: () => handleDeleteGroup(data),
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
      key: 'rates',
      label: 'Tax Rates',
      children: (
        <DataGridTable
          gridRef={gridRef}
          routeName="pos.tax-rates.listing"
          columns={[...rateColumns, rateActionsColumn]}
          instanceId="tax-rates"
          pageSize={20}
          height="calc(100vh - 320px)"
        />
      ),
    },
    {
      key: 'groups',
      label: 'Tax Groups',
      children: (
        <DataGridTable
          gridRef={groupGridRef}
          routeName="pos.tax-groups.listing"
          columns={[...groupColumns, groupActionsColumn]}
          instanceId="tax-groups"
          pageSize={20}
          height="calc(100vh - 320px)"
        />
      ),
    },
  ]

  return (
    <>
      <Head title="Tax Rates" />

      <GlobalPageHeader
        title="Tax Rates"
        parentPageTitle="Settings"
        parentPageRoute="settings.index"
        actionButtons={[
          activeTab === 'rates' ? {
            title: 'Add Tax Rate',
            icon: <PlusOutlined />,
            onClick: handleAddRate,
            type: 'primary',
          } : {
            title: 'Add Tax Group',
            icon: <PlusOutlined />,
            onClick: handleAddGroup,
            type: 'primary',
          },
        ]}
      />

      <Tabs
        activeKey={activeTab}
        onChange={setActiveTab}
        items={tabItems}
      />

      <TaxRateModal
        open={rateModalOpen}
        onClose={() => {
          setRateModalOpen(false)
          setEditingRate(null)
        }}
        onSuccess={handleRateSuccess}
        taxRate={editingRate}
      />

      <TaxGroupModal
        open={groupModalOpen}
        onClose={() => {
          setGroupModalOpen(false)
          setEditingGroup(null)
        }}
        onSuccess={handleGroupSuccess}
        taxGroup={editingGroup}
        taxRates={allTaxRates}
      />
    </>
  )
}
