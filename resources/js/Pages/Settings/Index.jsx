import { useState, useEffect } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Tabs, Form, Input, Button, Switch, InputNumber, Divider, message, Row, Col, Select, Space, Alert, Typography } from 'antd'
import { SaveOutlined, ShopOutlined, DollarOutlined, PrinterOutlined, BellOutlined, GiftOutlined, InboxOutlined, ReloadOutlined } from '@ant-design/icons'
import axios from 'axios'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { handleApiSuccess, handleApiError } from '@/Helpers/CONSTANT'

const { Option } = Select
const { TextArea } = Input
const { Text } = Typography

// Currency options
const CURRENCIES = [
  { code: 'SAR', name: 'Saudi Riyal', symbol: 'ر.س' },
  { code: 'AED', name: 'UAE Dirham', symbol: 'د.إ' },
  { code: 'KWD', name: 'Kuwaiti Dinar', symbol: 'د.ك' },
  { code: 'BHD', name: 'Bahraini Dinar', symbol: 'د.ب' },
  { code: 'OMR', name: 'Omani Rial', symbol: 'ر.ع' },
  { code: 'QAR', name: 'Qatari Riyal', symbol: 'ر.ق' },
  { code: 'EGP', name: 'Egyptian Pound', symbol: 'ج.م' },
  { code: 'JOD', name: 'Jordanian Dinar', symbol: 'د.أ' },
  { code: 'USD', name: 'US Dollar', symbol: '$' },
  { code: 'EUR', name: 'Euro', symbol: '€' },
  { code: 'GBP', name: 'British Pound', symbol: '£' },
  { code: 'INR', name: 'Indian Rupee', symbol: '₹' },
  { code: 'PKR', name: 'Pakistani Rupee', symbol: '₨' },
]

export default function Settings() {
  const { settings = {}, taxRates = [], flash } = usePage().props
  const [activeTab, setActiveTab] = useState('store')
  const [form] = Form.useForm()
  const [saving, setSaving] = useState(false)

  // Initialize form with settings
  useEffect(() => {
    form.setFieldsValue({
      store_name: settings.store_name || '',
      store_phone: settings.store_phone || '',
      store_email: settings.store_email || '',
      store_address: settings.store_address || '',
      tax_number: settings.tax_number || '',
      currency: settings.currency || 'SAR',
      currency_symbol: settings.currency_symbol || '',
      currency_position: settings.currency_position || 'before',
      default_tax_rate: parseFloat(settings.default_tax_rate) || 15,
      prices_include_tax: settings.prices_include_tax === '1' || settings.prices_include_tax === true,
      receipt_header: settings.receipt_header || '',
      receipt_footer: settings.receipt_footer || 'Thank you for your purchase!',
      auto_print_receipt: settings.auto_print_receipt === '1' || settings.auto_print_receipt === true,
      low_stock_threshold: parseInt(settings.low_stock_threshold) || 10,
      allow_negative_stock: settings.allow_negative_stock === '1' || settings.allow_negative_stock === true,
      loyalty_enabled: settings.loyalty_enabled === '1' || settings.loyalty_enabled === true,
      loyalty_points_per_currency: parseFloat(settings.loyalty_points_per_currency) || 1,
      loyalty_point_value: parseFloat(settings.loyalty_point_value) || 0.01,
    })
  }, [settings, form])

  // Show flash messages
  useEffect(() => {
    if (flash?.success) {
      message.success(flash.success)
    }
    if (flash?.error) {
      message.error(flash.error)
    }
  }, [flash])

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      const response = await axios.post(route('settings.update'), {
        ...values,
        _method: 'PUT', // Laravel method spoofing
      })
      handleApiSuccess(response)
      // Reload page to get updated settings
      router.reload()
    } catch (error) {
      handleApiError(error)
    } finally {
      setSaving(false)
    }
  }

  const handleCurrencyChange = (code) => {
    const currency = CURRENCIES.find(c => c.code === code)
    if (currency) {
      form.setFieldsValue({
        currency: code,
        currency_symbol: currency.symbol,
      })
    }
  }

  const tabItems = [
    {
      key: 'store',
      label: (
        <span>
          <ShopOutlined />
          Store Info
        </span>
      ),
      children: (
        <Card>
          <Row gutter={[16, 16]}>
            <Col xs={24} md={12}>
              <Form.Item name="store_name" label="Store Name" rules={[{ required: true, message: 'Store name is required' }]}>
                <Input placeholder="Enter store name" size="large" />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="store_phone" label="Phone Number">
                <Input placeholder="e.g., +966 50 123 4567" size="large" />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="store_email" label="Email" rules={[{ type: 'email', message: 'Invalid email' }]}>
                <Input placeholder="store@example.com" size="large" />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="tax_number" label="VAT/Tax Number">
                <Input placeholder="e.g., 123456789012345" size="large" />
              </Form.Item>
            </Col>
            <Col xs={24}>
              <Form.Item name="store_address" label="Address">
                <TextArea rows={2} placeholder="Enter store address" />
              </Form.Item>
            </Col>
          </Row>
        </Card>
      ),
    },
    {
      key: 'currency',
      label: (
        <span>
          <DollarOutlined />
          Currency & Tax
        </span>
      ),
      children: (
        <Card>
          <Row gutter={[16, 16]}>
            <Col xs={24} md={8}>
              <Form.Item name="currency" label="Currency">
                <Select size="large" onChange={handleCurrencyChange} showSearch optionFilterProp="children">
                  {CURRENCIES.map(c => (
                    <Option key={c.code} value={c.code}>
                      {c.code} - {c.name} ({c.symbol})
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item name="currency_symbol" label="Currency Symbol">
                <Input placeholder="e.g., $, ر.س" size="large" />
              </Form.Item>
            </Col>
            <Col xs={24} md={8}>
              <Form.Item name="currency_position" label="Symbol Position">
                <Select size="large">
                  <Option value="before">Before amount ($ 100)</Option>
                  <Option value="after">After amount (100 ر.س)</Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>
          <Divider />
          <Row gutter={[16, 16]}>
            <Col xs={24} md={8}>
              <Form.Item name="default_tax_rate" label="Default Tax Rate (%)">
                <InputNumber min={0} max={100} precision={2} style={{ width: '100%' }} size="large" />
              </Form.Item>
            </Col>
            <Col xs={24} md={16}>
              <Form.Item name="prices_include_tax" label="Prices Include Tax" valuePropName="checked">
                <Switch checkedChildren="Yes" unCheckedChildren="No" />
              </Form.Item>
              <Text type="secondary">When enabled, displayed prices already include tax</Text>
            </Col>
          </Row>
          {taxRates.length > 0 && (
            <>
              <Divider>Configured Tax Rates</Divider>
              <Space wrap>
                {taxRates.map(tax => (
                  <Alert
                    key={tax.id}
                    type={tax.is_default ? 'success' : 'info'}
                    message={`${tax.name}: ${tax.rate}%`}
                    showIcon={tax.is_default}
                  />
                ))}
              </Space>
            </>
          )}
        </Card>
      ),
    },
    {
      key: 'receipt',
      label: (
        <span>
          <PrinterOutlined />
          Receipt
        </span>
      ),
      children: (
        <Card>
          <Row gutter={[16, 16]}>
            <Col xs={24}>
              <Form.Item name="receipt_header" label="Receipt Header" extra="Text shown at the top of receipts (supports multiple lines)">
                <TextArea rows={3} placeholder="Welcome to our store!&#10;Best prices guaranteed" />
              </Form.Item>
            </Col>
            <Col xs={24}>
              <Form.Item name="receipt_footer" label="Receipt Footer" extra="Text shown at the bottom of receipts">
                <TextArea rows={3} placeholder="Thank you for your purchase!&#10;Please come again" />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="auto_print_receipt" label="Auto Print Receipt" valuePropName="checked">
                <Switch checkedChildren="Yes" unCheckedChildren="No" />
              </Form.Item>
              <Text type="secondary">Automatically print receipt after completing a sale</Text>
            </Col>
          </Row>
        </Card>
      ),
    },
    {
      key: 'inventory',
      label: (
        <span>
          <InboxOutlined />
          Inventory
        </span>
      ),
      children: (
        <Card>
          <Row gutter={[16, 16]}>
            <Col xs={24} md={12}>
              <Form.Item name="low_stock_threshold" label="Low Stock Threshold">
                <InputNumber min={1} max={1000} style={{ width: '100%' }} size="large" addonAfter="units" />
              </Form.Item>
              <Text type="secondary">Products with stock below this level will show alerts</Text>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="allow_negative_stock" label="Allow Negative Stock" valuePropName="checked">
                <Switch checkedChildren="Yes" unCheckedChildren="No" />
              </Form.Item>
              <Text type="secondary">Allow sales even when product is out of stock</Text>
            </Col>
          </Row>
        </Card>
      ),
    },
    {
      key: 'loyalty',
      label: (
        <span>
          <GiftOutlined />
          Loyalty
        </span>
      ),
      children: (
        <Card>
          <Row gutter={[16, 16]}>
            <Col xs={24}>
              <Form.Item name="loyalty_enabled" label="Enable Loyalty Program" valuePropName="checked">
                <Switch checkedChildren="Enabled" unCheckedChildren="Disabled" />
              </Form.Item>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="loyalty_points_per_currency" label="Points Per Currency Unit">
                <InputNumber min={0} precision={2} style={{ width: '100%' }} size="large" />
              </Form.Item>
              <Text type="secondary">Points earned per 1 currency unit spent</Text>
            </Col>
            <Col xs={24} md={12}>
              <Form.Item name="loyalty_point_value" label="Point Value (in currency)">
                <InputNumber min={0} precision={4} style={{ width: '100%' }} size="large" />
              </Form.Item>
              <Text type="secondary">Value of 1 point when redeemed</Text>
            </Col>
          </Row>
        </Card>
      ),
    },
  ]

  return (
    <>
      <Head title="Settings" />

      <GlobalPageHeader
        title="Settings"
        parentPageTitle="Dashboard"
        parentPageRoute="dashboard"
        extraContent={
          <Space>
            <Button icon={<ReloadOutlined />} onClick={() => router.reload()}>
              Refresh
            </Button>
            <Button type="primary" icon={<SaveOutlined />} onClick={handleSave} loading={saving}>
              Save All Settings
            </Button>
          </Space>
        }
      />

      <Form form={form} layout="vertical">
        <Tabs
          activeKey={activeTab}
          onChange={setActiveTab}
          items={tabItems}
          tabBarStyle={{ marginBottom: 16 }}
        />
      </Form>
    </>
  )
}
