import { Head, usePage, router } from '@inertiajs/react'
import { Card, Button, Space, Table, Typography, Divider, Row, Col, Tag, Descriptions } from 'antd'
import { PrinterOutlined, ArrowLeftOutlined, DownloadOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'

const { Title, Text, Paragraph } = Typography

const statusColors = {
  draft: 'default',
  sent: 'processing',
  accepted: 'success',
  rejected: 'error',
  expired: 'warning',
  converted: 'purple',
}

export default function QuotationPrint() {
  const { quotation } = usePage().props

  const handlePrint = () => {
    window.print()
  }

  const handleBack = () => {
    router.visit(route('pos.quotations.index'))
  }

  const itemColumns = [
    {
      title: '#',
      key: 'index',
      width: 50,
      render: (_, __, index) => index + 1,
    },
    {
      title: 'Description',
      dataIndex: 'product_name',
      key: 'product_name',
      render: (text, record) => (
        <div>
          <Text strong>{text}</Text>
          {record.product_sku && (
            <div>
              <Text type="secondary" style={{ fontSize: 12 }}>
                SKU: {record.product_sku}
              </Text>
            </div>
          )}
          {record.notes && (
            <div>
              <Text type="secondary" style={{ fontSize: 12, fontStyle: 'italic' }}>
                {record.notes}
              </Text>
            </div>
          )}
        </div>
      ),
    },
    {
      title: 'Qty',
      dataIndex: 'quantity',
      key: 'quantity',
      width: 80,
      align: 'center',
    },
    {
      title: 'Unit Price',
      dataIndex: 'unit_price',
      key: 'unit_price',
      width: 120,
      align: 'right',
      render: (value) => `$${parseFloat(value).toFixed(2)}`,
    },
    {
      title: 'Discount',
      dataIndex: 'discount',
      key: 'discount',
      width: 100,
      align: 'right',
      render: (value) => value > 0 ? `-$${parseFloat(value).toFixed(2)}` : '-',
    },
    {
      title: 'Tax',
      dataIndex: 'tax_amount',
      key: 'tax_amount',
      width: 100,
      align: 'right',
      render: (value) => `$${parseFloat(value || 0).toFixed(2)}`,
    },
    {
      title: 'Total',
      dataIndex: 'line_total',
      key: 'line_total',
      width: 120,
      align: 'right',
      render: (value) => <Text strong>${parseFloat(value).toFixed(2)}</Text>,
    },
  ]

  return (
    <>
      <Head title={`Quotation ${quotation?.quotation_number}`} />

      {/* Print-only styles */}
      <style>{`
        @media print {
          .no-print { display: none !important; }
          .print-container { padding: 0 !important; margin: 0 !important; }
          .ant-card { box-shadow: none !important; border: none !important; }
        }
      `}</style>

      {/* Action Bar - Hidden when printing */}
      <div className="no-print" style={{ marginBottom: 16 }}>
        <Space>
          <Button icon={<ArrowLeftOutlined />} onClick={handleBack}>
            Back
          </Button>
          <Button type="primary" icon={<PrinterOutlined />} onClick={handlePrint}>
            Print
          </Button>
        </Space>
      </div>

      <div className="print-container">
        <Card>
          {/* Header */}
          <Row justify="space-between" align="top" style={{ marginBottom: 24 }}>
            <Col>
              <Title level={2} style={{ margin: 0, color: '#1890ff' }}>
                QUOTATION
              </Title>
              <Text type="secondary" style={{ fontSize: 18 }}>
                #{quotation?.quotation_number}
              </Text>
            </Col>
            <Col style={{ textAlign: 'right' }}>
              <Title level={4} style={{ margin: 0 }}>
                {quotation?.store?.name || 'Company Name'}
              </Title>
              <Text type="secondary">
                {quotation?.store?.address || 'Company Address'}
              </Text>
              <br />
              <Text type="secondary">
                {quotation?.store?.phone || 'Phone'}
              </Text>
            </Col>
          </Row>

          <Divider />

          {/* Customer & Quotation Info */}
          <Row gutter={32} style={{ marginBottom: 24 }}>
            <Col span={12}>
              <Title level={5} style={{ marginBottom: 8 }}>Bill To:</Title>
              <Text strong style={{ fontSize: 16 }}>
                {quotation?.customer?.name || quotation?.customer_name}
              </Text>
              <br />
              {(quotation?.customer_email || quotation?.customer?.email) && (
                <>
                  <Text>{quotation?.customer_email || quotation?.customer?.email}</Text>
                  <br />
                </>
              )}
              {(quotation?.customer_phone || quotation?.customer?.phone) && (
                <Text>{quotation?.customer_phone || quotation?.customer?.phone}</Text>
              )}
            </Col>
            <Col span={12}>
              <Descriptions column={1} size="small">
                <Descriptions.Item label="Date">
                  {dayjs(quotation?.created_at).format('DD MMM YYYY')}
                </Descriptions.Item>
                <Descriptions.Item label="Valid Until">
                  {quotation?.valid_until
                    ? dayjs(quotation.valid_until).format('DD MMM YYYY')
                    : 'N/A'}
                </Descriptions.Item>
                <Descriptions.Item label="Status">
                  <Tag color={statusColors[quotation?.status]}>
                    {quotation?.status?.toUpperCase()}
                  </Tag>
                </Descriptions.Item>
                {quotation?.location?.name && (
                  <Descriptions.Item label="Location">
                    {quotation.location.name}
                  </Descriptions.Item>
                )}
              </Descriptions>
            </Col>
          </Row>

          {/* Items Table */}
          <Table
            dataSource={quotation?.items || []}
            columns={itemColumns}
            pagination={false}
            rowKey="id"
            bordered
            size="small"
          />

          {/* Totals */}
          <Row justify="end" style={{ marginTop: 24 }}>
            <Col span={8}>
              <div style={{ borderTop: '1px solid #f0f0f0', padding: '12px 0' }}>
                <Row justify="space-between" style={{ marginBottom: 8 }}>
                  <Text>Subtotal:</Text>
                  <Text>${parseFloat(quotation?.subtotal || 0).toFixed(2)}</Text>
                </Row>
                <Row justify="space-between" style={{ marginBottom: 8 }}>
                  <Text>Tax:</Text>
                  <Text>${parseFloat(quotation?.tax_amount || 0).toFixed(2)}</Text>
                </Row>
                {parseFloat(quotation?.discount_amount || 0) > 0 && (
                  <Row justify="space-between" style={{ marginBottom: 8 }}>
                    <Text>Discount:</Text>
                    <Text type="danger">
                      -${parseFloat(quotation?.discount_amount || 0).toFixed(2)}
                    </Text>
                  </Row>
                )}
                <Divider style={{ margin: '8px 0' }} />
                <Row justify="space-between">
                  <Title level={4} style={{ margin: 0 }}>Total:</Title>
                  <Title level={4} style={{ margin: 0 }}>
                    ${parseFloat(quotation?.total || 0).toFixed(2)}
                  </Title>
                </Row>
              </div>
            </Col>
          </Row>

          {/* Notes & Terms */}
          {quotation?.notes && (
            <div style={{ marginTop: 32 }}>
              <Title level={5}>Notes:</Title>
              <Paragraph>{quotation.notes}</Paragraph>
            </div>
          )}

          {quotation?.terms_and_conditions && (
            <div style={{ marginTop: 24 }}>
              <Title level={5}>Terms & Conditions:</Title>
              <Paragraph type="secondary" style={{ whiteSpace: 'pre-wrap' }}>
                {quotation.terms_and_conditions}
              </Paragraph>
            </div>
          )}

          {/* Footer */}
          <Divider />
          <div style={{ textAlign: 'center' }}>
            <Text type="secondary">
              Thank you for your business!
            </Text>
            <br />
            <Text type="secondary" style={{ fontSize: 12 }}>
              This quotation is valid until {quotation?.valid_until
                ? dayjs(quotation.valid_until).format('DD MMM YYYY')
                : '30 days from issue date'}
            </Text>
          </div>
        </Card>
      </div>
    </>
  )
}
