import { useRef, useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Dropdown, Modal, message } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  MoreOutlined,
  PlusOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import CustomerFormModal from './Components/CustomerFormModal'
import CustomerDetailDrawer from './Components/CustomerDetailDrawer'
import { formatCurrency } from '@/Helpers/formatters'

export default function Customers() {
  const gridRef = useRef()

  // Modal state
  const [formModalOpen, setFormModalOpen] = useState(false)
  const [editingCustomer, setEditingCustomer] = useState(null)
  const [detailDrawerOpen, setDetailDrawerOpen] = useState(false)
  const [selectedCustomerId, setSelectedCustomerId] = useState(null)

  // Fetch customers
  const fetchCustomers = useCallback(async (params) => {
    const response = await axios.get('/pos/customers', {
      params: {
        page: params.page,
        per_page: params.per_page,
        search: params.search,
        status: params.filterTree?.status,
      },
    })
    return {
      data: response.data.data,
      total: response.data.total,
    }
  }, [])

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/pos/customers', data),
    onSuccess: () => {
      message.success('Customer created successfully')
      setFormModalOpen(false)
      setEditingCustomer(null)
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create customer')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => axios.put(`/pos/customers/${id}`, data),
    onSuccess: () => {
      message.success('Customer updated successfully')
      setFormModalOpen(false)
      setEditingCustomer(null)
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update customer')
    },
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => axios.delete(`/pos/customers/${id}`),
    onSuccess: () => {
      message.success('Customer deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete customer')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingCustomer(null)
    setFormModalOpen(true)
  }

  const handleEdit = (customer) => {
    setEditingCustomer(customer)
    setFormModalOpen(true)
  }

  const handleView = (customer) => {
    setSelectedCustomerId(customer.id)
    setDetailDrawerOpen(true)
  }

  const handleDelete = (customer) => {
    Modal.confirm({
      title: 'Delete Customer',
      content: `Are you sure you want to delete "${customer.full_name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(customer.id),
    })
  }

  const handleSubmit = (values) => {
    if (editingCustomer) {
      updateMutation.mutate({ id: editingCustomer.id, data: values })
    } else {
      createMutation.mutate(values)
    }
  }

  const handleCloseModal = () => {
    setFormModalOpen(false)
    setEditingCustomer(null)
  }

  // Column definitions
  const columns = [
    {
      field: 'full_name',
      headerName: 'Name',
      flex: 1.5,
      minWidth: 150,
      filterType: 'text',
    },
    {
      field: 'email',
      headerName: 'Email',
      flex: 1.5,
      minWidth: 180,
    },
    {
      field: 'phone',
      headerName: 'Phone',
      flex: 1,
      minWidth: 120,
    },
    {
      field: 'loyalty_points',
      headerName: 'Points',
      flex: 0.8,
      minWidth: 80,
      cellRenderer: ({ value }) => (
        <span style={{ color: value > 0 ? '#1890ff' : 'inherit' }}>
          {value || 0}
        </span>
      ),
    },
    {
      field: 'credit_balance',
      headerName: 'Credit',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value || 0),
    },
    {
      field: 'status',
      headerName: 'Status',
      flex: 0.8,
      minWidth: 100,
      cellRenderer: ({ value }) => <StatusBadge status={value} />,
      filterType: 'select',
      filterOptions: [
        { label: 'Active', value: 'active' },
        { label: 'Inactive', value: 'inactive' },
      ],
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
              key: 'view',
              label: 'View Details',
              icon: <EyeOutlined />,
              onClick: () => handleView(data),
            },
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
              onClick: () => handleDelete(data),
            },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  // Filter fields
  const filterFields = [
    {
      field: 'status',
      label: 'Status',
      filterType: 'select',
      options: [
        { label: 'Active', value: 'active' },
        { label: 'Inactive', value: 'inactive' },
      ],
    },
  ]

  return (
    <>
      <Head title="Customers" />

      <GlobalPageHeader
        title="Customers"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'Add Customer',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchCustomers}
        title="Customers"
        searchPlaceholder="Search customers..."
        actionsColumn={actionsColumn}
        filterFields={filterFields}
        instanceId="customers"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <CustomerFormModal
        open={formModalOpen}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={createMutation.isPending || updateMutation.isPending}
        customer={editingCustomer}
      />

      <CustomerDetailDrawer
        open={detailDrawerOpen}
        onClose={() => {
          setDetailDrawerOpen(false)
          setSelectedCustomerId(null)
        }}
        customerId={selectedCustomerId}
      />
    </>
  )
}
