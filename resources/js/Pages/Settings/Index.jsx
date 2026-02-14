import { Head, useForm, router } from '@inertiajs/react';
import { Card, Form, Input, InputNumber, Button, Tabs, Space, message } from 'antd';
import { SaveOutlined, ShopOutlined, CreditCardOutlined, PercentageOutlined, TeamOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';

export default function SettingsIndex({ settings = {} }) {
    const { data, setData, put, processing } = useForm({
        store_name: settings.store_name || '',
        store_address: settings.store_address || '',
        store_phone: settings.store_phone || '',
        store_email: settings.store_email || '',
        tax_number: settings.tax_number || '',
        currency: settings.currency || 'SAR',
        currency_symbol: settings.currency_symbol || 'SAR',
        default_tax_rate: settings.default_tax_rate || 15,
        receipt_header: settings.receipt_header || '',
        receipt_footer: settings.receipt_footer || '',
    });

    const handleSubmit = () => {
        put('/settings', {
            onSuccess: () => message.success('Settings saved'),
        });
    };

    const tabItems = [
        {
            key: 'general',
            label: <span><ShopOutlined /> Store</span>,
            children: (
                <Form layout="vertical" style={{ maxWidth: 600 }}>
                    <Form.Item label="Store Name">
                        <Input value={data.store_name} onChange={(e) => setData('store_name', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Address">
                        <Input.TextArea rows={3} value={data.store_address} onChange={(e) => setData('store_address', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Phone">
                        <Input value={data.store_phone} onChange={(e) => setData('store_phone', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Email">
                        <Input value={data.store_email} onChange={(e) => setData('store_email', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Tax Number">
                        <Input value={data.tax_number} onChange={(e) => setData('tax_number', e.target.value)} />
                    </Form.Item>
                </Form>
            ),
        },
        {
            key: 'currency',
            label: <span><CreditCardOutlined /> Currency</span>,
            children: (
                <Form layout="vertical" style={{ maxWidth: 400 }}>
                    <Form.Item label="Currency Code">
                        <Input value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Currency Symbol">
                        <Input value={data.currency_symbol} onChange={(e) => setData('currency_symbol', e.target.value)} />
                    </Form.Item>
                    <Form.Item label="Default Tax Rate (%)">
                        <InputNumber min={0} max={100} value={data.default_tax_rate} onChange={(value) => setData('default_tax_rate', value)} style={{ width: '100%' }} />
                    </Form.Item>
                </Form>
            ),
        },
        {
            key: 'receipt',
            label: 'Receipt',
            children: (
                <Form layout="vertical" style={{ maxWidth: 600 }}>
                    <Form.Item label="Receipt Header">
                        <Input.TextArea rows={3} value={data.receipt_header} onChange={(e) => setData('receipt_header', e.target.value)} placeholder="Text to show at top of receipt" />
                    </Form.Item>
                    <Form.Item label="Receipt Footer">
                        <Input.TextArea rows={3} value={data.receipt_footer} onChange={(e) => setData('receipt_footer', e.target.value)} placeholder="Text to show at bottom of receipt" />
                    </Form.Item>
                </Form>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Settings" />
            <Card
                title="Settings"
                extra={
                    <Space>
                        <Button onClick={() => router.visit('/settings/stores')}>Stores</Button>
                        <Button onClick={() => router.visit('/settings/payment-methods')}>Payment Methods</Button>
                        <Button onClick={() => router.visit('/settings/tax-rates')}>Tax Rates</Button>
                        <Button onClick={() => router.visit('/settings/users')}>Users</Button>
                        <Button type="primary" icon={<SaveOutlined />} loading={processing} onClick={handleSubmit}>Save</Button>
                    </Space>
                }
            >
                <Tabs items={tabItems} />
            </Card>
        </MainLayout>
    );
}
