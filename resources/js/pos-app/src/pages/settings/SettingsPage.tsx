import { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Typography,
  Card,
  Form,
  Input,
  InputNumber,
  Switch,
  Button,
  Tabs,
  Table,
  Modal,
  Space,
  Upload,
  message,
  Popconfirm,
  Tag,
  Divider,
  Row,
  Col,
} from 'antd';
import {
  SaveOutlined,
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  UploadOutlined,
  SettingOutlined,
  PercentageOutlined,
  ShopOutlined,
  FileTextOutlined,
} from '@ant-design/icons';
import {
  settingsService,
  type SettingGroup,
  type TaxRate,
} from '../../api/services/settings.service';

const { Title } = Typography;
const { TextArea } = Input;

export default function SettingsPage() {
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const [taxForm] = Form.useForm();
  const [activeTab, setActiveTab] = useState('general');
  const [taxModalOpen, setTaxModalOpen] = useState(false);
  const [editingTax, setEditingTax] = useState<TaxRate | null>(null);

  const { data: settingGroups } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsService.getSettings,
  });

  const { data: taxRates, isLoading: taxLoading } = useQuery({
    queryKey: ['tax-rates'],
    queryFn: settingsService.getTaxRates,
  });

  useEffect(() => {
    if (settingGroups) {
      const values: Record<string, any> = {};
      settingGroups.forEach((group) => {
        group.settings?.forEach((setting) => {
          values[setting.key] = setting.type === 'boolean'
            ? setting.value === 'true' || setting.value === true
            : setting.value;
        });
      });
      form.setFieldsValue(values);
    }
  }, [settingGroups, form]);

  const updateMutation = useMutation({
    mutationFn: settingsService.updateSettings,
    onSuccess: () => {
      message.success('Settings saved successfully');
      queryClient.invalidateQueries({ queryKey: ['settings'] });
    },
    onError: () => {
      message.error('Failed to save settings');
    },
  });

  const createTaxMutation = useMutation({
    mutationFn: settingsService.createTaxRate,
    onSuccess: () => {
      message.success('Tax rate created');
      setTaxModalOpen(false);
      taxForm.resetFields();
      queryClient.invalidateQueries({ queryKey: ['tax-rates'] });
    },
  });

  const updateTaxMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<TaxRate> }) =>
      settingsService.updateTaxRate(id, data),
    onSuccess: () => {
      message.success('Tax rate updated');
      setTaxModalOpen(false);
      setEditingTax(null);
      taxForm.resetFields();
      queryClient.invalidateQueries({ queryKey: ['tax-rates'] });
    },
  });

  const deleteTaxMutation = useMutation({
    mutationFn: settingsService.deleteTaxRate,
    onSuccess: () => {
      message.success('Tax rate deleted');
      queryClient.invalidateQueries({ queryKey: ['tax-rates'] });
    },
  });

  const handleSaveSettings = (values: any) => {
    updateMutation.mutate(values);
  };

  const handleTaxSubmit = (values: any) => {
    if (editingTax) {
      updateTaxMutation.mutate({ id: editingTax.id, data: values });
    } else {
      createTaxMutation.mutate(values);
    }
  };

  const getGroupIcon = (slug: string) => {
    switch (slug) {
      case 'general':
        return <ShopOutlined />;
      case 'tax':
        return <PercentageOutlined />;
      case 'receipt':
        return <FileTextOutlined />;
      default:
        return <SettingOutlined />;
    }
  };

  const renderSettingInput = (setting: any) => {
    switch (setting.type) {
      case 'boolean':
        return (
          <Form.Item
            key={setting.key}
            name={setting.key}
            label={setting.label}
            valuePropName="checked"
          >
            <Switch />
          </Form.Item>
        );
      case 'number':
        return (
          <Form.Item
            key={setting.key}
            name={setting.key}
            label={setting.label}
          >
            <InputNumber style={{ width: '100%' }} />
          </Form.Item>
        );
      case 'select':
        return (
          <Form.Item
            key={setting.key}
            name={setting.key}
            label={setting.label}
          >
            <Input />
          </Form.Item>
        );
      case 'image':
        return (
          <Form.Item
            key={setting.key}
            name={setting.key}
            label={setting.label}
          >
            <Upload
              listType="picture-card"
              maxCount={1}
              beforeUpload={() => false}
            >
              <div>
                <UploadOutlined />
                <div style={{ marginTop: 8 }}>Upload</div>
              </div>
            </Upload>
          </Form.Item>
        );
      default:
        return (
          <Form.Item
            key={setting.key}
            name={setting.key}
            label={setting.label}
          >
            {setting.key.includes('address') || setting.key.includes('description') ? (
              <TextArea rows={3} />
            ) : (
              <Input />
            )}
          </Form.Item>
        );
    }
  };

  const taxColumns = [
    { title: 'Name', dataIndex: 'name', key: 'name' },
    {
      title: 'Rate',
      dataIndex: 'rate',
      key: 'rate',
      render: (rate: number) => `${rate}%`,
    },
    {
      title: 'Default',
      dataIndex: 'is_default',
      key: 'is_default',
      render: (isDefault: boolean) =>
        isDefault ? <Tag color="blue">Default</Tag> : null,
    },
    {
      title: 'Status',
      dataIndex: 'is_active',
      key: 'is_active',
      render: (isActive: boolean) => (
        <Tag color={isActive ? 'green' : 'red'}>
          {isActive ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_: any, record: TaxRate) => (
        <Space>
          <Button
            type="text"
            icon={<EditOutlined />}
            onClick={() => {
              setEditingTax(record);
              taxForm.setFieldsValue(record);
              setTaxModalOpen(true);
            }}
          />
          <Popconfirm
            title="Delete this tax rate?"
            onConfirm={() => deleteTaxMutation.mutate(record.id)}
          >
            <Button type="text" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  const tabItems = [
    ...(settingGroups?.map((group: SettingGroup) => ({
      key: group.slug,
      label: (
        <span>
          {getGroupIcon(group.slug)} {group.name}
        </span>
      ),
      children: (
        <Card>
          <Form
            form={form}
            layout="vertical"
            onFinish={handleSaveSettings}
          >
            <Row gutter={24}>
              {group.settings?.map((setting, index) => (
                <Col span={12} key={setting.key}>
                  {renderSettingInput(setting)}
                </Col>
              ))}
            </Row>
            <Divider />
            <Button
              type="primary"
              htmlType="submit"
              icon={<SaveOutlined />}
              loading={updateMutation.isPending}
            >
              Save Settings
            </Button>
          </Form>
        </Card>
      ),
    })) || []),
    {
      key: 'tax-rates',
      label: (
        <span>
          <PercentageOutlined /> Tax Rates
        </span>
      ),
      children: (
        <Card
          title="Tax Rates"
          extra={
            <Button
              type="primary"
              icon={<PlusOutlined />}
              onClick={() => {
                setEditingTax(null);
                taxForm.resetFields();
                setTaxModalOpen(true);
              }}
            >
              Add Tax Rate
            </Button>
          }
        >
          <Table
            dataSource={taxRates}
            columns={taxColumns}
            rowKey="id"
            loading={taxLoading}
            pagination={false}
          />
        </Card>
      ),
    },
  ];

  return (
    <div>
      <Title level={4} style={{ marginBottom: 24 }}>Settings</Title>

      <Tabs
        activeKey={activeTab}
        onChange={setActiveTab}
        items={tabItems}
        tabPosition="left"
        style={{ minHeight: 500 }}
      />

      {/* Tax Rate Modal */}
      <Modal
        title={editingTax ? 'Edit Tax Rate' : 'Add Tax Rate'}
        open={taxModalOpen}
        onCancel={() => {
          setTaxModalOpen(false);
          setEditingTax(null);
          taxForm.resetFields();
        }}
        footer={null}
      >
        <Form
          form={taxForm}
          layout="vertical"
          onFinish={handleTaxSubmit}
          initialValues={{ is_active: true, is_default: false }}
        >
          <Form.Item
            name="name"
            label="Name"
            rules={[{ required: true, message: 'Please enter tax name' }]}
          >
            <Input placeholder="e.g., VAT 15%" />
          </Form.Item>

          <Form.Item
            name="rate"
            label="Rate (%)"
            rules={[{ required: true, message: 'Please enter tax rate' }]}
          >
            <InputNumber
              style={{ width: '100%' }}
              min={0}
              max={100}
              precision={2}
              placeholder="15.00"
            />
          </Form.Item>

          <Form.Item name="description" label="Description">
            <TextArea rows={2} />
          </Form.Item>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="is_default" valuePropName="checked">
                <Switch /> Default Tax Rate
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="is_active" valuePropName="checked">
                <Switch /> Active
              </Form.Item>
            </Col>
          </Row>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={() => setTaxModalOpen(false)}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createTaxMutation.isPending || updateTaxMutation.isPending}
            >
              {editingTax ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>
    </div>
  );
}
