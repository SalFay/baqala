import React, { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Card,
  Button,
  Space,
  Typography,
  message,
  Tag,
  Table,
  Descriptions,
  Row,
  Col,
  Statistic,
  Modal,
  InputNumber,
  Form,
  Input,
  Divider,
  Steps,
  Timeline,
  Alert,
} from 'antd';
import {
  ArrowLeftOutlined,
  EditOutlined,
  CheckOutlined,
  CloseOutlined,
  PrinterOutlined,
  DownloadOutlined,
  SendOutlined,
  ShoppingCartOutlined,
} from '@ant-design/icons';
import { purchaseOrderService, PurchaseOrder, PurchaseOrderItem, PurchaseOrderStatus } from '../../api/services/purchaseOrder.service';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const statusColors: Record<PurchaseOrderStatus, string> = {
  draft: 'default',
  pending_approval: 'gold',
  approved: 'blue',
  ordered: 'cyan',
  partial: 'orange',
  received: 'green',
  cancelled: 'red',
};

const statusLabels: Record<PurchaseOrderStatus, string> = {
  draft: 'Draft',
  pending_approval: 'Pending Approval',
  approved: 'Approved',
  ordered: 'Ordered',
  partial: 'Partially Received',
  received: 'Received',
  cancelled: 'Cancelled',
};

const statusSteps = ['draft', 'pending_approval', 'approved', 'ordered', 'received'];

export default function PurchaseOrderDetailPage(): React.JSX.Element {
  const navigate = useNavigate();
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [receiveModalOpen, setReceiveModalOpen] = useState(false);
  const [receiveForm] = Form.useForm();

  const { data: order, isLoading } = useQuery({
    queryKey: ['purchase-order', id],
    queryFn: () => purchaseOrderService.getPurchaseOrder(Number(id)),
    enabled: !!id,
  });

  const approveMutation = useMutation({
    mutationFn: purchaseOrderService.approvePurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order approved');
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to approve');
    },
  });

  const submitMutation = useMutation({
    mutationFn: purchaseOrderService.submitPurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order submitted for approval');
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to submit');
    },
  });

  const orderMutation = useMutation({
    mutationFn: purchaseOrderService.orderPurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order marked as ordered');
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to mark as ordered');
    },
  });

  const receiveMutation = useMutation({
    mutationFn: ({ items, notes }: { items: any[]; notes?: string }) =>
      purchaseOrderService.receivePurchaseOrder(Number(id), items, notes),
    onSuccess: () => {
      message.success('Items received successfully');
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
      setReceiveModalOpen(false);
      receiveForm.resetFields();
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to receive items');
    },
  });

  const cancelMutation = useMutation({
    mutationFn: purchaseOrderService.cancelPurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order cancelled');
      queryClient.invalidateQueries({ queryKey: ['purchase-order', id] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to cancel');
    },
  });

  const handleReceive = (): void => {
    receiveForm.validateFields().then((values) => {
      const items = order?.items
        ?.filter((item) => values[`qty_${item.id}`] > 0)
        .map((item) => ({
          purchase_order_item_id: item.id,
          quantity_received: values[`qty_${item.id}`] || 0,
          condition: values[`condition_${item.id}`] || 'good',
          notes: values[`notes_${item.id}`],
        }));

      if (!items || items.length === 0) {
        message.error('Please enter quantities to receive');
        return;
      }

      receiveMutation.mutate({ items, notes: values.receipt_notes });
    });
  };

  const getCurrentStep = (): number => {
    if (!order) return 0;
    if (order.status === 'cancelled') return -1;
    return statusSteps.indexOf(order.status);
  };

  const itemColumns = [
    {
      title: 'Product',
      key: 'product',
      render: (_: unknown, record: PurchaseOrderItem) => (
        <Space direction="vertical" size={0}>
          <Text strong>{record.product?.name}</Text>
          {record.product?.sku && <Text type="secondary">{record.product.sku}</Text>}
        </Space>
      ),
    },
    {
      title: 'Ordered',
      dataIndex: 'quantity_ordered',
      key: 'quantity_ordered',
      width: 100,
      align: 'center' as const,
    },
    {
      title: 'Received',
      dataIndex: 'quantity_received',
      key: 'quantity_received',
      width: 100,
      align: 'center' as const,
      render: (received: number, record: PurchaseOrderItem) => (
        <Text type={received < record.quantity_ordered ? 'warning' : 'success'}>
          {received}
        </Text>
      ),
    },
    {
      title: 'Pending',
      key: 'pending',
      width: 100,
      align: 'center' as const,
      render: (_: unknown, record: PurchaseOrderItem) => {
        const pending = record.quantity_ordered - record.quantity_received;
        return (
          <Text type={pending > 0 ? 'danger' : undefined}>
            {pending}
          </Text>
        );
      },
    },
    {
      title: 'Unit Cost',
      dataIndex: 'unit_cost',
      key: 'unit_cost',
      width: 120,
      render: (val: number) => `${val.toFixed(2)} SAR`,
    },
    {
      title: 'Tax',
      dataIndex: 'tax_amount',
      key: 'tax_amount',
      width: 100,
      render: (val: number) => `${val.toFixed(2)} SAR`,
    },
    {
      title: 'Total',
      dataIndex: 'line_total',
      key: 'line_total',
      width: 120,
      render: (val: number) => <Text strong>{val.toFixed(2)} SAR</Text>,
    },
  ];

  if (isLoading) {
    return <Card loading />;
  }

  if (!order) {
    return (
      <Card>
        <Alert type="error" message="Purchase order not found" showIcon />
      </Card>
    );
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
        <Space>
          <Button icon={<ArrowLeftOutlined />} onClick={() => navigate('/purchase-orders')}>
            Back
          </Button>
          <Title level={4} style={{ margin: 0 }}>
            {order.po_number}
          </Title>
          <Tag color={statusColors[order.status]} style={{ fontSize: 14 }}>
            {statusLabels[order.status]}
          </Tag>
        </Space>

        <Space>
          {order.status === 'draft' && (
            <>
              <Button icon={<EditOutlined />} onClick={() => navigate(`/purchase-orders/${id}/edit`)}>
                Edit
              </Button>
              <Button type="primary" icon={<SendOutlined />} onClick={() => submitMutation.mutate(Number(id))}>
                Submit for Approval
              </Button>
            </>
          )}
          {order.status === 'pending_approval' && (
            <Button type="primary" icon={<CheckOutlined />} onClick={() => approveMutation.mutate(Number(id))}>
              Approve
            </Button>
          )}
          {order.status === 'approved' && (
            <Button type="primary" icon={<ShoppingCartOutlined />} onClick={() => orderMutation.mutate(Number(id))}>
              Mark as Ordered
            </Button>
          )}
          {['ordered', 'partial'].includes(order.status) && (
            <Button type="primary" icon={<DownloadOutlined />} onClick={() => setReceiveModalOpen(true)}>
              Receive Items
            </Button>
          )}
          {!['received', 'cancelled'].includes(order.status) && (
            <Button danger icon={<CloseOutlined />} onClick={() => cancelMutation.mutate(Number(id))}>
              Cancel
            </Button>
          )}
          <Button icon={<PrinterOutlined />}>Print</Button>
        </Space>
      </div>

      {order.status !== 'cancelled' && (
        <Card style={{ marginBottom: 24 }}>
          <Steps
            current={getCurrentStep()}
            items={statusSteps.map((s) => ({
              title: statusLabels[s as PurchaseOrderStatus],
            }))}
          />
        </Card>
      )}

      <Row gutter={24}>
        <Col span={16}>
          <Card title="Order Items" style={{ marginBottom: 24 }}>
            <Table
              dataSource={order.items}
              columns={itemColumns}
              rowKey="id"
              pagination={false}
              summary={() => (
                <Table.Summary fixed>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={5}>
                      <Text strong>Subtotal</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      <Text strong>{order.subtotal.toFixed(2)} SAR</Text>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={5}>
                      <Text strong>Tax</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      <Text strong>{order.tax_amount.toFixed(2)} SAR</Text>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={5}>
                      <Text strong style={{ fontSize: 16 }}>Total</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      <Text strong style={{ fontSize: 16 }}>{order.total.toFixed(2)} SAR</Text>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                </Table.Summary>
              )}
            />
          </Card>

          {order.notes && (
            <Card title="Notes" style={{ marginBottom: 24 }}>
              <Text>{order.notes}</Text>
            </Card>
          )}
        </Col>

        <Col span={8}>
          <Card title="Order Information" style={{ marginBottom: 24 }}>
            <Descriptions column={1} size="small">
              <Descriptions.Item label="PO Number">{order.po_number}</Descriptions.Item>
              <Descriptions.Item label="Vendor">
                <Text strong>{order.vendor?.name}</Text>
              </Descriptions.Item>
              <Descriptions.Item label="Store">
                {order.store?.name} ({order.store?.code})
              </Descriptions.Item>
              <Descriptions.Item label="Order Date">
                {dayjs(order.order_date).format('YYYY-MM-DD')}
              </Descriptions.Item>
              {order.expected_date && (
                <Descriptions.Item label="Expected Date">
                  {dayjs(order.expected_date).format('YYYY-MM-DD')}
                </Descriptions.Item>
              )}
              {order.received_date && (
                <Descriptions.Item label="Received Date">
                  {dayjs(order.received_date).format('YYYY-MM-DD')}
                </Descriptions.Item>
              )}
              <Descriptions.Item label="Created">
                {dayjs(order.created_at).format('YYYY-MM-DD HH:mm')}
              </Descriptions.Item>
            </Descriptions>
          </Card>

          <Card title="Summary">
            <Row gutter={[16, 16]}>
              <Col span={12}>
                <Statistic
                  title="Total Items"
                  value={order.items?.reduce((sum, i) => sum + i.quantity_ordered, 0) || 0}
                />
              </Col>
              <Col span={12}>
                <Statistic
                  title="Received"
                  value={order.items?.reduce((sum, i) => sum + i.quantity_received, 0) || 0}
                />
              </Col>
              <Col span={24}>
                <Statistic title="Total Amount" value={order.total} precision={2} suffix="SAR" />
              </Col>
            </Row>
          </Card>
        </Col>
      </Row>

      {/* Receive Items Modal */}
      <Modal
        title="Receive Items"
        open={receiveModalOpen}
        onCancel={() => setReceiveModalOpen(false)}
        onOk={handleReceive}
        okText="Receive"
        confirmLoading={receiveMutation.isPending}
        width={800}
      >
        <Form form={receiveForm} layout="vertical">
          <Alert
            message="Enter the quantity received for each item"
            type="info"
            showIcon
            style={{ marginBottom: 16 }}
          />

          <Table
            dataSource={order.items?.filter((i) => i.quantity_ordered > i.quantity_received)}
            rowKey="id"
            pagination={false}
            size="small"
            columns={[
              {
                title: 'Product',
                key: 'product',
                render: (_: unknown, record: PurchaseOrderItem) => (
                  <Space direction="vertical" size={0}>
                    <Text strong>{record.product?.name}</Text>
                    <Text type="secondary">
                      Pending: {record.quantity_ordered - record.quantity_received}
                    </Text>
                  </Space>
                ),
              },
              {
                title: 'Quantity',
                key: 'quantity',
                width: 120,
                render: (_: unknown, record: PurchaseOrderItem) => (
                  <Form.Item
                    name={`qty_${record.id}`}
                    initialValue={record.quantity_ordered - record.quantity_received}
                    style={{ margin: 0 }}
                  >
                    <InputNumber
                      min={0}
                      max={record.quantity_ordered - record.quantity_received}
                      style={{ width: '100%' }}
                    />
                  </Form.Item>
                ),
              },
              {
                title: 'Condition',
                key: 'condition',
                width: 120,
                render: (_: unknown, record: PurchaseOrderItem) => (
                  <Form.Item name={`condition_${record.id}`} initialValue="good" style={{ margin: 0 }}>
                    <select style={{ width: '100%', padding: 4 }}>
                      <option value="good">Good</option>
                      <option value="damaged">Damaged</option>
                    </select>
                  </Form.Item>
                ),
              },
            ]}
          />

          <Divider />

          <Form.Item name="receipt_notes" label="Receipt Notes">
            <Input.TextArea rows={2} placeholder="Any notes about this receipt..." />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
