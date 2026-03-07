import { Card, DatePicker, Button, Space, Row, Col, Typography } from 'antd'
import { FilterOutlined, DownloadOutlined, ReloadOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import LocationSelector from '@/Components/LocationSelector'

const { RangePicker } = DatePicker
const { Text } = Typography

export default function ReportFilters({
  dateRange,
  onDateRangeChange,
  locationId,
  onLocationChange,
  onRefresh,
  onExport,
  showLocation = true,
  showExport = true,
  loading = false,
  extraFilters,
}) {
  const presets = [
    { label: 'Today', value: [dayjs(), dayjs()] },
    { label: 'Yesterday', value: [dayjs().subtract(1, 'day'), dayjs().subtract(1, 'day')] },
    { label: 'Last 7 Days', value: [dayjs().subtract(7, 'day'), dayjs()] },
    { label: 'Last 30 Days', value: [dayjs().subtract(30, 'day'), dayjs()] },
    { label: 'This Month', value: [dayjs().startOf('month'), dayjs()] },
    { label: 'Last Month', value: [dayjs().subtract(1, 'month').startOf('month'), dayjs().subtract(1, 'month').endOf('month')] },
    { label: 'This Year', value: [dayjs().startOf('year'), dayjs()] },
  ]

  return (
    <Card size="small" style={{ marginBottom: 16 }}>
      <Row gutter={[16, 16]} align="middle">
        <Col>
          <Space>
            <FilterOutlined />
            <Text strong>Filters:</Text>
          </Space>
        </Col>

        <Col>
          <RangePicker
            value={dateRange}
            onChange={onDateRangeChange}
            presets={presets}
            allowClear={false}
            style={{ width: 280 }}
          />
        </Col>

        {showLocation && (
          <Col>
            <LocationSelector
              value={locationId}
              onChange={onLocationChange}
              placeholder="All Locations"
              allowClear
              style={{ width: 180 }}
            />
          </Col>
        )}

        {extraFilters}

        <Col flex="auto" />

        <Col>
          <Space>
            <Button
              icon={<ReloadOutlined />}
              onClick={onRefresh}
              loading={loading}
            >
              Refresh
            </Button>
            {showExport && onExport && (
              <Button
                icon={<DownloadOutlined />}
                onClick={onExport}
              >
                Export
              </Button>
            )}
          </Space>
        </Col>
      </Row>
    </Card>
  )
}
