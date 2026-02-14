import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Select, Input, Button, Table, InputNumber, Space, message } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, DeleteOutlined, SaveOutlined, SwapOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function StockTransferCreate({ stores = [], products = [] }) {
    const { data, setData, post, processing } = useForm({
        from_store_id: null,
        to_store_id: null,
        notes: '',
        items: [],
    });

    const [selectedProduct, setSelectedProduct] = useState(null);
    const [quantity, setQuantity] = useState(1);

    const addItem = () => {
        if (!selectedProduct) return;
        const product = products.find(p => p.id === selectedProduct);
        if (!product) return;

        const existingIndex = data.items.findIndex(i => i.product_id === selectedProduct);
        if (existingIndex >= 0) {
            const newItems = [...data.items];
            newItems[existingIndex].quantity += quantity;
            setData('items', newItems);
        } else {
            setData('items', [...data.items, {
                product_id: selectedProduct,
                product_name: product.name,
                sku: product.sku,
                quantity,
            }]);
        }
        setSelectedProduct(null);
        setQuantity(1);
    };

    const removeItem = (index) => {
        setData('items', data.items.filter((_, i) => i !== index));
    };

    const handleSubmit = () => {
        post('/stock-transfers', {
            onSuccess: () => message.success('Transfer created'),
        });
    };

    const columns = [
        { title: 'Product', dataIndex: 'product_name', key: 'product' },
        { title: 'SKU', dataIndex: 'sku', key: 'sku' },
        { title: 'Quantity', dataIndex: 'quantity', key: 'quantity' },
        {
            title: '',
            key: 'actions',
            render: (_, __, index) => (
                <Button danger icon={<DeleteOutlined />} size="small" onClick={() => removeItem(index)} />
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Create Stock Transfer" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/stock-transfers')}>Back</Button>
            </div>

            <Card title="Create Stock Transfer">
                <Form layout="vertical">
                    <Space style={{ width: '100%', marginBottom: 24 }} wrap align="end">
                        <Form.Item label="From Store" style={{ minWidth: 200 }}>
                            <Select
                                value={data.from_store_id}
                                onChange={(v) => setData('from_store_id', v)}
                                options={stores.map(s => ({ label: s.name, value: s.id }))}
                                placeholder="Select source"
                            />
                        </Form.Item>
                        <SwapOutlined style={{ fontSize: 24, marginBottom: 8 }} />
                        <Form.Item label="To Store" style={{ minWidth: 200 }}>
                            <Select
                                value={data.to_store_id}
                                onChange={(v) => setData('to_store_id', v)}
                                options={stores.filter(s => s.id !== data.from_store_id).map(s => ({ label: s.name, value: s.id }))}
                                placeholder="Select destination"
                            />
                        </Form.Item>
                    </Space>

                    <Card title="Add Items" size="small" style={{ marginBottom: 16 }}>
                        <Space wrap>
                            <Select
                                showSearch
                                style={{ width: 300 }}
                                value={selectedProduct}
                                onChange={setSelectedProduct}
                                options={products.map(p => ({ label: `${p.name} (${p.sku})`, value: p.id }))}
                                placeholder="Search product"
                                filterOption={(input, option) => option.label.toLowerCase().includes(input.toLowerCase())}
                            />
                            <InputNumber min={1} value={quantity} onChange={setQuantity} placeholder="Qty" />
                            <Button icon={<PlusOutlined />} onClick={addItem}>Add</Button>
                        </Space>
                    </Card>

                    <Table dataSource={data.items} columns={columns} rowKey="product_id" pagination={false} />

                    <Form.Item label="Notes" style={{ marginTop: 16 }}>
                        <Input.TextArea rows={2} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                    </Form.Item>

                    <Button type="primary" icon={<SaveOutlined />} loading={processing} onClick={handleSubmit} disabled={data.items.length === 0 || !data.from_store_id || !data.to_store_id}>
                        Create Transfer
                    </Button>
                </Form>
            </Card>
        </MainLayout>
    );
}
