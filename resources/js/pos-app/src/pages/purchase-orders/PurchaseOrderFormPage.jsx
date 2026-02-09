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
  DatePicker,
  Table,
  InputNumber,
  Row,
  Col,
  Divider,
  Popconfirm,
  AutoComplete,
} from 'antd';
import {
  ArrowLeftOutlined,
  PlusOutlined,
  DeleteOutlined,
  SaveOutlined,
  SendOutlined,
} from '@ant-design/icons';
import { purchaseOrderService } from '../../api/services/purchaseOrder.service';
import { vendorService } from '../../api/services/vendor.service';
import { storeService } from '../../api/services/store.service';
import { productService } from '../../api/services/product.service';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

export default function PurchaseOrderFormPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const [lines, setLines] = useState([]);
  const [productSearch, setProductSearch] = useState('');
  const [productOptions, setProductOptions] = useState([]);

  const isEditing = !!id;

  const { data: order, isLoading: orderLoading } = useQuery({
    queryKey: ['purchase-order', id],
    queryFn: () => purchaseOrderService.getPurchaseOrder(Number(id)),
    enabled: isEditing,
  });

  const { data: vendors } = useQuery({
    queryKey: ['vendors-active'],
    queryFn: () => vendorService.getVendors({ status: 'active', per_page: 100 }),
  });

  const { data: stores } = useQuery({
    queryKey: ['stores'],
    queryFn: () => storeService.getStores(),
  });

  const { data: products } = useQuery({
    queryKey: ['products-search', productSearch],
    queryFn: () => productService.getProducts({ search: productSearch, per_page: 20 }),
    enabled: productSearch.length > 1,
  });

  useEffect(() => {
    if (order && isEditing) {
      form.setFieldsValue({
        vendor_id: order.vendor_id,
        store_id: order.store_id,
        order_date: dayjs(order.order_date),
        expected_date: order.expected_date ? dayjs(order.expected_date) : null,
        notes: order.notes,
      });

      if (order.items) {
        setLines(
          order.items.map((item, index) => ({
            key: `line-${index}`,
            product_id: item.product_id,
            product_variant_id: item.product_variant_id,
            product_name: item.product?.name || '',
            sku: item.product?.sku || '',
            quantity_ordered: item.quantity_ordered,
            unit_cost: item.unit_cost,
            tax_rate: item.tax_rate,
            line_total: item.line_total,
          }))
        );
      }
    }
  }, [order, isEditing, form]);

  useEffect(() => {
    if (products?.data) {
      setProductOptions(
        products.data.map((p) => ({
          value: p.id,
          label: `${p.name} (${p.sku || 'No SKU'})`,
          product: p,
        }))
      );
    }
  }, [products]);

  const createMutation = useMutation({
    mutationFn: purchaseOrderService.createPurchaseOrder,
    onSuccess: (data) => {
      message.success('Purchase order created successfully');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
      navigate(`/purchase-orders/${data.id}`);
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create purchase order');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => purchaseOrderService.updatePurchaseOrder(id, data),
    onSuccess: () => {
      message.success('Purchase order updated successfully');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
      navigate(`/purchase-orders/${id}`);
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update purchase order');
    },
  });

  const submitMutation = useMutation({
    mutationFn: purchaseOrderService.submitPurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order submitted for approval');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
      navigate('/purchase-orders');
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to submit purchase order');
    },
  });

  const handleAddLine = () => {
    setLines([
      ...lines,
      {
        key: `line-${Date.now()}`,
        product_id: null,
        product_variant_id: null,
        product_name: '',
        sku: '',
        quantity_ordered: 1,
        unit_cost: 0,
        tax_rate: 15,
        line_total: 0,
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
          const updated = { ...line, [field]: value };
          const subtotal = updated.quantity_ordered * updated.unit_cost;
          const tax = subtotal * (updated.tax_rate / 100);
          updated.line_total = subtotal + tax;
          return updated;
        }
        return line;
      })
    );
  };

  const handleProductSelect = (key, productId) => {
    const option = productOptions.find((o) => o.value === productId);
    if (option) {
      const product = option.product;
      setLines(
        lines.map((line) => {
          if (line.key === key) {
            return {
              ...line,
              product_id: product.id,
              product_name: product.name,
              sku: product.sku || '',
              unit_cost: product.purchase_price || 0,
              line_total: (product.purchase_price || 0) * line.quantity_ordered,
            };
          }
          return line;
        })
      );
    }
  };

  const calculateTotals = () => {
    const subtotal = lines.reduce((sum, l) => sum + l.quantity_ordered * l.unit_cost, 0);
    const taxAmount = lines.reduce(
      (sum, l) => sum + l.quantity_ordered * l.unit_cost * (l.tax_rate / 100),
      0
    );
    const total = subtotal + taxAmount;
    return { subtotal, taxAmount, total };
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

      const data = {
        vendor_id: values.vendor_id,
        store_id: values.store_id,
        order_date: values.order_date.format('YYYY-MM-DD'),
        expected_date: values.expected_date?.format('YYYY-MM-DD'),
        notes: values.notes,
        items: lines.map((l) => ({
          product_id: l.product_id,
          product_variant_id: l.product_variant_id || undefined,
          quantity_ordered: l.quantity_ordered,
          unit_cost: l.unit_cost,
          tax_rate: l.tax_rate,
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

  const { subtotal, taxAmount, total } = calculateTotals();

  const lineColumns = [
    {
      title: 'Product',
      key: 'product',
      width: 300,
      render: (_, record) => (
        <AutoComplete
          value={record.product_name}
          options={productOptions}
          onSearch={setProductSearch}
          onSelect={(value) => handleProductSelect(record.key, value)}
          onChange={(value) => handleLineChange(record.key, 'product_name', value)}
          placeholder="Search product..."
          style={{ width: '100%' }}
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
      title: 'Quantity',
      key: 'quantity',
      width: 100,
      render: (_, record) => (
        <InputNumber
          min={1}
          value={record.quantity_ordered}
          onChange={(val) => handleLineChange(record.key, 'quantity_ordered', val || 1)}
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Unit Cost',
      key: 'unit_cost',
      width: 120,
      render: (_, record) => (
        <InputNumber
          min={0}
          precision={2}
          value={record.unit_cost}
          onChange={(val) => handleLineChange(record.key, 'unit_cost', val || 0)}
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Tax %',
      key: 'tax_rate',
      width: 80,
      render: (_, record) => (
        <InputNumber
          min={0}
          max={100}
          value={record.tax_rate}
          onChange={(val) => handleLineChange(record.key, 'tax_rate', val || 0)}
          style={{ width: '100%' }}
        />
      ),
    },
    {
      title: 'Total',
      key: 'line_total',
      width: 120,
      render: (_, record) => <Text strong>{record.line_total.toFixed(2)} SAR</Text>,
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

  if (orderLoading && isEditing) {
    return <Card loading />;
  }

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginBottom: 24 }}>
        <Button icon={<ArrowLeftOutlined />} onClick={() => navigate('/purchase-orders')}>
          Back
        </Button>
        <Title level={4} style={{ margin: 0 }}>
          {isEditing ? `Edit Purchase Order - ${order?.po_number}` : 'New Purchase Order'}
        </Title>
      </div>

      <Form form={form} layout="vertical" initialValues={{ order_date: dayjs() }}>
        <Row gutter={24}>
          <Col span={16}>
            <Card title="Order Details" style={{ marginBottom: 24 }}>
              <Row gutter={16}>
                <Col span={12}>
                  <Form.Item
                    name="vendor_id"
                    label="Vendor"
                    rules={[{ required: true, message: 'Vendor is required' }]}
                  >
                    <Select
                      showSearch
                      placeholder="Select vendor"
                      optionFilterProp="children"
                      filterOption={(input, option) =>
                        option?.children?.toLowerCase().includes(input.toLowerCase())
                      }
                    >
                      {vendors?.data?.map((vendor) => (
                        <Select.Option key={vendor.id} value={vendor.id}>
                          {vendor.name}
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                </Col>
                <Col span={12}>
                  <Form.Item
                    name="store_id"
                    label="Receiving Store"
                    rules={[{ required: true, message: 'Store is required' }]}
                  >
                    <Select placeholder="Select store">
                      {stores?.map((store) => (
                        <Select.Option key={store.id} value={store.id}>
                          {store.name} ({store.code})
                        </Select.Option>
                      ))}
                    </Select>
                  </Form.Item>
                </Col>
              </Row>

              <Row gutter={16}>
                <Col span={12}>
                  <Form.Item
                    name="order_date"
                    label="Order Date"
                    rules={[{ required: true, message: 'Order date is required' }]}
                  >
                    <DatePicker style={{ width: '100%' }} />
                  </Form.Item>
                </Col>
                <Col span={12}>
                  <Form.Item name="expected_date" label="Expected Delivery">
                    <DatePicker style={{ width: '100%' }} />
                  </Form.Item>
                </Col>
              </Row>

              <Form.Item name="notes" label="Notes">
                <Input.TextArea rows={2} placeholder="Additional notes..." />
              </Form.Item>
            </Card>

            <Card
              title="Order Items"
              extra={
                <Button type="primary" icon={<PlusOutlined />} onClick={handleAddLine}>
                  Add Item
                </Button>
              }
            >
              <Table
                dataSource={lines}
                columns={lineColumns}
                rowKey="key"
                pagination={false}
                scroll={{ x: 900 }}
                locale={{ emptyText: 'No items added. Click "Add Item" to start.' }}
              />
            </Card>
          </Col>

          <Col span={8}>
            <Card title="Order Summary" style={{ position: 'sticky', top: 24 }}>
              <Space direction="vertical" style={{ width: '100%' }} size="middle">
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text>Subtotal:</Text>
                  <Text>{subtotal.toFixed(2)} SAR</Text>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text>Tax:</Text>
                  <Text>{taxAmount.toFixed(2)} SAR</Text>
                </div>
                <Divider style={{ margin: '8px 0' }} />
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                  <Text strong style={{ fontSize: 16 }}>
                    Total:
                  </Text>
                  <Text strong style={{ fontSize: 16 }}>
                    {total.toFixed(2)} SAR
                  </Text>
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
                  {isEditing ? 'Update Order' : 'Save as Draft'}
                </Button>
                {isEditing && order?.status === 'draft' && (
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
