import { Card, Table } from 'antd';

export default function DataTable({
  title,
  data,
  columns,
  loading,
  pagination,
  rowKey = 'id',
  onPageChange,
  showCard = true,
  extra,
  ...tableProps
}) {
  const paginationConfig = pagination
    ? {
        current: pagination.current_page || pagination.currentPage || 1,
        total: pagination.total || 0,
        pageSize: pagination.per_page || pagination.pageSize || 20,
        onChange: onPageChange,
        showSizeChanger: false,
        showTotal: (total) => `Total ${total} items`,
      }
    : false;

  const table = (
    <Table
      dataSource={data}
      columns={columns}
      rowKey={rowKey}
      loading={loading}
      pagination={paginationConfig}
      size="middle"
      {...tableProps}
    />
  );

  if (!showCard) return table;

  return (
    <Card title={title} extra={extra} size="small">
      {table}
    </Card>
  );
}
