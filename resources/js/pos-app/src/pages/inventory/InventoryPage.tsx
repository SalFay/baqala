import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Input,
  Select,
  Space,
  Tag,
  Typography,
  Button,
  Card,
  Modal,
  Form,
  InputNumber,
  Tabs,
  Statistic,
  Row,
  Col,
  message,
  Badge,
} from 'antd';
import {
  SearchOutlined,
  PlusOutlined,
  MinusOutlined,
  WarningOutlined,
  SyncOutlined,
  HistoryOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import {
  inventoryService,
  type InventoryItem,
  type InventoryMovement,
  type InventoryFilters,
} from '../../api/services/inventory.service';
import { categoryService } from '../../api/services/category.service';

const { Title, Text } = Typography;
const { TextArea } = Input;

export default function InventoryPage() {
  const queryClient = useQueryClient();
  const [filters, setFilters] = useState<InventoryFilters>({ page: 1, per_page: 20 });
  const [movementFilters, setMovementFilters] = useState<any>({ page: 1, per_page: 20 });
  const [adjustModalOpen, setAdjustModalOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState<InventoryItem | null>(null);
  const [adjustType, setAdjustType] = useState<'add' | 'remove'>('add');
  const [form] = Form.useForm();
  const [activeTab, setActiveTab] = useState('stock');

  const { data: inventoryData, isLoading } = useQuery({
    queryKey: ['inventory', filters],
    queryFn: () => inventoryService.getInventory(filters),
    enabled: activeTab === 'stock',
  });

  const { data: lowStockData } = useQuery({
    queryKey: ['low-stock'],
    queryFn: () => inventoryService.getLowStock(),
    enabled: activeTab === 'low-stock',
  });

  const { data: movementsData, isLoading: movementsLoading } = useQuery({
    queryKey: ['inventory-movements', movementFilters],
    queryFn: () => inventoryService.getMovements(movementFilters),
    enabled: activeTab === 'movements',
  });

  const { data: categories } = useQuery({
    queryKey: ['categories'],
    queryFn: () => categoryService.getCategories(),
  });

  const { data: stores } = useQuery({
    queryKey: ['stores'],
    queryFn: inventoryService.getStores,
  });

  const adjustMutation = useMutation({
    mutationFn: inventoryService.adjustStock,
    onSuccess: () => {
      message.success('Stock adjusted successfully');
      setAdjustModalOpen(false);
      setSelectedItem(null);
      form.resetFields();
      queryClient.invalidateQueries({ queryKey: ['inventory'] });
      queryClient.invalidateQueries({ queryKey: ['low-stock'] });
      queryClient.invalidateQueries({ queryKey: ['inventory-movements'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to adjust stock');
    },
  });

  const stockColumns = [
    {
      title: 'Product',
      dataIndex: 'product_name',
      key: 'product',
      render: (name: string, record: InventoryItem) => (
        <div>
          <Text strong>{name}</Text>
          {record.variant_name && <Text type="secondary"> - {record.variant_name}</Text>}
          <br />
          <Text type="secondary" style={{ fontSize: 12 }}>{record.sku}</Text>
        </div>
      ),
    },
    {
      title: 'Barcode',
      dataIndex: 'barcode',
      key: 'barcode',
      render: (barcode: string) => barcode || '-',
    },
    {
      title: 'Store',
      dataIndex: 'store_name',
      key: 'store',
    },
    {
      title: 'Quantity',
      dataIndex: 'quantity',
      key: 'quantity',
      render: (qty: number, record: InventoryItem) => {
        const isLow = qty <= record.low_stock_threshold;
        return (
          <Badge
            count={isLow ? <WarningOutlined style={{ color: '#faad14' }} /> : 0}
            offset={[15, 0]}
          >
            <Text type={isLow ? 'warning' : undefined} strong={isLow}>
              {qty}
            </Text>
          </Badge>
        );
      },
    },
    {
      title: 'Reserved',
      dataIndex: 'reserved_quantity',
      key: 'reserved',
      render: (qty: number) => qty || 0,
    },
    {
      title: 'Available',
      dataIndex: 'available_quantity',
      key: 'available',
      render: (qty: number, record: InventoryItem) => (
        <Text type={qty <= 0 ? 'danger' : qty <= record.low_stock_threshold ? 'warning' : undefined}>
          {qty}
        </Text>
      ),
    },
    {
      title: 'Location',
      dataIndex: 'location',
      key: 'location',
      render: (loc: string) => loc || '-',
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_: any, record: InventoryItem) => (
        <Space>
          <Button
            type="text"
            icon={<PlusOutlined />}
            onClick={() => {
              setSelectedItem(record);
              setAdjustType('add');
              setAdjustModalOpen(true);
            }}
            title="Add Stock"
          />
          <Button
            type="text"
            icon={<MinusOutlined />}
            onClick={() => {
              setSelectedItem(record);
              setAdjustType('remove');
              setAdjustModalOpen(true);
            }}
            title="Remove Stock"
          />
        </Space>
      ),
    },
  ];

  const movementColumns = [
    {
      title: 'Date',
      dataIndex: 'created_at',
      key: 'date',
      render: (date: string) => dayjs(date).format('MMM D, YYYY h:mm A'),
    },
    {
      title: 'Product',
      dataIndex: 'product_name',
      key: 'product',
      render: (name: string, record: InventoryMovement) => (
        <span>
          {name}
          {record.variant_name && ` - ${record.variant_name}`}
        </span>
      ),
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (type: string) => {
        const colors: Record<string, string> = {
          purchase: 'green',
          sale: 'blue',
          return_from_customer: 'cyan',
          return_to_vendor: 'orange',
          transfer_in: 'purple',
          transfer_out: 'magenta',
          adjustment_add: 'green',
          adjustment_remove: 'red',
          damage: 'red',
          count: 'default',
        };
        return <Tag color={colors[type] || 'default'}>{type?.replace(/_/g, ' ').toUpperCase()}</Tag>;
      },
    },
    {
      title: 'Quantity',
      dataIndex: 'quantity',
      key: 'quantity',
      render: (qty: number, record: InventoryMovement) => {
        const isPositive = ['purchase', 'return_from_customer', 'transfer_in', 'adjustment_add'].includes(record.type);
        return (
          <Text type={isPositive ? 'success' : 'danger'}>
            {isPositive ? '+' : '-'}{Math.abs(qty)}
          </Text>
        );
      },
    },
    {
      title: 'Before',
      dataIndex: 'quantity_before',
      key: 'before',
    },
    {
      title: 'After',
      dataIndex: 'quantity_after',
      key: 'after',
    },
    {
      title: 'Reason',
      dataIndex: 'reason',
      key: 'reason',
      render: (reason: string) => reason || '-',
    },
    {
      title: 'By',
      dataIndex: 'created_by_name',
      key: 'by',
    },
  ];

  const handleAdjust = (values: any) => {
    if (!selectedItem) return;

    adjustMutation.mutate({
      product_id: selectedItem.product_id,
      product_variant_id: selectedItem.product_variant_id,
      store_id: selectedItem.store_id,
      type: adjustType === 'add' ? 'adjustment_add' : 'adjustment_remove',
      quantity: values.quantity,
      reason: values.reason,
      notes: values.notes,
    });
  };

  const lowStockCount = lowStockData?.length || 0;

  return (
    <div>
      <Title level={4} style={{ marginBottom: 24 }}>Inventory Management</Title>

      <Row gutter={16} style={{ marginBottom: 24 }}>
        <Col span={6}>
          <Card>
            <Statistic
              title="Total Products"
              value={inventoryData?.total || 0}
              prefix={<SyncOutlined />}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Low Stock Items"
              value={lowStockCount}
              prefix={<WarningOutlined style={{ color: '#faad14' }} />}
              valueStyle={{ color: lowStockCount > 0 ? '#faad14' : undefined }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Out of Stock"
              value={lowStockData?.filter((i: InventoryItem) => i.available_quantity <= 0).length || 0}
              valueStyle={{ color: '#ff4d4f' }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card>
            <Statistic
              title="Stores"
              value={stores?.length || 1}
            />
          </Card>
        </Col>
      </Row>

      <Card>
        <Tabs
          activeKey={activeTab}
          onChange={setActiveTab}
          items={[
            {
              key: 'stock',
              label: 'Stock Levels',
              children: (
                <>
                  <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                      placeholder="Search products..."
                      prefix={<SearchOutlined />}
                      value={filters.search || ''}
                      onChange={(e) => setFilters({ ...filters, search: e.target.value, page: 1 })}
                      style={{ width: 200 }}
                      allowClear
                    />
                    <Select
                      placeholder="Category"
                      value={filters.category_id}
                      onChange={(value) => setFilters({ ...filters, category_id: value, page: 1 })}
                      allowClear
                      style={{ width: 150 }}
                      options={categories?.map((c: any) => ({ label: c.name, value: c.id })) || []}
                    />
                    <Select
                      placeholder="Store"
                      value={filters.store_id}
                      onChange={(value) => setFilters({ ...filters, store_id: value, page: 1 })}
                      allowClear
                      style={{ width: 150 }}
                      options={stores?.map((s: any) => ({ label: s.name, value: s.id })) || []}
                    />
                    <Button
                      type={filters.low_stock ? 'primary' : 'default'}
                      icon={<WarningOutlined />}
                      onClick={() => setFilters({ ...filters, low_stock: !filters.low_stock, page: 1 })}
                    >
                      Low Stock Only
                    </Button>
                  </Space>

                  <Table
                    dataSource={inventoryData?.data}
                    columns={stockColumns}
                    rowKey="id"
                    loading={isLoading}
                    pagination={{
                      current: inventoryData?.current_page,
                      total: inventoryData?.total,
                      pageSize: inventoryData?.per_page,
                      onChange: (page) => setFilters({ ...filters, page }),
                      showSizeChanger: false,
                    }}
                  />
                </>
              ),
            },
            {
              key: 'low-stock',
              label: (
                <Badge count={lowStockCount} offset={[10, 0]}>
                  Low Stock Alerts
                </Badge>
              ),
              children: (
                <Table
                  dataSource={lowStockData}
                  columns={stockColumns}
                  rowKey="id"
                  pagination={false}
                />
              ),
            },
            {
              key: 'movements',
              label: (
                <span>
                  <HistoryOutlined /> Movement History
                </span>
              ),
              children: (
                <>
                  <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                      placeholder="Search products..."
                      prefix={<SearchOutlined />}
                      value={movementFilters.search || ''}
                      onChange={(e) => setMovementFilters({ ...movementFilters, search: e.target.value, page: 1 })}
                      style={{ width: 200 }}
                      allowClear
                    />
                    <Select
                      placeholder="Movement Type"
                      value={movementFilters.type}
                      onChange={(value) => setMovementFilters({ ...movementFilters, type: value, page: 1 })}
                      allowClear
                      style={{ width: 180 }}
                      options={[
                        { label: 'Purchase', value: 'purchase' },
                        { label: 'Sale', value: 'sale' },
                        { label: 'Return (Customer)', value: 'return_from_customer' },
                        { label: 'Return (Vendor)', value: 'return_to_vendor' },
                        { label: 'Transfer In', value: 'transfer_in' },
                        { label: 'Transfer Out', value: 'transfer_out' },
                        { label: 'Adjustment (Add)', value: 'adjustment_add' },
                        { label: 'Adjustment (Remove)', value: 'adjustment_remove' },
                        { label: 'Damage', value: 'damage' },
                        { label: 'Count', value: 'count' },
                      ]}
                    />
                    <Select
                      placeholder="Store"
                      value={movementFilters.store_id}
                      onChange={(value) => setMovementFilters({ ...movementFilters, store_id: value, page: 1 })}
                      allowClear
                      style={{ width: 150 }}
                      options={stores?.map((s: any) => ({ label: s.name, value: s.id })) || []}
                    />
                  </Space>

                  <Table
                    dataSource={movementsData?.data}
                    columns={movementColumns}
                    rowKey="id"
                    loading={movementsLoading}
                    pagination={{
                      current: movementsData?.current_page,
                      total: movementsData?.total,
                      pageSize: movementsData?.per_page,
                      onChange: (page) => setMovementFilters({ ...movementFilters, page }),
                      showSizeChanger: false,
                    }}
                  />
                </>
              ),
            },
          ]}
        />
      </Card>

      {/* Stock Adjustment Modal */}
      <Modal
        title={`${adjustType === 'add' ? 'Add' : 'Remove'} Stock`}
        open={adjustModalOpen}
        onCancel={() => {
          setAdjustModalOpen(false);
          setSelectedItem(null);
          form.resetFields();
        }}
        footer={null}
      >
        {selectedItem && (
          <Form form={form} layout="vertical" onFinish={handleAdjust}>
            <Card size="small" style={{ marginBottom: 16 }}>
              <Text strong>{selectedItem.product_name}</Text>
              {selectedItem.variant_name && <Text type="secondary"> - {selectedItem.variant_name}</Text>}
              <br />
              <Text type="secondary">Current Stock: {selectedItem.quantity}</Text>
            </Card>

            <Form.Item
              name="quantity"
              label="Quantity"
              rules={[
                { required: true, message: 'Please enter quantity' },
                { type: 'number', min: 1, message: 'Quantity must be at least 1' },
                ...(adjustType === 'remove'
                  ? [{ type: 'number' as const, max: selectedItem.quantity, message: `Cannot remove more than ${selectedItem.quantity}` }]
                  : []),
              ]}
            >
              <InputNumber
                style={{ width: '100%' }}
                min={1}
                max={adjustType === 'remove' ? selectedItem.quantity : undefined}
              />
            </Form.Item>

            <Form.Item name="reason" label="Reason" rules={[{ required: true }]}>
              <Select
                options={[
                  { label: 'Stock Count Correction', value: 'Stock count correction' },
                  { label: 'Received Shipment', value: 'Received shipment' },
                  { label: 'Damaged Goods', value: 'Damaged goods' },
                  { label: 'Theft/Loss', value: 'Theft or loss' },
                  { label: 'Transfer', value: 'Transfer' },
                  { label: 'Other', value: 'Other' },
                ]}
              />
            </Form.Item>

            <Form.Item name="notes" label="Notes">
              <TextArea rows={2} />
            </Form.Item>

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
              <Button onClick={() => setAdjustModalOpen(false)}>Cancel</Button>
              <Button
                type="primary"
                htmlType="submit"
                loading={adjustMutation.isPending}
                danger={adjustType === 'remove'}
              >
                {adjustType === 'add' ? 'Add Stock' : 'Remove Stock'}
              </Button>
            </div>
          </Form>
        )}
      </Modal>
    </div>
  );
}
