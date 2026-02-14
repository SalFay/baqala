import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Select, DatePicker, Input, Button, Table, InputNumber, Space, message } from 'antd';
import { ArrowLeftOutlined, PlusOutlined, DeleteOutlined, SaveOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency } from '@/Helpers/formatters';

export default function PurchaseOrderCreate({ vendors = [], stores = [], products = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        vendor_id: null,
        store_id: null,
        expected_date: null,
        notes: '',
        items: [],
    });

    const [selectedProduct, setSelectedProduct] = useState(null);
    const [quantity, setQuantity] = useState(1);
    const [unitCost, setUnitCost] = useState(0);

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
                quantity,
                unit_cost: unitCost || product.cost,
            }]);
        }
        setSelectedProduct(null);
        setQuantity(1);
        setUnitCost(0);
    };

    const removeItem = (index) => {
        setData('items', data.items.filter((_, i) => i !== index));
    };

    const handleSubmit = () => {
        post('/purchase-orders', {
            onSuccess: () => message.success('Purchase order created'),
        });
    };

    const total = data.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0);

    const columns = [
        { title: 'Product', dataIndex: 'product_name', key: 'product' },
        { title: 'Qty', dataIndex: 'quantity', key: 'quantity' },
        { title: 'Unit Cost', dataIndex: 'unit_cost', key: 'unit_cost', render: (val) => formatCurrency(val) },
        { title: 'Total', key: 'total', render: (_, r) => formatCurrency(r.quantity * r.unit_cost) },
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
            <Head title="Create Purchase Order" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/purchase-orders')}>Back</Button>
            </div>

            <Card title="Create Purchase Order">
                <Form layout="vertical">
                    <Space style={{ width: '100%', marginBottom: 24 }} wrap>
                        <Form.Item label="Vendor" style={{ minWidth: 200 }}>
                            <Select
                                value={data.vendor_id}
                                onChange={(v) => setData('vendor_id', v)}
                                options={vendors.map(v => ({ label: v.name, value: v.id }))}
                                placeholder="Select vendor"
                            />
                        </Form.Item>
                        <Form.Item label="Store" style={{ minWidth: 200 }}>
                            <Select
                                value={data.store_id}
                                onChange={(v) => setData('store_id', v)}
                                options={stores.map(s => ({ label: s.name, value: s.id }))}
                                placeholder="Select store"
                            />
                        </Form.Item>
                        <Form.Item label="Expected Date">
                            <DatePicker onChange={(d) => setData('expected_date', d?.format('YYYY-MM-DD'))} />
                        </Form.Item>
                    </Space>

                    <Card title="Add Items" size="small" style={{ marginBottom: 16 }}>
                        <Space wrap>
                            <Select
                                showSearch
                                style={{ width: 300 }}
                                value={selectedProduct}
                                onChange={(v) => {
                                    setSelectedProduct(v);
                                    const p = products.find(p => p.id === v);
                                    if (p) setUnitCost(p.cost);
                                }}
                                options={products.map(p => ({ label: `${p.name} (${p.sku})`, value: p.id }))}
                                placeholder="Search product"
                                filterOption={(input, option) => option.label.toLowerCase().includes(input.toLowerCase())}
                            />
                            <InputNumber min={1} value={quantity} onChange={setQuantity} placeholder="Qty" />
                            <InputNumber min={0} value={unitCost} onChange={setUnitCost} placeholder="Cost" prefix="$" />
                            <Button icon={<PlusOutlined />} onClick={addItem}>Add</Button>
                        </Space>
                    </Card>

                    <Table dataSource={data.items} columns={columns} rowKey="product_id" pagination={false}
                        summary={() => (
                            <Table.Summary.Row>
                                <Table.Summary.Cell colSpan={3} align="right"><strong>Total:</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatCurrency(total)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell />
                            </Table.Summary.Row>
                        )}
                    />

                    <Form.Item label="Notes" style={{ marginTop: 16 }}>
                        <Input.TextArea rows={2} value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                    </Form.Item>

                    <Button type="primary" icon={<SaveOutlined />} loading={processing} onClick={handleSubmit} disabled={data.items.length === 0}>
                        Create Purchase Order
                    </Button>
                </Form>
            </Card>
        </MainLayout>
    );
}
