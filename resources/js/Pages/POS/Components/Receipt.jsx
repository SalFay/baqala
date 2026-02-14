import { Typography, Divider } from 'antd';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';

const { Text, Title } = Typography;

export default function Receipt({ receipt, order }) {
    const data = receipt || order || {};
    const items = data.items || [];
    const store = data.store || {};

    return (
        <div style={{ width: 300, padding: 20, fontFamily: 'monospace', fontSize: 12 }}>
            {/* Store Header */}
            <div style={{ textAlign: 'center', marginBottom: 16 }}>
                <Title level={4} style={{ margin: 0 }}>{store.name || 'Store Name'}</Title>
                {store.address && <Text style={{ display: 'block' }}>{store.address}</Text>}
                {store.phone && <Text style={{ display: 'block' }}>Tel: {store.phone}</Text>}
                {store.tax_number && <Text style={{ display: 'block' }}>VAT: {store.tax_number}</Text>}
            </div>

            <Divider dashed style={{ margin: '8px 0' }} />

            {/* Order Info */}
            <div style={{ marginBottom: 8 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text>Order #:</Text>
                    <Text strong>{data.order_number || data.invoice_no || '-'}</Text>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text>Date:</Text>
                    <Text>{formatDateTime(data.created_at || new Date())}</Text>
                </div>
                {data.cashier_name && (
                    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Text>Cashier:</Text>
                        <Text>{data.cashier_name}</Text>
                    </div>
                )}
                {data.customer_name && (
                    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Text>Customer:</Text>
                        <Text>{data.customer_name}</Text>
                    </div>
                )}
            </div>

            <Divider dashed style={{ margin: '8px 0' }} />

            {/* Items */}
            <div style={{ marginBottom: 8 }}>
                {items.map((item, index) => (
                    <div key={index} style={{ marginBottom: 8 }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                            <Text ellipsis style={{ maxWidth: 180 }}>
                                {item.product_name || item.name}
                            </Text>
                            <Text>{formatCurrency(item.line_total || item.total)}</Text>
                        </div>
                        <Text type="secondary" style={{ fontSize: 10 }}>
                            {item.quantity} x {formatCurrency(item.unit_price || item.price)}
                        </Text>
                    </div>
                ))}
            </div>

            <Divider dashed style={{ margin: '8px 0' }} />

            {/* Totals */}
            <div style={{ marginBottom: 8 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text>Subtotal:</Text>
                    <Text>{formatCurrency(data.subtotal || 0)}</Text>
                </div>
                {(data.discount || 0) > 0 && (
                    <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <Text>Discount:</Text>
                        <Text>-{formatCurrency(data.discount)}</Text>
                    </div>
                )}
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text>Tax:</Text>
                    <Text>{formatCurrency(data.tax_amount || 0)}</Text>
                </div>
                <Divider dashed style={{ margin: '8px 0' }} />
                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                    <Text strong style={{ fontSize: 14 }}>TOTAL:</Text>
                    <Text strong style={{ fontSize: 14 }}>{formatCurrency(data.total || 0)}</Text>
                </div>
            </div>

            {/* Payment Info */}
            {data.payments && data.payments.length > 0 && (
                <>
                    <Divider dashed style={{ margin: '8px 0' }} />
                    <div style={{ marginBottom: 8 }}>
                        <Text strong>Payment:</Text>
                        {data.payments.map((payment, index) => (
                            <div key={index} style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>{payment.method || payment.payment_method}</Text>
                                <Text>{formatCurrency(payment.amount)}</Text>
                            </div>
                        ))}
                        {data.change_amount > 0 && (
                            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                <Text>Change:</Text>
                                <Text>{formatCurrency(data.change_amount)}</Text>
                            </div>
                        )}
                    </div>
                </>
            )}

            <Divider dashed style={{ margin: '8px 0' }} />

            {/* Footer */}
            <div style={{ textAlign: 'center', marginTop: 16 }}>
                <Text>Thank you for your purchase!</Text>
                <br />
                <Text type="secondary" style={{ fontSize: 10 }}>
                    Please retain this receipt for returns/exchanges
                </Text>
            </div>
        </div>
    );
}
