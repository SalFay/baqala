import { useRef, useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Button, Dropdown, Modal, message, Form, Input, Select, Row, Col } from 'antd'
import { EditOutlined, DeleteOutlined, MoreOutlined, PlusOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import DataGridTable from '@/Components/DataGridTable/DataGridTable'
import StatusBadge from '@/Components/StatusBadge'
import GlobalPageHeader from '@/Components/GlobalPageHeader'

const { Option } = Select
const { TextArea } = Input

export default function Vendors() {
  const gridRef = useRef()
  const [modalOpen, setModalOpen] = useState(false)
  const [editingVendor, setEditingVendor] = useState(null)
  const [form] = Form.useForm()

  const fetchVendors = useCallback(async (params) => {
    const response = await axios.get('/pos/vendors', {
      params: {
        page: params.page,
        per_page: params.per_page,
        search: params.search,
        status: params.filterTree?.status,
      },
    })
    return { data: response.data.data, total: response.data.total }
  }, [])

  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/pos/vendors', data),
    onSuccess: () => {
      message.success('Vendor created successfully')
      handleCloseModal()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to create vendor'),
  })

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => axios.put(`/pos/vendors/${id}`, data),
    onSuccess: () => {
      message.success('Vendor updated successfully')
      handleCloseModal()
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to update vendor'),
  })

  const deleteMutation = useMutation({
    mutationFn: (id) => axios.delete(`/pos/vendors/${id}`),
    onSuccess: () => {
      message.success('Vendor deleted successfully')
      gridRef.current?.reloadData()
    },
    onError: (error) => message.error(error.response?.data?.message || 'Failed to delete vendor'),
  })

  const handleAdd = () => {
    setEditingVendor(null)
    form.resetFields()
    form.setFieldsValue({ status: 'active' })
    setModalOpen(true)
  }

  const handleEdit = (vendor) => {
    setEditingVendor(vendor)
    form.setFieldsValue(vendor)
    setModalOpen(true)
  }

  const handleDelete = (vendor) => {
    Modal.confirm({
      title: 'Delete Vendor',
      content: `Are you sure you want to delete "${vendor.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(vendor.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      if (editingVendor) {
        updateMutation.mutate({ id: editingVendor.id, data: values })
      } else {
        createMutation.mutate(values)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingVendor(null)
    form.resetFields()
  }

  const columns = [
    { field: 'name', headerName: 'Name', flex: 1.5, minWidth: 150, filterType: 'text' },
    { field: 'email', headerName: 'Email', flex: 1.5, minWidth: 180 },
    { field: 'phone', headerName: 'Phone', flex: 1, minWidth: 120 },
    { field: 'address', headerName: 'Address', flex: 1.5, minWidth: 150 },
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
            { key: 'edit', label: 'Edit', icon: <EditOutlined />, onClick: () => handleEdit(data) },
            { type: 'divider' },
            { key: 'delete', label: 'Delete', icon: <DeleteOutlined />, danger: true, onClick: () => handleDelete(data) },
          ],
        }}
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    ),
  }

  return (
    <>
      <Head title="Vendors" />

      <GlobalPageHeader
        title="Vendors"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'Add Vendor',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        fetchData={fetchVendors}
        title="Vendors"
        searchPlaceholder="Search vendors..."
        actionsColumn={actionsColumn}
        instanceId="vendors"
        pageSize={20}
        height="calc(100vh - 260px)"
      />

      <Modal
        title={editingVendor ? 'Edit Vendor' : 'Add Vendor'}
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={handleCloseModal}
        okText={editingVendor ? 'Update' : 'Create'}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
        width={600}
        destroyOnClose
      >
        <Form form={form} layout="vertical" initialValues={{ status: 'active' }}>
          <Form.Item name="name" label="Vendor Name" rules={[{ required: true, message: 'Please enter vendor name' }]}>
            <Input placeholder="Enter vendor name" />
          </Form.Item>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="email" label="Email" rules={[{ type: 'email', message: 'Please enter valid email' }]}>
                <Input placeholder="Enter email" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="phone" label="Phone">
                <Input placeholder="Enter phone number" />
              </Form.Item>
            </Col>
          </Row>
          <Form.Item name="address" label="Address">
            <TextArea rows={2} placeholder="Enter address" />
          </Form.Item>
          <Form.Item name="status" label="Status">
            <Select>
              <Option value="active">Active</Option>
              <Option value="inactive">Inactive</Option>
            </Select>
          </Form.Item>
        </Form>
      </Modal>
    </>
  )
}
