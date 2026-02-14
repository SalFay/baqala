import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, Table, Button, Space, Modal, Form, Input, Select, InputNumber, message, Tag, Popconfirm } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function SettingsPaymentMethods({ paymentMethods = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        name: '',
        code: '',
        type: 'cash',
        processing_fee: 0,
        is_active: true,
    });

    const openCreateModal = () => {
        reset();
        setEditing(null);
        setModalOpen(true);
    };

    const openEditModal = (method) => {
        setEditing(method);
        setData({
            name: method.name,
            code: method.code || '',
            type: method.type || 'cash',
            processing_fee: method.processing_fee || 0,
            is_active: method.is_active,
        });
        setModalOpen(true);
    };

    const handleSubmit = () => {
        if (editing) {
            put(`/settings/payment-methods/${editing.id}`, {
                onSuccess: () => {
                    message.success('Payment method updated');
                    setModalOpen(false);
                    reset();
                },
            });
        } else {
            post('/settings/payment-methods', {
                onSuccess: () => {
                    message.success('Payment method created');
                    setModalOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id) => {
        router.delete(`/settings/payment-methods/${id}`, {
            onSuccess: () => message.success('Payment method deleted'),
        });
    };

    const typeOptions = [
        { label: 'Cash', value: 'cash' },
        { label: 'Card', value: 'card' },
        { label: 'Bank Transfer', value: 'bank_transfer' },
        { label: 'Mobile Payment', value: 'mobile' },
        { label: 'Credit', value: 'credit' },
    ];

    const columns = [
        { title: 'Name', dataIndex: 'name', key: 'name' },
        { title: 'Code', dataIndex: 'code', key: 'code' },
        { title: 'Type', dataIndex: 'type', key: 'type', render: (t) => <Tag>{t}</Tag> },
        { title: 'Processing Fee (%)', dataIndex: 'processing_fee', key: 'fee', render: (f) => `${f || 0}%` },
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
                    <Popconfirm title="Delete this payment method?" onConfirm={() => handleDelete(record.id)}>
                        <Button danger icon={<DeleteOutlined />} size="small" />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Payment Methods - Settings" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/settings')}>Back to Settings</Button>
            </div>
            <Card
                title="Payment Methods"
                extra={<Button type="primary" icon={<PlusOutlined />} onClick={openCreateModal}>Add Method</Button>}
            >
                <Table dataSource={paymentMethods} columns={columns} rowKey="id" pagination={false} />
            </Card>

            <Modal
                title={editing ? 'Edit Payment Method' : 'Add Payment Method'}
                open={modalOpen}
                onCancel={() => setModalOpen(false)}
                onOk={handleSubmit}
                confirmLoading={processing}
            >
                <Form layout="vertical">
                    <Form.Item label="Name" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="e.g., Credit Card" />
                    </Form.Item>
                    <Form.Item label="Code" validateStatus={errors.code ? 'error' : ''} help={errors.code}>
                        <Input value={data.code} onChange={(e) => setData('code', e.target.value)} placeholder="e.g., CARD" />
                    </Form.Item>
                    <Form.Item label="Type">
                        <Select value={data.type} onChange={(v) => setData('type', v)} options={typeOptions} />
                    </Form.Item>
                    <Form.Item label="Processing Fee (%)">
                        <InputNumber min={0} max={100} step={0.1} value={data.processing_fee} onChange={(v) => setData('processing_fee', v)} style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item label="Status">
                        <Select value={data.is_active} onChange={(v) => setData('is_active', v)} options={[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]} />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
}
