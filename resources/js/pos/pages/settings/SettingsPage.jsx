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
  List,
  Avatar,
  Spin,
  Alert,
  Checkbox,
  Steps,
  Result,
  Empty,
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
  MobileOutlined,
  MedicineBoxOutlined,
  ToolOutlined,
  ShoppingCartOutlined,
  LaptopOutlined,
  SkinOutlined,
  CoffeeOutlined,
  CheckCircleOutlined,
  ImportOutlined,
  EyeOutlined,
  RocketOutlined,
} from '@ant-design/icons';
import { settingsService } from '../../api/services/settings.service';
import { useBusinessTypeStore } from '../../store/businessTypeStore';

const { Title, Text, Paragraph } = Typography;
const { TextArea } = Input;

// Icon mapping for business types
const iconMap = {
  MobileOutlined: <MobileOutlined style={{ fontSize: 32 }} />,
  MedicineBoxOutlined: <MedicineBoxOutlined style={{ fontSize: 32 }} />,
  ToolOutlined: <ToolOutlined style={{ fontSize: 32 }} />,
  ShoppingCartOutlined: <ShoppingCartOutlined style={{ fontSize: 32 }} />,
  LaptopOutlined: <LaptopOutlined style={{ fontSize: 32 }} />,
  SkinOutlined: <SkinOutlined style={{ fontSize: 32 }} />,
  CoffeeOutlined: <CoffeeOutlined style={{ fontSize: 32 }} />,
};

export default function SettingsPage() {
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const [taxForm] = Form.useForm();
  const [activeTab, setActiveTab] = useState('business-type');
  const [taxModalOpen, setTaxModalOpen] = useState(false);
  const [editingTax, setEditingTax] = useState(null);

  // Business type wizard state
  const [wizardStep, setWizardStep] = useState(0);
  const [selectedType, setSelectedType] = useState(null);
  const [importProducts, setImportProducts] = useState(true);
  const [clearExisting, setClearExisting] = useState(false);

  const { currentType, setCurrentType, setAvailableTypes } = useBusinessTypeStore();

  const { data: settingGroups, isLoading } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsService.getSettings,
  });

  const { data: taxRates, isLoading: taxLoading } = useQuery({
    queryKey: ['tax-rates'],
    queryFn: settingsService.getTaxRates,
  });

  const { data: businessTypes, isLoading: typesLoading, refetch: refetchTypes } = useQuery({
    queryKey: ['business-types'],
    queryFn: settingsService.getBusinessTypes,
  });

  const { data: currentBusinessType, refetch: refetchCurrentType } = useQuery({
    queryKey: ['current-business-type'],
    queryFn: settingsService.getCurrentBusinessType,
  });

  const { data: previewData, isLoading: previewLoading, refetch: refetchPreview } = useQuery({
    queryKey: ['business-type-preview', selectedType?.id],
    queryFn: () => settingsService.previewBusinessType(selectedType?.id),
    enabled: !!selectedType?.id && wizardStep === 1,
  });

  useEffect(() => {
    if (businessTypes) {
      setAvailableTypes(businessTypes);
    }
  }, [businessTypes, setAvailableTypes]);

  useEffect(() => {
    if (currentBusinessType) {
      setCurrentType(currentBusinessType);
    }
  }, [currentBusinessType, setCurrentType]);

  useEffect(() => {
    if (settingGroups) {
      const values = {};
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

  const seedTypesMutation = useMutation({
    mutationFn: settingsService.seedBusinessTypes,
    onSuccess: () => {
      message.success('Business types seeded successfully');
      refetchTypes();
    },
    onError: () => {
      message.error('Failed to seed business types');
    },
  });

  const applyTypeMutation = useMutation({
    mutationFn: ({ id, options }) => settingsService.applyBusinessType(id, options),
    onSuccess: (data) => {
      message.success(data.message);
      setWizardStep(3);
      refetchCurrentType();
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['categories'] });
    },
    onError: () => {
      message.error('Failed to apply business type');
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
    mutationFn: ({ id, data }) => settingsService.updateTaxRate(id, data),
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

  const handleSaveSettings = (values) => {
    updateMutation.mutate(values);
  };

  const handleTaxSubmit = (values) => {
    if (editingTax) {
      updateTaxMutation.mutate({ id: editingTax.id, data: values });
    } else {
      createTaxMutation.mutate(values);
    }
  };

  const handleSelectType = (type) => {
    setSelectedType(type);
    setWizardStep(1);
  };

  const handleApplyType = () => {
    applyTypeMutation.mutate({
      id: selectedType.id,
      options: {
        import_products: importProducts,
        clear_existing: clearExisting,
      },
    });
  };

  const resetWizard = () => {
    setWizardStep(0);
    setSelectedType(null);
    setImportProducts(true);
    setClearExisting(false);
  };

  const getGroupIcon = (slug) => {
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

  const renderSettingInput = (setting) => {
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
      render: (rate) => `${rate}%`,
    },
    {
      title: 'Default',
      dataIndex: 'is_default',
      key: 'is_default',
      render: (isDefault) =>
        isDefault ? <Tag color="blue">Default</Tag> : null,
    },
    {
      title: 'Status',
      dataIndex: 'is_active',
      key: 'is_active',
      render: (isActive) => (
        <Tag color={isActive ? 'green' : 'red'}>
          {isActive ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_, record) => (
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

  // Business Type Selection Step
  const renderTypeSelection = () => (
    <div>
      {currentType && (
        <Alert
          message={`Current Business Type: ${currentType.name}`}
          description={currentType.description}
          type="info"
          showIcon
          style={{ marginBottom: 24 }}
        />
      )}

      {(!businessTypes || businessTypes.length === 0) ? (
        <Empty
          description="No business types available"
          style={{ padding: 40 }}
        >
          <Button
            type="primary"
            icon={<RocketOutlined />}
            onClick={() => seedTypesMutation.mutate()}
            loading={seedTypesMutation.isPending}
          >
            Initialize Business Types
          </Button>
        </Empty>
      ) : (
        <List
          grid={{ gutter: 16, xs: 1, sm: 2, md: 3, lg: 3, xl: 4 }}
          dataSource={businessTypes}
          loading={typesLoading}
          renderItem={(type) => (
            <List.Item>
              <Card
                hoverable
                onClick={() => handleSelectType(type)}
                style={{
                  textAlign: 'center',
                  borderColor: currentType?.id === type.id ? '#1890ff' : undefined,
                  borderWidth: currentType?.id === type.id ? 2 : 1,
                }}
              >
                <div style={{ marginBottom: 16 }}>
                  {iconMap[type.icon] || <ShopOutlined style={{ fontSize: 32 }} />}
                </div>
                <Title level={5} style={{ marginBottom: 4 }}>{type.name}</Title>
                <Text type="secondary" style={{ fontSize: 12 }}>{type.name_ar}</Text>
                <Paragraph
                  type="secondary"
                  ellipsis={{ rows: 2 }}
                  style={{ marginTop: 8, marginBottom: 0, fontSize: 12 }}
                >
                  {type.description}
                </Paragraph>
                {currentType?.id === type.id && (
                  <Tag color="blue" style={{ marginTop: 8 }}>Current</Tag>
                )}
              </Card>
            </List.Item>
          )}
        />
      )}
    </div>
  );

  // Preview Step
  const renderPreview = () => (
    <div>
      <Button onClick={() => setWizardStep(0)} style={{ marginBottom: 16 }}>
        Back to Selection
      </Button>

      <Card title={`Preview: ${selectedType?.name}`}>
        {previewLoading ? (
          <div style={{ textAlign: 'center', padding: 40 }}>
            <Spin size="large" />
            <div style={{ marginTop: 16 }}>Loading preview...</div>
          </div>
        ) : previewData ? (
          <>
            <Row gutter={16} style={{ marginBottom: 24 }}>
              <Col span={12}>
                <Card size="small" title="Categories">
                  <Text strong>{previewData.total_categories}</Text> categories
                  <div style={{ marginTop: 8 }}>
                    {previewData.categories?.slice(0, 5).map((cat, i) => (
                      <Tag key={i}>{cat.name}</Tag>
                    ))}
                    {previewData.categories?.length > 5 && (
                      <Tag>+{previewData.categories.length - 5} more</Tag>
                    )}
                  </div>
                </Card>
              </Col>
              <Col span={12}>
                <Card size="small" title="Products">
                  <Text strong>{previewData.total_products}</Text> products
                </Card>
              </Col>
            </Row>

            <Title level={5}>Sample Products</Title>
            <Table
              dataSource={previewData.products}
              rowKey="sku"
              pagination={false}
              size="small"
              columns={[
                { title: 'SKU', dataIndex: 'sku', key: 'sku', width: 120 },
                { title: 'Name', dataIndex: 'name', key: 'name' },
                { title: 'Arabic Name', dataIndex: 'name_ar', key: 'name_ar' },
                {
                  title: 'Price',
                  dataIndex: 'sale_price',
                  key: 'sale_price',
                  render: (price) => `SAR ${price}`,
                  width: 100,
                },
              ]}
            />

            <Divider />

            <Title level={5}>Import Options</Title>
            <div style={{ marginBottom: 16 }}>
              <Checkbox
                checked={importProducts}
                onChange={(e) => setImportProducts(e.target.checked)}
              >
                Import sample products and categories
              </Checkbox>
            </div>
            {importProducts && (
              <div style={{ marginBottom: 16, marginLeft: 24 }}>
                <Checkbox
                  checked={clearExisting}
                  onChange={(e) => setClearExisting(e.target.checked)}
                >
                  <Text type="danger">Clear existing products first (Warning: This will delete current products)</Text>
                </Checkbox>
              </div>
            )}

            <div style={{ marginTop: 24 }}>
              <Space>
                <Button onClick={() => setWizardStep(0)}>Back</Button>
                <Button
                  type="primary"
                  icon={<ImportOutlined />}
                  onClick={handleApplyType}
                  loading={applyTypeMutation.isPending}
                >
                  Apply Business Type
                </Button>
              </Space>
            </div>
          </>
        ) : (
          <Empty description="No preview available for this business type" />
        )}
      </Card>
    </div>
  );

  // Success Step
  const renderSuccess = () => (
    <Result
      status="success"
      title="Business Type Applied Successfully"
      subTitle={`Your store is now configured as ${selectedType?.name}`}
      extra={[
        <Button type="primary" key="done" onClick={resetWizard}>
          Done
        </Button>,
        <Button key="products" onClick={() => window.location.href = '/pos/products'}>
          View Products
        </Button>,
      ]}
    />
  );

  // Business Type Tab Content
  const renderBusinessTypeTab = () => (
    <Card>
      <Steps
        current={wizardStep}
        style={{ marginBottom: 32 }}
        items={[
          { title: 'Select Type', icon: <ShopOutlined /> },
          { title: 'Preview & Configure', icon: <EyeOutlined /> },
          { title: 'Apply', icon: <ImportOutlined /> },
          { title: 'Complete', icon: <CheckCircleOutlined /> },
        ]}
      />

      {wizardStep === 0 && renderTypeSelection()}
      {wizardStep === 1 && renderPreview()}
      {wizardStep === 2 && (
        <div style={{ textAlign: 'center', padding: 40 }}>
          <Spin size="large" />
          <div style={{ marginTop: 16 }}>Applying business type...</div>
        </div>
      )}
      {wizardStep === 3 && renderSuccess()}
    </Card>
  );

  const tabItems = [
    {
      key: 'business-type',
      label: (
        <span>
          <ShopOutlined /> Business Type
        </span>
      ),
      children: renderBusinessTypeTab(),
    },
    ...(settingGroups?.map((group) => ({
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
              {group.settings?.map((setting) => (
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
