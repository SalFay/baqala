import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Typography,
  Card,
  Row,
  Col,
  DatePicker,
  Select,
  Button,
  Table,
  Statistic,
  Tabs,
  Space,
  Spin,
  Progress,
  message,
} from 'antd';
import {
  DollarOutlined,
  ShoppingCartOutlined,
  RiseOutlined,
  DownloadOutlined,
  FileExcelOutlined,
  FilePdfOutlined,
} from '@ant-design/icons';
import { Column, Pie } from '@ant-design/charts';
import dayjs from 'dayjs';
import { reportService } from '../../api/services/report.service';
import { inventoryService } from '../../api/services/inventory.service';

const { Title, Text } = Typography;
const { RangePicker } = DatePicker;

export default function ReportsPage() {
  const [dateRange, setDateRange] = useState([
    dayjs().startOf('month').format('YYYY-MM-DD'),
    dayjs().format('YYYY-MM-DD'),
  ]);
  const [storeId, setStoreId] = useState(undefined);
  const [activeTab, setActiveTab] = useState('sales');
  const [exporting, setExporting] = useState(false);

  const filters = {
    from_date: dateRange[0],
    to_date: dateRange[1],
    store_id: storeId,
  };

  const { data: salesReport, isLoading: salesLoading } = useQuery({
    queryKey: ['sales-report', filters],
    queryFn: () => reportService.getSalesReport(filters),
    enabled: activeTab === 'sales',
  });

  const { data: productSales, isLoading: productLoading } = useQuery({
    queryKey: ['product-sales', filters],
    queryFn: () => reportService.getSalesByProduct(filters),
    enabled: activeTab === 'products',
  });

  const { data: categorySales, isLoading: categoryLoading } = useQuery({
    queryKey: ['category-sales', filters],
    queryFn: () => reportService.getSalesByCategory(filters),
    enabled: activeTab === 'categories',
  });

  const { data: inventoryReport, isLoading: inventoryLoading } = useQuery({
    queryKey: ['inventory-report', storeId],
    queryFn: () => reportService.getInventoryReport(storeId),
    enabled: activeTab === 'inventory',
  });

  const { data: profitLoss, isLoading: plLoading } = useQuery({
    queryKey: ['profit-loss', { from_date: dateRange[0], to_date: dateRange[1] }],
    queryFn: () => reportService.getProfitLossReport({ from_date: dateRange[0], to_date: dateRange[1] }),
    enabled: activeTab === 'profit-loss',
  });

  const { data: stores } = useQuery({
    queryKey: ['stores'],
    queryFn: inventoryService.getStores,
  });

  const handleExport = async (type, format) => {
    setExporting(true);
    try {
      const blob = await reportService.exportReport(type, format, filters);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${type}-report-${dayjs().format('YYYY-MM-DD')}.${format === 'excel' ? 'xlsx' : 'pdf'}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      message.success('Report exported successfully');
    } catch {
      message.error('Failed to export report');
    } finally {
      setExporting(false);
    }
  };

  const salesChartConfig = {
    data: salesReport?.daily_sales || [],
    xField: 'date',
    yField: 'sales',
    color: '#1890ff',
    label: {
      position: 'top',
      style: { fill: '#666' },
    },
    xAxis: {
      label: {
        formatter: (v) => dayjs(v).format('MMM D'),
      },
    },
    yAxis: {
      label: {
        formatter: (v) => `${v.toLocaleString()} SAR`,
      },
    },
  };

  const categoryPieConfig = {
    data: categorySales || [],
    angleField: 'total_sales',
    colorField: 'category_name',
    radius: 0.8,
    innerRadius: 0.6,
    label: {
      type: 'outer',
      content: '{name}: {percentage}',
    },
    interactions: [{ type: 'element-active' }],
    statistic: {
      title: false,
      content: {
        style: { fontSize: '16px' },
        content: 'Sales by Category',
      },
    },
  };

  const productColumns = [
    { title: 'Product', dataIndex: 'product_name', key: 'name' },
    { title: 'SKU', dataIndex: 'sku', key: 'sku' },
    { title: 'Qty Sold', dataIndex: 'quantity_sold', key: 'qty', sorter: (a, b) => a.quantity_sold - b.quantity_sold },
    { title: 'Total Sales', dataIndex: 'total_sales', key: 'sales', render: (v) => `${v.toFixed(2)} SAR`, sorter: (a, b) => a.total_sales - b.total_sales },
    { title: 'Profit', dataIndex: 'total_profit', key: 'profit', render: (v) => `${v.toFixed(2)} SAR`, sorter: (a, b) => a.total_profit - b.total_profit },
    { title: 'Margin', dataIndex: 'profit_margin', key: 'margin', render: (v) => <Progress percent={v} size="small" />, sorter: (a, b) => a.profit_margin - b.profit_margin },
  ];

  const categoryColumns = [
    { title: 'Category', dataIndex: 'category_name', key: 'name' },
    { title: 'Qty Sold', dataIndex: 'quantity_sold', key: 'qty' },
    { title: 'Total Sales', dataIndex: 'total_sales', key: 'sales', render: (v) => `${v.toFixed(2)} SAR` },
    { title: 'Percentage', dataIndex: 'percentage', key: 'percentage', render: (v) => <Progress percent={v} size="small" /> },
  ];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>Reports</Title>
        <Space>
          <RangePicker
            value={[dayjs(dateRange[0]), dayjs(dateRange[1])]}
            onChange={(dates) => {
              if (dates) {
                setDateRange([dates[0].format('YYYY-MM-DD'), dates[1].format('YYYY-MM-DD')]);
              }
            }}
          />
          <Select
            placeholder="All Stores"
            value={storeId}
            onChange={setStoreId}
            allowClear
            style={{ width: 150 }}
            options={stores?.map((s) => ({ label: s.name, value: s.id })) || []}
          />
        </Space>
      </div>

      <Tabs
        activeKey={activeTab}
        onChange={setActiveTab}
        items={[
          {
            key: 'sales',
            label: 'Sales Overview',
            children: salesLoading ? (
              <div style={{ textAlign: 'center', padding: 50 }}><Spin size="large" /></div>
            ) : (
              <>
                <Row gutter={16} style={{ marginBottom: 24 }}>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Total Orders"
                        value={salesReport?.summary.total_orders || 0}
                        prefix={<ShoppingCartOutlined />}
                      />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Gross Sales"
                        value={salesReport?.summary.total_sales || 0}
                        prefix={<DollarOutlined />}
                        suffix="SAR"
                        precision={2}
                      />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Net Sales"
                        value={salesReport?.summary.net_sales || 0}
                        prefix={<RiseOutlined />}
                        suffix="SAR"
                        precision={2}
                        valueStyle={{ color: '#3f8600' }}
                      />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Avg Order Value"
                        value={salesReport?.summary.average_order_value || 0}
                        suffix="SAR"
                        precision={2}
                      />
                    </Card>
                  </Col>
                </Row>

                <Card
                  title="Daily Sales"
                  extra={
                    <Space>
                      <Button
                        icon={<FileExcelOutlined />}
                        onClick={() => handleExport('sales', 'excel')}
                        loading={exporting}
                      >
                        Excel
                      </Button>
                      <Button
                        icon={<FilePdfOutlined />}
                        onClick={() => handleExport('sales', 'pdf')}
                        loading={exporting}
                      >
                        PDF
                      </Button>
                    </Space>
                  }
                  style={{ marginBottom: 24 }}
                >
                  <Column {...salesChartConfig} height={300} />
                </Card>

                <Card title="Payment Breakdown">
                  <Table
                    dataSource={salesReport?.payment_breakdown || []}
                    columns={[
                      { title: 'Method', dataIndex: 'method', key: 'method', render: (v) => v?.toUpperCase() },
                      { title: 'Transactions', dataIndex: 'count', key: 'count' },
                      { title: 'Total', dataIndex: 'total', key: 'total', render: (v) => `${v.toFixed(2)} SAR` },
                    ]}
                    rowKey="method"
                    pagination={false}
                    size="small"
                  />
                </Card>
              </>
            ),
          },
          {
            key: 'products',
            label: 'Product Sales',
            children: productLoading ? (
              <div style={{ textAlign: 'center', padding: 50 }}><Spin size="large" /></div>
            ) : (
              <Card
                extra={
                  <Button
                    icon={<DownloadOutlined />}
                    onClick={() => handleExport('products', 'excel')}
                    loading={exporting}
                  >
                    Export
                  </Button>
                }
              >
                <Table
                  dataSource={productSales}
                  columns={productColumns}
                  rowKey="product_id"
                  pagination={{ pageSize: 20 }}
                />
              </Card>
            ),
          },
          {
            key: 'categories',
            label: 'Category Sales',
            children: categoryLoading ? (
              <div style={{ textAlign: 'center', padding: 50 }}><Spin size="large" /></div>
            ) : (
              <Row gutter={24}>
                <Col span={12}>
                  <Card title="Sales Distribution">
                    <Pie {...categoryPieConfig} height={300} />
                  </Card>
                </Col>
                <Col span={12}>
                  <Card title="Category Breakdown">
                    <Table
                      dataSource={categorySales}
                      columns={categoryColumns}
                      rowKey="category_id"
                      pagination={false}
                      size="small"
                    />
                  </Card>
                </Col>
              </Row>
            ),
          },
          {
            key: 'inventory',
            label: 'Inventory Report',
            children: inventoryLoading ? (
              <div style={{ textAlign: 'center', padding: 50 }}><Spin size="large" /></div>
            ) : (
              <>
                <Row gutter={16} style={{ marginBottom: 24 }}>
                  <Col span={6}>
                    <Card>
                      <Statistic title="Total Products" value={inventoryReport?.total_products || 0} />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Inventory Value"
                        value={inventoryReport?.total_value || 0}
                        suffix="SAR"
                        precision={2}
                      />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Low Stock"
                        value={inventoryReport?.low_stock_count || 0}
                        valueStyle={{ color: '#faad14' }}
                      />
                    </Card>
                  </Col>
                  <Col span={6}>
                    <Card>
                      <Statistic
                        title="Out of Stock"
                        value={inventoryReport?.out_of_stock_count || 0}
                        valueStyle={{ color: '#ff4d4f' }}
                      />
                    </Card>
                  </Col>
                </Row>

                <Card title="Inventory by Category">
                  <Table
                    dataSource={inventoryReport?.by_category || []}
                    columns={[
                      { title: 'Category', dataIndex: 'category_name', key: 'name' },
                      { title: 'Products', dataIndex: 'product_count', key: 'products' },
                      { title: 'Total Qty', dataIndex: 'total_quantity', key: 'qty' },
                      { title: 'Value', dataIndex: 'total_value', key: 'value', render: (v) => `${v.toFixed(2)} SAR` },
                    ]}
                    rowKey="category_name"
                    pagination={false}
                  />
                </Card>
              </>
            ),
          },
          {
            key: 'profit-loss',
            label: 'Profit & Loss',
            children: plLoading ? (
              <div style={{ textAlign: 'center', padding: 50 }}><Spin size="large" /></div>
            ) : (
              <Row gutter={24}>
                <Col span={12}>
                  <Card title="Revenue" style={{ marginBottom: 16 }}>
                    <Table
                      dataSource={[
                        { label: 'Gross Sales', value: profitLoss?.revenue.gross_sales || 0 },
                        { label: 'Less: Discounts', value: -(profitLoss?.revenue.discounts || 0) },
                        { label: 'Less: Returns', value: -(profitLoss?.revenue.returns || 0) },
                        { label: 'Net Sales', value: profitLoss?.revenue.net_sales || 0, bold: true },
                      ]}
                      columns={[
                        { title: '', dataIndex: 'label', key: 'label', render: (v, r) => r.bold ? <Text strong>{v}</Text> : v },
                        { title: '', dataIndex: 'value', key: 'value', align: 'right', render: (v, r) => <Text strong={r.bold} type={v < 0 ? 'danger' : undefined}>{v.toFixed(2)} SAR</Text> },
                      ]}
                      rowKey="label"
                      pagination={false}
                      showHeader={false}
                      size="small"
                    />
                  </Card>

                  <Card title="Cost of Goods Sold" style={{ marginBottom: 16 }}>
                    <Statistic value={profitLoss?.cost_of_goods || 0} suffix="SAR" precision={2} />
                  </Card>

                  <Card title="Gross Profit" style={{ marginBottom: 16 }}>
                    <Row gutter={16}>
                      <Col span={12}>
                        <Statistic
                          value={profitLoss?.gross_profit || 0}
                          suffix="SAR"
                          precision={2}
                          valueStyle={{ color: (profitLoss?.gross_profit || 0) >= 0 ? '#3f8600' : '#cf1322' }}
                        />
                      </Col>
                      <Col span={12}>
                        <Statistic
                          title="Margin"
                          value={profitLoss?.gross_margin || 0}
                          suffix="%"
                          precision={1}
                        />
                      </Col>
                    </Row>
                  </Card>
                </Col>

                <Col span={12}>
                  <Card title="Expenses" style={{ marginBottom: 16 }}>
                    <Table
                      dataSource={profitLoss?.expenses || []}
                      columns={[
                        { title: 'Category', dataIndex: 'category', key: 'category' },
                        { title: 'Amount', dataIndex: 'amount', key: 'amount', align: 'right', render: (v) => `${v.toFixed(2)} SAR` },
                      ]}
                      rowKey="category"
                      pagination={false}
                      size="small"
                      summary={() => (
                        <Table.Summary.Row>
                          <Table.Summary.Cell index={0}><Text strong>Total Expenses</Text></Table.Summary.Cell>
                          <Table.Summary.Cell index={1} align="right">
                            <Text strong>{(profitLoss?.total_expenses || 0).toFixed(2)} SAR</Text>
                          </Table.Summary.Cell>
                        </Table.Summary.Row>
                      )}
                    />
                  </Card>

                  <Card
                    title="Net Profit"
                    style={{
                      background: (profitLoss?.net_profit || 0) >= 0 ? '#f6ffed' : '#fff1f0',
                    }}
                  >
                    <Row gutter={16}>
                      <Col span={12}>
                        <Statistic
                          value={profitLoss?.net_profit || 0}
                          suffix="SAR"
                          precision={2}
                          valueStyle={{
                            color: (profitLoss?.net_profit || 0) >= 0 ? '#3f8600' : '#cf1322',
                            fontSize: 28,
                          }}
                        />
                      </Col>
                      <Col span={12}>
                        <Statistic
                          title="Net Margin"
                          value={profitLoss?.net_margin || 0}
                          suffix="%"
                          precision={1}
                        />
                      </Col>
                    </Row>
                  </Card>
                </Col>
              </Row>
            ),
          },
        ]}
      />
    </div>
  );
}
