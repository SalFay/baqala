import { Card, Space, Input, Select, DatePicker } from 'antd';
import { SearchOutlined } from '@ant-design/icons';

const { RangePicker } = DatePicker;

export default function FilterBar({
  search,
  onSearchChange,
  searchPlaceholder = 'Search...',
  filters = [],
  dateRange,
  onDateRangeChange,
  showDateRange = false,
  extra,
  children,
}) {
  return (
    <Card size="small" style={{ marginBottom: 16 }}>
      <Space wrap>
        {search !== undefined && (
          <Input
            placeholder={searchPlaceholder}
            prefix={<SearchOutlined />}
            value={search}
            onChange={(e) => onSearchChange?.(e.target.value)}
            style={{ width: 200 }}
            allowClear
          />
        )}

        {filters.map((filter) => (
          <Select
            key={filter.key}
            placeholder={filter.placeholder}
            value={filter.value}
            onChange={filter.onChange}
            allowClear={filter.allowClear !== false}
            style={{ width: filter.width || 150 }}
            showSearch={filter.showSearch}
            optionFilterProp="label"
            options={filter.options}
          />
        ))}

        {showDateRange && (
          <RangePicker
            value={dateRange}
            onChange={onDateRangeChange}
            allowClear
          />
        )}

        {extra}
        {children}
      </Space>
    </Card>
  );
}
