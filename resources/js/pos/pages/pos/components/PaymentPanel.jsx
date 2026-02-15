import { useState, useRef, useMemo } from 'react';
import { Button, Radio, Space, Typography, Input, message, Result } from 'antd';
import {
  ArrowLeftOutlined,
  DollarOutlined,
  CreditCardOutlined,
  PrinterOutlined,
  CheckCircleOutlined,
} from '@ant-design/icons';
import { useReactToPrint } from 'react-to-print';
import { useCartStore } from '../../../store/cartStore';
import Receipt from './Receipt';

const { Title, Text } = Typography;

export default function PaymentPanel({ cart, summary, onBack, onComplete }) {
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [cashReceived, setCashReceived] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [completedOrder, setCompletedOrder] = useState(null);
  const [receipt, setReceipt] = useState(null);
  const [orderTotal, setOrderTotal] = useState(null); // Store total before checkout

  const receiptRef = useRef(null);
  const { checkout } = useCartStore();

  const handlePrint = useReactToPrint({
    content: () => receiptRef.current,
  });

  // Use stored orderTotal after checkout, otherwise use summary.total
  // Ensure total is always a number
  const total = Number(orderTotal ?? summary?.total ?? 0) || 0;
  const itemsCount = cart?.items?.length ?? 0;

  const change = useMemo(() => {
    if (paymentMethod === 'cash' && cashReceived) {
      return parseFloat(cashReceived) - total;
    }
    return 0;
  }, [paymentMethod, cashReceived, total]);

  const handlePayment = async () => {
    if (paymentMethod === 'cash' && change < 0) {
      message.error('Insufficient cash amount');
      return;
    }

    try {
      setIsProcessing(true);
      // Store the total before checkout clears the cart
      setOrderTotal(summary?.total ?? 0);

      const result = await checkout(paymentMethod, {
        cash_received: paymentMethod === 'cash' ? parseFloat(cashReceived) : undefined,
        payment_reference: paymentMethod === 'card' ? 'Manual Card Payment' : undefined,
      });
      setCompletedOrder(result.order);
      setReceipt(result.receipt);
      message.success('Payment successful!');
    } catch (error) {
      setOrderTotal(null); // Reset on error
      message.error(error.message || 'Payment failed');
    } finally {
      setIsProcessing(false);
    }
  };

  const handleDone = () => {
    setCompletedOrder(null);
    setReceipt(null);
    setOrderTotal(null);
    setCashReceived('');
    onComplete();
  };

  const quickCashAmounts = [10, 20, 50, 100, 200, 500];

  // Success Screen
  if (completedOrder) {
    const storedTotal = Number(orderTotal ?? 0) || 0;
    const changeAmount = paymentMethod === 'cash' && cashReceived
      ? parseFloat(cashReceived) - storedTotal
      : 0;

    return (
      <div style={{ padding: 16, height: '100%', display: 'flex', flexDirection: 'column' }}>
        <Result
          status="success"
          icon={<CheckCircleOutlined style={{ color: '#52c41a' }} />}
          title="Payment Successful!"
          subTitle={`Order #${completedOrder.order_number}`}
        />

        {paymentMethod === 'cash' && changeAmount > 0 && (
          <div
            style={{
              background: '#f6ffed',
              border: '1px solid #b7eb8f',
              borderRadius: 8,
              padding: 16,
              textAlign: 'center',
              marginBottom: 24,
            }}
          >
            <Text>Change Due</Text>
            <Title level={2} style={{ margin: 0, color: '#52c41a' }}>
              {changeAmount.toFixed(2)} SAR
            </Title>
          </div>
        )}

        {/* Hidden receipt for printing */}
        {receipt && (
          <div style={{ display: 'none' }}>
            <div ref={receiptRef}>
              <Receipt receipt={receipt} />
            </div>
          </div>
        )}

        <div style={{ flex: 1 }} />

        <Space direction="vertical" style={{ width: '100%' }} size="middle">
          {receipt && (
            <Button
              icon={<PrinterOutlined />}
              size="large"
              block
              onClick={handlePrint}
            >
              Print Receipt
            </Button>
          )}
          <Button type="primary" size="large" block onClick={handleDone}>
            Done - New Order
          </Button>
        </Space>
      </div>
    );
  }

  // Payment Form
  return (
    <div style={{ padding: 16, height: '100%', display: 'flex', flexDirection: 'column' }}>
      <div style={{ display: 'flex', alignItems: 'center', marginBottom: 24 }}>
        <Button icon={<ArrowLeftOutlined />} onClick={onBack} type="text" />
        <Title level={4} style={{ margin: 0, marginLeft: 8 }}>
          Payment
        </Title>
      </div>

      {/* Order Summary */}
      <div style={{ marginBottom: 24 }}>
        <Text type="secondary">{itemsCount} items</Text>
        <Title level={2} style={{ margin: '8px 0', color: '#1890ff' }}>
          {total.toFixed(2)} SAR
        </Title>
      </div>

      {/* Payment Method */}
      <div style={{ marginBottom: 24 }}>
        <Text strong style={{ display: 'block', marginBottom: 12 }}>
          Payment Method
        </Text>
        <Radio.Group
          value={paymentMethod}
          onChange={(e) => setPaymentMethod(e.target.value)}
          size="large"
          style={{ width: '100%' }}
        >
          <Space direction="vertical" style={{ width: '100%' }}>
            <Radio.Button value="cash" style={{ width: '100%', height: 48 }}>
              <DollarOutlined /> Cash
            </Radio.Button>
            <Radio.Button value="card" style={{ width: '100%', height: 48 }}>
              <CreditCardOutlined /> Card
            </Radio.Button>
          </Space>
        </Radio.Group>
      </div>

      {/* Cash input */}
      {paymentMethod === 'cash' && (
        <div style={{ marginBottom: 24 }}>
          <Text strong style={{ display: 'block', marginBottom: 12 }}>
            Cash Received
          </Text>
          <Input
            size="large"
            type="number"
            prefix="SAR"
            value={cashReceived}
            onChange={(e) => setCashReceived(e.target.value)}
            style={{ marginBottom: 12 }}
          />
          <Space wrap>
            {quickCashAmounts.map((amount) => (
              <Button
                key={amount}
                onClick={() => setCashReceived(amount.toString())}
              >
                {amount}
              </Button>
            ))}
            <Button onClick={() => setCashReceived(total.toFixed(2))}>
              Exact
            </Button>
          </Space>

          {cashReceived && (
            <div
              style={{
                marginTop: 16,
                padding: 12,
                background: change >= 0 ? '#f6ffed' : '#fff2f0',
                borderRadius: 8,
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <Text>Change</Text>
                <Text
                  strong
                  style={{ color: change >= 0 ? '#52c41a' : '#ff4d4f' }}
                >
                  {change >= 0 ? change.toFixed(2) : '(Insufficient)'} SAR
                </Text>
              </div>
            </div>
          )}
        </div>
      )}

      <div style={{ flex: 1 }} />

      {/* Complete payment button */}
      <Button
        type="primary"
        size="large"
        block
        onClick={handlePayment}
        loading={isProcessing}
        disabled={paymentMethod === 'cash' && change < 0}
      >
        {isProcessing ? 'Processing...' : `Pay ${total.toFixed(2)} SAR`}
      </Button>
    </div>
  );
}
