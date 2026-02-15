import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Button,
  Input,
  Select,
  DatePicker,
  Space,
  Tag,
  Typography,
  message,
  Modal,
  Form,
  InputNumber,
  Card,
  Row,
  Col,
  Statistic,
  Descriptions,
  Upload,
  Popconfirm,
  Dropdown,
} from 'antd';
import {
  PlusOutlined,
  SearchOutlined,
  EditOutlined,
  DeleteOutlined,
  EyeOutlined,
  CheckOutlined,
  CloseOutlined,
  DollarOutlined,
  UploadOutlined,
  MoreOutlined,
  FileTextOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import { expenseService } from '../../api/services/expense.service';

const { Title, Text } = Typography;
const { RangePicker } = DatePicker;
const { TextArea } = Input;

const statusColors = {
  draft: 'default',
  pending: 'warning',
  approved: 'success',
  rejected: 'error',
  paid: 'blue',
};

const paymentMethods = [
  { label: 'Cash', value: 'cash' },
  { label: 'Card', value: 'card' },
  { label: 'Bank Transfer', value: 'bank_transfer' },
  { label: 'Cheque', value: 'cheque' },
];

export default function ExpensesPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState(undefined);
  const [categoryId, setCategoryId] = useState(undefined);
  const [dateRange, setDateRange] = useState(undefined);
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState(null);
  const [editingExpense, setEditingExpense] = useState(null);
  const [rejectModal, setRejectModal] = useState(null);
  const [rejectReason, setRejectReason] = useState('');
  const [form] = Form.useForm();

  // Queries
  const { data: expensesData, isLoading } = useQuery({
    queryKey: ['expenses', { search, status, categoryId, dateRange, page }],
    queryFn: () =>
      expenseService.getExpenses({
        search: search || undefined,
        status,
        category_id: categoryId,
        start_date: dateRange?.[0],
        end_date: dateRange?.[1],
        page,
        per_page: 20,
      }),
  });

  const { data: summary } = useQuery({
    queryKey: ['expenses-summary'],
    queryFn: () => expenseService.getSummary(),
  });

  const { data: categories } = useQuery({
    queryKey: ['expense-categories-flat'],
    queryFn: () => expenseService.getCategoriesFlat(),
  });

  const { data: vendors } = useQuery({
    queryKey: ['expense-vendors'],
    queryFn: () => expenseService.getVendors(),
  });

  // Mutations
  const createMutation = useMutation({
    mutationFn: expenseService.createExpense,
    onSuccess: () => {
      message.success('Expense created successfully');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create expense');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => expenseService.updateExpense(id, data),
    onSuccess: () => {
      message.success('Expense updated successfully');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
      handleCloseModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to update expense');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: expenseService.deleteExpense,
    onSuccess: () => {
      message.success('Expense deleted successfully');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to delete expense');
    },
  });

  const approveMutation = useMutation({
    mutationFn: expenseService.approveExpense,
    onSuccess: () => {
      message.success('Expense approved');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
      setDetailModal(null);
    },
    onError: (error) => {
      message.error(error.response?.data?.error || 'Failed to approve expense');
    },
  });

  const rejectMutation = useMutation({
    mutationFn: ({ id, reason }) => expenseService.rejectExpense(id, reason),
    onSuccess: () => {
      message.success('Expense rejected');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
      setRejectModal(null);
      setRejectReason('');
      setDetailModal(null);
    },
    onError: (error) => {
      message.error(error.response?.data?.error || 'Failed to reject expense');
    },
  });

  const markPaidMutation = useMutation({
    mutationFn: expenseService.markPaid,
    onSuccess: () => {
      message.success('Expense marked as paid');
      queryClient.invalidateQueries({ queryKey: ['expenses'] });
      queryClient.invalidateQueries({ queryKey: ['expenses-summary'] });
      setDetailModal(null);
    },
    onError: (error) => {
      message.error(error.response?.data?.error || 'Failed to mark expense as paid');
    },
  });

  const handleOpenModal = (expense) => {
    if (expense) {
      setEditingExpense(expense);
      form.setFieldsValue({
        ...expense,
        expense_category_id: expense.category_id,
        expense_date: expense.expense_date ? dayjs(expense.expense_date) : null,
      });
    } else {
      setEditingExpense(null);
      form.resetFields();
      form.setFieldsValue({
        expense_date: dayjs(),
        payment_method: 'cash',
        status: 'pending',
      });
    }
    setModalOpen(true);
  };

  const handleCloseModal = () => {
    setModalOpen(false);
    setEditingExpense(null);
    form.resetFields();
  };

  const handleSubmit = (values) => {
    const data = {
      ...values,
      expense_date: values.expense_date?.format('YYYY-MM-DD'),
    };

    if (editingExpense) {
      updateMutation.mutate({ id: editingExpense.id, data });
    } else {
      createMutation.mutate(data);
    }
  };

  const handleReject = () => {
    if (!rejectReason.trim()) {
      message.error('Please provide a rejection reason');
      return;
    }
    rejectMutation.mutate({ id: rejectModal.id, reason: rejectReason });
  };

  const getActionItems = (record) => {
    const items = [];

    if (record.status === 'pending' || record.status === 'draft') {
      items.push({
        key: 'approve',
        icon: <CheckOutlined />,
        label: 'Approve',
        onClick: () => approveMutation.mutate(record.id),
      });
      items.push({
        key: 'reject',
        icon: <CloseOutlined />,
        label: 'Reject',
        danger: true,
        onClick: () => setRejectModal(record),
      });
    }

    if (record.status === 'approved') {
      items.push({
        key: 'paid',
        icon: <DollarOutlined />,
        label: 'Mark as Paid',
        onClick: () => markPaidMutation.mutate(record.id),
      });
    }

    if (record.status !== 'paid') {
      items.push({
        key: 'edit',
        icon: <EditOutlined />,
        label: 'Edit',
        onClick: () => handleOpenModal(record),
      });
      items.push({
        key: 'delete',
        icon: <DeleteOutlined />,
        label: 'Delete',
        danger: true,
        onClick: () => {
          Modal.confirm({
            title: 'Delete Expense',
            icon: <ExclamationCircleOutlined />,
            content: 'Are you sure you want to delete this expense?',
            okText: 'Yes',
            okType: 'danger',
            cancelText: 'No',
            onOk: () => deleteMutation.mutate(record.id),
          });
        },
      });
    }

    return items;
  };

  const columns = [
    {
      title: 'Expense #',
      dataIndex: 'expense_number',
      key: 'expense_number',
      render: (text) => <Text strong>{text}</Text>,
    },
    {
      title: 'Date',
      dataIndex: 'expense_date',
      key: 'expense_date',
      render: (date) => dayjs(date).format('MMM D, YYYY'),
    },
    {
      title: 'Category',
      dataIndex: 'category',
      key: 'category',
    },
    {
      title: 'Vendor',
      dataIndex: 'vendor',
      key: 'vendor',
      render: (text) => text || '-',
    },
    {
      title: 'Amount',
      dataIndex: 'amount',
      key: 'amount',
      render: (val) => `${parseFloat(val).toFixed(2)} SAR`,
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      render: (val) => <Text strong>{parseFloat(val).toFixed(2)} SAR</Text>,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (s, record) => (
        <Tag color={statusColors[s] || 'default'}>{record.status_label}</Tag>
      ),
    },
    {
      title: 'Actions',
      key: 'actions',
      width: 100,
      render: (_, record) => (
        <Space>
          <Button
            type="text"
            icon={<EyeOutlined />}
            onClick={() => setDetailModal(record)}
          />
          <Dropdown
            menu={{ items: getActionItems(record) }}
            trigger={['click']}
          >
            <Button type="text" icon={<MoreOutlined />} />
          </Dropdown>
        </Space>
      ),
    },
  ];

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 24 }}>
        <Title level={4} style={{ margin: 0 }}>
          Expenses
        </Title>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => handleOpenModal()}>
          Add Expense
        </Button>
      </div>

      {/* Summary Stats */}
      <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="This Month Total"
              value={summary?.this_month_total || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Pending Approval"
              value={summary?.pending_count || 0}
              suffix={summary?.pending_total ? `(${summary.pending_total.toFixed(2)} SAR)` : ''}
              valueStyle={{ color: '#faad14' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Approved This Month"
              value={summary?.approved_this_month || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={6}>
          <Card>
            <Statistic
              title="Paid This Month"
              value={summary?.paid_this_month || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
      </Row>

      {/* Filters */}
      <Card style={{ marginBottom: 16 }}>
        <Space wrap>
          <Input
            placeholder="Search expenses..."
            prefix={<SearchOutlined />}
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            style={{ width: 200 }}
            allowClear
          />
          <Select
            placeholder="Status"
            value={status}
            onChange={(v) => {
              setStatus(v);
              setPage(1);
            }}
            allowClear
            style={{ width: 150 }}
            options={[
              { label: 'Draft', value: 'draft' },
              { label: 'Pending', value: 'pending' },
              { label: 'Approved', value: 'approved' },
              { label: 'Rejected', value: 'rejected' },
              { label: 'Paid', value: 'paid' },
            ]}
          />
          <Select
            placeholder="Category"
            value={categoryId}
            onChange={(v) => {
              setCategoryId(v);
              setPage(1);
            }}
            allowClear
            style={{ width: 200 }}
            showSearch
            optionFilterProp="label"
            options={categories?.map((c) => ({ label: c.name, value: c.id })) || []}
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
              setPage(1);
            }}
          />
        </Space>
      </Card>

      {/* Table */}
      <Card>
        <Table
          dataSource={expensesData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: expensesData?.meta?.current_page || page,
            total: expensesData?.meta?.total,
            pageSize: expensesData?.meta?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} expenses`,
          }}
        />
      </Card>

      {/* Create/Edit Modal */}
      <Modal
        title={editingExpense ? 'Edit Expense' : 'New Expense'}
        open={modalOpen}
        onCancel={handleCloseModal}
        footer={null}
        destroyOnClose
        width={600}
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
          initialValues={{
            expense_date: dayjs(),
            payment_method: 'cash',
            status: 'pending',
          }}
        >
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="expense_category_id"
                label="Category"
                rules={[{ required: true, message: 'Please select a category' }]}
              >
                <Select
                  placeholder="Select category"
                  showSearch
                  optionFilterProp="label"
                  options={categories?.map((c) => ({ label: c.name, value: c.id })) || []}
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="vendor_id" label="Vendor">
                <Select
                  placeholder="Select vendor (optional)"
                  showSearch
                  allowClear
                  optionFilterProp="label"
                  options={vendors?.map((v) => ({ label: v.name, value: v.id })) || []}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="expense_date"
                label="Expense Date"
                rules={[{ required: true, message: 'Please select date' }]}
              >
                <DatePicker style={{ width: '100%' }} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name="payment_method"
                label="Payment Method"
                rules={[{ required: true, message: 'Please select payment method' }]}
              >
                <Select options={paymentMethods} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                name="amount"
                label="Amount"
                rules={[{ required: true, message: 'Please enter amount' }]}
              >
                <InputNumber
                  min={0}
                  precision={2}
                  style={{ width: '100%' }}
                  addonAfter="SAR"
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name="tax_amount" label="Tax Amount">
                <InputNumber
                  min={0}
                  precision={2}
                  style={{ width: '100%' }}
                  addonAfter="SAR"
                />
              </Form.Item>
            </Col>
          </Row>

          <Form.Item name="reference_number" label="Reference Number">
            <Input placeholder="Invoice number, receipt number, etc." />
          </Form.Item>

          <Form.Item name="description" label="Description">
            <TextArea rows={3} placeholder="Enter description" />
          </Form.Item>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item name="is_recurring" label="Recurring Expense" valuePropName="checked">
                <Select
                  placeholder="Is this recurring?"
                  allowClear
                  options={[
                    { label: 'No', value: false },
                    { label: 'Yes', value: true },
                  ]}
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                noStyle
                shouldUpdate={(prev, curr) => prev.is_recurring !== curr.is_recurring}
              >
                {({ getFieldValue }) =>
                  getFieldValue('is_recurring') ? (
                    <Form.Item name="recurring_frequency" label="Frequency">
                      <Select
                        options={[
                          { label: 'Daily', value: 'daily' },
                          { label: 'Weekly', value: 'weekly' },
                          { label: 'Monthly', value: 'monthly' },
                          { label: 'Yearly', value: 'yearly' },
                        ]}
                      />
                    </Form.Item>
                  ) : null
                }
              </Form.Item>
            </Col>
          </Row>

          {!editingExpense && (
            <Form.Item name="status" label="Status">
              <Select
                options={[
                  { label: 'Draft', value: 'draft' },
                  { label: 'Pending Approval', value: 'pending' },
                ]}
              />
            </Form.Item>
          )}

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={handleCloseModal}>Cancel</Button>
            <Button
              type="primary"
              htmlType="submit"
              loading={createMutation.isPending || updateMutation.isPending}
            >
              {editingExpense ? 'Update' : 'Create'}
            </Button>
          </div>
        </Form>
      </Modal>

      {/* Detail Modal */}
      <Modal
        title={`Expense Details - ${detailModal?.expense_number}`}
        open={!!detailModal}
        onCancel={() => setDetailModal(null)}
        footer={
          detailModal ? (
            <Space>
              <Button onClick={() => setDetailModal(null)}>Close</Button>
              {(detailModal.status === 'pending' || detailModal.status === 'draft') && (
                <>
                  <Button
                    danger
                    icon={<CloseOutlined />}
                    onClick={() => setRejectModal(detailModal)}
                  >
                    Reject
                  </Button>
                  <Button
                    type="primary"
                    icon={<CheckOutlined />}
                    onClick={() => approveMutation.mutate(detailModal.id)}
                    loading={approveMutation.isPending}
                  >
                    Approve
                  </Button>
                </>
              )}
              {detailModal.status === 'approved' && (
                <Button
                  type="primary"
                  icon={<DollarOutlined />}
                  onClick={() => markPaidMutation.mutate(detailModal.id)}
                  loading={markPaidMutation.isPending}
                >
                  Mark as Paid
                </Button>
              )}
            </Space>
          ) : null
        }
        width={700}
      >
        {detailModal && (
          <div>
            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
              <Col span={8}>
                <Card>
                  <Statistic
                    title="Amount"
                    value={detailModal.amount}
                    precision={2}
                    suffix="SAR"
                  />
                </Card>
              </Col>
              <Col span={8}>
                <Card>
                  <Statistic
                    title="Tax"
                    value={detailModal.tax_amount || 0}
                    precision={2}
                    suffix="SAR"
                  />
                </Card>
              </Col>
              <Col span={8}>
                <Card>
                  <Statistic
                    title="Total"
                    value={detailModal.total}
                    precision={2}
                    suffix="SAR"
                    valueStyle={{ color: '#1890ff' }}
                  />
                </Card>
              </Col>
            </Row>

            <Descriptions bordered column={2}>
              <Descriptions.Item label="Expense Number">{detailModal.expense_number}</Descriptions.Item>
              <Descriptions.Item label="Date">{dayjs(detailModal.expense_date).format('MMMM D, YYYY')}</Descriptions.Item>
              <Descriptions.Item label="Category">{detailModal.category}</Descriptions.Item>
              <Descriptions.Item label="Vendor">{detailModal.vendor || '-'}</Descriptions.Item>
              <Descriptions.Item label="Payment Method">
                {paymentMethods.find((m) => m.value === detailModal.payment_method)?.label || detailModal.payment_method}
              </Descriptions.Item>
              <Descriptions.Item label="Status">
                <Tag color={statusColors[detailModal.status]}>{detailModal.status_label}</Tag>
              </Descriptions.Item>
              <Descriptions.Item label="Reference Number">{detailModal.reference_number || '-'}</Descriptions.Item>
              <Descriptions.Item label="Recurring">
                {detailModal.is_recurring ? `Yes (${detailModal.recurring_frequency})` : 'No'}
              </Descriptions.Item>
              <Descriptions.Item label="Description" span={2}>
                {detailModal.description || '-'}
              </Descriptions.Item>
              <Descriptions.Item label="Created By">{detailModal.created_by}</Descriptions.Item>
              <Descriptions.Item label="Created At">{detailModal.created_at}</Descriptions.Item>
              {detailModal.approved_by && (
                <>
                  <Descriptions.Item label="Approved/Rejected By">{detailModal.approved_by}</Descriptions.Item>
                  <Descriptions.Item label="Approved/Rejected At">{detailModal.approved_at}</Descriptions.Item>
                </>
              )}
              {detailModal.status === 'rejected' && detailModal.rejection_reason && (
                <Descriptions.Item label="Rejection Reason" span={2}>
                  <Text type="danger">{detailModal.rejection_reason}</Text>
                </Descriptions.Item>
              )}
              {detailModal.receipt_url && (
                <Descriptions.Item label="Receipt" span={2}>
                  <a href={detailModal.receipt_url} target="_blank" rel="noopener noreferrer">
                    <FileTextOutlined /> View Receipt
                  </a>
                </Descriptions.Item>
              )}
            </Descriptions>
          </div>
        )}
      </Modal>

      {/* Reject Reason Modal */}
      <Modal
        title="Reject Expense"
        open={!!rejectModal}
        onCancel={() => {
          setRejectModal(null);
          setRejectReason('');
        }}
        onOk={handleReject}
        okText="Reject"
        okButtonProps={{ danger: true, loading: rejectMutation.isPending }}
      >
        <p>Please provide a reason for rejecting this expense:</p>
        <TextArea
          rows={4}
          value={rejectReason}
          onChange={(e) => setRejectReason(e.target.value)}
          placeholder="Enter rejection reason..."
        />
      </Modal>
    </div>
  );
}
