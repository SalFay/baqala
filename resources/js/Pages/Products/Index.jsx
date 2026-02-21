import { useRef, useState } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Dropdown, Modal, message } from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  MoreOutlined,
  PlusOutlined,
} from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ProductFormModal from './Components/ProductFormModal'
import { formatCurrency } from '@/Helpers/formatters'

export default function Products() {
  const { categories } = usePage().props
  const gridRef = useRef()

  // Modal state
  const [modalOpen, setModalOpen] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/pos/products', data),
    onSuccess: () => {
      message.success('Product created successfully')
      setModalOpen(false)
      setEditingProduct(null)
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create product')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => axios.put(`/pos/products/${id}`, data),
    onSuccess: () => {
      message.success('Product updated successfully')
      setModalOpen(false)
      setEditingProduct(null)
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update product')
    },
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => axios.delete(`/pos/products/${id}`),
    onSuccess: () => {
      message.success('Product deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete product')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingProduct(null)
    setModalOpen(true)
  }

  const handleEdit = (product) => {
    setEditingProduct(product)
    setModalOpen(true)
  }

  const handleDelete = (product) => {
    Modal.confirm({
      title: 'Delete Product',
      content: `Are you sure you want to delete "${product.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(product.id),
    })
  }

  const handleSubmit = (values) => {
    if (editingProduct) {
      updateMutation.mutate({ id: editingProduct.id, data: values })
    } else {
      createMutation.mutate(values)
    }
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingProduct(null)
  }

  // Column definitions
  const columns = [
    {
      field: 'name',
      headerName: 'Product Name',
      flex: 2,
      minWidth: 200,
    },
    {
      field: 'sku',
      headerName: 'SKU',
      flex: 1,
      minWidth: 100,
    },
    {
      field: 'barcode',
      headerName: 'Barcode',
      flex: 1,
      minWidth: 120,
    },
    {
      field: 'category',
      headerName: 'Category',
      flex: 1,
      minWidth: 120,
      valueGetter: ({ data }) => data?.category?.name || '-',
    },
    {
      field: 'price',
      headerName: 'Price',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => formatCurrency(value),
    },
    {
      field: 'stock_qty',
      headerName: 'Stock',
      flex: 1,
      minWidth: 80,
      cellRenderer: ({ value }) => (
        <span style={{ color: value <= 0 ? '#ff4d4f' : value <= 10 ? '#faad14' : 'inherit' }}>
          {value ?? 0}
        </span>
      ),
    },
    {
      field: 'is_active',
      headerName: 'Status',
      flex: 1,
      minWidth: 100,
      cellRenderer: ({ value }) => <StatusBadge status={value ? 'active' : 'inactive'} />,
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
              key: 'edit',
              label: 'Edit',
              icon: <EditOutlined />,
              onClick: () => handleEdit(data),
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

  // Filter fields for GlobalFilter
  const filterFields = {
    SELECTS: {
      category_id: {
        name: 'category_id',
        label: 'Category',
        params: {
          options: categories?.map((c) => ({ label: c.name, value: c.id })) || [],
        },
      },
      is_active: {
        name: 'is_active',
        label: 'Status',
        params: {
          options: [
            { label: 'Active', value: true },
            { label: 'Inactive', value: false },
          ],
        },
      },
    },
    DATES: {},
    RANGES: {},
  }

  return (
    <>
      <Head title="Products" />

      <GlobalPageHeader
        title="Products"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'Add Product',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={[...columns, actionsColumn]}
        routeName="pos.products.listing"
        filterFields={filterFields}
        instanceId="products"
        pageSize={20}
        height="calc(100vh - 260px)"
        showSoftDeleted={true}
      />

      <ProductFormModal
        open={modalOpen}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        loading={createMutation.isPending || updateMutation.isPending}
        product={editingProduct}
        categories={categories || []}
      />
    </>
  )
}
