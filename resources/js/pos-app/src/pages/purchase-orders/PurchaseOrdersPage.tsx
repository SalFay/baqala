import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Button,
  Input,
  Space,
  Tag,
  Typography,
  message,
  Popconfirm,
  Select,
  DatePicker,
  Card,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EyeOutlined,
  DeleteOutlined,
  CheckOutlined,
  CloseOutlined,
} from '@ant-design/icons';
import { purchaseOrderService, PurchaseOrder, PurchaseOrderStatus } from '../../api/services/purchaseOrder.service';
import dayjs from 'dayjs';

const { Title } = Typography;
const { RangePicker } = DatePicker;

const statusColors: Record<PurchaseOrderStatus, string> = {
  draft: 'default',
  pending_approval: 'gold',
  approved: 'blue',
  ordered: 'cyan',
  partial: 'orange',
  received: 'green',
  cancelled: 'red',
};

const statusLabels: Record<PurchaseOrderStatus, string> = {
  draft: 'Draft',
  pending_approval: 'Pending Approval',
  approved: 'Approved',
  ordered: 'Ordered',
  partial: 'Partially Received',
  received: 'Received',
  cancelled: 'Cancelled',
};

export default function PurchaseOrdersPage(): React.JSX.Element {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<string | undefined>();
  const [dateRange, setDateRange] = useState<[dayjs.Dayjs, dayjs.Dayjs] | null>(null);
  const [page, setPage] = useState(1);

  const { data: ordersData, isLoading } = useQuery({
    queryKey: ['purchase-orders', { search, status, dateRange, page }],
    queryFn: () =>
      purchaseOrderService.getPurchaseOrders({
        search: search || undefined,
        status: status as PurchaseOrderStatus || undefined,
        from_date: dateRange?.[0]?.format('YYYY-MM-DD'),
        to_date: dateRange?.[1]?.format('YYYY-MM-DD'),
        page,
        per_page: 20,
      }),
  });

  const deleteMutation = useMutation({
    mutationFn: purchaseOrderService.deletePurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order deleted');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to delete');
    },
  });

  const approveMutation = useMutation({
    mutationFn: purchaseOrderService.approvePurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order approved');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to approve');
    },
  });

  const cancelMutation = useMutation({
    mutationFn: purchaseOrderService.cancelPurchaseOrder,
    onSuccess: () => {
      message.success('Purchase order cancelled');
      queryClient.invalidateQueries({ queryKey: ['purchase-orders'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to cancel');
    },
  });

  const columns = [
    {
      title: 'PO Number',
      dataIndex: 'po_number',
      key: 'po_number',
      render: (text: string, record: PurchaseOrder) => (
        <a onClick={() => navigate(`/purchase-orders/${record.id}`)}>{text}</a>
      ),
    },
    {
      title: 'Vendor',
      key: 'vendor',
      render: (_: any, record: PurchaseOrder) => record.vendor?.name,
    },
    {
      title: 'Store',
      key: 'store',
      render: (_: any, record: PurchaseOrder) => record.store?.name,
    },
    {
      title: 'Order Date',
      dataIndex: 'order_date',
      key: 'order_date',
      render: (date: string) => dayjs(date).format('YYYY-MM-DD'),
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      render: (val: number) => `${val?.toFixed(2)} SAR`,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status: PurchaseOrderStatus) => (
        <Tag color={statusColors[status]}>{statusLabels[status]}</Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 160,
      render: (_: any, record: PurchaseOrder) => (
        <Space>
          <Button
            type="text"
            icon={<EyeOutlined />}
            onClick={() => navigate(`/purchase-orders/${record.id}`)}
          />
          {record.status === 'pending_approval' && (
            <Popconfirm
              title="Approve this order?"
              onConfirm={() => approveMutation.mutate(record.id)}
            >
              <Button type="text" icon={<CheckOutlined />} style={{ color: 'green' }} />
            </Popconfirm>
          )}
          {record.status === 'draft' && (
            <Popconfirm
              title="Delete this order?"
              onConfirm={() => deleteMutation.mutate(record.id)}
            >
              <Button type="text" danger icon={<DeleteOutlined />} />
            </Popconfirm>
          )}
          {!['received', 'cancelled'].includes(record.status) && (
            <Popconfirm
              title="Cancel this order?"
              onConfirm={() => cancelMutation.mutate(record.id)}
            >
              <Button type="text" danger icon={<CloseOutlined />} />
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>
          Purchase Orders
        </Title>
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={() => navigate('/purchase-orders/new')}
        >
          New Purchase Order
        </Button>
      </div>

      <Card>
        <Space style={{ marginBottom: 16 }} wrap>
          <Input
            placeholder="Search by PO number..."
            prefix={<SearchOutlined />}
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            style={{ width: 220 }}
            allowClear
          />
          <Select
            placeholder="Status"
            value={status}
            onChange={(val) => {
              setStatus(val);
              setPage(1);
            }}
            style={{ width: 180 }}
            allowClear
          >
            {Object.entries(statusLabels).map(([key, label]) => (
              <Select.Option key={key} value={key}>
                {label}
              </Select.Option>
            ))}
          </Select>
          <RangePicker
            value={dateRange}
            onChange={(dates) => {
              setDateRange(dates as [dayjs.Dayjs, dayjs.Dayjs] | null);
              setPage(1);
            }}
          />
        </Space>

        <Table
          dataSource={ordersData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: ordersData?.current_page || page,
            total: ordersData?.total,
            pageSize: ordersData?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} purchase orders`,
          }}
        />
      </Card>
    </div>
  );
}
