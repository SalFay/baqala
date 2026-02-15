import { Typography, Divider } from 'antd';

const { Text, Title } = Typography;

// Helper to safely format numbers
const formatPrice = (value) => {
  const num = Number(value) || 0;
  return num.toFixed(2);
};

export default function Receipt({ receipt }) {
  return (
    <div
      className="print-content"
      style={{
        width: '80mm',
        padding: 10,
        fontFamily: 'monospace',
        fontSize: 12,
      }}
    >
      {/* Header */}
      <div style={{ textAlign: 'center', marginBottom: 16 }}>
        <Title level={4} style={{ margin: 0 }}>
          {receipt.store?.name || 'Baqala POS'}
        </Title>
        {receipt.store?.address && (
          <Text style={{ display: 'block' }}>{receipt.store.address}</Text>
        )}
        {receipt.store?.phone && (
          <Text style={{ display: 'block' }}>Tel: {receipt.store.phone}</Text>
        )}
      </div>

      <Divider dashed style={{ margin: '8px 0' }} />

      {/* Order info */}
      <div style={{ marginBottom: 8 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>Order #:</Text>
          <Text>{receipt.order?.order_number}</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>Date:</Text>
          <Text>{receipt.date}</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>Cashier:</Text>
          <Text>{receipt.cashier}</Text>
        </div>
        {receipt.customer && (
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Text>Customer:</Text>
            <Text>{receipt.customer}</Text>
          </div>
        )}
      </div>

      <Divider dashed style={{ margin: '8px 0' }} />

      {/* Items */}
      <div style={{ marginBottom: 8 }}>
        {receipt.items.map((item, idx) => (
          <div key={idx} style={{ marginBottom: 4 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
              <Text>{item.name}</Text>
            </div>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                paddingLeft: 16,
              }}
            >
              <Text type="secondary">
                {item.quantity} x {formatPrice(item.price)}
              </Text>
              <Text>{formatPrice(item.total)}</Text>
            </div>
          </div>
        ))}
      </div>

      <Divider dashed style={{ margin: '8px 0' }} />

      {/* Totals */}
      <div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>Subtotal:</Text>
          <Text>{formatPrice(receipt.subtotal)} SAR</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>VAT (15%):</Text>
          <Text>{formatPrice(receipt.tax)} SAR</Text>
        </div>
        {Number(receipt.discount) > 0 && (
          <div style={{ display: 'flex', justifyContent: 'space-between' }}>
            <Text>Discount:</Text>
            <Text>-{formatPrice(receipt.discount)} SAR</Text>
          </div>
        )}
        <Divider dashed style={{ margin: '8px 0' }} />
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            fontWeight: 'bold',
          }}
        >
          <Text strong>TOTAL:</Text>
          <Text strong>{formatPrice(receipt.total)} SAR</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text>Payment:</Text>
          <Text>{(receipt.payment_type || 'CASH').toUpperCase()}</Text>
        </div>
      </div>

      <Divider dashed style={{ margin: '8px 0' }} />

      {/* Footer */}
      <div style={{ textAlign: 'center', marginTop: 16 }}>
        <Text>Thank you for your purchase!</Text>
        <br />
        <Text type="secondary" style={{ fontSize: 10 }}>
          VAT included in all prices
        </Text>
      </div>
    </div>
  );
}
