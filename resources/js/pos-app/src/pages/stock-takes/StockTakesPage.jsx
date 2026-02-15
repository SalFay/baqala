import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Table,
  Button,
  Select,
  Space,
  Typography,
  message,
  Modal,
  Form,
  Input,
  Card,
  Progress,
  InputNumber,
  Descriptions,
  Tag,
} from 'antd';
import {
  PlusOutlined,
  PlayCircleOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  EyeOutlined,
  DeleteOutlined,
  ScanOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import { stockTakeService } from '../../api/services/stockTake.service';
import { categoryService } from '../../api/services/category.service';
import { PageHeader, StatusTag, SummaryStats, ActionDropdown } from '../../Components/Common';

const { Text } = Typography;
const { TextArea } = Input;

const typeLabels = {
  full: 'Full Count',
  partial: 'Partial',
  category: 'By Category',
  location: 'By Location',
};

export default function StockTakesPage() {
  const queryClient = useQueryClient();
  const [status, setStatus] = useState(undefined);
  const [page, setPage] = useState(1);
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [countModalOpen, setCountModalOpen] = useState(false);
  const [selectedStockTake, setSelectedStockTake] = useState(null);
  const [form] = Form.useForm();

  // Queries
  const { data: stockTakesData, isLoading } = useQuery({
    queryKey: ['stock-takes', { status, page }],
    queryFn: () => stockTakeService.getStockTakes({ status, page, per_page: 20 }),
  });

  const { data: summary } = useQuery({
    queryKey: ['stock-takes-summary'],
    queryFn: () => stockTakeService.getSummary(),
  });

  const { data: stockTakeDetail } = useQuery({
    queryKey: ['stock-take', selectedStockTake?.id],
    queryFn: () => stockTakeService.getStockTake(selectedStockTake.id),
    enabled: !!selectedStockTake?.id && countModalOpen,
  });

  const { data: categories } = useQuery({
    queryKey: ['categories-list'],
    queryFn: () => categoryService.getCategories(),
    enabled: createModalOpen,
  });

  // Mutations
  const createMutation = useMutation({
    mutationFn: stockTakeService.createStockTake,
    onSuccess: () => {
      message.success('Stock take created successfully');
      queryClient.invalidateQueries({ queryKey: ['stock-takes'] });
      queryClient.invalidateQueries({ queryKey: ['stock-takes-summary'] });
      setCreateModalOpen(false);
      form.resetFields();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create stock take');
    },
  });

  const startMutation = useMutation({
    mutationFn: stockTakeService.startStockTake,
    onSuccess: () => {
      message.success('Stock take started');
      queryClient.invalidateQueries({ queryKey: ['stock-takes'] });
    },
  });

  const completeMutation = useMutation({
    mutationFn: ({ id, applyAdjustments }) =>
      stockTakeService.completeStockTake(id, applyAdjustments),
    onSuccess: () => {
      message.success('Stock take completed');
      queryClient.invalidateQueries({ queryKey: ['stock-takes'] });
      queryClient.invalidateQueries({ queryKey: ['stock-takes-summary'] });
      setCountModalOpen(false);
      setSelectedStockTake(null);
    },
  });

  const cancelMutation = useMutation({
    mutationFn: stockTakeService.cancelStockTake,
    onSuccess: () => {
      message.success('Stock take cancelled');
      queryClient.invalidateQueries({ queryKey: ['stock-takes'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: stockTakeService.deleteStockTake,
    onSuccess: () => {
      message.success('Stock take deleted');
      queryClient.invalidateQueries({ queryKey: ['stock-takes'] });
    },
  });

  const countItemMutation = useMutation({
    mutationFn: ({ stockTakeId, itemId, quantity, notes }) =>
      stockTakeService.countItem(stockTakeId, itemId, quantity, notes),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['stock-take', selectedStockTake?.id] });
    },
  });

  const handleCreate = (values) => {
    createMutation.mutate(values);
  };

  const handleComplete = () => {
    Modal.confirm({
      title: 'Complete Stock Take',
      icon: <ExclamationCircleOutlined />,
      content: 'Do you want to apply the inventory adjustments?',
      okText: 'Apply Adjustments',
      cancelText: 'Complete Without Adjustments',
      onOk: () => completeMutation.mutate({ id: selectedStockTake.id, applyAdjustments: true }),
      onCancel: () => completeMutation.mutate({ id: selectedStockTake.id, applyAdjustments: false }),
    });
  };

  const getActionItems = (record) => {
    const items = [];

    if (record.status === 'draft') {
      items.push({
        key: 'start',
        icon: <PlayCircleOutlined />,
        label: 'Start Counting',
        onClick: () => startMutation.mutate(record.id),
      });
      items.push({
        key: 'delete',
        icon: <DeleteOutlined />,
        label: 'Delete',
        danger: true,
        onClick: () => deleteMutation.mutate(record.id),
      });
    }

    if (record.status === 'in_progress') {
      items.push({
        key: 'count',
        icon: <ScanOutlined />,
        label: 'Continue Counting',
        onClick: () => {
          setSelectedStockTake(record);
          setCountModalOpen(true);
        },
      });
      items.push({
        key: 'cancel',
        icon: <CloseCircleOutlined />,
        label: 'Cancel',
        danger: true,
        onClick: () => cancelMutation.mutate(record.id),
      });
    }

    return items;
  };

  const columns = [
    {
      title: 'Stock Take #',
      dataIndex: 'stock_take_number',
      key: 'stock_take_number',
      render: (text) => <Text strong>{text}</Text>,
    },
    {
      title: 'Type',
      dataIndex: 'type',
      key: 'type',
      render: (type) => typeLabels[type] || type,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (s, record) => <StatusTag status={s} label={record.status_label} />,
    },
    {
      title: 'Progress',
      key: 'progress',
      render: (_, record) => (
        <Progress
          percent={record.progress}
          size="small"
          format={(p) => `${record.counted_items}/${record.total_items}`}
        />
      ),
    },
    {
      title: 'Variance',
      key: 'variance',
      render: (_, record) => (
        <Space>
          {record.positive_variance_count > 0 && (
            <Tag color="blue">+{record.positive_variance_count}</Tag>
          )}
          {record.negative_variance_count > 0 && (
            <Tag color="red">-{record.negative_variance_count}</Tag>
          )}
          {record.positive_variance_count === 0 && record.negative_variance_count === 0 && '-'}
        </Space>
      ),
    },
    {
      title: 'Created',
      dataIndex: 'created_at',
      key: 'created_at',
      render: (date) => dayjs(date).format('MMM D, YYYY'),
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
            onClick={() => {
              setSelectedStockTake(record);
              setCountModalOpen(true);
            }}
          />
          {record.status !== 'completed' && record.status !== 'cancelled' && (
            <ActionDropdown items={getActionItems(record)} />
          )}
        </Space>
      ),
    },
  ];

  const watchedType = Form.useWatch('type', form);

  return (
    <div>
      <PageHeader title="Stock Takes">
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setCreateModalOpen(true)}>
          New Stock Take
        </Button>
      </PageHeader>

      {/* Summary Stats */}
      <SummaryStats
        colSpan={{ xs: 24, sm: 8 }}
        stats={[
          {
            title: 'In Progress',
            value: summary?.in_progress || 0,
            precision: 0,
            color: '#1890ff',
          },
          {
            title: 'Completed This Month',
            value: summary?.completed_this_month || 0,
            precision: 0,
            color: '#52c41a',
          },
          {
            title: 'Total Adjustments (This Month)',
            value: summary?.total_adjustments_this_month || 0,
            precision: 0,
            prefix: summary?.total_adjustments_this_month > 0 ? '+' : '',
            color: summary?.total_adjustments_this_month > 0 ? '#52c41a' : '#ff4d4f',
          },
        ]}
      />

      {/* Filters */}
      <Card style={{ marginBottom: 16 }}>
        <Space>
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
              { label: 'In Progress', value: 'in_progress' },
              { label: 'Completed', value: 'completed' },
              { label: 'Cancelled', value: 'cancelled' },
            ]}
          />
        </Space>
      </Card>

      {/* Table */}
      <Card>
        <Table
          dataSource={stockTakesData?.data}
          columns={columns}
          rowKey="id"
          loading={isLoading}
          pagination={{
            current: stockTakesData?.meta?.current_page || page,
            total: stockTakesData?.meta?.total,
            pageSize: stockTakesData?.meta?.per_page || 20,
            onChange: setPage,
            showSizeChanger: false,
            showTotal: (total) => `Total ${total} stock takes`,
          }}
        />
      </Card>

      {/* Create Modal */}
      <Modal
        title="New Stock Take"
        open={createModalOpen}
        onCancel={() => {
          setCreateModalOpen(false);
          form.resetFields();
        }}
        footer={null}
        width={500}
      >
        <Form form={form} layout="vertical" onFinish={handleCreate} initialValues={{ type: 'full' }}>
          <Form.Item
            name="type"
            label="Type"
            rules={[{ required: true, message: 'Please select type' }]}
          >
            <Select
              options={[
                { label: 'Full Count (All Products)', value: 'full' },
                { label: 'By Category', value: 'category' },
                { label: 'By Location', value: 'location' },
                { label: 'Partial (Select Products)', value: 'partial' },
              ]}
            />
          </Form.Item>

          {watchedType === 'category' && (
            <Form.Item
              name="category_id"
              label="Category"
              rules={[{ required: true, message: 'Please select category' }]}
            >
              <Select
                showSearch
                optionFilterProp="label"
                options={categories?.data?.map((c) => ({ label: c.name, value: c.id })) || []}
              />
            </Form.Item>
          )}

          {watchedType === 'location' && (
            <Form.Item
              name="location"
              label="Location"
              rules={[{ required: true, message: 'Please enter location' }]}
            >
              <Input placeholder="Enter location (e.g., Shelf A, Warehouse 1)" />
            </Form.Item>
          )}

          <Form.Item name="notes" label="Notes">
            <TextArea rows={3} placeholder="Optional notes for this stock take" />
          </Form.Item>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
            <Button onClick={() => setCreateModalOpen(false)}>Cancel</Button>
            <Button type="primary" htmlType="submit" loading={createMutation.isPending}>
              Create Stock Take
            </Button>
          </div>
        </Form>
      </Modal>

      {/* Count Modal */}
      <Modal
        title={`Stock Take ${selectedStockTake?.stock_take_number}`}
        open={countModalOpen}
        onCancel={() => {
          setCountModalOpen(false);
          setSelectedStockTake(null);
        }}
        footer={
          stockTakeDetail?.status === 'in_progress' ? (
            <Space>
              <Button onClick={() => setCountModalOpen(false)}>Close</Button>
              <Button
                type="primary"
                icon={<CheckCircleOutlined />}
                onClick={handleComplete}
                loading={completeMutation.isPending}
              >
                Complete
              </Button>
            </Space>
          ) : (
            <Button onClick={() => setCountModalOpen(false)}>Close</Button>
          )
        }
        width={900}
      >
        {stockTakeDetail && (
          <>
            <Descriptions bordered column={3} size="small" style={{ marginBottom: 16 }}>
              <Descriptions.Item label="Status">
                <StatusTag status={stockTakeDetail.status} label={stockTakeDetail.status_label} />
              </Descriptions.Item>
              <Descriptions.Item label="Progress">
                <Progress percent={stockTakeDetail.progress} size="small" />
              </Descriptions.Item>
              <Descriptions.Item label="Items">
                {stockTakeDetail.counted_items} / {stockTakeDetail.total_items}
              </Descriptions.Item>
            </Descriptions>

            <Table
              dataSource={stockTakeDetail.items}
              columns={[
                {
                  title: 'Product',
                  dataIndex: 'product_name',
                  key: 'product_name',
                  ellipsis: true,
                },
                {
                  title: 'SKU',
                  dataIndex: 'sku',
                  key: 'sku',
                  width: 100,
                },
                {
                  title: 'Expected',
                  dataIndex: 'expected_quantity',
                  key: 'expected',
                  width: 80,
                  align: 'center',
                },
                {
                  title: 'Counted',
                  dataIndex: 'counted_quantity',
                  key: 'counted',
                  width: 120,
                  align: 'center',
                  render: (val, record) =>
                    stockTakeDetail.status === 'in_progress' ? (
                      <InputNumber
                        min={0}
                        value={val}
                        onChange={(v) =>
                          countItemMutation.mutate({
                            stockTakeId: selectedStockTake.id,
                            itemId: record.id,
                            quantity: v,
                          })
                        }
                        style={{ width: 80 }}
                      />
                    ) : (
                      val ?? '-'
                    ),
                },
                {
                  title: 'Variance',
                  dataIndex: 'variance',
                  key: 'variance',
                  width: 80,
                  align: 'center',
                  render: (val, record) =>
                    val !== null ? (
                      <Tag color={record.variance_color}>
                        {val > 0 ? '+' : ''}
                        {val}
                      </Tag>
                    ) : (
                      '-'
                    ),
                },
              ]}
              rowKey="id"
              pagination={{ pageSize: 10 }}
              size="small"
              scroll={{ y: 400 }}
            />
          </>
        )}
      </Modal>
    </div>
  );
}
