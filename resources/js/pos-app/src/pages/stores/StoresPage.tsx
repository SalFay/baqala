import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Button,
  Input,
  Space,
  Tag,
  Typography,
  message,
  Popconfirm,
  Modal,
  Form,
  Switch,
  Card,
  Row,
  Col,
  Statistic,
  Tabs,
  Descriptions,
  Select,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EditOutlined,
  DeleteOutlined,
  ShopOutlined,
  BarChartOutlined,
  EyeOutlined,
  UserOutlined,
  EnvironmentOutlined,
  PhoneOutlined,
} from '@ant-design/icons';
import { storeService } from '../../api/services/store.service';
import { userService } from '../../api/services/user.service';
import type { Store, User } from '../../types';

const { Title, Text } = Typography;

export default function StoresPage(): React.JSX.Element {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState<Store | null>(null);
  const [editingStore, setEditingStore] = useState<Store | null>(null);
  const [form] = Form.useForm();

  const { data: stores, isLoading } = useQuery({
    queryKey: ['stores', { search }],
    queryFn: () => storeService.getStores({ search: search || undefined }),
  });

  const { data: storeDetail } = useQuery({
    queryKey: ['store-detail', detailModal?.id],
    queryFn: () => storeService.getStore(detailModal!.id),
    enabled: !!detailModal,
  });

  const { data: storeStats } = useQuery({
    queryKey: ['store-stats', detailModal?.id],
    queryFn: () => storeService.getStoreStats(detailModal!.id),
    enabled: !!detailModal,
  });

  const { data: storeInventory } = useQuery({
    queryKey: ['store-inventory', detailModal?.id, { low_stock: true }],
    queryFn: () => storeService.getStoreInventory(detailModal!.id, { low_stock: true, per_page: 10 }),
    enabled: !!detailModal,
  });

  const { data: users } = useQuery({
    queryKey: ['users-list'],
    queryFn: () => userService.getUsers({ per_page: 100 }),
  });

  const createMutation = useMutation({
    mutationFn: storeService.createStore,
    onSuccess: () => {
      message.success('Store created successfully');
      queryClient.invalidateQueries({ queryKey: ['stores'] });
      handleCloseModal();
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to create store');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Store> }) =>
      storeService.updateStore(id, data),
    onSuccess: () => {
      message.success('Store updated successfully');
      queryClient.invalidateQueries({ queryKey: ['stores'] });
      handleCloseModal();
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to update store');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: storeService.deleteStore,
    onSuccess: () => {
      message.success('Store deleted successfully');
      queryClient.invalidateQueries({ queryKey: ['stores'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to delete store');
    },
  });

  const toggleStatusMutation = useMutation({
    mutationFn: storeService.toggleStatus,
    onSuccess: () => {
      message.success('Store status updated');
      queryClient.invalidateQueries({ queryKey: ['stores'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to update status');
    },
  });

  const handleOpenModal = (store?: Store): void => {
    if (store) {
      setEditingStore(store);
      form.setFieldsValue(store);
    } else {
      setEditingStore(null);
      form.resetFields();
      form.setFieldsValue({
        is_active: true,
        is_warehouse: false,
        currency_code: 'SAR',
        timezone: 'Asia/Riyadh',
      });
    }
    setModalOpen(true);
  };

  const handleCloseModal = (): void => {
    setModalOpen(false);
    setEditingStore(null);
    form.resetFields();
  };

  const handleSubmit = (values: any): void => {
    if (editingStore) {
      updateMutation.mutate({ id: editingStore.id, data: values });
    } else {
      createMutation.mutate(values);
    }
  };

  const columns = [
    {
      title: 'Code',
      dataIndex: 'code',
      key: 'code',
      width: 100,
      render: (code: string) => <Tag>{code}</Tag>,
    },
    {
      title: 'Store',
      dataIndex: 'name',
      key: 'name',
      render: (name: string, record: Store) => (
        <Space>
          <ShopOutlined style={{ fontSize: 18 }} />
          <div>
            <Text strong>{name}</Text>
            {record.name_ar && (
              <div>
                <Text type="secondary" style={{ fontSize: 12 }}>
                  {record.name_ar}
                </Text>
              </div>
            )}
          </div>
          {record.is_warehouse && <Tag color="purple">Warehouse</Tag>}
        </Space>
      ),
    },
    {
      title: 'Location',
      key: 'location',
      render: (_: unknown, record: Store) => (
        <Space direction="vertical" size={0}>
          {record.city && (
            <Space>
              <EnvironmentOutlined />
              {record.city}
            </Space>
          )}
          {record.phone && (
            <Space>
              <PhoneOutlined />
              {record.phone}
            </Space>
          )}
        </Space>
      ),
    },
    {
      title: 'Manager',
      dataIndex: 'manager_name',
      key: 'manager_name',
      render: (name: string) =>
        name ? (
          <Space>
            <UserOutlined />
            {name}
          </Space>
        ) : (
          '-'
        ),
    },
    {
      title: 'Status',
      dataIndex: 'is_active',
      key: 'is_active',
      render: (isActive: boolean, record: Store) => (
        <Tag
          color={isActive ? 'green' : 'default'}
          style={{ cursor: 'pointer' }}
          onClick={() => toggleStatusMutation.mutate(record.id)}
        >
          {isActive ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 150,
      render: (_: unknown, record: Store) => (
        <Space>
          <Button type="text" icon={<EyeOutlined />} onClick={() => setDetailModal(record)} />
          <Button type="text" icon={<EditOutlined />} onClick={() => handleOpenModal(record)} />
          <Popconfirm
            title="Delete Store"
            description="Are you sure? This will affect all related inventory."
            onConfirm={() => deleteMutation.mutate(record.id)}
            okText="Yes"
            cancelText="No"
          >
            <Button type="text" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>
          Stores
        </Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => handleOpenModal()}>
          Add Store
        </Button>
      </div>

      <Card>
        <Input
          placeholder="Search stores by name, code, or city..."
          prefix={<SearchOutlined />}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          style={{ width: 400, marginBottom: 16 }}
          allowClear
        />

        <Table
          dataSource={stores}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={false}
        />
      </Card>

      {/* Create/Edit Modal */}
      <Modal
        title={editingStore ? 'Edit Store' : 'New Store'}
        open={modalOpen}
        onCancel={handleCloseModal}
        footer={null}
        destroyOnClose
        width={650}
      >
        <Form form={form} layout="vertical" onFinish={handleSubmit}>
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
                <Select>
                  <Select.Option value="Asia/Riyadh">Asia/Riyadh (UTC+3)</Select.Option>
                  <Select.Option value="Asia/Dubai">Asia/Dubai (UTC+4)</Select.Option>
                  <Select.Option value="UTC">UTC</Select.Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="currency_code" label="Currency">
                <Select>
                  <Select.Option value="SAR">SAR - Saudi Riyal</Select.Option>
                  <Select.Option value="AED">AED - UAE Dirham</Select.Option>
                  <Select.Option value="USD">USD - US Dollar</Select.Option>
                </Select>
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

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={handleCloseModal}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createMutation.isPending || updateMutation.isPending}
            >
              {editingStore ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>

      {/* Detail Modal */}
      <Modal
        title={
          <Space>
            <ShopOutlined />
            {detailModal?.name}
          </Space>
        }
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        footer={[
          <Button key="close" onClick={() => setDetailModal(null)}>
            Close
          </Button>,
          <Button
            key="edit"
            type="primary"
            onClick={() => {
              setDetailModal(null);
              handleOpenModal(detailModal!);
            }}
          >
            Edit
          </Button>,
        ]}
        width={800}
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
                        render: (_: unknown, record: any) => (
                          <Space>
                            <Text strong>{record.product?.name}</Text>
                            {record.product_variant && (
                              <Tag>{record.product_variant.name}</Tag>
                            )}
                          </Space>
                        ),
                      },
                      {
                        title: 'SKU',
                        key: 'sku',
                        render: (_: unknown, record: any) =>
                          record.product_variant?.sku || record.product?.sku || '-',
                      },
                      {
                        title: 'Quantity',
                        dataIndex: 'quantity',
                        render: (qty: number, record: any) => (
                          <Text
                            type={qty <= (record.low_stock_threshold || 5) ? 'danger' : undefined}
                            strong
                          >
                            {qty}
                          </Text>
                        ),
                      },
                      {
                        title: 'Threshold',
                        dataIndex: 'low_stock_threshold',
                        render: (val: number) => val || 5,
                      },
                    ]}
                    pagination={false}
                  />
                ),
              },
            ]}
          />
        )}
      </Modal>
    </div>
  );
}
