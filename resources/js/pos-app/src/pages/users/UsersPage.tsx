import React, { useState } from 'react';
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
  Select,
  Card,
  Row,
  Col,
  Statistic,
  Descriptions,
  Tabs,
  Avatar,
  Transfer,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  UserOutlined,
  MailOutlined,
  PhoneOutlined,
  ShopOutlined,
} from '@ant-design/icons';
import { userService } from '../../api/services/user.service';
import { roleService } from '../../api/services/role.service';
import { storeService } from '../../api/services/store.service';
import type { User, Role, Store } from '../../types';

const { Title, Text } = Typography;

export default function UsersPage(): React.JSX.Element {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState<User | null>(null);
  const [storeModalOpen, setStoreModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [selectedStores, setSelectedStores] = useState<string[]>([]);
  const [primaryStoreId, setPrimaryStoreId] = useState<number | undefined>();
  const [form] = Form.useForm();

  const { data: usersData, isLoading } = useQuery({
    queryKey: ['users', { search, page }],
    queryFn: () =>
      userService.getUsers({
        search: search || undefined,
        page,
        per_page: 20,
      }),
  });

  const { data: userDetail } = useQuery({
    queryKey: ['user-detail', detailModal?.id],
    queryFn: () => userService.getUser(detailModal!.id),
    enabled: !!detailModal,
  });

  const { data: roles } = useQuery({
    queryKey: ['roles'],
    queryFn: roleService.getRoles,
  });

  const { data: stores } = useQuery({
    queryKey: ['stores'],
    queryFn: () => storeService.getStores(),
  });

  const createMutation = useMutation({
    mutationFn: userService.createUser,
    onSuccess: () => {
      message.success('User created successfully');
      queryClient.invalidateQueries({ queryKey: ['users'] });
      handleCloseModal();
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to create user');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: any }) =>
      userService.updateUser(id, data),
    onSuccess: () => {
      message.success('User updated successfully');
      queryClient.invalidateQueries({ queryKey: ['users'] });
      handleCloseModal();
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to update user');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: userService.deleteUser,
    onSuccess: () => {
      message.success('User deleted successfully');
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to delete user');
    },
  });

  const toggleStatusMutation = useMutation({
    mutationFn: userService.toggleStatus,
    onSuccess: () => {
      message.success('User status updated');
      queryClient.invalidateQueries({ queryKey: ['users'] });
      if (detailModal) {
        queryClient.invalidateQueries({ queryKey: ['user-detail', detailModal.id] });
      }
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to update status');
    },
  });

  const assignStoresMutation = useMutation({
    mutationFn: ({ userId, storeIds, primaryStoreId }: { userId: number; storeIds: number[]; primaryStoreId?: number }) =>
      userService.assignStores(userId, storeIds, primaryStoreId),
    onSuccess: () => {
      message.success('Stores assigned successfully');
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.invalidateQueries({ queryKey: ['user-detail'] });
      setStoreModalOpen(false);
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to assign stores');
    },
  });

  const handleOpenModal = (user?: User): void => {
    if (user) {
      setEditingUser(user);
      form.setFieldsValue({
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone,
        role_id: user.role_id,
      });
    } else {
      setEditingUser(null);
      form.resetFields();
    }
    setModalOpen(true);
  };

  const handleCloseModal = (): void => {
    setModalOpen(false);
    setEditingUser(null);
    form.resetFields();
  };

  const handleSubmit = (values: any): void => {
    if (editingUser) {
      if (!values.password) {
        delete values.password;
        delete values.password_confirmation;
      }
      updateMutation.mutate({ id: editingUser.id, data: values });
    } else {
      createMutation.mutate(values);
    }
  };

  const handleOpenStoreModal = (user: User): void => {
    setEditingUser(user);
    if (userDetail?.stores) {
      setSelectedStores(userDetail.stores.map((s: Store) => s.id.toString()));
      const primaryStore = userDetail.stores.find((s: any) => s.pivot?.is_primary);
      setPrimaryStoreId(primaryStore?.id);
    } else {
      setSelectedStores([]);
      setPrimaryStoreId(undefined);
    }
    setStoreModalOpen(true);
  };

  const handleAssignStores = (): void => {
    if (editingUser) {
      assignStoresMutation.mutate({
        userId: editingUser.id,
        storeIds: selectedStores.map(Number),
        primaryStoreId,
      });
    }
  };

  const columns = [
    {
      title: 'User',
      key: 'user',
      render: (_: unknown, record: User) => (
        <Space>
          <Avatar icon={<UserOutlined />} />
          <div>
            <Text strong>{record.name}</Text>
            <br />
            <Text type="secondary" style={{ fontSize: 12 }}>
              {record.email}
            </Text>
          </div>
        </Space>
      ),
    },
    {
      title: 'Phone',
      dataIndex: 'phone',
      key: 'phone',
      render: (phone: string) =>
        phone ? (
          <Space>
            <PhoneOutlined />
            {phone}
          </Space>
        ) : (
          '-'
        ),
    },
    {
      title: 'Role',
      key: 'role',
      render: (_: unknown, record: User) => (
        <Tag color={record.role?.color || 'blue'}>{record.role?.name}</Tag>
      ),
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status: string, record: User) => (
        <Tag
          color={status === 'active' ? 'green' : 'default'}
          style={{ cursor: 'pointer' }}
          onClick={() => toggleStatusMutation.mutate(record.id)}
        >
          {status === 'active' ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 150,
      render: (_: unknown, record: User) => (
        <Space>
          <Button
            type="text"
            icon={<EyeOutlined />}
            onClick={() => setDetailModal(record)}
          />
          <Button
            type="text"
            icon={<EditOutlined />}
            onClick={() => handleOpenModal(record)}
          />
          <Popconfirm
            title="Delete User"
            description="Are you sure you want to delete this user?"
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
          Users
        </Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => handleOpenModal()}>
          Add User
        </Button>
      </div>

      <Card>
        <Input
          placeholder="Search users by name, email, or phone..."
          prefix={<SearchOutlined />}
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          style={{ width: 400, marginBottom: 16 }}
          allowClear
        />

        <Table
          dataSource={usersData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: usersData?.current_page || page,
            total: usersData?.total,
            pageSize: usersData?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} users`,
          }}
        />
      </Card>

      {/* Create/Edit Modal */}
      <Modal
        title={editingUser ? 'Edit User' : 'New User'}
        open={modalOpen}
        onCancel={handleCloseModal}
        footer={null}
        destroyOnClose
        width={500}
      >
        <Form form={form} layout="vertical" onFinish={handleSubmit}>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="first_name"
                label="First Name"
                rules={[{ required: true, message: 'First name is required' }]}
              >
                <Input prefix={<UserOutlined />} placeholder="First name" />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="last_name"
                label="Last Name"
                rules={[{ required: true, message: 'Last name is required' }]}
              >
                <Input placeholder="Last name" />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item
            name="email"
            label="Email"
            rules={[
              { required: true, message: 'Email is required' },
              { type: 'email', message: 'Invalid email' },
            ]}
          >
            <Input prefix={<MailOutlined />} placeholder="Email address" />
          </Form.Item>

          <Form.Item name="phone" label="Phone">
            <Input prefix={<PhoneOutlined />} placeholder="Phone number" />
          </Form.Item>

          <Form.Item
            name="role_id"
            label="Role"
            rules={[{ required: true, message: 'Role is required' }]}
          >
            <Select placeholder="Select role">
              {roles?.map((role: Role) => (
                <Select.Option key={role.id} value={role.id}>
                  <Space>
                    {role.color && (
                      <span
                        style={{
                          display: 'inline-block',
                          width: 10,
                          height: 10,
                          borderRadius: '50%',
                          backgroundColor: role.color,
                        }}
                      />
                    )}
                    {role.name}
                  </Space>
                </Select.Option>
              ))}
            </Select>
          </Form.Item>

          <Form.Item
            name="password"
            label="Password"
            rules={editingUser ? [] : [{ required: true, message: 'Password is required' }]}
          >
            <Input.Password placeholder={editingUser ? 'Leave blank to keep current' : 'Password'} />
          </Form.Item>

          <Form.Item
            name="password_confirmation"
            label="Confirm Password"
            dependencies={['password']}
            rules={[
              ({ getFieldValue }) => ({
                validator(_, value) {
                  if (!getFieldValue('password') || getFieldValue('password') === value) {
                    return Promise.resolve();
                  }
                  return Promise.reject(new Error('Passwords do not match'));
                },
              }),
            ]}
          >
            <Input.Password placeholder="Confirm password" />
          </Form.Item>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={handleCloseModal}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createMutation.isPending || updateMutation.isPending}
            >
              {editingUser ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>

      {/* Detail Modal */}
      <Modal
        title={`User Details - ${detailModal?.name}`}
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        footer={[
          <Button key="close" onClick={() => setDetailModal(null)}>
            Close
          </Button>,
          <Button
            key="stores"
            icon={<ShopOutlined />}
            onClick={() => {
              if (detailModal) handleOpenStoreModal(detailModal);
            }}
          >
            Assign Stores
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
        width={700}
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
                      <Col span={8}>
                        <Card>
                          <Statistic title="Role" value={userDetail?.role?.name || '-'} />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic
                            title="Status"
                            value={userDetail?.status === 'active' ? 'Active' : 'Inactive'}
                            valueStyle={{
                              color: userDetail?.status === 'active' ? '#52c41a' : '#999',
                            }}
                          />
                        </Card>
                      </Col>
                      <Col span={8}>
                        <Card>
                          <Statistic title="Assigned Stores" value={userDetail?.stores?.length || 0} />
                        </Card>
                      </Col>
                    </Row>

                    <Descriptions bordered column={1}>
                      <Descriptions.Item label="Name">{userDetail?.name || detailModal.name}</Descriptions.Item>
                      <Descriptions.Item label="Email">{userDetail?.email || detailModal.email}</Descriptions.Item>
                      <Descriptions.Item label="Phone">{userDetail?.phone || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Role">
                        <Tag color={userDetail?.role?.color || 'blue'}>
                          {userDetail?.role?.name || '-'}
                        </Tag>
                      </Descriptions.Item>
                      <Descriptions.Item label="Status">
                        <Tag color={userDetail?.status === 'active' ? 'green' : 'default'}>
                          {userDetail?.status}
                        </Tag>
                      </Descriptions.Item>
                      <Descriptions.Item label="Created">
                        {new Date(detailModal.created_at).toLocaleDateString()}
                      </Descriptions.Item>
                    </Descriptions>
                  </div>
                ),
              },
              {
                key: 'stores',
                label: 'Assigned Stores',
                children: (
                  <Table
                    dataSource={userDetail?.stores || []}
                    rowKey="id"
                    size="small"
                    columns={[
                      {
                        title: 'Code',
                        dataIndex: 'code',
                        width: 100,
                      },
                      {
                        title: 'Name',
                        dataIndex: 'name',
                        render: (name: string, record: any) => (
                          <Space>
                            <ShopOutlined />
                            {name}
                            {record.pivot?.is_primary && (
                              <Tag color="blue">Primary</Tag>
                            )}
                          </Space>
                        ),
                      },
                      { title: 'City', dataIndex: 'city' },
                      {
                        title: 'Status',
                        dataIndex: 'is_active',
                        render: (active: boolean) => (
                          <Tag color={active ? 'green' : 'default'}>
                            {active ? 'Active' : 'Inactive'}
                          </Tag>
                        ),
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

      {/* Store Assignment Modal */}
      <Modal
        title={`Assign Stores - ${editingUser?.name}`}
        open={storeModalOpen}
        onCancel={() => setStoreModalOpen(false)}
        onOk={handleAssignStores}
        okText="Save"
        confirmLoading={assignStoresMutation.isPending}
        width={600}
      >
        <div style={{ marginBottom: 16 }}>
          <Text strong>Select Stores:</Text>
        </div>
        <Transfer
          dataSource={stores?.map((s: Store) => ({
            key: s.id.toString(),
            title: `${s.name} (${s.code})`,
            description: s.city,
          })) || []}
          titles={['Available', 'Assigned']}
          targetKeys={selectedStores}
          onChange={(keys) => setSelectedStores(keys as string[])}
          render={(item) => item.title}
          listStyle={{ width: 230, height: 300 }}
        />
        {selectedStores.length > 0 && (
          <div style={{ marginTop: 16 }}>
            <Text strong>Primary Store:</Text>
            <Select
              value={primaryStoreId}
              onChange={setPrimaryStoreId}
              style={{ width: '100%', marginTop: 8 }}
              placeholder="Select primary store"
            >
              {stores
                ?.filter((s: Store) => selectedStores.includes(s.id.toString()))
                .map((s: Store) => (
                  <Select.Option key={s.id} value={s.id}>
                    {s.name} ({s.code})
                  </Select.Option>
                ))}
            </Select>
          </div>
        )}
      </Modal>
    </div>
  );
}
