import { useRef, useState, useCallback, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
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
  Card,
  Statistic,
  Descriptions,
  Tabs,
  Table,
} from 'antd';
import {
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  PhoneOutlined,
  EnvironmentOutlined,
} from '@ant-design/icons';
import { DataGridTable } from '../../Components/DataGridTable';
import CustomModal from '../../Components/CustomModal';
import { vendorService } from '../../api/services/vendor.service';
import { STATUS_COLORS } from '../../constants';

export default function VendorsPage() {
  const gridRef = useRef(null);
  const [form] = Form.useForm();
  const [modalVisible, setModalVisible] = useState(false);
  const [detailModal, setDetailModal] = useState(null);
  const [editingRecord, setEditingRecord] = useState(null);
  const [loading, setLoading] = useState(false);

  // Vendor detail queries
  const { data: vendorBalance } = useQuery({
    queryKey: ['vendor-balance', detailModal?.id],
    queryFn: () => vendorService.getVendorBalance(detailModal.id),
    enabled: !!detailModal,
  });

  const { data: vendorPurchaseOrders } = useQuery({
    queryKey: ['vendor-purchase-orders', detailModal?.id],
    queryFn: () => vendorService.getVendorPurchaseOrders(detailModal.id),
    enabled: !!detailModal,
  });

  // Fetch data for grid
  const fetchData = useCallback(async (params) => {
    const result = await vendorService.getVendors({
      page: params.page,
      per_page: params.per_page,
      search: params.search,
    });
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
    form.setFieldsValue({ status: 'active' });
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
        await vendorService.updateVendor(editingRecord.id, values);
        message.success('Vendor updated successfully');
      } else {
        await vendorService.createVendor(values);
        message.success('Vendor created successfully');
      }
      handleCancel();
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  }, [editingRecord, handleCancel, handleRefresh]);

  // Delete vendor
  const handleDelete = useCallback(async (id) => {
    try {
      await vendorService.deleteVendor(id);
      message.success('Vendor deleted successfully');
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
      minWidth: 180,
      flex: 2,
      filterType: 'text',
    },
    {
      field: 'mobile',
      headerName: 'Mobile',
      minWidth: 140,
      flex: 1,
      filterType: 'text',
      cellRenderer: (params) => params.value ? (
        <Space>
          <PhoneOutlined />
          {params.value}
        </Space>
      ) : '-',
    },
    {
      field: 'address',
      headerName: 'Address',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
      cellRenderer: (params) => params.value ? (
        <Space>
          <EnvironmentOutlined />
          <span style={{ maxWidth: 180, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', display: 'inline-block' }}>
            {params.value}
          </span>
        </Space>
      ) : '-',
    },
    {
      field: 'status',
      headerName: 'Status',
      minWidth: 100,
      flex: 1,
      filterType: 'select',
      filterOptions: [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive' },
      ],
      cellRenderer: (params) => (
        <Tag color={STATUS_COLORS[params.value] || 'default'}>
          {params.value === 'active' ? 'Active' : 'Inactive'}
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
          onClick={() => setDetailModal(params.data)}
        />
        <Button
          type="text"
          icon={<EditOutlined />}
          onClick={() => handleEdit(params.data)}
        />
        <Popconfirm
          title="Delete this vendor?"
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
        title="Vendors"
        onAdd={handleCreate}
        addButtonText="Add Vendor"
        searchPlaceholder="Search vendors..."
        height={600}
        pageSize={20}
      />

      {/* Vendor Form Modal */}
      <CustomModal
        open={modalVisible}
        onCancel={handleCancel}
        title={editingRecord ? 'Edit Vendor' : 'Create Vendor'}
        width={500}
        footer={null}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
          initialValues={{ status: 'active' }}
        >
          <Form.Item
            name="name"
            label="Vendor Name"
            rules={[{ required: true, message: 'Vendor name is required' }]}
          >
            <Input placeholder="Enter vendor name" />
          </Form.Item>

          <Form.Item
            name="mobile"
            label="Mobile Number"
          >
            <Input placeholder="Enter mobile number" />
          </Form.Item>

          <Form.Item
            name="address"
            label="Address"
          >
            <Input.TextArea rows={3} placeholder="Enter address" />
          </Form.Item>

          <Form.Item
            name="status"
            label="Status"
            rules={[{ required: true, message: 'Please select status' }]}
          >
            <Select>
              <Select.Option value="active">Active</Select.Option>
              <Select.Option value="inactive">Inactive</Select.Option>
            </Select>
          </Form.Item>

          {/* Form Actions */}
          <div style={{ textAlign: 'right', marginTop: 24, paddingTop: 16, borderTop: '1px solid #f0f0f0' }}>
            <Space>
              <Button onClick={handleCancel}>Cancel</Button>
              <Button type="primary" htmlType="submit" loading={loading}>
                {editingRecord ? 'Update' : 'Create'} Vendor
              </Button>
            </Space>
          </div>
        </Form>
      </CustomModal>

      {/* Detail Modal */}
      <CustomModal
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        title={`Vendor Details - ${detailModal?.name || ''}`}
        width={700}
        footer={null}
      >
        {detailModal && (
          <Tabs
            items={[
              {
                key: 'info',
                label: 'Information',
                children: (
                  <div>
                    <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                      <Col span={12}>
                        <Card>
                          <Statistic
                            title="Balance"
                            value={vendorBalance?.balance || 0}
                            precision={2}
                            suffix="SAR"
                          />
                        </Card>
                      </Col>
                      <Col span={12}>
                        <Card>
                          <Statistic
                            title="Purchase Orders"
                            value={vendorPurchaseOrders?.total || 0}
                          />
                        </Card>
                      </Col>
                    </Row>

                    <Descriptions bordered column={1}>
                      <Descriptions.Item label="Name">{detailModal.name}</Descriptions.Item>
                      <Descriptions.Item label="Mobile">{detailModal.mobile || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Address">{detailModal.address || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Status">
                        <Tag color={STATUS_COLORS[detailModal.status] || 'default'}>
                          {detailModal.status}
                        </Tag>
                      </Descriptions.Item>
                      <Descriptions.Item label="Created">
                        {new Date(detailModal.created_at).toLocaleDateString()}
                      </Descriptions.Item>
                    </Descriptions>

                    <div style={{ marginTop: 16, textAlign: 'right' }}>
                      <Button
                        type="primary"
                        onClick={() => {
                          setDetailModal(null);
                          handleEdit(detailModal);
                        }}
                      >
                        Edit Vendor
                      </Button>
                    </div>
                  </div>
                ),
              },
              {
                key: 'orders',
                label: 'Purchase Orders',
                children: (
                  <Table
                    dataSource={vendorPurchaseOrders?.data || []}
                    rowKey="id"
                    size="small"
                    columns={[
                      { title: 'PO Number', dataIndex: 'po_number' },
                      { title: 'Date', dataIndex: 'order_date' },
                      {
                        title: 'Total',
                        dataIndex: 'total',
                        render: (v) => `${v?.toFixed(2)} SAR`,
                      },
                      {
                        title: 'Status',
                        dataIndex: 'status',
                        render: (s) => <Tag>{s}</Tag>,
                      },
                    ]}
                    pagination={{ pageSize: 5 }}
                  />
                ),
              },
            ]}
          />
        )}
      </CustomModal>
    </div>
  );
}
