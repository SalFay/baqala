import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Input, InputNumber, Select, Button, Space, message } from 'antd';
import { ArrowLeftOutlined, SaveOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function CustomerEdit({ customer }) {
    const { data, setData, put, processing, errors } = useForm({
        first_name: customer.first_name || '',
        last_name: customer.last_name || '',
        email: customer.email || '',
        phone: customer.phone || '',
        address: customer.address || '',
        city: customer.city || '',
        credit_limit: customer.credit_limit || 0,
        status: customer.status || 'active',
    });

    const handleSubmit = () => {
        put(`/customers/${customer.id}`, {
            onSuccess: () => {
                message.success('Customer updated successfully');
            },
        });
    };

    return (
        <MainLayout>
            <Head title={`Edit Customer: ${customer.first_name}`} />

            <div style={{ marginBottom: 24 }}>
                <Button
                    icon={<ArrowLeftOutlined />}
                    onClick={() => router.visit('/customers')}
                >
                    Back to Customers
                </Button>
            </div>

            <Card title="Edit Customer">
                <Form
                    layout="vertical"
                    onFinish={handleSubmit}
                    style={{ maxWidth: 600 }}
                >
                    <Space style={{ width: '100%' }} size="large">
                        <Form.Item
                            label="First Name"
                            validateStatus={errors.first_name ? 'error' : ''}
                            help={errors.first_name}
                            required
                            style={{ flex: 1 }}
                        >
                            <Input
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                            />
                        </Form.Item>

                        <Form.Item
                            label="Last Name"
                            validateStatus={errors.last_name ? 'error' : ''}
                            help={errors.last_name}
                            required
                            style={{ flex: 1 }}
                        >
                            <Input
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                            />
                        </Form.Item>
                    </Space>

                    <Form.Item
                        label="Email"
                        validateStatus={errors.email ? 'error' : ''}
                        help={errors.email}
                    >
                        <Input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Phone"
                        validateStatus={errors.phone ? 'error' : ''}
                        help={errors.phone}
                    >
                        <Input
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Address"
                        validateStatus={errors.address ? 'error' : ''}
                        help={errors.address}
                    >
                        <Input.TextArea
                            rows={3}
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="City"
                        validateStatus={errors.city ? 'error' : ''}
                        help={errors.city}
                    >
                        <Input
                            value={data.city}
                            onChange={(e) => setData('city', e.target.value)}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Credit Limit"
                        validateStatus={errors.credit_limit ? 'error' : ''}
                        help={errors.credit_limit}
                    >
                        <InputNumber
                            value={data.credit_limit}
                            onChange={(value) => setData('credit_limit', value)}
                            min={0}
                            precision={2}
                            style={{ width: '100%' }}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Status"
                        validateStatus={errors.status ? 'error' : ''}
                        help={errors.status}
                    >
                        <Select
                            value={data.status}
                            onChange={(value) => setData('status', value)}
                            options={[
                                { label: 'Active', value: 'active' },
                                { label: 'Inactive', value: 'inactive' },
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
                            Update Customer
                        </Button>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
}
