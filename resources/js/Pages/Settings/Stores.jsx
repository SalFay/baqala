import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, Table, Button, Space, Modal, Form, Input, Select, message, Tag, Popconfirm } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function SettingsStores({ stores = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editingStore, setEditingStore] = useState(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        name: '',
        code: '',
        address: '',
        phone: '',
        email: '',
        is_active: true,
    });

    const openCreateModal = () => {
        reset();
        setEditingStore(null);
        setModalOpen(true);
    };

    const openEditModal = (store) => {
        setEditingStore(store);
        setData({
            name: store.name,
            code: store.code || '',
            address: store.address || '',
            phone: store.phone || '',
            email: store.email || '',
            is_active: store.is_active,
        });
        setModalOpen(true);
    };

    const handleSubmit = () => {
        if (editingStore) {
            put(`/settings/stores/${editingStore.id}`, {
                onSuccess: () => {
                    message.success('Store updated');
                    setModalOpen(false);
                    reset();
                },
            });
        } else {
            post('/settings/stores', {
                onSuccess: () => {
                    message.success('Store created');
                    setModalOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id) => {
        router.delete(`/settings/stores/${id}`, {
            onSuccess: () => message.success('Store deleted'),
        });
    };

    const columns = [
        { title: 'Name', dataIndex: 'name', key: 'name' },
        { title: 'Code', dataIndex: 'code', key: 'code' },
        { title: 'Phone', dataIndex: 'phone', key: 'phone' },
        { title: 'Email', dataIndex: 'email', key: 'email' },
        {
            title: 'Status',
            dataIndex: 'is_active',
            key: 'status',
            render: (active) => <Tag color={active ? 'green' : 'default'}>{active ? 'Active' : 'Inactive'}</Tag>,
        },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Space>
                    <Button icon={<EditOutlined />} size="small" onClick={() => openEditModal(record)} />
                    <Popconfirm title="Delete this store?" onConfirm={() => handleDelete(record.id)}>
                        <Button danger icon={<DeleteOutlined />} size="small" />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Stores - Settings" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/settings')}>Back to Settings</Button>
            </div>
            <Card
                title="Stores"
                extra={<Button type="primary" icon={<PlusOutlined />} onClick={openCreateModal}>Add Store</Button>}
            >
                <Table dataSource={stores} columns={columns} rowKey="id" pagination={false} />
            </Card>

            <Modal
                title={editingStore ? 'Edit Store' : 'Add Store'}
                open={modalOpen}
                onCancel={() => setModalOpen(false)}
                onOk={handleSubmit}
                confirmLoading={processing}
            >
                <Form layout="vertical">
                    <Form.Item label="Name" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Code" validateStatus={errors.code ? 'error' : ''} help={errors.code}>
                        <Input value={data.code} onChange={(e) => setData('code', e.target.value)} placeholder="e.g., MAIN, BRANCH1" />
                    </Form.Item>
                    <Form.Item label="Address">
                        <Input.TextArea rows={2} value={data.address} onChange={(e) => setData('address', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Phone">
                        <Input value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Email">
                        <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Status">
                        <Select value={data.is_active} onChange={(v) => setData('is_active', v)} options={[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]} />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
}
