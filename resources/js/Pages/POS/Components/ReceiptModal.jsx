import { Typography, Button, Space, theme } from 'antd'
import { PrinterOutlined, CheckCircleOutlined } from '@ant-design/icons'
import { usePage } from '@inertiajs/react'
import CustomModal from '@/Components/CustomModal'
import { formatAmount, formatReceiptDateTime, getCurrency } from '@/Helpers/formatters'

const { Text } = Typography

// Thermal receipt width (80mm = ~48 chars, 58mm = ~32 chars)
const RECEIPT_WIDTH = 48

// Helper to center text
const centerText = (text, width = RECEIPT_WIDTH) => {
  const padding = Math.max(0, Math.floor((width - text.length) / 2))
  return ' '.repeat(padding) + text
}

// Helper to create line with left and right text
const lineLeftRight = (left, right, width = RECEIPT_WIDTH) => {
  const spaces = width - left.length - right.length
  return left + ' '.repeat(Math.max(1, spaces)) + right
}

// Helper to create dashed line
const dashedLine = (width = RECEIPT_WIDTH) => '-'.repeat(width)

// Format currency for receipt (compact, no symbol prefix for alignment)
const fmtPrice = (value, currency) => {
  return `${formatAmount(value)} ${currency}`
}

// Generate thermal receipt text
const generateThermalReceipt = (order, receipt, appSettings) => {
  const lines = []
  const w = RECEIPT_WIDTH
  const currency = appSettings?.currency || getCurrency()

  // Store Header from settings or receipt
  const storeName = appSettings?.store_name || receipt.store?.name || 'BAQALA POS'
  const storeAddress = appSettings?.store_address || receipt.store?.address
  const storePhone = appSettings?.store_phone || receipt.store?.phone
  const taxNumber = appSettings?.tax_number || receipt.store?.vat_number
  const receiptFooter = appSettings?.receipt_footer || 'Thank you for your purchase!'

  lines.push(centerText(storeName.toUpperCase(), w))
  if (storeAddress) {
    lines.push(centerText(storeAddress, w))
  }
  if (storePhone) {
    lines.push(centerText(`Tel: ${storePhone}`, w))
  }
  if (taxNumber) {
    lines.push(centerText(`VAT: ${taxNumber}`, w))
  }

  // Custom receipt header
  if (appSettings?.receipt_header) {
    lines.push('')
    appSettings.receipt_header.split('\n').forEach(line => {
      lines.push(centerText(line.trim(), w))
    })
  }

  lines.push('')
  lines.push(dashedLine(w))

  // Order Info
  const orderNum = order?.order_number || receipt.order?.order_number || ''
  lines.push(lineLeftRight('Order #:', orderNum, w))
  lines.push(lineLeftRight('Date:', formatReceiptDateTime(receipt.date), w))
  if (receipt.customer) {
    const customerName = typeof receipt.customer === 'string'
      ? receipt.customer
      : receipt.customer.full_name || receipt.customer.name
    if (customerName && customerName !== 'Walk-in') {
      lines.push(lineLeftRight('Customer:', customerName, w))
    }
  }
  lines.push(lineLeftRight('Payment:', (receipt.payment_type || 'Cash').toUpperCase(), w))
  if (receipt.reference) {
    lines.push(lineLeftRight('Ref:', receipt.reference, w))
  }
  lines.push(dashedLine(w))

  // Items Header
  lines.push(lineLeftRight('ITEM', 'QTY   PRICE    TOTAL', w))
  lines.push(dashedLine(w))

  // Items
  const items = receipt.items || []
  items.forEach(item => {
    const name = item.name.length > 24 ? item.name.substring(0, 24) + '...' : item.name
    lines.push(name)
    const qty = String(item.quantity).padStart(3)
    const price = formatAmount(item.price).padStart(8)
    const total = formatAmount(item.total || item.line_total || item.quantity * item.price).padStart(10)
    lines.push(lineLeftRight('', `${qty} x${price} =${total}`, w))
  })

  lines.push(dashedLine(w))

  // Totals
  lines.push(lineLeftRight('Subtotal:', fmtPrice(receipt.subtotal, currency), w))

  if (receipt.discount > 0) {
    lines.push(lineLeftRight('Discount:', `-${fmtPrice(receipt.discount, currency)}`, w))
  }

  if (receipt.tax > 0) {
    const taxRate = receipt.tax_rate || appSettings?.default_tax_rate || 15
    const taxName = appSettings?.tax_name || 'VAT'
    lines.push(lineLeftRight(`${taxName} (${taxRate}%):`, fmtPrice(receipt.tax, currency), w))
  }

  lines.push(dashedLine(w))
  lines.push(lineLeftRight('TOTAL:', fmtPrice(receipt.total, currency), w))
  lines.push(dashedLine(w))

  // Payment Details
  if (receipt.cash_received) {
    lines.push(lineLeftRight('Cash Received:', fmtPrice(receipt.cash_received, currency), w))
    lines.push(lineLeftRight('Change:', fmtPrice(receipt.change, currency), w))
    lines.push('')
  }

  // Footer
  lines.push('')
  if (receiptFooter) {
    receiptFooter.split('\n').forEach(line => {
      lines.push(centerText(line.trim(), w))
    })
  }
  lines.push('')

  // Barcode placeholder (order number)
  lines.push(centerText(`*${orderNum}*`, w))
  lines.push('')
  lines.push('')
  lines.push('') // Extra lines for paper cut

  return lines.join('\n')
}

// Generate HTML for thermal print
const generatePrintHTML = (receiptText, storeName) => {
  return `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Receipt - ${storeName}</title>
  <style>
    @page {
      size: 80mm auto;
      margin: 0;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Courier New', 'Lucida Console', Monaco, monospace;
      font-size: 12px;
      line-height: 1.4;
      width: 80mm;
      padding: 4mm;
      background: white;
      color: black;
    }
    pre {
      font-family: inherit;
      font-size: inherit;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
    @media print {
      body {
        width: 80mm;
        padding: 2mm;
      }
    }
  </style>
</head>
<body>
  <pre>${receiptText}</pre>
  <script>
    window.onload = function() {
      window.print();
      setTimeout(function() { window.close(); }, 500);
    }
  </script>
</body>
</html>`
}

export default function ReceiptModal({
  open,
  onClose,
  order,
  receipt,
}) {
  const { token } = theme.useToken()
  const { appSettings } = usePage().props

  if (!order || !receipt) return null

  const handlePrint = () => {
    const receiptText = generateThermalReceipt(order, receipt, appSettings)
    const storeName = appSettings?.store_name || receipt.store?.name || 'Baqala POS'
    const printHTML = generatePrintHTML(receiptText, storeName)

    // Open print window
    const printWindow = window.open('', '_blank', 'width=350,height=600')
    if (printWindow) {
      printWindow.document.write(printHTML)
      printWindow.document.close()
    }
  }

  // Preview receipt text for display
  const receiptPreview = generateThermalReceipt(order, receipt, appSettings)

  return (
    <CustomModal
      title={
        <Space>
          <CheckCircleOutlined style={{ color: token.colorSuccess, fontSize: 24 }} />
          <span>Order Completed!</span>
        </Space>
      }
      open={open}
      onCancel={onClose}
      width={420}
      footer={
        <Space style={{ width: '100%', justifyContent: 'flex-end' }}>
          <Button onClick={onClose}>
            Close
          </Button>
          <Button type="primary" icon={<PrinterOutlined />} onClick={handlePrint}>
            Print Receipt
          </Button>
        </Space>
      }
    >
      {/* Receipt Preview */}
      <div
        style={{
          background: '#fff',
          border: `1px solid ${token.colorBorder}`,
          borderRadius: 8,
          padding: 16,
          maxHeight: 400,
          overflow: 'auto',
        }}
      >
        <pre
          style={{
            fontFamily: "'Courier New', 'Lucida Console', Monaco, monospace",
            fontSize: 11,
            lineHeight: 1.4,
            margin: 0,
            whiteSpace: 'pre-wrap',
            color: '#000',
          }}
        >
          {receiptPreview}
        </pre>
      </div>

      {/* Print Info */}
      <div style={{ marginTop: 12, textAlign: 'center' }}>
        <Text type="secondary" style={{ fontSize: 12 }}>
          Optimized for 80mm thermal printers
        </Text>
      </div>
    </CustomModal>
  )
}
