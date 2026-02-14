import React, { useState, useEffect } from 'react';
import { Modal, Form, InputNumber, Select, Button, Space, Table, Typography, Alert, Divider } from 'antd';
import { PlusOutlined, DeleteOutlined, DollarOutlined, CreditCardOutlined, WalletOutlined } from '@ant-design/icons';
import { useTheme } from '@/contexts/ThemeContext';

const { Text, Title } = Typography;
const { Option } = Select;

interface Payment {
  id: string;
  type: 'cash' | 'card' | 'credit';
  amount: number;
  reference?: string;
}

interface SplitPaymentProps {
  open: boolean;
  onClose: () => void;
  onConfirm: (payments: Payment[]) => void;
  totalAmount: number;
  currency?: string;
}

const paymentTypes = [
  { value: 'cash', label: 'Cash', icon: <DollarOutlined /> },
  { value: 'card', label: 'Card', icon: <CreditCardOutlined /> },
  { value: 'credit', label: 'Store Credit', icon: <WalletOutlined /> },
];

export function SplitPayment({
  open,
  onClose,
  onConfirm,
  totalAmount,
  currency = 'SAR',
}: SplitPaymentProps) {
  const { isDark, colors } = useTheme();
  const [payments, setPayments] = useState<Payment[]>([]);
  const [newPaymentType, setNewPaymentType] = useState<'cash' | 'card' | 'credit'>('cash');
  const [newPaymentAmount, setNewPaymentAmount] = useState<number>(0);

  // Calculate totals
  const paidAmount = payments.reduce((sum, p) => sum + p.amount, 0);
  const remainingAmount = totalAmount - paidAmount;
  const isComplete = remainingAmount <= 0;
  const changeAmount = Math.max(0, paidAmount - totalAmount);

  // Reset when modal opens
  useEffect(() => {
    if (open) {
      setPayments([]);
      setNewPaymentAmount(totalAmount);
    }
  }, [open, totalAmount]);

  // Update suggested amount when remaining changes
  useEffect(() => {
    if (remainingAmount > 0) {
      setNewPaymentAmount(remainingAmount);
    }
  }, [remainingAmount]);

  const addPayment = () => {
    if (newPaymentAmount <= 0) return;

    const payment: Payment = {
      id: Date.now().toString(),
      type: newPaymentType,
      amount: newPaymentAmount,
    };

    setPayments([...payments, payment]);
    setNewPaymentAmount(Math.max(0, remainingAmount - newPaymentAmount));
  };

  const removePayment = (id: string) => {
    setPayments(payments.filter(p => p.id !== id));
  };

  const handleConfirm = () => {
    if (payments.length === 0) return;
    onConfirm(payments);
    onClose();
  };

  const formatMoney = (amount: number) => {
    return `${amount.toFixed(2)} ${currency}`;
  };

  const columns = [
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (type: string) => {
        const paymentType = paymentTypes.find(p => p.value === type);
        return (
          <Space>
            {paymentType?.icon}
            <span>{paymentType?.label}</span>
          </Space>
        );
      },
    },
    {
      title: 'Amount',
      dataIndex: 'amount',
      key: 'amount',
      render: (amount: number) => (
        <Text strong style={{ color: colors.primaryColor }}>
          {formatMoney(amount)}
        </Text>
      ),
    },
    {
      title: '',
      key: 'action',
      width: 60,
      render: (_: unknown, record: Payment) => (
        <Button
          type="text"
          danger
          icon={<DeleteOutlined />}
          onClick={() => removePayment(record.id)}
        />
      ),
    },
  ];

  return (
    <Modal
      title="Split Payment"
      open={open}
      onCancel={onClose}
      width={500}
      footer={null}
      destroyOnClose
    >
      {/* Total Due */}
      <div
        style={{
          backgroundColor: isDark ? '#1f1f1f' : '#f5f5f5',
          padding: 16,
          borderRadius: 8,
          marginBottom: 16,
          textAlign: 'center',
        }}
      >
        <Text type="secondary">Total Due</Text>
        <Title level={2} style={{ margin: 0, color: colors.primaryColor }}>
          {formatMoney(totalAmount)}
        </Title>
      </div>

      {/* Add Payment Form */}
      <div style={{ marginBottom: 16 }}>
        <Text strong style={{ marginBottom: 8, display: 'block' }}>
          Add Payment
        </Text>
        <Space.Compact style={{ width: '100%' }}>
          <Select
            value={newPaymentType}
            onChange={setNewPaymentType}
            style={{ width: 150 }}
          >
            {paymentTypes.map(type => (
              <Option key={type.value} value={type.value}>
                <Space>
                  {type.icon}
                  {type.label}
                </Space>
              </Option>
            ))}
          </Select>
          <InputNumber
            value={newPaymentAmount}
            onChange={(value) => setNewPaymentAmount(value || 0)}
            min={0}
            step={0.01}
            precision={2}
            style={{ flex: 1, width: '100%' }}
            addonAfter={currency}
          />
          <Button
            type="primary"
            icon={<PlusOutlined />}
            onClick={addPayment}
            disabled={newPaymentAmount <= 0}
          >
            Add
          </Button>
        </Space.Compact>

        {/* Quick Amount Buttons */}
        <Space style={{ marginTop: 8 }}>
          {[50, 100, 200, 500].map(amount => (
            <Button
              key={amount}
              size="small"
              onClick={() => setNewPaymentAmount(amount)}
            >
              {amount}
            </Button>
          ))}
          <Button
            size="small"
            type="primary"
            ghost
            onClick={() => setNewPaymentAmount(remainingAmount)}
          >
            Remaining ({formatMoney(remainingAmount)})
          </Button>
        </Space>
      </div>

      {/* Payments List */}
      {payments.length > 0 && (
        <>
          <Table
            dataSource={payments}
            columns={columns}
            rowKey="id"
            pagination={false}
            size="small"
            style={{ marginBottom: 16 }}
          />

          <Divider style={{ margin: '16px 0' }} />
        </>
      )}

      {/* Summary */}
      <div
        style={{
          backgroundColor: isDark ? '#1f1f1f' : '#f5f5f5',
          padding: 16,
          borderRadius: 8,
          marginBottom: 16,
        }}
      >
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text>Total Due:</Text>
          <Text>{formatMoney(totalAmount)}</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text>Paid:</Text>
          <Text type="success">{formatMoney(paidAmount)}</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text strong>Remaining:</Text>
          <Text strong type={remainingAmount > 0 ? 'danger' : 'success'}>
            {formatMoney(remainingAmount)}
          </Text>
        </div>
        {changeAmount > 0 && (
          <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 8 }}>
            <Text type="warning">Change:</Text>
            <Text type="warning" strong>{formatMoney(changeAmount)}</Text>
          </div>
        )}
      </div>

      {/* Warning if incomplete */}
      {!isComplete && payments.length > 0 && (
        <Alert
          type="warning"
          message={`Payment incomplete. ${formatMoney(remainingAmount)} remaining.`}
          style={{ marginBottom: 16 }}
        />
      )}

      {/* Actions */}
      <Space style={{ width: '100%', justifyContent: 'flex-end' }}>
        <Button onClick={onClose}>Cancel</Button>
        <Button
          type="primary"
          onClick={handleConfirm}
          disabled={payments.length === 0 || !isComplete}
        >
          Complete Payment
        </Button>
      </Space>
    </Modal>
  );
}

export default SplitPayment;
