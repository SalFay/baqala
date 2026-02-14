import { useState, useRef } from 'react';
import { Button, Radio, Space, Typography, Input, message, Divider } from 'antd';
import {
    ArrowLeftOutlined, DollarOutlined, CreditCardOutlined,
    PrinterOutlined, MobileOutlined, BankOutlined, CheckCircleOutlined,
} from '@ant-design/icons';
import { useReactToPrint } from 'react-to-print';
import Receipt from './Receipt';
import { formatCurrency } from '@/Helpers/formatters';

const { Title, Text } = Typography;

export default function PaymentPanel({ cart, summary, onBack, onCheckout, onComplete }) {
    const [paymentMethod, setPaymentMethod] = useState('cash');
    const [cashReceived, setCashReceived] = useState('');
    const [isProcessing, setIsProcessing] = useState(false);
    const [completedOrder, setCompletedOrder] = useState(null);
    const [receipt, setReceipt] = useState(null);

    const receiptRef = useRef(null);

    const handlePrint = useReactToPrint({
        content: () => receiptRef.current,
    });

    const change = paymentMethod === 'cash' && cashReceived
        ? parseFloat(cashReceived) - (summary?.total || 0)
        : 0;

    const handlePayment = async () => {
        if (paymentMethod === 'cash' && change < 0) {
            message.error('Insufficient cash amount');
            return;
        }

        try {
            setIsProcessing(true);
            const result = await onCheckout(paymentMethod, parseFloat(cashReceived) || 0);
            setCompletedOrder(result.order);
            setReceipt(result.receipt);
            message.success('Payment successful!');
        } catch (error) {
            message.error(error.message || 'Payment failed');
        } finally {
            setIsProcessing(false);
        }
    };

    const handleDone = () => {
        setCompletedOrder(null);
        setReceipt(null);
        onComplete();
    };

    const quickCashAmounts = [10, 20, 50, 100, 200, 500];

    // Payment methods - can be extended for different business types
    const paymentMethods = [
        { key: 'cash', label: 'Cash', icon: <DollarOutlined /> },
        { key: 'card', label: 'Card', icon: <CreditCardOutlined /> },
        { key: 'mobile', label: 'Mobile Pay', icon: <MobileOutlined /> },
        { key: 'bank_transfer', label: 'Bank Transfer', icon: <BankOutlined /> },
    ];

    if (completedOrder) {
        return (
            <div style={{ padding: 16, height: '100%', display: 'flex', flexDirection: 'column' }}>
                <div style={{ textAlign: 'center', marginBottom: 24 }}>
                    <div
                        style={{
                            width: 80,
                            height: 80,
                            borderRadius: '50%',
                            background: '#52c41a',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            margin: '0 auto 16px',
                        }}
                    >
                        <CheckCircleOutlined style={{ fontSize: 40, color: '#fff' }} />
                    </div>
                    <Title level={3} style={{ color: '#52c41a', marginBottom: 8 }}>
                        Payment Successful!
                    </Title>
                    <Text type="secondary">Order #{completedOrder.order_number}</Text>
                </div>

                {paymentMethod === 'cash' && change > 0 && (
                    <div
                        style={{
                            background: '#f6ffed',
                            border: '1px solid #b7eb8f',
                            borderRadius: 8,
                            padding: 20,
                            textAlign: 'center',
                            marginBottom: 24,
                        }}
                    >
                        <Text>Change Due</Text>
                        <Title level={2} style={{ margin: '8px 0 0', color: '#52c41a' }}>
                            {formatCurrency(change)}
                        </Title>
                    </div>
                )}

                {/* Hidden receipt for printing */}
                <div style={{ display: 'none' }}>
                    <div ref={receiptRef}>
                        <Receipt receipt={receipt} order={completedOrder} />
                    </div>
                </div>

                <div style={{ flex: 1 }} />

                <Space direction="vertical" style={{ width: '100%' }} size="middle">
                    <Button
                        icon={<PrinterOutlined />}
                        size="large"
                        block
                        onClick={handlePrint}
                    >
                        Print Receipt
                    </Button>
                    <Button type="primary" size="large" block onClick={handleDone}>
                        Done - New Order
                    </Button>
                </Space>
            </div>
        );
    }

    return (
        <div style={{ padding: 16, height: '100%', display: 'flex', flexDirection: 'column' }}>
            <div style={{ display: 'flex', alignItems: 'center', marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={onBack} type="text" />
                <Title level={4} style={{ margin: 0, marginLeft: 8 }}>Payment</Title>
            </div>

            {/* Order Summary */}
            <div style={{ marginBottom: 24, textAlign: 'center' }}>
                <Text type="secondary">{summary?.items_count || 0} items</Text>
                <Title level={2} style={{ margin: '8px 0', color: '#1890ff' }}>
                    {formatCurrency(summary?.total || 0)}
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
                        {paymentMethods.map((method) => (
                            <Radio.Button
                                key={method.key}
                                value={method.key}
                                style={{ width: '100%', height: 48, display: 'flex', alignItems: 'center' }}
                            >
                                {method.icon} <span style={{ marginLeft: 8 }}>{method.label}</span>
                            </Radio.Button>
                        ))}
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
                        prefix={<DollarOutlined />}
                        value={cashReceived}
                        onChange={(e) => setCashReceived(e.target.value)}
                        placeholder="Enter amount"
                        style={{ marginBottom: 12 }}
                    />
                    <Space wrap>
                        {quickCashAmounts.map((amount) => (
                            <Button key={amount} onClick={() => setCashReceived(amount.toString())}>
                                {amount}
                            </Button>
                        ))}
                        <Button type="dashed" onClick={() => setCashReceived(Number(summary?.total || 0).toFixed(2))}>
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
                                border: `1px solid ${change >= 0 ? '#b7eb8f' : '#ffccc7'}`,
                            }}
                        >
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>Change</Text>
                                <Text strong style={{ color: change >= 0 ? '#52c41a' : '#ff4d4f' }}>
                                    {change >= 0 ? formatCurrency(change) : '(Insufficient)'}
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
                style={{ height: 50 }}
            >
                {isProcessing ? 'Processing...' : `Pay ${formatCurrency(summary?.total || 0)}`}
            </Button>
        </div>
    );
}
