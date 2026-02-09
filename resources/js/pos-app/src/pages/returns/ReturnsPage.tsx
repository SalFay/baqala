import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Input,
  Select,
  DatePicker,
  Space,
  Tag,
  Typography,
  Button,
  Card,
  Modal,
  Form,
  InputNumber,
  Checkbox,
  message,
  Descriptions,
  List,
} from 'antd';
import {
  SearchOutlined,
  EyeOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import { returnService, type ReturnFilters } from '../../api/services/return.service';
import { orderService } from '../../api/services/order.service';
import type { OrderReturn, Order } from '../../types';

const { Title, Text } = Typography;
const { RangePicker } = DatePicker;
const { TextArea } = Input;

export default function ReturnsPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [filters, setFilters] = useState<ReturnFilters>({ page: 1, per_page: 20 });
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [detailModalOpen, setDetailModalOpen] = useState(false);
  const [selectedReturn, setSelectedReturn] = useState<OrderReturn | null>(null);
  const [orderSearch, setOrderSearch] = useState('');
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [form] = Form.useForm();

  const { data: returnsData, isLoading } = useQuery({
    queryKey: ['returns', filters],
    queryFn: () => returnService.getReturns(filters),
  });

  const { data: orders } = useQuery({
    queryKey: ['orders-for-return', orderSearch],
    queryFn: () => orderService.getOrders({ search: orderSearch, status: 'completed', per_page: 10 }),
    enabled: createModalOpen && orderSearch.length > 2,
  });

  const { data: returnableItems } = useQuery({
    queryKey: ['returnable-items', selectedOrder?.id],
    queryFn: () => returnService.getReturnableItems(selectedOrder!.id),
    enabled: !!selectedOrder?.id,
  });

  const createMutation = useMutation({
    mutationFn: returnService.createReturn,
    onSuccess: () => {
      message.success('Return created successfully');
      setCreateModalOpen(false);
      setSelectedOrder(null);
      form.resetFields();
      queryClient.invalidateQueries({ queryKey: ['returns'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to create return');
    },
  });

  const approveMutation = useMutation({
    mutationFn: returnService.approveReturn,
    onSuccess: () => {
      message.success('Return approved');
      queryClient.invalidateQueries({ queryKey: ['returns'] });
      setDetailModalOpen(false);
    },
  });

  const rejectMutation = useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) =>
      returnService.rejectReturn(id, reason),
    onSuccess: () => {
      message.success('Return rejected');
      queryClient.invalidateQueries({ queryKey: ['returns'] });
      setDetailModalOpen(false);
    },
  });

  const processMutation = useMutation({
    mutationFn: returnService.processReturn,
    onSuccess: () => {
      message.success('Return processed');
      queryClient.invalidateQueries({ queryKey: ['returns'] });
      setDetailModalOpen(false);
    },
  });

  const statusColors: Record<string, string> = {
    pending: 'orange',
    approved: 'blue',
    processing: 'cyan',
    completed: 'green',
    rejected: 'red',
  };

  const typeColors: Record<string, string> = {
    refund: 'green',
    exchange: 'blue',
    store_credit: 'purple',
  };

  const columns = [
    {
      title: 'Return #',
      dataIndex: 'return_number',
      key: 'return_number',
    },
    {
      title: 'Order #',
      dataIndex: ['order', 'order_number'],
      key: 'order_number',
    },
    {
      title: 'Customer',
      dataIndex: ['customer', 'full_name'],
      key: 'customer',
      render: (text: string) => text || 'Walk-in',
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (type: string) => (
        <Tag color={typeColors[type] || 'default'}>{type?.toUpperCase()}</Tag>
      ),
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status: string) => (
        <Tag color={statusColors[status] || 'default'}>{status?.toUpperCase()}</Tag>
      ),
    },
    {
      title: 'Amount',
      dataIndex: 'total_amount',
      key: 'total_amount',
      render: (val: number) => `${val?.toFixed(2) || '0.00'} SAR`,
    },
    {
      title: 'Date',
      dataIndex: 'created_at',
      key: 'date',
      render: (date: string) => dayjs(date).format('MMM D, YYYY'),
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_: any, record: OrderReturn) => (
        <Button
          type="text"
          icon={<EyeOutlined />}
          onClick={() => {
            setSelectedReturn(record);
            setDetailModalOpen(true);
          }}
        />
      ),
    },
  ];

  const handleCreateReturn = (values: any) => {
    if (!selectedOrder) return;

    const items = values.items
      .filter((item: any) => item.selected && item.quantity > 0)
      .map((item: any) => ({
        order_item_id: item.order_item_id,
        quantity: item.quantity,
        condition: item.condition || 'sellable',
        restock: item.restock ?? true,
        reason: item.reason,
      }));

    if (items.length === 0) {
      message.error('Please select at least one item to return');
      return;
    }

    createMutation.mutate({
      order_id: selectedOrder.id,
      type: values.type,
      items,
      reason: values.reason,
      notes: values.notes,
      restocking_fee: values.restocking_fee,
      refund_method: values.refund_method,
    });
  };

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>Returns & Exchanges</Title>
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={() => setCreateModalOpen(true)}
        >
          New Return
        </Button>
      </div>

      <Card style={{ marginBottom: 16 }}>
        <Space wrap>
          <Input
            placeholder="Search returns..."
            prefix={<SearchOutlined />}
            value={filters.search || ''}
            onChange={(e) => setFilters({ ...filters, search: e.target.value, page: 1 })}
            style={{ width: 200 }}
            allowClear
          />
          <Select
            placeholder="Status"
            value={filters.status}
            onChange={(value) => setFilters({ ...filters, status: value, page: 1 })}
            allowClear
            style={{ width: 150 }}
            options={[
              { label: 'Pending', value: 'pending' },
              { label: 'Approved', value: 'approved' },
              { label: 'Processing', value: 'processing' },
              { label: 'Completed', value: 'completed' },
              { label: 'Rejected', value: 'rejected' },
            ]}
          />
          <Select
            placeholder="Type"
            value={filters.type}
            onChange={(value) => setFilters({ ...filters, type: value, page: 1 })}
            allowClear
            style={{ width: 150 }}
            options={[
              { label: 'Refund', value: 'refund' },
              { label: 'Exchange', value: 'exchange' },
              { label: 'Store Credit', value: 'store_credit' },
            ]}
          />
          <RangePicker
            onChange={(dates) => {
              if (dates) {
                setFilters({
                  ...filters,
                  from_date: dates[0]!.format('YYYY-MM-DD'),
                  to_date: dates[1]!.format('YYYY-MM-DD'),
                  page: 1,
                });
              } else {
                setFilters({ ...filters, from_date: undefined, to_date: undefined, page: 1 });
              }
            }}
          />
        </Space>
      </Card>

      <Table
        dataSource={returnsData?.data}
        columns={columns}
        rowKey="id"
        loading={isLoading}
        pagination={{
          current: returnsData?.current_page,
          total: returnsData?.total,
          pageSize: returnsData?.per_page,
          onChange: (page) => setFilters({ ...filters, page }),
          showSizeChanger: false,
        }}
      />

      {/* Create Return Modal */}
      <Modal
        title="Create Return"
        open={createModalOpen}
        onCancel={() => {
          setCreateModalOpen(false);
          setSelectedOrder(null);
          setOrderSearch('');
          form.resetFields();
        }}
        footer={null}
        width={800}
      >
        {!selectedOrder ? (
          <div>
            <Text>Search for the order to process return:</Text>
            <Input
              placeholder="Search by order number..."
              prefix={<SearchOutlined />}
              value={orderSearch}
              onChange={(e) => setOrderSearch(e.target.value)}
              style={{ marginTop: 8, marginBottom: 16 }}
            />
            <List
              loading={!orders && orderSearch.length > 2}
              dataSource={orders?.data || []}
              renderItem={(order: Order) => (
                <List.Item
                  style={{ cursor: 'pointer' }}
                  onClick={() => setSelectedOrder(order)}
                >
                  <List.Item.Meta
                    title={`Order #${order.order_number}`}
                    description={`${dayjs(order.created_at).format('MMM D, YYYY')} - ${order.total.toFixed(2)} SAR`}
                  />
                  <Tag color="green">{order.status}</Tag>
                </List.Item>
              )}
            />
          </div>
        ) : (
          <Form form={form} layout="vertical" onFinish={handleCreateReturn}>
            <Descriptions column={2} style={{ marginBottom: 16 }}>
              <Descriptions.Item label="Order">{selectedOrder.order_number}</Descriptions.Item>
              <Descriptions.Item label="Customer">
                {selectedOrder.customer?.full_name || 'Walk-in'}
              </Descriptions.Item>
              <Descriptions.Item label="Date">
                {dayjs(selectedOrder.created_at).format('MMM D, YYYY')}
              </Descriptions.Item>
              <Descriptions.Item label="Total">{selectedOrder.total.toFixed(2)} SAR</Descriptions.Item>
            </Descriptions>

            <Form.Item name="type" label="Return Type" rules={[{ required: true }]}>
              <Select
                options={[
                  { label: 'Refund', value: 'refund' },
                  { label: 'Exchange', value: 'exchange' },
                  { label: 'Store Credit', value: 'store_credit' },
                ]}
              />
            </Form.Item>

            <Text strong style={{ display: 'block', marginBottom: 8 }}>Select Items to Return:</Text>
            <Form.List name="items" initialValue={returnableItems?.items?.map((item: any) => ({
              order_item_id: item.id,
              product_name: item.product_name,
              max_quantity: item.quantity - (item.returned_quantity || 0),
              quantity: 0,
              selected: false,
              restock: true,
              condition: 'sellable',
            })) || []}>
              {(fields) => (
                <div style={{ marginBottom: 16 }}>
                  {fields.map((field, index) => (
                    <Card size="small" key={field.key} style={{ marginBottom: 8 }}>
                      <Space style={{ width: '100%', justifyContent: 'space-between' }}>
                        <Form.Item name={[field.name, 'selected']} valuePropName="checked" noStyle>
                          <Checkbox>
                            {form.getFieldValue(['items', index, 'product_name'])}
                          </Checkbox>
                        </Form.Item>
                        <Space>
                          <Form.Item name={[field.name, 'quantity']} noStyle>
                            <InputNumber
                              min={0}
                              max={form.getFieldValue(['items', index, 'max_quantity'])}
                              placeholder="Qty"
                            />
                          </Form.Item>
                          <Form.Item name={[field.name, 'condition']} noStyle>
                            <Select
                              style={{ width: 120 }}
                              options={[
                                { label: 'Sellable', value: 'sellable' },
                                { label: 'Damaged', value: 'damaged' },
                                { label: 'Defective', value: 'defective' },
                              ]}
                            />
                          </Form.Item>
                          <Form.Item name={[field.name, 'restock']} valuePropName="checked" noStyle>
                            <Checkbox>Restock</Checkbox>
                          </Form.Item>
                        </Space>
                      </Space>
                    </Card>
                  ))}
                </div>
              )}
            </Form.List>

            <Form.Item name="refund_method" label="Refund Method">
              <Select
                options={[
                  { label: 'Cash', value: 'cash' },
                  { label: 'Card', value: 'card' },
                  { label: 'Store Credit', value: 'store_credit' },
                ]}
              />
            </Form.Item>

            <Form.Item name="reason" label="Reason">
              <TextArea rows={2} />
            </Form.Item>

            <Form.Item name="notes" label="Notes">
              <TextArea rows={2} />
            </Form.Item>

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
              <Button onClick={() => setSelectedOrder(null)}>Back</Button>
              <Button type="primary" htmlType="submit" loading={createMutation.isPending}>
                Create Return
              </Button>
            </div>
          </Form>
        )}
      </Modal>

      {/* Return Detail Modal */}
      <Modal
        title={`Return ${selectedReturn?.return_number}`}
        open={detailModalOpen}
        onCancel={() => {
          setDetailModalOpen(false);
          setSelectedReturn(null);
        }}
        footer={
          selectedReturn?.status === 'pending' ? (
            <Space>
              <Button
                danger
                icon={<CloseCircleOutlined />}
                onClick={() => {
                  Modal.confirm({
                    title: 'Reject Return',
                    content: (
                      <Input.TextArea
                        placeholder="Reason for rejection"
                        id="reject-reason"
                      />
                    ),
                    onOk: () => {
                      const reason = (document.getElementById('reject-reason') as HTMLTextAreaElement)?.value;
                      if (selectedReturn) {
                        rejectMutation.mutate({ id: selectedReturn.id, reason: reason || 'Rejected' });
                      }
                    },
                  });
                }}
              >
                Reject
              </Button>
              <Button
                type="primary"
                icon={<CheckCircleOutlined />}
                onClick={() => selectedReturn && approveMutation.mutate(selectedReturn.id)}
                loading={approveMutation.isPending}
              >
                Approve
              </Button>
            </Space>
          ) : selectedReturn?.status === 'approved' ? (
            <Button
              type="primary"
              onClick={() => selectedReturn && processMutation.mutate(selectedReturn.id)}
              loading={processMutation.isPending}
            >
              Process Return
            </Button>
          ) : null
        }
        width={700}
      >
        {selectedReturn && (
          <>
            <Descriptions column={2} bordered size="small" style={{ marginBottom: 16 }}>
              <Descriptions.Item label="Return Number">{selectedReturn.return_number}</Descriptions.Item>
              <Descriptions.Item label="Order Number">{selectedReturn.order?.order_number}</Descriptions.Item>
              <Descriptions.Item label="Customer">
                {selectedReturn.customer?.full_name || 'Walk-in'}
              </Descriptions.Item>
              <Descriptions.Item label="Type">
                <Tag color={typeColors[selectedReturn.type]}>{selectedReturn.type?.toUpperCase()}</Tag>
              </Descriptions.Item>
              <Descriptions.Item label="Status">
                <Tag color={statusColors[selectedReturn.status]}>{selectedReturn.status?.toUpperCase()}</Tag>
              </Descriptions.Item>
              <Descriptions.Item label="Date">
                {dayjs(selectedReturn.created_at).format('MMM D, YYYY h:mm A')}
              </Descriptions.Item>
              <Descriptions.Item label="Reason" span={2}>{selectedReturn.reason || '-'}</Descriptions.Item>
            </Descriptions>

            <Title level={5}>Items</Title>
            <Table
              dataSource={selectedReturn.items}
              columns={[
                { title: 'Product', dataIndex: 'product_name', key: 'product' },
                { title: 'Quantity', dataIndex: 'quantity', key: 'quantity' },
                { title: 'Unit Price', dataIndex: 'unit_price', key: 'price', render: (v: number) => `${v?.toFixed(2)} SAR` },
                { title: 'Total', dataIndex: 'total', key: 'total', render: (v: number) => `${v?.toFixed(2)} SAR` },
                { title: 'Condition', dataIndex: 'condition', key: 'condition' },
              ]}
              rowKey="id"
              pagination={false}
              size="small"
              summary={() => (
                <Table.Summary.Row>
                  <Table.Summary.Cell index={0} colSpan={3}>
                    <Text strong>Total Refund</Text>
                  </Table.Summary.Cell>
                  <Table.Summary.Cell index={1} colSpan={2}>
                    <Text strong style={{ color: '#1890ff' }}>
                      {selectedReturn.total_amount?.toFixed(2)} SAR
                    </Text>
                  </Table.Summary.Cell>
                </Table.Summary.Row>
              )}
            />
          </>
        )}
      </Modal>
    </div>
  );
}
