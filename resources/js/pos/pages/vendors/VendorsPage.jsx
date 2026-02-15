import { useState } from 'react';
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
  Modal,
  Form,
  Select,
  Card,
  Row,
  Col,
  Statistic,
  Descriptions,
  Tabs,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  PhoneOutlined,
  EnvironmentOutlined,
} from '@ant-design/icons';
import { vendorService } from '../../api/services/vendor.service';

const { Title, Text } = Typography;

export default function VendorsPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState(null);
  const [editingVendor, setEditingVendor] = useState(null);
  const [form] = Form.useForm();

  const { data: vendorsData, isLoading } = useQuery({
    queryKey: ['vendors', { search, page }],
    queryFn: () =>
      vendorService.getVendors({
        search: search || undefined,
        page,
        per_page: 20,
      }),
  });

  const { data: vendorBalance } = useQuery({
    queryKey: ['vendor-balance', detailModal?.id],
    queryFn: () => vendorService.getVendorBalance(detailModal.id),
    enabled: !!detailModal,
  });

  const { data: vendorPurchaseOrders } = useQuery({
    queryKey: ['vendor-purchase-orders', detailModal?.id],
    queryFn: () => vendorService.getVendorPurchaseOrders(detailModal.id),
    enabled: !!detailModal,
  });

  const createMutation = useMutation({
    mutationFn: vendorService.createVendor,
    onSuccess: () => {
      message.success('Vendor created successfully');
      queryClient.invalidateQueries({ queryKey: ['vendors'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create vendor');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => vendorService.updateVendor(id, data),
    onSuccess: () => {
      message.success('Vendor updated successfully');
      queryClient.invalidateQueries({ queryKey: ['vendors'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update vendor');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: vendorService.deleteVendor,
    onSuccess: () => {
      message.success('Vendor deleted successfully');
      queryClient.invalidateQueries({ queryKey: ['vendors'] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete vendor');
    },
  });

  const handleOpenModal = (vendor) => {
    if (vendor) {
      setEditingVendor(vendor);
      form.setFieldsValue(vendor);
    } else {
      setEditingVendor(null);
      form.resetFields();
      form.setFieldsValue({ status: 'active' });
    }
    setModalOpen(true);
  };

  const handleCloseModal = () => {
    setModalOpen(false);
    setEditingVendor(null);
    form.resetFields();
  };

  const handleSubmit = (values) => {
    if (editingVendor) {
      updateMutation.mutate({ id: editingVendor.id, data: values });
    } else {
      createMutation.mutate(values);
    }
  };

  const columns = [
    {
      title: 'Name',
      dataIndex: 'name',
      key: 'name',
      render: (name) => <Text strong>{name}</Text>,
    },
    {
      title: 'Mobile',
      dataIndex: 'mobile',
      key: 'mobile',
      render: (mobile) =>
        mobile ? (
          <Space>
            <PhoneOutlined />
            {mobile}
          </Space>
        ) : (
          '-'
        ),
    },
    {
      title: 'Address',
      dataIndex: 'address',
      key: 'address',
      ellipsis: true,
      render: (address) =>
        address ? (
          <Space>
            <EnvironmentOutlined />
            {address}
          </Space>
        ) : (
          '-'
        ),
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => (
        <Tag color={status === 'active' ? 'green' : 'default'}>
          {status === 'active' ? 'Active' : 'Inactive'}
        </Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 150,
      render: (_, record) => (
        <Space>
          <Button
            type="text"
            icon={<EyeOutlined />}
            onClick={() => setDetailModal(record)}
          />
          <Button
            type="text"
            icon={<EditOutlined />}
            onClick={() => handleOpenModal(record)}
          />
          <Popconfirm
            title="Delete Vendor"
            description="Are you sure you want to delete this vendor?"
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
          Vendors
        </Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => handleOpenModal()}>
          Add Vendor
        </Button>
      </div>

      <Card>
        <Input
          placeholder="Search vendors by name, mobile, or address..."
          prefix={<SearchOutlined />}
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          style={{ width: 400, marginBottom: 16 }}
          allowClear
        />

        <Table
          dataSource={vendorsData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: vendorsData?.current_page || page,
            total: vendorsData?.total,
            pageSize: vendorsData?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} vendors`,
          }}
        />
      </Card>

      {/* Create/Edit Modal */}
      <Modal
        title={editingVendor ? 'Edit Vendor' : 'New Vendor'}
        open={modalOpen}
        onCancel={handleCloseModal}
        footer={null}
        destroyOnClose
        width={500}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
          initialValues={{ status: 'active' }}
        >
          <Form.Item
            name="name"
            label="Vendor Name"
            rules={[{ required: true, message: 'Please enter vendor name' }]}
          >
            <Input placeholder="Enter vendor name" />
          </Form.Item>

          <Form.Item name="mobile" label="Mobile Number">
            <Input placeholder="Enter mobile number" />
          </Form.Item>

          <Form.Item name="address" label="Address">
            <Input.TextArea rows={3} placeholder="Enter address" />
          </Form.Item>

          <Form.Item
            name="status"
            label="Status"
            rules={[{ required: true, message: 'Please select status' }]}
          >
            <Select>
              <Select.Option value="active">Active</Select.Option>
              <Select.Option value="inactive">Inactive</Select.Option>
            </Select>
          </Form.Item>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={handleCloseModal}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createMutation.isPending || updateMutation.isPending}
            >
              {editingVendor ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>

      {/* Detail Modal */}
      <Modal
        title={`Vendor Details - ${detailModal?.name}`}
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        footer={[
          <Button key="close" onClick={() => setDetailModal(null)}>
            Close
          </Button>,
          <Button
            key="edit"
            type="primary"
            onClick={() => {
              setDetailModal(null);
              handleOpenModal(detailModal);
            }}
          >
            Edit
          </Button>,
        ]}
        width={700}
      >
        {detailModal && (
          <Tabs
            items={[
              {
                key: 'info',
                label: 'Information',
                children: (
                  <div>
                    <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                      <Col span={12}>
                        <Card>
                          <Statistic
                            title="Balance"
                            value={vendorBalance?.balance || 0}
                            precision={2}
                            suffix="SAR"
                          />
                        </Card>
                      </Col>
                      <Col span={12}>
                        <Card>
                          <Statistic
                            title="Purchase Orders"
                            value={vendorPurchaseOrders?.total || 0}
                          />
                        </Card>
                      </Col>
                    </Row>

                    <Descriptions bordered column={1}>
                      <Descriptions.Item label="Name">{detailModal.name}</Descriptions.Item>
                      <Descriptions.Item label="Mobile">{detailModal.mobile || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Address">{detailModal.address || '-'}</Descriptions.Item>
                      <Descriptions.Item label="Status">
                        <Tag color={detailModal.status === 'active' ? 'green' : 'default'}>
                          {detailModal.status}
                        </Tag>
                      </Descriptions.Item>
                      <Descriptions.Item label="Created">
                        {new Date(detailModal.created_at).toLocaleDateString()}
                      </Descriptions.Item>
                    </Descriptions>
                  </div>
                ),
              },
              {
                key: 'orders',
                label: 'Purchase Orders',
                children: (
                  <Table
                    dataSource={vendorPurchaseOrders?.data || []}
                    rowKey="id"
                    size="small"
                    columns={[
                      { title: 'PO Number', dataIndex: 'po_number' },
                      { title: 'Date', dataIndex: 'order_date' },
                      {
                        title: 'Total',
                        dataIndex: 'total',
                        render: (v) => `${v?.toFixed(2)} SAR`,
                      },
                      {
                        title: 'Status',
                        dataIndex: 'status',
                        render: (s) => <Tag>{s}</Tag>,
                      },
                    ]}
                    pagination={{ pageSize: 5 }}
                  />
                ),
              },
            ]}
          />
        )}
      </Modal>
    </div>
  );
}
