import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Card,
  Table,
  Button,
  DatePicker,
  Space,
  Statistic,
  Row,
  Col,
  Tag,
  Modal,
  Form,
  InputNumber,
  Input,
  Radio,
  Typography,
  message,
  Empty,
} from 'antd';
import {
  PlusOutlined,
  PrinterOutlined,
  DownloadOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import statementService from '../../../api/services/statement.service';

const { RangePicker } = DatePicker;
const { Text } = Typography;

export default function StatementView({ entityType, entityId, entityName }) {
  const queryClient = useQueryClient();
  const [form] = Form.useForm();
  const [dateRange, setDateRange] = useState([
    dayjs().subtract(3, 'month'),
    dayjs(),
  ]);
  const [creditModalVisible, setCreditModalVisible] = useState(false);

  const isCustomer = entityType === 'customer';

  // Fetch statement
  const { data: statement, isLoading } = useQuery({
    queryKey: ['statement', entityType, entityId, dateRange],
    queryFn: () => {
      const params = {
        from_date: dateRange[0]?.format('YYYY-MM-DD'),
        to_date: dateRange[1]?.format('YYYY-MM-DD'),
      };
      return isCustomer
        ? statementService.getCustomerStatement(entityId, params)
        : statementService.getVendorStatement(entityId, params);
    },
    enabled: !!entityId,
  });

  // Add credit mutation
  const addCreditMutation = useMutation({
    mutationFn: (data) =>
      isCustomer
        ? statementService.addCustomerCredit(entityId, data)
        : statementService.addVendorCredit(entityId, data),
    onSuccess: () => {
      message.success('Transaction added successfully');
      queryClient.invalidateQueries({ queryKey: ['statement', entityType, entityId] });
      setCreditModalVisible(false);
      form.resetFields();
    },
    onError: () => {
      message.error('Failed to add transaction');
    },
  });

  const handleAddCredit = (values) => {
    addCreditMutation.mutate(values);
  };

  const columns = [
    {
      title: 'Date',
      dataIndex: 'date',
      key: 'date',
      width: 150,
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      width: 120,
      render: (type) => {
        const colors = {
          order: 'blue',
          payment: 'green',
          credit: 'cyan',
          debit: 'orange',
          purchase_order: 'purple',
        };
        return <Tag color={colors[type] || 'default'}>{type.replace('_', ' ').toUpperCase()}</Tag>;
      },
    },
    {
      title: 'Reference',
      dataIndex: 'reference',
      key: 'reference',
      width: 150,
    },
    {
      title: 'Description',
      dataIndex: 'description',
      key: 'description',
    },
    {
      title: 'Debit',
      dataIndex: 'debit',
      key: 'debit',
      width: 120,
      align: 'right',
      render: (val) => (val > 0 ? <Text type="danger">{val.toFixed(2)}</Text> : '-'),
    },
    {
      title: 'Credit',
      dataIndex: 'credit',
      key: 'credit',
      width: 120,
      align: 'right',
      render: (val) => (val > 0 ? <Text type="success">{val.toFixed(2)}</Text> : '-'),
    },
    {
      title: 'Balance',
      dataIndex: 'balance',
      key: 'balance',
      width: 120,
      align: 'right',
      render: (val) => (
        <Text strong type={val >= 0 ? 'danger' : 'success'}>
          {Math.abs(val).toFixed(2)} {val >= 0 ? 'DR' : 'CR'}
        </Text>
      ),
    },
  ];

  if (!entityId) {
    return (
      <Card>
        <Empty description={`Select a ${entityType} to view statement`} />
      </Card>
    );
  }

  return (
    <div>
      {/* Header */}
      <Card size="small" style={{ marginBottom: 16 }}>
        <Row gutter={24} align="middle">
          <Col flex="auto">
            <Space>
              <Text strong>{entityName}</Text>
              <RangePicker
                value={dateRange}
                onChange={setDateRange}
                allowClear={false}
              />
            </Space>
          </Col>
          <Col>
            <Space>
              <Button icon={<PrinterOutlined />} onClick={() => window.print()}>
                Print
              </Button>
              <Button
                icon={<DownloadOutlined />}
                onClick={() => {
                  const url = isCustomer
                    ? `/pos/customers/${entityId}/statement/pdf`
                    : `/pos/vendors/${entityId}/statement/pdf`;
                  const params = new URLSearchParams({
                    from_date: dateRange[0]?.format('YYYY-MM-DD'),
                    to_date: dateRange[1]?.format('YYYY-MM-DD'),
                  });
                  window.open(`${url}?${params}`, '_blank');
                }}
              >
                Download PDF
              </Button>
              <Button
                type="primary"
                icon={<PlusOutlined />}
                onClick={() => setCreditModalVisible(true)}
              >
                Add Transaction
              </Button>
            </Space>
          </Col>
        </Row>
      </Card>

      {/* Summary */}
      <Row gutter={16} style={{ marginBottom: 16 }}>
        <Col span={6}>
          <Card size="small">
            <Statistic
              title="Opening Balance"
              value={statement?.opening_balance || 0}
              precision={2}
              suffix="SAR"
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card size="small">
            <Statistic
              title="Total Debit"
              value={statement?.total_debit || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: '#cf1322' }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card size="small">
            <Statistic
              title="Total Credit"
              value={statement?.total_credit || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: '#3f8600' }}
            />
          </Card>
        </Col>
        <Col span={6}>
          <Card size="small">
            <Statistic
              title="Closing Balance"
              value={Math.abs(statement?.closing_balance || 0)}
              precision={2}
              suffix={`SAR ${(statement?.closing_balance || 0) >= 0 ? 'DR' : 'CR'}`}
              valueStyle={{
                color: (statement?.closing_balance || 0) >= 0 ? '#cf1322' : '#3f8600',
              }}
            />
          </Card>
        </Col>
      </Row>

      {/* Transactions */}
      <Card title="Transactions" size="small">
        <Table
          dataSource={statement?.transactions || []}
          columns={columns}
          rowKey={(record, index) => `${record.date}-${record.reference}-${index}`}
          loading={isLoading}
          pagination={false}
          size="small"
          scroll={{ x: 900 }}
        />
      </Card>

      {/* Add Credit Modal */}
      <Modal
        title="Add Transaction"
        open={creditModalVisible}
        onCancel={() => {
          setCreditModalVisible(false);
          form.resetFields();
        }}
        footer={null}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleAddCredit}
          initialValues={{ type: 'credit' }}
        >
          <Form.Item
            name="type"
            label="Transaction Type"
            rules={[{ required: true }]}
          >
            <Radio.Group>
              <Radio.Button value="credit">
                {isCustomer ? 'Add Credit' : 'Payment to Vendor'}
              </Radio.Button>
              <Radio.Button value="debit">
                {isCustomer ? 'Use Credit' : 'Credit from Vendor'}
              </Radio.Button>
            </Radio.Group>
          </Form.Item>

          <Form.Item
            name="amount"
            label="Amount"
            rules={[
              { required: true, message: 'Amount is required' },
              { type: 'number', min: 0.01, message: 'Amount must be greater than 0' },
            ]}
          >
            <InputNumber
              style={{ width: '100%' }}
              precision={2}
              min={0.01}
              addonAfter="SAR"
            />
          </Form.Item>

          <Form.Item name="reference" label="Reference">
            <Input placeholder="e.g., INV-001, Receipt #123" />
          </Form.Item>

          <Form.Item name="notes" label="Notes">
            <Input.TextArea rows={2} placeholder="Optional notes" />
          </Form.Item>

          <Form.Item style={{ marginBottom: 0, textAlign: 'right' }}>
            <Space>
              <Button onClick={() => setCreditModalVisible(false)}>Cancel</Button>
              <Button
                type="primary"
                htmlType="submit"
                loading={addCreditMutation.isPending}
              >
                Save
              </Button>
            </Space>
          </Form.Item>
        </Form>
      </Modal>
    </div>
  );
}
