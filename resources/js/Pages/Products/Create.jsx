import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Input, InputNumber, Select, Button, Space, message } from 'antd';
import { ArrowLeftOutlined, SaveOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function ProductCreate({ categories = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        sku: '',
        barcode: '',
        category_id: null,
        type: 'simple',
        sale_price: 0,
        cost_price: 0,
        is_active: true,
    });

    const handleSubmit = () => {
        post('/products', {
            onSuccess: () => {
                message.success('Product created successfully');
            },
        });
    };

    return (
        <MainLayout>
            <Head title="Create Product" />

            <div style={{ marginBottom: 24 }}>
                <Button
                    icon={<ArrowLeftOutlined />}
                    onClick={() => router.visit('/products')}
                >
                    Back to Products
                </Button>
            </div>

            <Card title="Create Product">
                <Form
                    layout="vertical"
                    onFinish={handleSubmit}
                    style={{ maxWidth: 600 }}
                >
                    <Form.Item
                        label="Name"
                        validateStatus={errors.name ? 'error' : ''}
                        help={errors.name}
                        required
                    >
                        <Input
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Description"
                        validateStatus={errors.description ? 'error' : ''}
                        help={errors.description}
                    >
                        <Input.TextArea
                            rows={4}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                    </Form.Item>

                    <Space style={{ width: '100%' }} size="large">
                        <Form.Item
                            label="SKU"
                            validateStatus={errors.sku ? 'error' : ''}
                            help={errors.sku}
                            style={{ flex: 1 }}
                        >
                            <Input
                                value={data.sku}
                                onChange={(e) => setData('sku', e.target.value)}
                            />
                        </Form.Item>

                        <Form.Item
                            label="Barcode"
                            validateStatus={errors.barcode ? 'error' : ''}
                            help={errors.barcode}
                            style={{ flex: 1 }}
                        >
                            <Input
                                value={data.barcode}
                                onChange={(e) => setData('barcode', e.target.value)}
                            />
                        </Form.Item>
                    </Space>

                    <Form.Item
                        label="Category"
                        validateStatus={errors.category_id ? 'error' : ''}
                        help={errors.category_id}
                    >
                        <Select
                            value={data.category_id}
                            onChange={(value) => setData('category_id', value)}
                            options={categories.map(c => ({ label: c.name, value: c.id }))}
                            placeholder="Select category"
                        />
                    </Form.Item>

                    <Space style={{ width: '100%' }} size="large">
                        <Form.Item
                            label="Sale Price"
                            validateStatus={errors.sale_price ? 'error' : ''}
                            help={errors.sale_price}
                            required
                            style={{ flex: 1 }}
                        >
                            <InputNumber
                                value={data.sale_price}
                                onChange={(value) => setData('sale_price', value)}
                                min={0}
                                precision={2}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>

                        <Form.Item
                            label="Cost Price"
                            validateStatus={errors.cost_price ? 'error' : ''}
                            help={errors.cost_price}
                            required
                            style={{ flex: 1 }}
                        >
                            <InputNumber
                                value={data.cost_price}
                                onChange={(value) => setData('cost_price', value)}
                                min={0}
                                precision={2}
                                style={{ width: '100%' }}
                            />
                        </Form.Item>
                    </Space>

                    <Form.Item
                        label="Status"
                        validateStatus={errors.is_active ? 'error' : ''}
                        help={errors.is_active}
                    >
                        <Select
                            value={data.is_active}
                            onChange={(value) => setData('is_active', value)}
                            options={[
                                { label: 'Active', value: true },
                                { label: 'Inactive', value: false },
                            ]}
                        />
                    </Form.Item>

                    <Form.Item>
                        <Button
                            type="primary"
                            htmlType="submit"
                            icon={<SaveOutlined />}
                            loading={processing}
                        >
                            Save Product
                        </Button>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
}
