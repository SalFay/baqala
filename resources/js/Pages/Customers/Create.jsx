import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Input, Select, Button, message } from 'antd';
import { ArrowLeftOutlined, SaveOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function CustomerCreate() {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        status: 'active',
    });

    const handleSubmit = () => {
        post('/customers', {
            onSuccess: () => {
                message.success('Customer created successfully');
            },
        });
    };

    return (
        <MainLayout>
            <Head title="Create Customer" />

            <div style={{ marginBottom: 24 }}>
                <Button
                    icon={<ArrowLeftOutlined />}
                    onClick={() => router.visit('/customers')}
                >
                    Back to Customers
                </Button>
            </div>

            <Card title="Create Customer">
                <Form
                    layout="vertical"
                    onFinish={handleSubmit}
                    style={{ maxWidth: 600 }}
                >
                    <Form.Item
                        label="First Name"
                        validateStatus={errors.first_name ? 'error' : ''}
                        help={errors.first_name}
                        required
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
                    >
                        <Input
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                        />
                    </Form.Item>

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
                            Save Customer
                        </Button>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
}
