import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, Table, Button, Space, Modal, Form, Input, Select, message, Tag, Popconfirm, Avatar } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, EditOutlined, DeleteOutlined, UserOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatDateTime } from '@/Helpers/formatters';

export default function SettingsUsers({ users = [], roles = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'cashier',
        is_active: true,
    });

    const openCreateModal = () => {
        reset();
        setEditing(null);
        setModalOpen(true);
    };

    const openEditModal = (user) => {
        setEditing(user);
        setData({
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role || 'cashier',
            is_active: user.is_active,
        });
        setModalOpen(true);
    };

    const handleSubmit = () => {
        if (editing) {
            put(`/settings/users/${editing.id}`, {
                onSuccess: () => {
                    message.success('User updated');
                    setModalOpen(false);
                    reset();
                },
            });
        } else {
            post('/settings/users', {
                onSuccess: () => {
                    message.success('User created');
                    setModalOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id) => {
        router.delete(`/settings/users/${id}`, {
            onSuccess: () => message.success('User deleted'),
        });
    };

    const roleOptions = roles.length > 0 ? roles : [
        { label: 'Admin', value: 'admin' },
        { label: 'Manager', value: 'manager' },
        { label: 'Cashier', value: 'cashier' },
        { label: 'Inventory', value: 'inventory' },
    ];

    const columns = [
        {
            title: 'User',
            key: 'user',
            render: (_, record) => (
                <Space>
                    <Avatar icon={<UserOutlined />} />
                    <div>
                        <div>{record.first_name} {record.last_name}</div>
                        <div style={{ fontSize: 12, color: '#888' }}>{record.email}</div>
                    </div>
                </Space>
            ),
        },
        {
            title: 'Role',
            dataIndex: 'role',
            key: 'role',
            render: (role) => {
                const colors = { admin: 'red', manager: 'blue', cashier: 'green', inventory: 'orange' };
                return <Tag color={colors[role] || 'default'}>{role}</Tag>;
            },
        },
        {
            title: 'Status',
            dataIndex: 'is_active',
            key: 'status',
            render: (active) => <Tag color={active ? 'green' : 'default'}>{active ? 'Active' : 'Inactive'}</Tag>,
        },
        {
            title: 'Last Login',
            dataIndex: 'last_login_at',
            key: 'last_login',
            render: (date) => date ? formatDateTime(date) : 'Never',
        },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Space>
                    <Button icon={<EditOutlined />} size="small" onClick={() => openEditModal(record)} />
                    <Popconfirm title="Delete this user?" onConfirm={() => handleDelete(record.id)}>
                        <Button danger icon={<DeleteOutlined />} size="small" />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Users - Settings" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/settings')}>Back to Settings</Button>
            </div>
            <Card
                title="Users"
                extra={<Button type="primary" icon={<PlusOutlined />} onClick={openCreateModal}>Add User</Button>}
            >
                <Table dataSource={users} columns={columns} rowKey="id" pagination={false} />
            </Card>

            <Modal
                title={editing ? 'Edit User' : 'Add User'}
                open={modalOpen}
                onCancel={() => setModalOpen(false)}
                onOk={handleSubmit}
                confirmLoading={processing}
                width={500}
            >
                <Form layout="vertical">
                    <Space style={{ width: '100%' }} direction="vertical" size={0}>
                        <Space style={{ width: '100%' }}>
                            <Form.Item label="First Name" required validateStatus={errors.first_name ? 'error' : ''} help={errors.first_name} style={{ flex: 1 }}>
                                <Input value={data.first_name} onChange={(e) => setData('first_name', e.target.value)} />
                            </Form.Item>
                            <Form.Item label="Last Name" required validateStatus={errors.last_name ? 'error' : ''} help={errors.last_name} style={{ flex: 1 }}>
                                <Input value={data.last_name} onChange={(e) => setData('last_name', e.target.value)} />
                            </Form.Item>
                        </Space>
                    </Space>
                    <Form.Item label="Email" required validateStatus={errors.email ? 'error' : ''} help={errors.email}>
                        <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </Form.Item>
                    <Form.Item label={editing ? 'New Password (leave blank to keep current)' : 'Password'} required={!editing} validateStatus={errors.password ? 'error' : ''} help={errors.password}>
                        <Input.Password value={data.password} onChange={(e) => setData('password', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Confirm Password" required={!editing || data.password}>
                        <Input.Password value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Role">
                        <Select value={data.role} onChange={(v) => setData('role', v)} options={roleOptions.map(r => typeof r === 'string' ? { label: r, value: r } : r)} />
                    </Form.Item>
                    <Form.Item label="Status">
                        <Select value={data.is_active} onChange={(v) => setData('is_active', v)} options={[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]} />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
}
