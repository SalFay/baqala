import { useRef, useState, useCallback, useMemo } from 'react';
import {
  Button,
  Form,
  Input,
  Select,
  Space,
  Tag,
  message,
  Popconfirm,
  Row,
  Col,
  DatePicker,
  Switch,
} from 'antd';
import { EditOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import { DataGridTable } from '../../Components/DataGridTable';
import CustomModal from '../../Components/CustomModal';
import { customerService } from '../../api/services/customer.service';
import { formatCurrency, DEFAULT_CURRENCY } from '../../constants';

export default function CustomersPage() {
  const gridRef = useRef(null);
  const [form] = Form.useForm();
  const [modalVisible, setModalVisible] = useState(false);
  const [editingRecord, setEditingRecord] = useState(null);
  const [loading, setLoading] = useState(false);

  // Fetch data for grid
  const fetchData = useCallback(async (params) => {
    const result = await customerService.getCustomers(params);
    return {
      data: result.data || result,
      total: result.total || (result.data?.length || 0),
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
    form.setFieldsValue({
      ...record,
      dob: record.dob ? record.dob : null,
    });
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
        await customerService.updateCustomer(editingRecord.id, values);
        message.success('Customer updated successfully');
      } else {
        await customerService.createCustomer(values);
        message.success('Customer created successfully');
      }
      handleCancel();
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  }, [editingRecord, handleCancel, handleRefresh]);

  // Delete customer
  const handleDelete = useCallback(async (id) => {
    try {
      await customerService.deleteCustomer(id);
      message.success('Customer deleted successfully');
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Delete failed');
    }
  }, [handleRefresh]);

  // Column definitions for AG Grid (filterType is used by GlobalFilter)
  const columns = useMemo(() => [
    {
      field: 'full_name',
      headerName: 'Name',
      minWidth: 180,
      flex: 2,
      filterType: 'text',
    },
    {
      field: 'email',
      headerName: 'Email',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
    },
    {
      field: 'phone',
      headerName: 'Phone',
      minWidth: 140,
      flex: 1,
      filterType: 'text',
    },
    {
      field: 'loyalty_points',
      headerName: 'Loyalty Points',
      minWidth: 120,
      flex: 1,
      filterType: 'number',
      cellRenderer: (params) => (
        <Tag color={params.value > 0 ? 'gold' : 'default'}>
          {params.value || 0}
        </Tag>
      ),
    },
    {
      field: 'credit_balance',
      headerName: 'Credit Balance',
      minWidth: 130,
      flex: 1,
      filterType: 'number',
      valueFormatter: (params) => formatCurrency(params.value),
    },
    {
      field: 'status',
      headerName: 'Status',
      minWidth: 100,
      flex: 1,
      filterType: 'select',
      filterOptions: [
        { value: 'Active', label: 'Active' },
        { value: 'Inactive', label: 'Inactive' },
      ],
      cellRenderer: (params) => (
        <Tag color={params.value === 'Active' ? 'green' : 'default'}>
          {params.value || 'Inactive'}
        </Tag>
      ),
    },
  ], []);

  // Actions column
  const actionsColumn = useMemo(() => ({
    field: 'actions',
    headerName: 'Actions',
    minWidth: 140,
    maxWidth: 140,
    sortable: false,
    cellRenderer: (params) => (
      <Space>
        <Button
          type="text"
          icon={<EyeOutlined />}
          onClick={() => handleEdit(params.data)}
        />
        <Button
          type="text"
          icon={<EditOutlined />}
          onClick={() => handleEdit(params.data)}
        />
        <Popconfirm
          title="Delete this customer?"
          description="This action cannot be undone."
          onConfirm={() => handleDelete(params.data.id)}
          okText="Yes"
          cancelText="No"
        >
          <Button type="text" danger icon={<DeleteOutlined />} />
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
        title="Customers"
        onAdd={handleCreate}
        addButtonText="Add Customer"
        searchPlaceholder="Search customers..."
        height={600}
        pageSize={20}
      />

      {/* Customer Form Modal */}
      <CustomModal
        open={modalVisible}
        onCancel={handleCancel}
        title={editingRecord ? 'Edit Customer' : 'Create Customer'}
        width={700}
        footer={null}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
          initialValues={{ status: 'Active', accepts_marketing: true }}
        >
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="first_name"
                label="First Name"
                rules={[{ required: true, message: 'First name is required' }]}
              >
                <Input placeholder="Enter first name" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="last_name"
                label="Last Name"
              >
                <Input placeholder="Enter last name" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="email"
                label="Email"
                rules={[{ type: 'email', message: 'Invalid email format' }]}
              >
                <Input placeholder="Enter email address" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="phone"
                label="Phone"
              >
                <Input placeholder="Enter phone number" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="gender"
                label="Gender"
              >
                <Select placeholder="Select gender" allowClear>
                  <Select.Option value="male">Male</Select.Option>
                  <Select.Option value="female">Female</Select.Option>
                  <Select.Option value="other">Other</Select.Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="dob"
                label="Date of Birth"
              >
                <DatePicker style={{ width: '100%' }} placeholder="Select date" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="company"
                label="Company"
              >
                <Input placeholder="Enter company name" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="status"
                label="Status"
              >
                <Select>
                  <Select.Option value="Active">Active</Select.Option>
                  <Select.Option value="Inactive">Inactive</Select.Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>

          <Form.Item
            name="address"
            label="Address"
          >
            <Input.TextArea rows={2} placeholder="Enter address" />
          </Form.Item>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="credit_limit"
                label="Credit Limit"
              >
                <Input type="number" placeholder="0.00" addonAfter={DEFAULT_CURRENCY} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="accepts_marketing"
                label="Accepts Marketing"
                valuePropName="checked"
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item
            name="notes"
            label="Notes"
          >
            <Input.TextArea rows={3} placeholder="Additional notes..." />
          </Form.Item>

          {/* Form Actions */}
          <div style={{ textAlign: 'right', marginTop: 24, paddingTop: 16, borderTop: '1px solid #f0f0f0' }}>
            <Space>
              <Button onClick={handleCancel}>Cancel</Button>
              <Button type="primary" htmlType="submit" loading={loading}>
                {editingRecord ? 'Update' : 'Create'} Customer
              </Button>
            </Space>
          </div>
        </Form>
      </CustomModal>
    </div>
  );
}
