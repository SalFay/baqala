import { useState } from 'react';
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
  Card,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EyeOutlined,
  DeleteOutlined,
  CloseOutlined,
} from '@ant-design/icons';
import { stockTransferService, StockTransfer, StockTransferStatus } from '../../api/services/stockTransfer.service';
import { storeService } from '../../api/services/store.service';
import dayjs from 'dayjs';

const { Title } = Typography;

const statusColors: Record<StockTransferStatus, string> = {
  draft: 'default',
  pending: 'gold',
  in_transit: 'blue',
  received: 'green',
  cancelled: 'red',
};

const statusLabels: Record<StockTransferStatus, string> = {
  draft: 'Draft',
  pending: 'Pending Approval',
  in_transit: 'In Transit',
  received: 'Received',
  cancelled: 'Cancelled',
};

export default function StockTransfersPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<string | undefined>();
  const [page, setPage] = useState(1);

  const { data: transfersData, isLoading } = useQuery({
    queryKey: ['stock-transfers', { search, status, page }],
    queryFn: () =>
      stockTransferService.getStockTransfers({
        search: search || undefined,
        status: status as StockTransferStatus || undefined,
        page,
        per_page: 20,
      }),
  });

  useQuery({
    queryKey: ['stores'],
    queryFn: () => storeService.getStores(),
  });

  const deleteMutation = useMutation({
    mutationFn: stockTransferService.deleteStockTransfer,
    onSuccess: () => {
      message.success('Transfer deleted');
      queryClient.invalidateQueries({ queryKey: ['stock-transfers'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to delete');
    },
  });

  const cancelMutation = useMutation({
    mutationFn: stockTransferService.cancelStockTransfer,
    onSuccess: () => {
      message.success('Transfer cancelled');
      queryClient.invalidateQueries({ queryKey: ['stock-transfers'] });
    },
    onError: (error: any) => {
      message.error(error.response?.data?.message || 'Failed to cancel');
    },
  });

  const columns = [
    {
      title: 'Transfer #',
      dataIndex: 'transfer_number',
      key: 'transfer_number',
      render: (text: string, record: StockTransfer) => (
        <a onClick={() => navigate(`/stock-transfers/${record.id}`)}>{text}</a>
      ),
    },
    {
      title: 'From Store',
      key: 'from_store',
      render: (_: any, record: StockTransfer) => record.from_store?.name,
    },
    {
      title: 'To Store',
      key: 'to_store',
      render: (_: any, record: StockTransfer) => record.to_store?.name,
    },
    {
      title: 'Created',
      dataIndex: 'created_at',
      key: 'created_at',
      render: (date: string) => dayjs(date).format('YYYY-MM-DD HH:mm'),
    },
    {
      title: 'Shipped',
      dataIndex: 'shipped_at',
      key: 'shipped_at',
      render: (date: string | null) => date ? dayjs(date).format('YYYY-MM-DD HH:mm') : '-',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status: StockTransferStatus) => (
        <Tag color={statusColors[status]}>{statusLabels[status]}</Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 160,
      render: (_: any, record: StockTransfer) => (
        <Space>
          <Button
            type="text"
            icon={<EyeOutlined />}
            onClick={() => navigate(`/stock-transfers/${record.id}`)}
          />
          {record.status === 'draft' && (
            <Popconfirm
              title="Delete this transfer?"
              onConfirm={() => deleteMutation.mutate(record.id)}
            >
              <Button type="text" danger icon={<DeleteOutlined />} />
            </Popconfirm>
          )}
          {!['received', 'cancelled'].includes(record.status) && (
            <Popconfirm
              title="Cancel this transfer?"
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
          Stock Transfers
        </Title>
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={() => navigate('/stock-transfers/new')}
        >
          New Transfer
        </Button>
      </div>

      <Card>
        <Space style={{ marginBottom: 16 }} wrap>
          <Input
            placeholder="Search by transfer number..."
            prefix={<SearchOutlined />}
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            style={{ width: 240 }}
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
        </Space>

        <Table
          dataSource={transfersData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: transfersData?.current_page || page,
            total: transfersData?.total,
            pageSize: transfersData?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} transfers`,
          }}
        />
      </Card>
    </div>
  );
}
