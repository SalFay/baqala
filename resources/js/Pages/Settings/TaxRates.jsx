import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, Table, Button, Space, Modal, Form, Input, InputNumber, message, Tag, Popconfirm, Switch } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function SettingsTaxRates({ taxRates = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        name: '',
        rate: 0,
        is_default: false,
        is_active: true,
    });

    const openCreateModal = () => {
        reset();
        setEditing(null);
        setModalOpen(true);
    };

    const openEditModal = (taxRate) => {
        setEditing(taxRate);
        setData({
            name: taxRate.name,
            rate: taxRate.rate || 0,
            is_default: taxRate.is_default || false,
            is_active: taxRate.is_active,
        });
        setModalOpen(true);
    };

    const handleSubmit = () => {
        if (editing) {
            put(`/settings/tax-rates/${editing.id}`, {
                onSuccess: () => {
                    message.success('Tax rate updated');
                    setModalOpen(false);
                    reset();
                },
            });
        } else {
            post('/settings/tax-rates', {
                onSuccess: () => {
                    message.success('Tax rate created');
                    setModalOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id) => {
        router.delete(`/settings/tax-rates/${id}`, {
            onSuccess: () => message.success('Tax rate deleted'),
        });
    };

    const columns = [
        { title: 'Name', dataIndex: 'name', key: 'name' },
        { title: 'Rate (%)', dataIndex: 'rate', key: 'rate', render: (r) => `${r}%` },
        {
            title: 'Default',
            dataIndex: 'is_default',
            key: 'default',
            render: (d) => d ? <Tag color="blue">Default</Tag> : '-',
        },
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
                    <Popconfirm title="Delete this tax rate?" onConfirm={() => handleDelete(record.id)}>
                        <Button danger icon={<DeleteOutlined />} size="small" />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Tax Rates - Settings" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/settings')}>Back to Settings</Button>
            </div>
            <Card
                title="Tax Rates"
                extra={<Button type="primary" icon={<PlusOutlined />} onClick={openCreateModal}>Add Tax Rate</Button>}
            >
                <Table dataSource={taxRates} columns={columns} rowKey="id" pagination={false} />
            </Card>

            <Modal
                title={editing ? 'Edit Tax Rate' : 'Add Tax Rate'}
                open={modalOpen}
                onCancel={() => setModalOpen(false)}
                onOk={handleSubmit}
                confirmLoading={processing}
            >
                <Form layout="vertical">
                    <Form.Item label="Name" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="e.g., VAT, Sales Tax" />
                    </Form.Item>
                    <Form.Item label="Rate (%)" required validateStatus={errors.rate ? 'error' : ''} help={errors.rate}>
                        <InputNumber min={0} max={100} step={0.01} value={data.rate} onChange={(v) => setData('rate', v)} style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item label="Default">
                        <Switch checked={data.is_default} onChange={(v) => setData('is_default', v)} />
                        <span style={{ marginLeft: 8 }}>Set as default tax rate</span>
                    </Form.Item>
                    <Form.Item label="Active">
                        <Switch checked={data.is_active} onChange={(v) => setData('is_active', v)} />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
}
