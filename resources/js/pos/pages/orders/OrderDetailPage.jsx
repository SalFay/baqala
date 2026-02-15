import { useNavigate, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Card,
  Row,
  Col,
  Table,
  Tag,
  Typography,
  Button,
  Spin,
  Descriptions,
  Space,
  Popconfirm,
  message,
} from 'antd';
import { ArrowLeftOutlined, PrinterOutlined, CloseCircleOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { orderService } from '../../api/services/order.service';

const { Title, Text } = Typography;

export default function OrderDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: order, isLoading } = useQuery({
    queryKey: ['order', id],
    queryFn: () => orderService.getOrder(parseInt(id)),
  });

  const cancelMutation = useMutation({
    mutationFn: () => orderService.cancelOrder(parseInt(id)),
    onSuccess: () => {
      message.success('Order cancelled');
      queryClient.invalidateQueries({ queryKey: ['order', id] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to cancel order');
    },
  });

  if (isLoading) {
    return (
      <div style={{ textAlign: 'center', padding: 100 }}>
        <Spin size="large" />
      </div>
    );
  }

  if (!order) {
    return <div>Order not found</div>;
  }

  const statusColors = {
    pending: 'orange',
    processing: 'blue',
    completed: 'green',
    cancelled: 'red',
    refunded: 'purple',
  };

  const itemColumns = [
    {
      title: 'Product',
      dataIndex: 'product_name',
      key: 'product',
      render: (name, record) => (
        <span>
          {name}
          {record.variant_name && ` - ${record.variant_name}`}
        </span>
      ),
    },
    { title: 'SKU', dataIndex: 'sku', key: 'sku' },
    {
      title: 'Price',
      dataIndex: 'sale_price',
      key: 'price',
      render: (val) => `${val.toFixed(2)} SAR`,
    },
    { title: 'Qty', dataIndex: 'stock', key: 'qty' },
    {
      title: 'Total',
      dataIndex: 'line_total',
      key: 'total',
      render: (val) => `${val.toFixed(2)} SAR`,
    },
  ];

  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <Button
            icon={<ArrowLeftOutlined />}
            onClick={() => navigate('/orders')}
            type="text"
          />
          <Title level={4} style={{ margin: 0, marginLeft: 8 }}>
            Order {order.order_number}
          </Title>
          <Tag color={statusColors[order.status] || 'default'} style={{ marginLeft: 12 }}>
            {order.status?.toUpperCase()}
          </Tag>
        </div>
        <Space>
          <Button icon={<PrinterOutlined />}>Print Receipt</Button>
          {order.status === 'completed' && (
            <Popconfirm
              title="Cancel this order?"
              onConfirm={() => cancelMutation.mutate()}
            >
              <Button danger icon={<CloseCircleOutlined />} loading={cancelMutation.isPending}>
                Cancel Order
              </Button>
            </Popconfirm>
          )}
        </Space>
      </div>

      <Row gutter={24}>
        <Col xs={24} lg={16}>
          <Card title="Order Items" style={{ marginBottom: 24 }}>
            <Table
              dataSource={order.items}
              columns={itemColumns}
              rowKey="id"
              pagination={false}
              summary={() => (
                <>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={4}>
                      <Text strong>Subtotal</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      <Text strong>{order.sub_total.toFixed(2)} SAR</Text>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={4}>
                      <Text>Tax (VAT 15%)</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      {order.tax_amount.toFixed(2)} SAR
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                  {order.discount > 0 && (
                    <Table.Summary.Row>
                      <Table.Summary.Cell index={0} colSpan={4}>
                        <Text type="success">Discount</Text>
                      </Table.Summary.Cell>
                      <Table.Summary.Cell index={1}>
                        <Text type="success">-{order.discount.toFixed(2)} SAR</Text>
                      </Table.Summary.Cell>
                    </Table.Summary.Row>
                  )}
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0} colSpan={4}>
                      <Text strong style={{ fontSize: 16 }}>Total</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1}>
                      <Text strong style={{ fontSize: 16, color: '#1890ff' }}>
                        {order.total.toFixed(2)} SAR
                      </Text>
                    </Table.Summary.Cell>
                  </Table.Summary.Row>
                </>
              )}
            />
          </Card>
        </Col>

        <Col xs={24} lg={8}>
          <Card title="Order Details" style={{ marginBottom: 24 }}>
            <Descriptions column={1} size="small">
              <Descriptions.Item label="Order Number">{order.order_number}</Descriptions.Item>
              <Descriptions.Item label="Date">
                {dayjs(order.created_at).format('MMM D, YYYY h:mm A')}
              </Descriptions.Item>
              <Descriptions.Item label="Payment Type">
                {order.payment_type?.toUpperCase()}
              </Descriptions.Item>
              <Descriptions.Item label="Payment Status">
                <Tag color={order.payment_status === 'paid' ? 'green' : 'orange'}>
                  {order.payment_status?.toUpperCase()}
                </Tag>
              </Descriptions.Item>
              <Descriptions.Item label="Cashier">{order.cashier_name}</Descriptions.Item>
            </Descriptions>
          </Card>

          <Card title="Customer">
            {order.customer ? (
              <Descriptions column={1} size="small">
                <Descriptions.Item label="Name">{order.customer.full_name}</Descriptions.Item>
                <Descriptions.Item label="Phone">{order.customer.phone_mobile}</Descriptions.Item>
                <Descriptions.Item label="Email">{order.customer.email}</Descriptions.Item>
              </Descriptions>
            ) : (
              <Text type="secondary">Walk-in Customer</Text>
            )}
          </Card>
        </Col>
      </Row>
    </div>
  );
}
