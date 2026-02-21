import { useState } from 'react'
import { Head, usePage } from '@inertiajs/react'
import { Card, Tabs, Form, Input, Button, Switch, InputNumber, Divider, message, Row, Col, Select } from 'antd'
import { SaveOutlined, ShopOutlined, DollarOutlined, PrinterOutlined, BellOutlined } from '@ant-design/icons'
import { useMutation } from '@tanstack/react-query'
import axios from 'axios'
import GlobalPageHeader from '@/Components/GlobalPageHeader'

const { Option } = Select
const { TextArea } = Input

export default function Settings() {
  const [activeTab, setActiveTab] = useState('store')
  const [storeForm] = Form.useForm()
  const [taxForm] = Form.useForm()
  const [receiptForm] = Form.useForm()
  const [notificationForm] = Form.useForm()

  const saveMutation = useMutation({
    mutationFn: ({ section, data }) => axios.put(`/settings/${section}`, data),
    onSuccess: () => message.success('Settings saved successfully'),
    onError: (error) => message.error(error.response?.data?.message || 'Failed to save settings'),
  })

  const handleSaveStore = async () => {
    try {
      const values = await storeForm.validateFields()
      saveMutation.mutate({ section: 'store', data: values })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleSaveTax = async () => {
    try {
      const values = await taxForm.validateFields()
      saveMutation.mutate({ section: 'tax', data: values })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleSaveReceipt = async () => {
    try {
      const values = await receiptForm.validateFields()
      saveMutation.mutate({ section: 'receipt', data: values })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const handleSaveNotifications = async () => {
    try {
      const values = await notificationForm.validateFields()
      saveMutation.mutate({ section: 'notifications', data: values })
    } catch (error) {
      console.error('Validation failed:', error)
    }
  }

  const tabItems = [
    {
      key: 'store',
      label: (
        <span>
          <ShopOutlined />
          Store
        </span>
      ),
      children: (
        <Card>
          <Form form={storeForm} layout="vertical" initialValues={{ currency: 'SAR' }}>
            <Row gutter={16}>
              <Col span={12}>
                <Form.Item name="store_name" label="Store Name" rules={[{ required: true }]}>
                  <Input placeholder="Enter store name" />
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item name="phone" label="Phone Number">
                  <Input placeholder="Enter phone number" />
                </Form.Item>
              </Col>
            </Row>
            <Row gutter={16}>
              <Col span={12}>
                <Form.Item name="email" label="Email">
                  <Input placeholder="Enter email" />
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item name="currency" label="Currency">
                  <Select>
                    <Option value="SAR">SAR - Saudi Riyal</Option>
                    <Option value="USD">USD - US Dollar</Option>
                    <Option value="EUR">EUR - Euro</Option>
                  </Select>
                </Form.Item>
              </Col>
            </Row>
            <Form.Item name="address" label="Address">
              <TextArea rows={2} placeholder="Enter store address" />
            </Form.Item>
            <Button type="primary" icon={<SaveOutlined />} onClick={handleSaveStore} loading={saveMutation.isPending}>
              Save Store Settings
            </Button>
          </Form>
        </Card>
      ),
    },
    {
      key: 'tax',
      label: (
        <span>
          <DollarOutlined />
          Tax & Pricing
        </span>
      ),
      children: (
        <Card>
          <Form form={taxForm} layout="vertical" initialValues={{ tax_rate: 15, prices_include_tax: true }}>
            <Row gutter={16}>
              <Col span={12}>
                <Form.Item name="tax_rate" label="Tax Rate (%)">
                  <InputNumber min={0} max={100} precision={2} style={{ width: '100%' }} />
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item name="tax_number" label="Tax Number (VAT)">
                  <Input placeholder="Enter tax registration number" />
                </Form.Item>
              </Col>
            </Row>
            <Form.Item name="prices_include_tax" label="Prices Include Tax" valuePropName="checked">
              <Switch checkedChildren="Yes" unCheckedChildren="No" />
            </Form.Item>
            <Button type="primary" icon={<SaveOutlined />} onClick={handleSaveTax} loading={saveMutation.isPending}>
              Save Tax Settings
            </Button>
          </Form>
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
          <Form form={receiptForm} layout="vertical">
            <Form.Item name="receipt_header" label="Receipt Header">
              <TextArea rows={3} placeholder="Text to appear at the top of receipts" />
            </Form.Item>
            <Form.Item name="receipt_footer" label="Receipt Footer">
              <TextArea rows={3} placeholder="Text to appear at the bottom of receipts (e.g., Thank you message)" />
            </Form.Item>
            <Row gutter={16}>
              <Col span={12}>
                <Form.Item name="show_tax_breakdown" label="Show Tax Breakdown" valuePropName="checked">
                  <Switch checkedChildren="Yes" unCheckedChildren="No" />
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item name="auto_print" label="Auto Print Receipt" valuePropName="checked">
                  <Switch checkedChildren="Yes" unCheckedChildren="No" />
                </Form.Item>
              </Col>
            </Row>
            <Button type="primary" icon={<SaveOutlined />} onClick={handleSaveReceipt} loading={saveMutation.isPending}>
              Save Receipt Settings
            </Button>
          </Form>
        </Card>
      ),
    },
    {
      key: 'notifications',
      label: (
        <span>
          <BellOutlined />
          Notifications
        </span>
      ),
      children: (
        <Card>
          <Form form={notificationForm} layout="vertical" initialValues={{ low_stock_threshold: 10 }}>
            <Form.Item name="low_stock_alerts" label="Low Stock Alerts" valuePropName="checked">
              <Switch checkedChildren="Enabled" unCheckedChildren="Disabled" />
            </Form.Item>
            <Form.Item name="low_stock_threshold" label="Low Stock Threshold">
              <InputNumber min={1} style={{ width: 200 }} addonAfter="units" />
            </Form.Item>
            <Divider />
            <Form.Item name="daily_sales_report" label="Daily Sales Report Email" valuePropName="checked">
              <Switch checkedChildren="Enabled" unCheckedChildren="Disabled" />
            </Form.Item>
            <Form.Item name="report_email" label="Report Email Address">
              <Input placeholder="Enter email for reports" style={{ width: 300 }} />
            </Form.Item>
            <Button type="primary" icon={<SaveOutlined />} onClick={handleSaveNotifications} loading={saveMutation.isPending}>
              Save Notification Settings
            </Button>
          </Form>
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
      />

      <Tabs activeKey={activeTab} onChange={setActiveTab} items={tabItems} />
    </>
  )
}
