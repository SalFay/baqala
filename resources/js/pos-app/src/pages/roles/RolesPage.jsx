import { useState, useMemo } from 'react';
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
  Checkbox,
  Collapse,
  Divider,
  Card,
  Row,
  Col,
  ColorPicker,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EditOutlined,
  DeleteOutlined,
  CopyOutlined,
  CheckOutlined,
  CloseOutlined,
} from '@ant-design/icons';
import { roleService } from '../../api/services/role.service';

const { Title, Text } = Typography;

export default function RolesPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [editingRole, setEditingRole] = useState(null);
  const [selectedPermissions, setSelectedPermissions] = useState([]);
  const [form] = Form.useForm();

  const { data: roles, isLoading } = useQuery({
    queryKey: ['roles'],
    queryFn: roleService.getRoles,
  });

  const { data: permissions } = useQuery({
    queryKey: ['permissions'],
    queryFn: roleService.getPermissions,
  });

  const createMutation = useMutation({
    mutationFn: roleService.createRole,
    onSuccess: () => {
      message.success('Role created successfully');
      queryClient.invalidateQueries({ queryKey: ['roles'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create role');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => roleService.updateRole(id, data),
    onSuccess: () => {
      message.success('Role updated successfully');
      queryClient.invalidateQueries({ queryKey: ['roles'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update role');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: roleService.deleteRole,
    onSuccess: () => {
      message.success('Role deleted successfully');
      queryClient.invalidateQueries({ queryKey: ['roles'] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete role');
    },
  });

  const duplicateMutation = useMutation({
    mutationFn: roleService.duplicateRole,
    onSuccess: () => {
      message.success('Role duplicated successfully');
      queryClient.invalidateQueries({ queryKey: ['roles'] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to duplicate role');
    },
  });

  const allPermissionKeys = useMemo(() => {
    if (!permissions) return [];
    return Object.values(permissions).flatMap((group) => Object.keys(group.permissions));
  }, [permissions]);

  const handleOpenModal = (role) => {
    if (role) {
      setEditingRole(role);
      setSelectedPermissions(role.permissions || []);
      form.setFieldsValue({
        name: role.name,
        description: role.description,
        color: role.color,
      });
    } else {
      setEditingRole(null);
      setSelectedPermissions([]);
      form.resetFields();
    }
    setModalOpen(true);
  };

  const handleCloseModal = () => {
    setModalOpen(false);
    setEditingRole(null);
    setSelectedPermissions([]);
    form.resetFields();
  };

  const handleSubmit = (values) => {
    const data = {
      ...values,
      color: typeof values.color === 'object' ? values.color.toHexString() : values.color,
      permissions: selectedPermissions,
    };

    if (editingRole) {
      updateMutation.mutate({ id: editingRole.id, data });
    } else {
      createMutation.mutate(data);
    }
  };

  const handleTogglePermission = (permKey) => {
    setSelectedPermissions((prev) =>
      prev.includes(permKey) ? prev.filter((p) => p !== permKey) : [...prev, permKey]
    );
  };

  const handleSelectAllGroup = (groupKey) => {
    if (!permissions) return;
    const groupPermKeys = Object.keys(permissions[groupKey].permissions);
    const allSelected = groupPermKeys.every((k) => selectedPermissions.includes(k));

    if (allSelected) {
      setSelectedPermissions((prev) => prev.filter((p) => !groupPermKeys.includes(p)));
    } else {
      setSelectedPermissions((prev) => [...new Set([...prev, ...groupPermKeys])]);
    }
  };

  const handleSelectAll = () => {
    if (selectedPermissions.length === allPermissionKeys.length) {
      setSelectedPermissions([]);
    } else {
      setSelectedPermissions([...allPermissionKeys]);
    }
  };

  const isGroupSelected = (groupKey) => {
    if (!permissions) return false;
    const groupPermKeys = Object.keys(permissions[groupKey].permissions);
    return groupPermKeys.every((k) => selectedPermissions.includes(k));
  };

  const isGroupPartiallySelected = (groupKey) => {
    if (!permissions) return false;
    const groupPermKeys = Object.keys(permissions[groupKey].permissions);
    const selectedCount = groupPermKeys.filter((k) => selectedPermissions.includes(k)).length;
    return selectedCount > 0 && selectedCount < groupPermKeys.length;
  };

  const filteredRoles = roles?.filter(
    (role) => !search || role.name.toLowerCase().includes(search.toLowerCase())
  );

  const columns = [
    {
      title: 'Role',
      dataIndex: 'name',
      key: 'name',
      render: (name, record) => (
        <Space>
          {record.color && (
            <span
              style={{
                display: 'inline-block',
                width: 12,
                height: 12,
                borderRadius: '50%',
                backgroundColor: record.color,
              }}
            />
          )}
          <Text strong>{name}</Text>
        </Space>
      ),
    },
    {
      title: 'Description',
      dataIndex: 'description',
      key: 'description',
      ellipsis: true,
      render: (desc) => <Text type="secondary">{desc || '-'}</Text>,
    },
    {
      title: 'Permissions',
      key: 'permissions',
      render: (_, record) => (
        <Tag color="blue">{record.permissions?.length || 0} permissions</Tag>
      ),
    },
    {
      title: 'Users',
      dataIndex: 'users_count',
      key: 'users_count',
      width: 100,
      render: (count) => <Tag>{count || 0} users</Tag>,
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 160,
      render: (_, record) => (
        <Space>
          <Button type="text" icon={<EditOutlined />} onClick={() => handleOpenModal(record)} />
          <Button
            type="text"
            icon={<CopyOutlined />}
            onClick={() => duplicateMutation.mutate(record.id)}
            title="Duplicate"
          />
          <Popconfirm
            title="Delete Role"
            description="Are you sure? Users with this role will lose access."
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

  const collapseItems = permissions
    ? Object.entries(permissions).map(([key, group]) => ({
        key,
        label: (
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%' }}>
            <Space>
              <Checkbox
                checked={isGroupSelected(key)}
                indeterminate={isGroupPartiallySelected(key)}
                onClick={(e) => {
                  e.stopPropagation();
                  handleSelectAllGroup(key);
                }}
              />
              <Text strong>{group.title}</Text>
            </Space>
            <Text type="secondary">
              {Object.keys(group.permissions).filter((k) => selectedPermissions.includes(k)).length} /{' '}
              {Object.keys(group.permissions).length}
            </Text>
          </div>
        ),
        children: (
          <Row gutter={[16, 12]}>
            {Object.entries(group.permissions).map(([permKey, perm]) => (
              <Col span={12} key={permKey}>
                <Card
                  size="small"
                  style={{
                    cursor: 'pointer',
                    borderColor: selectedPermissions.includes(permKey) ? '#1890ff' : undefined,
                    backgroundColor: selectedPermissions.includes(permKey) ? '#e6f7ff' : undefined,
                  }}
                  onClick={() => handleTogglePermission(permKey)}
                >
                  <Space>
                    <Checkbox checked={selectedPermissions.includes(permKey)} />
                    <div>
                      <Text>{perm.title}</Text>
                      {perm.description && (
                        <div>
                          <Text type="secondary" style={{ fontSize: 12 }}>
                            {perm.description}
                          </Text>
                        </div>
                      )}
                    </div>
                  </Space>
                </Card>
              </Col>
            ))}
          </Row>
        ),
      }))
    : [];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>
          Roles & Permissions
        </Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => handleOpenModal()}>
          Add Role
        </Button>
      </div>

      <Card>
        <Input
          placeholder="Search roles..."
          prefix={<SearchOutlined />}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          style={{ width: 300, marginBottom: 16 }}
          allowClear
        />

        <Table
          dataSource={filteredRoles}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={false}
        />
      </Card>

      <Modal
        title={editingRole ? 'Edit Role' : 'New Role'}
        open={modalOpen}
        onCancel={handleCloseModal}
        footer={null}
        destroyOnClose
        width={800}
        styles={{ body: { maxHeight: '70vh', overflowY: 'auto' } }}
      >
        <Form form={form} layout="vertical" onFinish={handleSubmit} initialValues={{ permissions: [] }}>
          <Row gutter={16}>
            <Col span={16}>
              <Form.Item
                name="name"
                label="Role Name"
                rules={[{ required: true, message: 'Name is required' }]}
              >
                <Input placeholder="e.g., Store Manager" />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name="color" label="Color">
                <ColorPicker format="hex" />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item name="description" label="Description">
            <Input.TextArea rows={2} placeholder="Brief description of this role's responsibilities" />
          </Form.Item>

          <Divider>
            <Space>
              <Text strong>Permissions</Text>
              <Button size="small" onClick={handleSelectAll}>
                {selectedPermissions.length === allPermissionKeys.length ? (
                  <>
                    <CloseOutlined /> Deselect All
                  </>
                ) : (
                  <>
                    <CheckOutlined /> Select All
                  </>
                )}
              </Button>
            </Space>
          </Divider>

          <div style={{ marginBottom: 16 }}>
            <Text type="secondary">
              Selected: {selectedPermissions.length} / {allPermissionKeys.length} permissions
            </Text>
          </div>

          <Collapse items={collapseItems} defaultActiveKey={permissions ? Object.keys(permissions).slice(0, 2) : []} />

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 24 }}>
            <Button onClick={handleCloseModal}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createMutation.isPending || updateMutation.isPending}
            >
              {editingRole ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>
    </div>
  );
}
