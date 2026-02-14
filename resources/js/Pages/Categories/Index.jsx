import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Card, Table, Button, Space, Modal, Form, Input, Select, message, Tag, Popconfirm, Tree } from 'antd';
import { PlusOutlined, EditOutlined, DeleteOutlined, FolderOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function CategoriesIndex({ categories = [], flatCategories = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);

    const { data, setData, post, put, processing, reset, errors } = useForm({
        name: '',
        parent_id: null,
        description: '',
        is_active: true,
    });

    const openCreateModal = (parentId = null) => {
        reset();
        setData('parent_id', parentId);
        setEditing(null);
        setModalOpen(true);
    };

    const openEditModal = (category) => {
        setEditing(category);
        setData({
            name: category.name,
            parent_id: category.parent_id || null,
            description: category.description || '',
            is_active: category.is_active,
        });
        setModalOpen(true);
    };

    const handleSubmit = () => {
        if (editing) {
            put(`/categories/${editing.id}`, {
                onSuccess: () => {
                    message.success('Category updated');
                    setModalOpen(false);
                    reset();
                },
            });
        } else {
            post('/categories', {
                onSuccess: () => {
                    message.success('Category created');
                    setModalOpen(false);
                    reset();
                },
            });
        }
    };

    const handleDelete = (id) => {
        router.delete(`/categories/${id}`, {
            onSuccess: () => message.success('Category deleted'),
        });
    };

    // Convert to tree data for display
    const convertToTreeData = (items) => {
        return items.map(item => ({
            key: item.id,
            title: (
                <Space>
                    <span>{item.name}</span>
                    {!item.is_active && <Tag color="default">Inactive</Tag>}
                    <Button icon={<EditOutlined />} type="link" size="small" onClick={(e) => { e.stopPropagation(); openEditModal(item); }} />
                    <Button icon={<PlusOutlined />} type="link" size="small" onClick={(e) => { e.stopPropagation(); openCreateModal(item.id); }} />
                    <Popconfirm title="Delete this category?" onConfirm={() => handleDelete(item.id)}>
                        <Button danger icon={<DeleteOutlined />} type="link" size="small" />
                    </Popconfirm>
                </Space>
            ),
            children: item.children?.length > 0 ? convertToTreeData(item.children) : undefined,
        }));
    };

    const columns = [
        { title: 'Name', dataIndex: 'name', key: 'name' },
        { title: 'Parent', dataIndex: ['parent', 'name'], key: 'parent', render: (v) => v || '-' },
        { title: 'Products', dataIndex: 'products_count', key: 'products', render: (v) => v || 0 },
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
                    <Button icon={<PlusOutlined />} size="small" onClick={() => openCreateModal(record.id)}>Sub</Button>
                    <Popconfirm title="Delete this category?" onConfirm={() => handleDelete(record.id)}>
                        <Button danger icon={<DeleteOutlined />} size="small" />
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    const treeData = convertToTreeData(categories);

    return (
        <MainLayout>
            <Head title="Categories" />
            <Card
                title="Categories"
                extra={<Button type="primary" icon={<PlusOutlined />} onClick={() => openCreateModal()}>Add Category</Button>}
            >
                {categories.length > 0 && treeData.length > 0 ? (
                    <Tree
                        treeData={treeData}
                        defaultExpandAll
                        showIcon
                        icon={<FolderOutlined />}
                    />
                ) : (
                    <Table
                        dataSource={flatCategories}
                        columns={columns}
                        rowKey="id"
                        pagination={false}
                    />
                )}
            </Card>

            <Modal
                title={editing ? 'Edit Category' : 'Add Category'}
                open={modalOpen}
                onCancel={() => setModalOpen(false)}
                onOk={handleSubmit}
                confirmLoading={processing}
            >
                <Form layout="vertical">
                    <Form.Item label="Name" required validateStatus={errors.name ? 'error' : ''} help={errors.name}>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Parent Category">
                        <Select
                            value={data.parent_id}
                            onChange={(v) => setData('parent_id', v)}
                            allowClear
                            placeholder="Select parent category (optional)"
                            options={flatCategories.filter(c => c.id !== editing?.id).map(c => ({ label: c.name, value: c.id }))}
                        />
                    </Form.Item>
                    <Form.Item label="Description">
                        <Input.TextArea rows={2} value={data.description} onChange={(e) => setData('description', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Status">
                        <Select
                            value={data.is_active}
                            onChange={(v) => setData('is_active', v)}
                            options={[{ label: 'Active', value: true }, { label: 'Inactive', value: false }]}
                        />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
}
