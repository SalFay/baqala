import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Card,
  Tabs,
  Select,
  Typography,
  Space,
  Input,
} from 'antd';
import { UserOutlined, ShopOutlined, SearchOutlined } from '@ant-design/icons';
import { customerService } from '../../api/services/customer.service';
import { vendorService } from '../../api/services/vendor.service';
import StatementView from './components/StatementView';

const { Title } = Typography;

export default function StatementsPage() {
  const [activeTab, setActiveTab] = useState('customers');
  const [selectedCustomerId, setSelectedCustomerId] = useState(null);
  const [selectedVendorId, setSelectedVendorId] = useState(null);
  const [customerSearch, setCustomerSearch] = useState('');
  const [vendorSearch, setVendorSearch] = useState('');

  // Fetch customers
  const { data: customersData } = useQuery({
    queryKey: ['customers-list', customerSearch],
    queryFn: () => customerService.getCustomers({ search: customerSearch, per_page: 50 }),
  });

  // Fetch vendors
  const { data: vendorsData } = useQuery({
    queryKey: ['vendors-list', vendorSearch],
    queryFn: () => vendorService.getVendors({ search: vendorSearch, per_page: 50 }),
  });

  const customers = customersData?.data || [];
  const vendors = vendorsData?.data || [];

  const selectedCustomer = customers.find((c) => c.id === selectedCustomerId);
  const selectedVendor = vendors.find((v) => v.id === selectedVendorId);

  const tabItems = [
    {
      key: 'customers',
      label: (
        <span>
          <UserOutlined /> Customer Statements
        </span>
      ),
      children: (
        <div>
          <Space direction="vertical" style={{ width: '100%', marginBottom: 16 }}>
            <Select
              showSearch
              allowClear
              placeholder="Select a customer"
              style={{ width: 400 }}
              value={selectedCustomerId}
              onChange={setSelectedCustomerId}
              onSearch={setCustomerSearch}
              filterOption={false}
              options={customers.map((c) => ({
                value: c.id,
                label: `${c.full_name} - ${c.phone || c.email || 'No contact'}`,
              }))}
              notFoundContent={customers.length === 0 ? 'No customers found' : null}
            />
          </Space>
          <StatementView
            entityType="customer"
            entityId={selectedCustomerId}
            entityName={selectedCustomer?.full_name}
          />
        </div>
      ),
    },
    {
      key: 'vendors',
      label: (
        <span>
          <ShopOutlined /> Vendor Statements
        </span>
      ),
      children: (
        <div>
          <Space direction="vertical" style={{ width: '100%', marginBottom: 16 }}>
            <Select
              showSearch
              allowClear
              placeholder="Select a vendor"
              style={{ width: 400 }}
              value={selectedVendorId}
              onChange={setSelectedVendorId}
              onSearch={setVendorSearch}
              filterOption={false}
              options={vendors.map((v) => ({
                value: v.id,
                label: `${v.name} - ${v.phone || v.email || 'No contact'}`,
              }))}
              notFoundContent={vendors.length === 0 ? 'No vendors found' : null}
            />
          </Space>
          <StatementView
            entityType="vendor"
            entityId={selectedVendorId}
            entityName={selectedVendor?.name}
          />
        </div>
      ),
    },
  ];

  return (
    <div>
      <Title level={4} style={{ marginBottom: 24 }}>
        Account Statements
      </Title>

      <Card>
        <Tabs
          activeKey={activeTab}
          onChange={setActiveTab}
          items={tabItems}
        />
      </Card>
    </div>
  );
}
