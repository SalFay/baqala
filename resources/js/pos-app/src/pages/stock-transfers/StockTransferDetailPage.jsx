import { useState } from 'react';
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
  Steps,
  Alert,
} from 'antd';
import {
  ArrowLeftOutlined,
  EditOutlined,
  CloseOutlined,
  PrinterOutlined,
  SendOutlined,
  CarOutlined,
  DownloadOutlined,
  SwapOutlined,
  ShopOutlined,
} from '@ant-design/icons';
import { stockTransferService } from '../../api/services/stockTransfer.service';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const statusColors = {
  draft: 'default',
  pending: 'gold',
  in_transit: 'blue',
  received: 'green',
  cancelled: 'red',
};

const statusLabels = {
  draft: 'Draft',
  pending: 'Pending Approval',
  in_transit: 'In Transit',
  received: 'Received',
  cancelled: 'Cancelled',
};

const statusSteps = ['draft', 'pending', 'in_transit', 'received'];

export default function StockTransferDetailPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [shipModalOpen, setShipModalOpen] = useState(false);
  const [receiveModalOpen, setReceiveModalOpen] = useState(false);
  const [shipForm] = Form.useForm();
  const [receiveForm] = Form.useForm();

  const { data: transfer, isLoading } = useQuery({
    queryKey: ['stock-transfer', id],
    queryFn: () => stockTransferService.getStockTransfer(Number(id)),
    enabled: !!id,
  });

  const submitMutation = useMutation({
    mutationFn: stockTransferService.submitStockTransfer,
    onSuccess: () => {
      message.success('Stock transfer submitted for approval');
      queryClient.invalidateQueries({ queryKey: ['stock-transfer', id] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to submit');
    },
  });

  const shipMutation = useMutation({
    mutationFn: (items) => stockTransferService.shipStockTransfer(Number(id), items),
    onSuccess: () => {
      message.success('Items shipped successfully');
      queryClient.invalidateQueries({ queryKey: ['stock-transfer', id] });
      setShipModalOpen(false);
      shipForm.resetFields();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to ship items');
    },
  });

  const receiveMutation = useMutation({
    mutationFn: (items) => stockTransferService.receiveStockTransfer(Number(id), items),
    onSuccess: () => {
      message.success('Items received successfully');
      queryClient.invalidateQueries({ queryKey: ['stock-transfer', id] });
      setReceiveModalOpen(false);
      receiveForm.resetFields();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to receive items');
    },
  });

  const cancelMutation = useMutation({
    mutationFn: stockTransferService.cancelStockTransfer,
    onSuccess: () => {
      message.success('Stock transfer cancelled');
      queryClient.invalidateQueries({ queryKey: ['stock-transfer', id] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to cancel');
    },
  });

  const handleShip = () => {
    shipForm.validateFields().then((values) => {
      const items = transfer?.items
        ?.filter((item) => values[`qty_${item.id}`] > 0)
        .map((item) => ({
          stock_transfer_item_id: item.id,
          quantity_sent: values[`qty_${item.id}`] || 0,
        }));

      if (!items || items.length === 0) {
        message.error('Please enter quantities to ship');
        return;
      }

      shipMutation.mutate(items);
    });
  };

  const handleReceive = () => {
    receiveForm.validateFields().then((values) => {
      const items = transfer?.items
        ?.filter((item) => values[`qty_${item.id}`] > 0)
        .map((item) => ({
          stock_transfer_item_id: item.id,
          quantity_received: values[`qty_${item.id}`] || 0,
          notes: values[`notes_${item.id}`],
        }));

      if (!items || items.length === 0) {
        message.error('Please enter quantities to receive');
        return;
      }

      receiveMutation.mutate(items);
    });
  };

  const getCurrentStep = () => {
    if (!transfer) return 0;
    if (transfer.status === 'cancelled') return -1;
    return statusSteps.indexOf(transfer.status);
  };

  const itemColumns = [
    {
      title: 'Product',
      key: 'product',
      render: (_, record) => (
        <Space direction="vertical" size={0}>
          <Text strong>{record.product?.name}</Text>
          {record.product?.sku && <Text type="secondary">{record.product.sku}</Text>}
        </Space>
      ),
    },
    {
      title: 'Requested',
      dataIndex: 'quantity_requested',
      key: 'quantity_requested',
      width: 100,
      align: 'center',
    },
    {
      title: 'Sent',
      dataIndex: 'quantity_sent',
      key: 'quantity_sent',
      width: 100,
      align: 'center',
      render: (sent, record) => (
        <Text type={sent < record.quantity_requested ? 'warning' : 'success'}>{sent}</Text>
      ),
    },
    {
      title: 'Received',
      dataIndex: 'quantity_received',
      key: 'quantity_received',
      width: 100,
      align: 'center',
      render: (received, record) => (
        <Text type={received < record.quantity_sent ? 'warning' : 'success'}>{received}</Text>
      ),
    },
    {
      title: 'Status',
      key: 'status',
      width: 120,
      render: (_, record) => {
        if (record.quantity_received >= record.quantity_requested) {
          return <Tag color="green">Complete</Tag>;
        }
        if (record.quantity_sent > 0) {
          return <Tag color="orange">In Transit</Tag>;
        }
        return <Tag color="default">Pending</Tag>;
      },
    },
  ];

  if (isLoading) {
    return <Card loading />;
  }

  if (!transfer) {
    return (
      <Card>
        <Alert type="error" message="Stock transfer not found" showIcon />
      </Card>
    );
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
        <Space>
          <Button icon={<ArrowLeftOutlined />} onClick={() => navigate('/stock-transfers')}>
            Back
          </Button>
          <Title level={4} style={{ margin: 0 }}>
            {transfer.transfer_number}
          </Title>
          <Tag color={statusColors[transfer.status]} style={{ fontSize: 14 }}>
            {statusLabels[transfer.status]}
          </Tag>
        </Space>

        <Space>
          {transfer.status === 'draft' && (
            <>
              <Button icon={<EditOutlined />} onClick={() => navigate(`/stock-transfers/${id}/edit`)}>
                Edit
              </Button>
              <Button
                type="primary"
                icon={<SendOutlined />}
                onClick={() => submitMutation.mutate(Number(id))}
              >
                Submit for Approval
              </Button>
            </>
          )}
          {transfer.status === 'pending' && (
            <Button type="primary" icon={<CarOutlined />} onClick={() => setShipModalOpen(true)}>
              Ship Items
            </Button>
          )}
          {transfer.status === 'in_transit' && (
            <Button type="primary" icon={<DownloadOutlined />} onClick={() => setReceiveModalOpen(true)}>
              Receive Items
            </Button>
          )}
          {!['received', 'cancelled'].includes(transfer.status) && (
            <Button danger icon={<CloseOutlined />} onClick={() => cancelMutation.mutate(Number(id))}>
              Cancel
            </Button>
          )}
          <Button icon={<PrinterOutlined />}>Print</Button>
        </Space>
      </div>

      {transfer.status !== 'cancelled' && (
        <Card style={{ marginBottom: 24 }}>
          <Steps
            current={getCurrentStep()}
            items={statusSteps.map((s) => ({
              title: statusLabels[s],
            }))}
          />
        </Card>
      )}

      <Row gutter={24}>
        <Col span={16}>
          {/* Transfer Flow Card */}
          <Card style={{ marginBottom: 24 }}>
            <Row gutter={16} align="middle">
              <Col span={10}>
                <Card size="small" style={{ backgroundColor: '#f6ffed', borderColor: '#b7eb8f' }}>
                  <Space direction="vertical" align="center" style={{ width: '100%' }}>
                    <ShopOutlined style={{ fontSize: 32, color: '#52c41a' }} />
                    <Text strong>From</Text>
                    <Text>{transfer.from_store?.name}</Text>
                    <Text type="secondary">{transfer.from_store?.code}</Text>
                  </Space>
                </Card>
              </Col>
              <Col span={4} style={{ textAlign: 'center' }}>
                <SwapOutlined style={{ fontSize: 32, color: '#1890ff' }} />
              </Col>
              <Col span={10}>
                <Card size="small" style={{ backgroundColor: '#e6f7ff', borderColor: '#91d5ff' }}>
                  <Space direction="vertical" align="center" style={{ width: '100%' }}>
                    <ShopOutlined style={{ fontSize: 32, color: '#1890ff' }} />
                    <Text strong>To</Text>
                    <Text>{transfer.to_store?.name}</Text>
                    <Text type="secondary">{transfer.to_store?.code}</Text>
                  </Space>
                </Card>
              </Col>
            </Row>
          </Card>

          <Card title="Transfer Items" style={{ marginBottom: 24 }}>
            <Table
              dataSource={transfer.items}
              columns={itemColumns}
              rowKey="id"
              pagination={false}
              summary={() => (
                <Table.Summary fixed>
                  <Table.Summary.Row>
                    <Table.Summary.Cell index={0}>
                      <Text strong>Totals</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={1} align="center">
                      <Text strong>
                        {transfer.items?.reduce((s, i) => s + i.quantity_requested, 0)}
                      </Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={2} align="center">
                      <Text strong>{transfer.items?.reduce((s, i) => s + i.quantity_sent, 0)}</Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={3} align="center">
                      <Text strong>
                        {transfer.items?.reduce((s, i) => s + i.quantity_received, 0)}
                      </Text>
                    </Table.Summary.Cell>
                    <Table.Summary.Cell index={4} />
                  </Table.Summary.Row>
                </Table.Summary>
              )}
            />
          </Card>

          {transfer.notes && (
            <Card title="Notes" style={{ marginBottom: 24 }}>
              <Text>{transfer.notes}</Text>
            </Card>
          )}
        </Col>

        <Col span={8}>
          <Card title="Transfer Information" style={{ marginBottom: 24 }}>
            <Descriptions column={1} size="small">
              <Descriptions.Item label="Transfer #">{transfer.transfer_number}</Descriptions.Item>
              <Descriptions.Item label="From Store">
                <Text strong>{transfer.from_store?.name}</Text>
              </Descriptions.Item>
              <Descriptions.Item label="To Store">
                <Text strong>{transfer.to_store?.name}</Text>
              </Descriptions.Item>
              <Descriptions.Item label="Created">
                {dayjs(transfer.created_at).format('YYYY-MM-DD HH:mm')}
              </Descriptions.Item>
              {transfer.shipped_at && (
                <Descriptions.Item label="Shipped">
                  {dayjs(transfer.shipped_at).format('YYYY-MM-DD HH:mm')}
                </Descriptions.Item>
              )}
              {transfer.received_at && (
                <Descriptions.Item label="Received">
                  {dayjs(transfer.received_at).format('YYYY-MM-DD HH:mm')}
                </Descriptions.Item>
              )}
            </Descriptions>
          </Card>

          <Card title="Summary">
            <Row gutter={[16, 16]}>
              <Col span={12}>
                <Statistic
                  title="Requested"
                  value={transfer.items?.reduce((s, i) => s + i.quantity_requested, 0) || 0}
                />
              </Col>
              <Col span={12}>
                <Statistic
                  title="Sent"
                  value={transfer.items?.reduce((s, i) => s + i.quantity_sent, 0) || 0}
                />
              </Col>
              <Col span={12}>
                <Statistic
                  title="Received"
                  value={transfer.items?.reduce((s, i) => s + i.quantity_received, 0) || 0}
                />
              </Col>
              <Col span={12}>
                <Statistic title="Items" value={transfer.items?.length || 0} />
              </Col>
            </Row>
          </Card>
        </Col>
      </Row>

      {/* Ship Items Modal */}
      <Modal
        title="Ship Items"
        open={shipModalOpen}
        onCancel={() => setShipModalOpen(false)}
        onOk={handleShip}
        okText="Ship"
        confirmLoading={shipMutation.isPending}
        width={700}
      >
        <Form form={shipForm} layout="vertical">
          <Alert
            message="Enter the quantity to ship for each item"
            type="info"
            showIcon
            style={{ marginBottom: 16 }}
          />

          <Table
            dataSource={transfer.items?.filter((i) => i.quantity_sent < i.quantity_requested)}
            rowKey="id"
            pagination={false}
            size="small"
            columns={[
              {
                title: 'Product',
                key: 'product',
                render: (_, record) => (
                  <Space direction="vertical" size={0}>
                    <Text strong>{record.product?.name}</Text>
                    <Text type="secondary">
                      Pending: {record.quantity_requested - record.quantity_sent}
                    </Text>
                  </Space>
                ),
              },
              {
                title: 'Quantity',
                key: 'quantity',
                width: 120,
                render: (_, record) => (
                  <Form.Item
                    name={`qty_${record.id}`}
                    initialValue={record.quantity_requested - record.quantity_sent}
                    style={{ margin: 0 }}
                  >
                    <InputNumber
                      min={0}
                      max={record.quantity_requested - record.quantity_sent}
                      style={{ width: '100%' }}
                    />
                  </Form.Item>
                ),
              },
            ]}
          />
        </Form>
      </Modal>

      {/* Receive Items Modal */}
      <Modal
        title="Receive Items"
        open={receiveModalOpen}
        onCancel={() => setReceiveModalOpen(false)}
        onOk={handleReceive}
        okText="Receive"
        confirmLoading={receiveMutation.isPending}
        width={700}
      >
        <Form form={receiveForm} layout="vertical">
          <Alert
            message="Enter the quantity received for each item"
            type="info"
            showIcon
            style={{ marginBottom: 16 }}
          />

          <Table
            dataSource={transfer.items?.filter((i) => i.quantity_received < i.quantity_sent)}
            rowKey="id"
            pagination={false}
            size="small"
            columns={[
              {
                title: 'Product',
                key: 'product',
                render: (_, record) => (
                  <Space direction="vertical" size={0}>
                    <Text strong>{record.product?.name}</Text>
                    <Text type="secondary">
                      Sent: {record.quantity_sent} | Received: {record.quantity_received}
                    </Text>
                  </Space>
                ),
              },
              {
                title: 'Quantity',
                key: 'quantity',
                width: 120,
                render: (_, record) => (
                  <Form.Item
                    name={`qty_${record.id}`}
                    initialValue={record.quantity_sent - record.quantity_received}
                    style={{ margin: 0 }}
                  >
                    <InputNumber
                      min={0}
                      max={record.quantity_sent - record.quantity_received}
                      style={{ width: '100%' }}
                    />
                  </Form.Item>
                ),
              },
              {
                title: 'Notes',
                key: 'notes',
                width: 150,
                render: (_, record) => (
                  <Form.Item name={`notes_${record.id}`} style={{ margin: 0 }}>
                    <Input placeholder="Notes..." />
                  </Form.Item>
                ),
              },
            ]}
          />
        </Form>
      </Modal>
    </div>
  );
}
