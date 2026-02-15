import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import {
  Table,
  Input,
  Select,
  DatePicker,
  Space,
  Tag,
  Typography,
  Button,
} from 'antd';
import { SearchOutlined, EyeOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { orderService } from '../../api/services/order.service';

const { Title } = Typography;
const { RangePicker } = DatePicker;

export default function OrdersPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState(undefined);
  const [dateRange, setDateRange] = useState(undefined);
  const [page, setPage] = useState(1);

  const { data: ordersData, isLoading } = useQuery({
    queryKey: ['orders', { search, status, dateRange, page }],
    queryFn: () =>
      orderService.getOrders({
        search: search || undefined,
        status,
        from_date: dateRange?.[0],
        to_date: dateRange?.[1],
        page,
        per_page: 20,
      }),
  });

  const statusColors = {
    pending: 'orange',
    processing: 'blue',
    completed: 'green',
    cancelled: 'red',
    refunded: 'purple',
  };

  const paymentStatusColors = {
    pending: 'orange',
    paid: 'green',
    partially_paid: 'blue',
    failed: 'red',
    refunded: 'purple',
  };

  const columns = [
    {
      title: 'Order #',
      dataIndex: 'order_number',
      key: 'order_number',
    },
    {
      title: 'Customer',
      dataIndex: ['customer', 'full_name'],
      key: 'customer',
      render: (text) => text || 'Walk-in',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (s) => (
        <Tag color={statusColors[s] || 'default'}>{s?.toUpperCase()}</Tag>
      ),
    },
    {
      title: 'Payment',
      dataIndex: 'payment_status',
      key: 'payment_status',
      render: (s) => (
        <Tag color={paymentStatusColors[s] || 'default'}>{s?.toUpperCase()}</Tag>
      ),
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      render: (val) => `${val.toFixed(2)} SAR`,
    },
    {
      title: 'Date',
      dataIndex: 'created_at',
      key: 'date',
      render: (date) => dayjs(date).format('MMM D, YYYY h:mm A'),
    },
    {
      title: 'Actions',
      key: 'actions',
      render: (_, record) => (
        <Button
          type="text"
          icon={<EyeOutlined />}
          onClick={() => navigate(`/orders/${record.id}`)}
        />
      ),
    },
  ];

  return (
    <div>
      <Title level={4} style={{ marginBottom: 24 }}>
        Orders
      </Title>

      <Space style={{ marginBottom: 16 }} wrap>
        <Input
          placeholder="Search orders..."
          prefix={<SearchOutlined />}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          style={{ width: 200 }}
          allowClear
        />
        <Select
          placeholder="Status"
          value={status}
          onChange={setStatus}
          allowClear
          style={{ width: 150 }}
          options={[
            { label: 'Pending', value: 'pending' },
            { label: 'Completed', value: 'completed' },
            { label: 'Cancelled', value: 'cancelled' },
            { label: 'Refunded', value: 'refunded' },
          ]}
        />
        <RangePicker
          onChange={(dates) => {
            if (dates) {
              setDateRange([
                dates[0].format('YYYY-MM-DD'),
                dates[1].format('YYYY-MM-DD'),
              ]);
            } else {
              setDateRange(undefined);
            }
          }}
        />
      </Space>

      <Table
        dataSource={ordersData?.data}
        columns={columns}
        rowKey="id"
        loading={isLoading}
        pagination={{
          current: ordersData?.current_page,
          total: ordersData?.total,
          pageSize: ordersData?.per_page,
          onChange: setPage,
          showSizeChanger: false,
        }}
      />
    </div>
  );
}
