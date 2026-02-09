import { useState } from 'react';
import { Modal, Input, List, Button, Empty, Typography, Tag, Space, message } from 'antd';
import { SearchOutlined, UserOutlined, PlusOutlined, CloseOutlined } from '@ant-design/icons';
import { useQuery } from '@tanstack/react-query';
import { customerService } from '../../../api/services/customer.service';
import { useCartStore } from '../../../store/cartStore';
import type { Customer } from '../../../types';

const { Text } = Typography;

interface CustomerSearchModalProps {
  open: boolean;
  onClose: () => void;
}

export default function CustomerSearchModal({ open, onClose }: CustomerSearchModalProps) {
  const [searchTerm, setSearchTerm] = useState('');
  const { cart, setCustomer } = useCartStore();

  const { data: customers = [], isLoading } = useQuery({
    queryKey: ['customers-search', searchTerm],
    queryFn: () => customerService.searchCustomers(searchTerm),
    enabled: searchTerm.length >= 2,
  });

  const handleSelectCustomer = async (customer: Customer) => {
    await setCustomer(customer);
    message.success(`Customer ${customer.full_name} selected`);
    onClose();
  };

  const handleRemoveCustomer = async () => {
    await setCustomer(null);
    message.success('Customer removed');
  };

  return (
    <Modal
      title="Select Customer"
      open={open}
      onCancel={onClose}
      footer={null}
      width={600}
    >
      {cart?.customer && (
        <div
          style={{
            padding: 12,
            background: '#f0f5ff',
            borderRadius: 8,
            marginBottom: 16,
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
          }}
        >
          <Space>
            <UserOutlined />
            <div>
              <Text strong>{cart.customer.full_name}</Text>
              {cart.customer.phone_mobile && (
                <Text type="secondary" style={{ display: 'block', fontSize: 12 }}>
                  {cart.customer.phone_mobile}
                </Text>
              )}
            </div>
            {cart.customer.loyalty_points > 0 && (
              <Tag color="gold">{cart.customer.loyalty_points} pts</Tag>
            )}
          </Space>
          <Button
            type="text"
            danger
            icon={<CloseOutlined />}
            onClick={handleRemoveCustomer}
          >
            Remove
          </Button>
        </div>
      )}

      <Input
        placeholder="Search by name, phone, or email..."
        prefix={<SearchOutlined />}
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        style={{ marginBottom: 16 }}
        allowClear
        autoFocus
      />

      {searchTerm.length >= 2 ? (
        <List
          dataSource={customers}
          loading={isLoading}
          locale={{ emptyText: <Empty description="No customers found" /> }}
          renderItem={(customer: Customer) => (
            <List.Item
              style={{ cursor: 'pointer' }}
              onClick={() => handleSelectCustomer(customer)}
            >
              <List.Item.Meta
                avatar={<UserOutlined style={{ fontSize: 24 }} />}
                title={customer.full_name}
                description={
                  <Space>
                    {customer.phone_mobile && (
                      <Text type="secondary">{customer.phone_mobile}</Text>
                    )}
                    {customer.email && (
                      <Text type="secondary">{customer.email}</Text>
                    )}
                  </Space>
                }
              />
              {customer.loyalty_points > 0 && (
                <Tag color="gold">{customer.loyalty_points} pts</Tag>
              )}
            </List.Item>
          )}
        />
      ) : (
        <Empty
          description="Enter at least 2 characters to search"
          image={Empty.PRESENTED_IMAGE_SIMPLE}
        />
      )}

      <Button
        type="dashed"
        icon={<PlusOutlined />}
        block
        style={{ marginTop: 16 }}
        onClick={() => {
          // Navigate to create customer page
          window.open('/customers/new', '_blank');
        }}
      >
        Add New Customer
      </Button>
    </Modal>
  );
}
