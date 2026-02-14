import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Input, Select, Button, message } from 'antd';
import { ArrowLeftOutlined, SaveOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function VendorCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        country: '',
        tax_number: '',
        status: 'active',
    });

    const handleSubmit = () => {
        post('/vendors', {
            onSuccess: () => message.success('Vendor created successfully'),
        });
    };

    return (
        <MainLayout>
            <Head title="Create Vendor" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/vendors')}>Back to Vendors</Button>
            </div>
            <Card title="Create Vendor">
                <Form layout="vertical" onFinish={handleSubmit} style={{ maxWidth: 600 }}>
                    <Form.Item label="Name" validateStatus={errors.name ? 'error' : ''} help={errors.name} required>
                        <Input value={data.name} onChange={(e) => setData('name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Email" validateStatus={errors.email ? 'error' : ''} help={errors.email}>
                        <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Phone" validateStatus={errors.phone ? 'error' : ''} help={errors.phone}>
                        <Input value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Address">
                        <Input.TextArea rows={3} value={data.address} onChange={(e) => setData('address', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="City">
                        <Input value={data.city} onChange={(e) => setData('city', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Country">
                        <Input value={data.country} onChange={(e) => setData('country', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Tax Number">
                        <Input value={data.tax_number} onChange={(e) => setData('tax_number', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Status">
                        <Select value={data.status} onChange={(value) => setData('status', value)} options={[{ label: 'Active', value: 'active' }, { label: 'Inactive', value: 'inactive' }]} />
                    </Form.Item>
                    <Form.Item>
                        <Button type="primary" htmlType="submit" icon={<SaveOutlined />} loading={processing}>Save Vendor</Button>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
}
