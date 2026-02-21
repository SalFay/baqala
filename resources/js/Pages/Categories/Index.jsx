import { useState, useCallback } from 'react'
import { Head, usePage } from '@inertiajs/react'
import {
  Typography,
  Button,
  Card,
  Table,
  Space,
  Tag,
  Modal,
  Form,
  Input,
  Select,
  Switch,
  message,
  Dropdown,
} from 'antd'
import {
  EditOutlined,
  DeleteOutlined,
  PlusOutlined,
  MoreOutlined,
  FolderOutlined,
} from '@ant-design/icons'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import axios from 'axios'
import GlobalPageHeader from '@/Components/GlobalPageHeader'

const { Text } = Typography
const { TextArea } = Input
const { Option } = Select

export default function Categories() {
  const { categories: initialCategories, flatCategories: initialFlatCategories } = usePage().props
  const queryClient = useQueryClient()

  // State
  const [modalOpen, setModalOpen] = useState(false)
  const [editingCategory, setEditingCategory] = useState(null)
  const [form] = Form.useForm()

  // Fetch categories
  const { data: categories, isLoading, refetch } = useQuery({
    queryKey: ['categories'],
    queryFn: async () => {
      const response = await axios.get('/pos/categories')
      return response.data.data || []
    },
    initialData: initialFlatCategories || [],
  })

  // Create mutation
  const createMutation = useMutation({
    mutationFn: (data) => axios.post('/pos/categories', data),
    onSuccess: () => {
      message.success('Category created successfully')
      setModalOpen(false)
      setEditingCategory(null)
      form.resetFields()
      refetch()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create category')
    },
  })

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => axios.put(`/pos/categories/${id}`, data),
    onSuccess: () => {
      message.success('Category updated successfully')
      setModalOpen(false)
      setEditingCategory(null)
      form.resetFields()
      refetch()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update category')
    },
  })

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => axios.delete(`/pos/categories/${id}`),
    onSuccess: () => {
      message.success('Category deleted successfully')
      refetch()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete category')
    },
  })

  // Handlers
  const handleAdd = () => {
    setEditingCategory(null)
    form.resetFields()
    form.setFieldsValue({ is_active: true })
    setModalOpen(true)
  }

  const handleEdit = (category) => {
    setEditingCategory(category)
    form.setFieldsValue({
      name: category.name,
      parent_id: category.parent_id,
      description: category.description,
      is_active: category.is_active,
    })
    setModalOpen(true)
  }

  const handleDelete = (category) => {
    Modal.confirm({
      title: 'Delete Category',
      content: `Are you sure you want to delete "${category.name}"?`,
      okText: 'Delete',
      okType: 'danger',
      onOk: () => deleteMutation.mutate(category.id),
    })
  }

  const handleSubmit = async () => {
    try {
      const values = await form.validateFields()
      if (editingCategory) {
        updateMutation.mutate({ id: editingCategory.id, data: values })
      } else {
        createMutation.mutate(values)
      }
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleCloseModal = () => {
    setModalOpen(false)
    setEditingCategory(null)
    form.resetFields()
  }

  // Get parent category name
  const getParentName = (parentId) => {
    const parent = categories?.find((c) => c.id === parentId)
    return parent?.name || '-'
  }

  // Column definitions for Ant Design Table
  const columns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      render: (text) => (
        <Space>
          <FolderOutlined style={{ color: '#1890ff' }} />
          <Text strong>{text}</Text>
        </Space>
      ),
    },
    {
      title: 'Parent',
      dataIndex: 'parent_id',
      key: 'parent_id',
      render: (value) => (value ? getParentName(value) : '-'),
    },
    {
      title: 'Products',
      dataIndex: 'products_count',
      key: 'products_count',
      width: 100,
      align: 'center',
    },
    {
      title: 'Status',
      dataIndex: 'is_active',
      key: 'is_active',
      width: 100,
      render: (value) => (
        <Tag color={value ? 'green' : 'default'}>
          {value ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 80,
      render: (_, record) => (
        <Dropdown
          trigger={['click']}
          menu={{
            items: [
              {
                key: 'edit',
                label: 'Edit',
                icon: <EditOutlined />,
                onClick: () => handleEdit(record),
              },
              {
                key: 'delete',
                label: 'Delete',
                icon: <DeleteOutlined />,
                danger: true,
                onClick: () => handleDelete(record),
              },
            ],
          }}
        >
          <Button type="text" icon={<MoreOutlined />} />
        </Dropdown>
      ),
    },
  ]

  // Get parent options (exclude self and children when editing)
  const getParentOptions = () => {
    if (!categories) return []
    return categories.filter((c) => c.id !== editingCategory?.id)
  }

  return (
    <>
      <Head title="Categories" />

      <GlobalPageHeader
        title="Categories"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        actionButtons={[
          {
            title: 'Add Category',
            icon: <PlusOutlined />,
            onClick: handleAdd,
            type: 'primary',
          },
        ]}
      />

      <Card>
        <Table
          dataSource={categories || []}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            pageSize: 20,
            showTotal: (total) => `Total ${total} categories`,
          }}
        />
      </Card>

      {/* Form Modal */}
      <Modal
        title={editingCategory ? 'Edit Category' : 'Add Category'}
        open={modalOpen}
        onOk={handleSubmit}
        onCancel={handleCloseModal}
        okText={editingCategory ? 'Update' : 'Create'}
        confirmLoading={createMutation.isPending || updateMutation.isPending}
        destroyOnClose
      >
        <Form form={form} layout="vertical" initialValues={{ is_active: true }}>
          <Form.Item
            name="name"
            label="Category Name"
            rules={[{ required: true, message: 'Please enter category name' }]}
          >
            <Input placeholder="Enter category name" />
          </Form.Item>

          <Form.Item name="parent_id" label="Parent Category">
            <Select placeholder="Select parent category (optional)" allowClear>
              {getParentOptions().map((cat) => (
                <Option key={cat.id} value={cat.id}>
                  {cat.name}
                </Option>
              ))}
            </Select>
          </Form.Item>

          <Form.Item name="description" label="Description">
            <TextArea rows={3} placeholder="Category description (optional)" />
          </Form.Item>

          <Form.Item name="is_active" label="Status" valuePropName="checked">
            <Switch checkedChildren="Active" unCheckedChildren="Inactive" />
          </Form.Item>
        </Form>
      </Modal>
    </>
  )
}
