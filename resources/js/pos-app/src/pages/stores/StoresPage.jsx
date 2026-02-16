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
  Switch,
  Typography,
} from 'antd';
import {
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  ShopOutlined,
  UserOutlined,
  EnvironmentOutlined,
  PhoneOutlined,
} from '@ant-design/icons';
import { DataGridTable } from '../../Components/DataGridTable';
import CustomModal from '../../Components/CustomModal';
import { storeService } from '../../api/services/store.service';
import {
  TIMEZONE_OPTIONS,
  CURRENCY_OPTIONS,
  DEFAULT_CURRENCY,
  DEFAULT_TIMEZONE,
  DEFAULT_LOW_STOCK_THRESHOLD,
} from '../../constants';

const { Text } = Typography;

export default function StoresPage() {
  const gridRef = useRef(null);
  const [form] = Form.useForm();
  const [modalVisible, setModalVisible] = useState(false);
  const [detailModal, setDetailModal] = useState(null);
  const [editingRecord, setEditingRecord] = useState(null);
  const [loading, setLoading] = useState(false);

  // Store detail queries
  const { data: storeStats } = useQuery({
    queryKey: ['store-stats', detailModal?.id],
    queryFn: () => storeService.getStoreStats(detailModal.id),
    enabled: !!detailModal,
  });

  const { data: storeInventory } = useQuery({
    queryKey: ['store-inventory', detailModal?.id, { low_stock: true }],
    queryFn: () => storeService.getStoreInventory(detailModal.id, { low_stock: true, per_page: 10 }),
    enabled: !!detailModal,
  });

  // Fetch data for grid - stores don't support pagination
  const fetchData = useCallback(async (params) => {
    const result = await storeService.getStores({ search: params.search || undefined });
    const data = Array.isArray(result) ? result : (result.data || []);
    return {
      data,
      total: data.length,
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
    form.setFieldsValue({
      is_active: true,
      is_warehouse: false,
      currency_code: DEFAULT_CURRENCY,
      timezone: DEFAULT_TIMEZONE,
    });
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
        await storeService.updateStore(editingRecord.id, values);
        message.success('Store updated successfully');
      } else {
        await storeService.createStore(values);
        message.success('Store created successfully');
      }
      handleCancel();
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Operation failed');
    } finally {
      setLoading(false);
    }
  }, [editingRecord, handleCancel, handleRefresh]);

  // Delete store
  const handleDelete = useCallback(async (id) => {
    try {
      await storeService.deleteStore(id);
      message.success('Store deleted successfully');
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Delete failed');
    }
  }, [handleRefresh]);

  // Toggle status
  const handleToggleStatus = useCallback(async (id) => {
    try {
      await storeService.toggleStatus(id);
      message.success('Store status updated');
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Failed to update status');
    }
  }, [handleRefresh]);

  // Column definitions for AG Grid (filterType is used by GlobalFilter)
  const columns = useMemo(() => [
    {
      field: 'code',
      headerName: 'Code',
      minWidth: 100,
      maxWidth: 100,
      filterType: 'text',
      cellRenderer: (params) => <Tag>{params.value}</Tag>,
    },
    {
      field: 'name',
      headerName: 'Store',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
      cellRenderer: (params) => (
        <Space>
          <ShopOutlined style={{ fontSize: 18 }} />
          <div>
            <Text strong>{params.value}</Text>
            {params.data.name_ar && (
              <div>
                <Text type="secondary" style={{ fontSize: 12 }}>
                  {params.data.name_ar}
                </Text>
              </div>
            )}
          </div>
          {params.data.is_warehouse && <Tag color="purple">Warehouse</Tag>}
        </Space>
      ),
    },
    {
      field: 'city',
      headerName: 'Location',
      minWidth: 180,
      flex: 1,
      filterType: 'text',
      cellRenderer: (params) => (
        <Space direction="vertical" size={0}>
          {params.value && (
            <Space>
              <EnvironmentOutlined />
              {params.value}
            </Space>
          )}
          {params.data.phone && (
            <Space>
              <PhoneOutlined />
              {params.data.phone}
            </Space>
          )}
        </Space>
      ),
    },
    {
      field: 'manager_name',
      headerName: 'Manager',
      minWidth: 140,
      flex: 1,
      filterType: 'text',
      cellRenderer: (params) => params.value ? (
        <Space>
          <UserOutlined />
          {params.value}
        </Space>
      ) : '-',
    },
    {
      field: 'is_active',
      headerName: 'Status',
      minWidth: 100,
      flex: 1,
      filterType: 'select',
      filterOptions: [
        { value: true, label: 'Active' },
        { value: false, label: 'Inactive' },
      ],
      cellRenderer: (params) => (
        <Tag
          color={params.value ? 'green' : 'default'}
          style={{ cursor: 'pointer' }}
          onClick={() => handleToggleStatus(params.data.id)}
        >
          {params.value ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
  ], [handleToggleStatus]);

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
          title="Delete this store?"
          description="This will affect all related inventory."
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
        title="Stores"
        onAdd={handleCreate}
        addButtonText="Add Store"
        searchPlaceholder="Search stores..."
        height={500}
        pageSize={50}
        pagination={false}
        showFilter={false}
      />

      {/* Store Form Modal */}
      <CustomModal
        open={modalVisible}
        onCancel={handleCancel}
        title={editingRecord ? 'Edit Store' : 'Create Store'}
        width={650}
        footer={null}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
        >
          <Row gutter={16}>
            <Col span={8}>
              <Form.Item
                name="code"
                label="Store Code"
                rules={[{ required: true, message: 'Code is required' }]}
              >
                <Input placeholder="e.g., STR001" />
              </Form.Item>
            </Col>
            <Col span={16}>
              <Form.Item
                name="name"
                label="Store Name"
                rules={[{ required: true, message: 'Name is required' }]}
              >
                <Input placeholder="Store name" />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item name="name_ar" label="Arabic Name">
            <Input dir="rtl" placeholder="Store name in Arabic" />
          </Form.Item>

          <Form.Item name="address" label="Address">
            <Input.TextArea rows={2} placeholder="Full address" />
          </Form.Item>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="city" label="City">
                <Input placeholder="City" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="phone" label="Phone">
                <Input placeholder="Phone number" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="email" label="Email">
                <Input type="email" placeholder="Store email" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="manager_name" label="Manager Name">
                <Input placeholder="Manager name" />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="timezone" label="Timezone">
                <Select options={TIMEZONE_OPTIONS} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="currency_code" label="Currency">
                <Select options={CURRENCY_OPTIONS} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="is_active" label="Active" valuePropName="checked">
                <Switch checkedChildren="Yes" unCheckedChildren="No" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="is_warehouse" label="Is Warehouse" valuePropName="checked">
                <Switch checkedChildren="Yes" unCheckedChildren="No" />
              </Form.Item>
            </Col>
          </Row>

          {/* Form Actions */}
          <div style={{ textAlign: 'right', marginTop: 24, paddingTop: 16, borderTop: '1px solid #f0f0f0' }}>
            <Space>
              <Button onClick={handleCancel}>Cancel</Button>
              <Button type="primary" htmlType="submit" loading={loading}>
                {editingRecord ? 'Update' : 'Create'} Store
              </Button>
            </Space>
          </div>
        </Form>
      </CustomModal>

      {/* Detail Modal */}
      <CustomModal
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        title={
          <Space>
            <ShopOutlined />
            {detailModal?.name || ''}
          </Space>
        }
        width={800}
        footer={null}
      >
        {detailModal && (
          <Tabs
            items={[
              {
                key: 'overview',
                label: 'Overview',
                children: (
                  <div>
                    <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                      <Col span={8}>
                        <Card>
                          <Statistic
                            title="Total Products"
                            value={storeStats?.total_products || 0}
                            prefix={<ShopOutlined />}
                          />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic title="Total Orders" value={storeStats?.total_orders || 0} />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic title="Today's Orders" value={storeStats?.today_orders || 0} />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic
                            title="Today's Sales"
                            value={storeStats?.today_sales || 0}
                            precision={2}
                            suffix="SAR"
                          />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic
                            title="Low Stock Items"
                            value={storeStats?.low_stock_items || 0}
                            valueStyle={{
                              color: (storeStats?.low_stock_items || 0) > 0 ? '#cf1322' : 'inherit',
                            }}
                          />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic title="Active Users" value={storeStats?.active_users || 0} />
                        </Card>
                      </Col>
                    </Row>

                    <Descriptions bordered column={2}>
                      <Descriptions.Item label="Code">{detailModal.code}</Descriptions.Item>
                      <Descriptions.Item label="Status">
                        <Tag color={detailModal.is_active ? 'green' : 'default'}>
                          {detailModal.is_active ? 'Active' : 'Inactive'}
                        </Tag>
                      </Descriptions.Item>
                      <Descriptions.Item label="Name">{detailModal.name}</Descriptions.Item>
                      <Descriptions.Item label="Arabic Name">{detailModal.name_ar || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Address" span={2}>
                        {detailModal.address || '-'}
                      </Descriptions.Item>
                      <Descriptions.Item label="City">{detailModal.city || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Phone">{detailModal.phone || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Email">{detailModal.email || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Manager">{detailModal.manager_name || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Type">
                        {detailModal.is_warehouse ? (
                          <Tag color="purple">Warehouse</Tag>
                        ) : (
                          <Tag color="blue">Retail Store</Tag>
                        )}
                      </Descriptions.Item>
                      <Descriptions.Item label="Timezone">{detailModal.timezone || 'Asia/Riyadh'}</Descriptions.Item>
                    </Descriptions>

                    <div style={{ marginTop: 16, textAlign: 'right' }}>
                      <Button
                        type="primary"
                        onClick={() => {
                          setDetailModal(null);
                          handleEdit(detailModal);
                        }}
                      >
                        Edit Store
                      </Button>
                    </div>
                  </div>
                ),
              },
              {
                key: 'low-stock',
                label: (
                  <Space>
                    Low Stock
                    {(storeStats?.low_stock_items || 0) > 0 && (
                      <Tag color="red">{storeStats?.low_stock_items}</Tag>
                    )}
                  </Space>
                ),
                children: (
                  <Table
                    dataSource={storeInventory?.data || []}
                    rowKey="id"
                    size="small"
                    columns={[
                      {
                        title: 'Product',
                        key: 'product',
                        render: (_, record) => (
                          <Space>
                            <Text strong>{record.product?.name}</Text>
                            {record.product_variant && <Tag>{record.product_variant.name}</Tag>}
                          </Space>
                        ),
                      },
                      {
                        title: 'SKU',
                        key: 'sku',
                        render: (_, record) =>
                          record.product_variant?.sku || record.product?.sku || '-',
                      },
                      {
                        title: 'Quantity',
                        dataIndex: 'quantity',
                        render: (qty, record) => (
                          <Text
                            type={qty <= (record.low_stock_threshold || DEFAULT_LOW_STOCK_THRESHOLD) ? 'danger' : undefined}
                            strong
                          >
                            {qty}
                          </Text>
                        ),
                      },
                      {
                        title: 'Threshold',
                        dataIndex: 'low_stock_threshold',
                        render: (val) => val || DEFAULT_LOW_STOCK_THRESHOLD,
                      },
                    ]}
                    pagination={false}
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
