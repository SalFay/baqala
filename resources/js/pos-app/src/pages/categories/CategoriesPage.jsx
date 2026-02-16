import { useRef, useState, useCallback, useMemo } from 'react';
import {
  Button,
  Form,
  Input,
  Space,
  Tag,
  message,
  Popconfirm,
} from 'antd';
import { EditOutlined, DeleteOutlined } from '@ant-design/icons';
import { DataGridTable } from '../../Components/DataGridTable';
import CustomModal from '../../Components/CustomModal';
import { categoryService } from '../../api/services/category.service';

export default function CategoriesPage() {
  const gridRef = useRef(null);
  const [form] = Form.useForm();
  const [modalVisible, setModalVisible] = useState(false);
  const [editingRecord, setEditingRecord] = useState(null);
  const [loading, setLoading] = useState(false);

  // Fetch data for grid - wrap non-paginated API
  const fetchData = useCallback(async (params) => {
    const data = await categoryService.getCategories(params.search);
    // Filter client-side if search provided (for non-paginated APIs)
    let filtered = data;
    if (params.search) {
      const searchLower = params.search.toLowerCase();
      filtered = data.filter(cat =>
        cat.name?.toLowerCase().includes(searchLower) ||
        cat.code?.toLowerCase().includes(searchLower)
      );
    }
    return {
      data: filtered,
      total: filtered.length,
    };
  }, []);

  // Refresh grid
  const handleRefresh = useCallback(() => {
    if (gridRef.current?.reloadData) {
      gridRef.current.reloadData();
    }
  }, []);

  // Open create modal
  const handleCreate = useCallback(() => {
    setEditingRecord(null);
    form.resetFields();
    setModalVisible(true);
  }, [form]);

  // Open edit modal
  const handleEdit = useCallback((record) => {
    setEditingRecord(record);
    form.setFieldsValue(record);
    setModalVisible(true);
  }, [form]);

  // Close modal
  const handleCancel = useCallback(() => {
    setModalVisible(false);
    setEditingRecord(null);
    form.resetFields();
  }, [form]);

  // Submit form
  const handleSubmit = useCallback(async (values) => {
    setLoading(true);
    try {
      if (editingRecord) {
        await categoryService.updateCategory(editingRecord.id, values);
        message.success('Category updated successfully');
      } else {
        await categoryService.createCategory(values);
        message.success('Category created successfully');
      }
      handleCancel();
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  }, [editingRecord, handleCancel, handleRefresh]);

  // Delete category
  const handleDelete = useCallback(async (id) => {
    try {
      await categoryService.deleteCategory(id);
      message.success('Category deleted successfully');
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Delete failed');
    }
  }, [handleRefresh]);

  // Column definitions for AG Grid (filterType is used by GlobalFilter)
  const columns = useMemo(() => [
    {
      field: 'name',
      headerName: 'Name',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
    },
    {
      field: 'code',
      headerName: 'Code',
      minWidth: 120,
      flex: 1,
      filterType: 'text',
      cellRenderer: (params) => params.value ? <Tag>{params.value}</Tag> : '-',
    },
    {
      field: 'products_count',
      headerName: 'Products',
      minWidth: 100,
      flex: 1,
      filterType: 'number',
      cellRenderer: (params) => (
        <Tag color={params.value > 0 ? 'blue' : 'default'}>
          {params.value || 0}
        </Tag>
      ),
    },
    {
      field: 'description',
      headerName: 'Description',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
      cellRenderer: (params) => params.value || '-',
    },
  ], []);

  // Actions column
  const actionsColumn = useMemo(() => ({
    field: 'actions',
    headerName: 'Actions',
    minWidth: 120,
    maxWidth: 120,
    sortable: false,
    cellRenderer: (params) => (
      <Space>
        <Button
          type="text"
          icon={<EditOutlined />}
          onClick={() => handleEdit(params.data)}
        />
        <Popconfirm
          title="Delete this category?"
          description="This cannot be undone. Products will be unassigned."
          onConfirm={() => handleDelete(params.data.id)}
          okText="Yes"
          cancelText="No"
        >
          <Button
            type="text"
            danger
            icon={<DeleteOutlined />}
            disabled={params.data.products_count > 0}
          />
        </Popconfirm>
      </Space>
    ),
  }), [handleEdit, handleDelete]);

  return (
    <div style={{ padding: 24 }}>
      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        actionsColumn={actionsColumn}
        fetchData={fetchData}
        title="Categories"
        onAdd={handleCreate}
        addButtonText="Add Category"
        searchPlaceholder="Search categories..."
        height={500}
        pageSize={50}
        pagination={false}
      />

      {/* Category Form Modal */}
      <CustomModal
        open={modalVisible}
        onCancel={handleCancel}
        title={editingRecord ? 'Edit Category' : 'Create Category'}
        width={500}
        footer={null}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
        >
          <Form.Item
            name="name"
            label="Category Name"
            rules={[{ required: true, message: 'Category name is required' }]}
          >
            <Input placeholder="Enter category name" />
          </Form.Item>

          <Form.Item
            name="code"
            label="Code"
          >
            <Input placeholder="Enter category code (optional)" />
          </Form.Item>

          <Form.Item
            name="description"
            label="Description"
          >
            <Input.TextArea rows={3} placeholder="Enter description (optional)" />
          </Form.Item>

          {/* Form Actions */}
          <div style={{ textAlign: 'right', marginTop: 24, paddingTop: 16, borderTop: '1px solid #f0f0f0' }}>
            <Space>
              <Button onClick={handleCancel}>Cancel</Button>
              <Button type="primary" htmlType="submit" loading={loading}>
                {editingRecord ? 'Update' : 'Create'} Category
              </Button>
            </Space>
          </div>
        </Form>
      </CustomModal>
    </div>
  );
}
