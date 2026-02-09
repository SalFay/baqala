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
} from 'antd';
import { PlusOutlined, SearchOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import { customerService } from '../../api/services/customer.service';

const { Title } = Typography;

export default function CustomersPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  const { data: customersData, isLoading } = useQuery({
    queryKey: ['customers', { search, page }],
    queryFn: () =>
      customerService.getCustomers({
        search: search || undefined,
        page,
        per_page: 20,
      }),
  });

  const deleteMutation = useMutation({
    mutationFn: customerService.deleteCustomer,
    onSuccess: () => {
      message.success('Customer deleted');
      queryClient.invalidateQueries({ queryKey: ['customers'] });
    },
    onError: () => {
      message.error('Failed to delete customer');
    },
  });

  const columns = [
    {
      title: 'Name',
      dataIndex: 'full_name',
      key: 'name',
    },
    {
      title: 'Phone',
      dataIndex: 'phone_mobile',
      key: 'phone',
    },
    {
      title: 'Email',
      dataIndex: 'email',
      key: 'email',
    },
    {
      title: 'Loyalty Points',
      dataIndex: 'loyalty_points',
      key: 'points',
      render: (points) => (
        <Tag color={points > 0 ? 'gold' : 'default'}>{points || 0}</Tag>
      ),
    },
    {
      title: 'Credit Balance',
      dataIndex: 'credit_balance',
      key: 'credit',
      render: (val) => `${(val || 0).toFixed(2)} SAR`,
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 120,
      render: (_, record) => (
        <Space>
          <Button
            type="text"
            icon={<EditOutlined />}
            onClick={() => navigate(`/customers/${record.id}/edit`)}
          />
          <Popconfirm
            title="Delete this customer?"
            onConfirm={() => deleteMutation.mutate(record.id)}
            okText="Yes"
            cancelText="No"
          >
            <Button type="text" danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>
          Customers
        </Title>
        <Button
          type="primary"
          icon={<PlusOutlined />}
          onClick={() => navigate('/customers/new')}
        >
          Add Customer
        </Button>
      </div>

      <Input
        placeholder="Search customers..."
        prefix={<SearchOutlined />}
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        style={{ width: 300, marginBottom: 16 }}
        allowClear
      />

      <Table
        dataSource={customersData?.data}
        columns={columns}
        rowKey="id"
        loading={isLoading}
        pagination={{
          current: customersData?.current_page,
          total: customersData?.total,
          pageSize: customersData?.per_page,
          onChange: setPage,
          showSizeChanger: false,
        }}
      />
    </div>
  );
}
