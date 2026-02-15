import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Card,
  Button,
  Input,
  Space,
  Typography,
  message,
  Form,
  Select,
  Table,
  InputNumber,
  Row,
  Col,
  Divider,
  Popconfirm,
  AutoComplete,
  Alert,
} from 'antd';
import {
  ArrowLeftOutlined,
  PlusOutlined,
  DeleteOutlined,
  SaveOutlined,
  SendOutlined,
  SwapOutlined,
} from '@ant-design/icons';
import { stockTransferService } from '../../api/services/stockTransfer.service';
import { storeService } from '../../api/services/store.service';

const { Title, Text } = Typography;

export default function StockTransferFormPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const [lines, setLines] = useState([]);
  const [productSearch, setProductSearch] = useState('');
  const [productOptions, setProductOptions] = useState([]);
  const [fromStoreId, setFromStoreId] = useState(null);

  const isEditing = !!id;

  const { data: transfer, isLoading: transferLoading } = useQuery({
    queryKey: ['stock-transfer', id],
    queryFn: () => stockTransferService.getStockTransfer(Number(id)),
    enabled: isEditing,
  });

  const { data: stores } = useQuery({
    queryKey: ['stores'],
    queryFn: () => storeService.getStores(),
  });

  const { data: storeInventory } = useQuery({
    queryKey: ['store-inventory-transfer', fromStoreId, productSearch],
    queryFn: () => storeService.getStoreInventory(fromStoreId, { search: productSearch, per_page: 20 }),
    enabled: !!fromStoreId && productSearch.length > 1,
  });

  useEffect(() => {
    if (transfer && isEditing) {
      form.setFieldsValue({
        from_store_id: transfer.from_store_id,
        to_store_id: transfer.to_store_id,
        notes: transfer.notes,
      });
      setFromStoreId(transfer.from_store_id);

      if (transfer.items) {
        setLines(
          transfer.items.map((item, index) => ({
            key: `line-${index}`,
            product_id: item.product_id,
            product_variant_id: item.product_variant_id,
            product_name: item.product?.name || '',
            sku: item.product?.sku || '',
            available_qty: 0,
            quantity_requested: item.quantity_requested,
          }))
        );
      }
    }
  }, [transfer, isEditing, form]);

  useEffect(() => {
    if (storeInventory?.data) {
      setProductOptions(
        storeInventory.data.map((inv) => ({
          value: inv.product_id,
          label: `${inv.product?.name} (${inv.product?.sku || 'No SKU'}) - Available: ${inv.quantity}`,
          inventory: inv,
        }))
      );
    }
  }, [storeInventory]);

  const createMutation = useMutation({
    mutationFn: stockTransferService.createStockTransfer,
    onSuccess: (data) => {
      message.success('Stock transfer created successfully');
      queryClient.invalidateQueries({ queryKey: ['stock-transfers'] });
      navigate(`/stock-transfers/${data.id}`);
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create stock transfer');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => stockTransferService.updateStockTransfer(id, data),
    onSuccess: () => {
      message.success('Stock transfer updated successfully');
      queryClient.invalidateQueries({ queryKey: ['stock-transfers'] });
      queryClient.invalidateQueries({ queryKey: ['stock-transfer', id] });
      navigate(`/stock-transfers/${id}`);
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update stock transfer');
    },
  });

  const submitMutation = useMutation({
    mutationFn: stockTransferService.submitStockTransfer,
    onSuccess: () => {
      message.success('Stock transfer submitted for approval');
      queryClient.invalidateQueries({ queryKey: ['stock-transfers'] });
      navigate('/stock-transfers');
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to submit stock transfer');
    },
  });

  const handleAddLine = () => {
    if (!fromStoreId) {
      message.warning('Please select the source store first');
      return;
    }

    setLines([
      ...lines,
      {
        key: `line-${Date.now()}`,
        product_id: null,
        product_variant_id: null,
        product_name: '',
        sku: '',
        available_qty: 0,
        quantity_requested: 1,
      },
    ]);
  };

  const handleRemoveLine = (key) => {
    setLines(lines.filter((l) => l.key !== key));
  };

  const handleLineChange = (key, field, value) => {
    setLines(
      lines.map((line) => {
        if (line.key === key) {
          return { ...line, [field]: value };
        }
        return line;
      })
    );
  };

  const handleProductSelect = (key, productId) => {
    const option = productOptions.find((o) => o.value === productId);
    if (option) {
      const inv = option.inventory;
      setLines(
        lines.map((line) => {
          if (line.key === key) {
            return {
              ...line,
              product_id: inv.product_id,
              product_variant_id: inv.product_variant_id,
              product_name: inv.product?.name || '',
              sku: inv.product?.sku || '',
              available_qty: inv.quantity,
              quantity_requested: Math.min(1, inv.quantity),
            };
          }
          return line;
        })
      );
    }
  };

  const handleFromStoreChange = (storeId) => {
    setFromStoreId(storeId);
    setLines([]);
    setProductOptions([]);
  };

  const handleSave = () => {
    form.validateFields().then((values) => {
      if (lines.length === 0) {
        message.error('Please add at least one item');
        return;
      }

      if (lines.some((l) => !l.product_id)) {
        message.error('Please select a product for all items');
        return;
      }

      if (values.from_store_id === values.to_store_id) {
        message.error('Source and destination stores must be different');
        return;
      }

      const data = {
        from_store_id: values.from_store_id,
        to_store_id: values.to_store_id,
        notes: values.notes,
        items: lines.map((l) => ({
          product_id: l.product_id,
          product_variant_id: l.product_variant_id || undefined,
          quantity_requested: l.quantity_requested,
        })),
      };

      if (isEditing) {
        updateMutation.mutate({ id: Number(id), data });
      } else {
        createMutation.mutate(data);
      }
    });
  };

  const handleSubmitForApproval = () => {
    if (isEditing && id) {
      submitMutation.mutate(Number(id));
    }
  };

  const totalItems = lines.reduce((sum, l) => sum + l.quantity_requested, 0);

  const lineColumns = [
    {
      title: 'Product',
      key: 'product',
      width: 350,
      render: (_, record) => (
        <AutoComplete
          value={record.product_name}
          options={productOptions}
          onSearch={setProductSearch}
          onSelect={(value) => handleProductSelect(record.key, value)}
          onChange={(value) => handleLineChange(record.key, 'product_name', value)}
          placeholder="Search product..."
          style={{ width: '100%' }}
          disabled={!fromStoreId}
        />
      ),
    },
    {
      title: 'SKU',
      dataIndex: 'sku',
      key: 'sku',
      width: 120,
    },
    {
      title: 'Available',
      dataIndex: 'available_qty',
      key: 'available_qty',
      width: 100,
      render: (qty) => <Text type={qty === 0 ? 'danger' : undefined}>{qty}</Text>,
    },
    {
      title: 'Quantity',
      key: 'quantity',
      width: 120,
      render: (_, record) => (
        <InputNumber
          min={1}
          max={record.available_qty || undefined}
          value={record.quantity_requested}
          onChange={(val) => handleLineChange(record.key, 'quantity_requested', val || 1)}
          style={{ width: '100%' }}
          status={record.quantity_requested > record.available_qty ? 'error' : undefined}
        />
      ),
    },
    {
      title: '',
      key: 'actions',
      width: 50,
      render: (_, record) => (
        <Popconfirm title="Remove this item?" onConfirm={() => handleRemoveLine(record.key)}>
          <Button type="text" danger icon={<DeleteOutlined />} />
        </Popconfirm>
      ),
    },
  ];

  if (transferLoading && isEditing) {
    return <Card loading />;
  }

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 24 }}>
        <Button icon={<ArrowLeftOutlined />} onClick={() => navigate('/stock-transfers')}>
          Back
        </Button>
        <Title level={4} style={{ margin: 0 }}>
          {isEditing ? `Edit Stock Transfer - ${transfer?.transfer_number}` : 'New Stock Transfer'}
        </Title>
      </div>

      <Form form={form} layout="vertical">
        <Row gutter={24}>
          <Col span={16}>
            <Card title="Transfer Details" style={{ marginBottom: 24 }}>
              <Row gutter={16} align="middle">
                <Col span={10}>
                  <Form.Item
                    name="from_store_id"
                    label="From Store (Source)"
                    rules={[{ required: true, message: 'Source store is required' }]}
                  >
                    <Select
                      placeholder="Select source store"
                      onChange={handleFromStoreChange}
                      disabled={isEditing && lines.length > 0}
                    >
                      {stores?.map((store) => (
                        <Select.Option key={store.id} value={store.id}>
                          {store.name} ({store.code})
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                </Col>
                <Col span={4} style={{ textAlign: 'center', paddingTop: 8 }}>
                  <SwapOutlined style={{ fontSize: 24, color: '#1890ff' }} />
                </Col>
                <Col span={10}>
                  <Form.Item
                    name="to_store_id"
                    label="To Store (Destination)"
                    rules={[{ required: true, message: 'Destination store is required' }]}
                  >
                    <Select placeholder="Select destination store">
                      {stores
                        ?.filter((s) => s.id !== fromStoreId)
                        .map((store) => (
                          <Select.Option key={store.id} value={store.id}>
                            {store.name} ({store.code})
                          </Select.Option>
                        ))}
                    </Select>
                  </Form.Item>
                </Col>
              </Row>

              <Form.Item name="notes" label="Notes">
                <Input.TextArea rows={2} placeholder="Reason for transfer..." />
              </Form.Item>
            </Card>

            <Card
              title="Transfer Items"
              extra={
                <Button type="primary" icon={<PlusOutlined />} onClick={handleAddLine} disabled={!fromStoreId}>
                  Add Item
                </Button>
              }
            >
              {!fromStoreId && (
                <Alert
                  message="Please select a source store to add items"
                  type="info"
                  showIcon
                  style={{ marginBottom: 16 }}
                />
              )}

              <Table
                dataSource={lines}
                columns={lineColumns}
                rowKey="key"
                pagination={false}
                scroll={{ x: 750 }}
                locale={{
                  emptyText: fromStoreId
                    ? 'No items added. Click "Add Item" to start.'
                    : 'Select a source store first.',
                }}
              />
            </Card>
          </Col>

          <Col span={8}>
            <Card title="Transfer Summary" style={{ position: 'sticky', top: 24 }}>
              <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text>Total Items:</Text>
                  <Text strong>{lines.length}</Text>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text>Total Quantity:</Text>
                  <Text strong>{totalItems}</Text>
                </div>
              </Space>

              <Divider />

              <Space direction="vertical" style={{ width: '100%' }}>
                <Button
                  type="primary"
                  icon={<SaveOutlined />}
                  size="large"
                  block
                  loading={createMutation.isPending || updateMutation.isPending}
                  onClick={handleSave}
                >
                  {isEditing ? 'Update Transfer' : 'Save as Draft'}
                </Button>
                {isEditing && transfer?.status === 'draft' && (
                  <Button
                    icon={<SendOutlined />}
                    size="large"
                    block
                    loading={submitMutation.isPending}
                    onClick={handleSubmitForApproval}
                  >
                    Submit for Approval
                  </Button>
                )}
              </Space>
            </Card>
          </Col>
        </Row>
      </Form>
    </div>
  );
}
