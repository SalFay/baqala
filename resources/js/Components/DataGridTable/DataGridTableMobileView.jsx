import React from 'react'
import { Button, Card, Collapse, Empty, Flex, Space, Typography } from 'antd'
import { LoadingOutlined } from '@ant-design/icons'
import {
  getColumnDisplayValue,
  getPrimaryDisplayValue,
  MAX_DISPLAY_LENGTH,
  toDisplayString,
  truncateText,
} from '@/Components/DataGridTable/DataGridTableUtils.js'

const { Text } = Typography
const { Panel } = Collapse

const DataGridTableMobileView = ({
  rowData,
  filteredColumns,
  actionsColumn,
  showActionsColumn,
  token,
  theme,
  pagination,
  totalRows,
  pageSize,
  currentPage,
  mobileLoading,
  onPrevPage,
  onNextPage,
}) => {
  if (mobileLoading) {
    return (
      <Flex
        vertical
        align="center"
        justify="center"
        style={{
          padding: '48px 16px',
          minHeight: '300px',
        }}
      >
        <LoadingOutlined
          style={{
            fontSize: '32px',
            color: token.colorPrimary,
            marginBottom: '16px',
          }}
        />
        <Text type="secondary">Loading data...</Text>
      </Flex>
    )
  }

  return (
    <div style={{ width: '100%' }}>
      {rowData.length === 0 ? (
        <Empty description="No Data Found" />
      ) : (
        <div style={{ border: `1px solid ${token.colorBorder}`, borderRadius: token.borderRadius }}>
          <Collapse bordered={false}>
            {rowData.map((row, index) => {
              const primaryValue = getPrimaryDisplayValue(row, filteredColumns)
              const primaryDisplay = toDisplayString(primaryValue)
              const headerText = typeof primaryDisplay === 'string'
                ? truncateText(primaryDisplay, MAX_DISPLAY_LENGTH)
                : primaryDisplay
              const headerTitle = typeof primaryDisplay === 'string' ? primaryDisplay : undefined

              return (
                <Panel
                  showArrow={false}
                  header={
                    <Flex justify="space-between" align="center">
                      <Text
                        style={{
                          flex: 1,
                          fontWeight: 500,
                          fontSize: '14px',
                          lineHeight: '1.4',
                          wordBreak: 'break-word',
                          overflow: 'hidden',
                          display: '-webkit-box',
                          WebkitLineClamp: 2,
                          WebkitBoxOrient: 'vertical',
                          minWidth: 0,
                        }}
                        title={headerTitle}
                      >
                        {headerText}
                      </Text>
                      {showActionsColumn && (
                        <div
                          onClick={(e) => e.stopPropagation()}
                          onKeyDown={(e) => e.stopPropagation()}
                          style={{ display: 'flex', alignItems: 'center' }}
                        >
                          {actionsColumn?.cellRenderer({ data: row })}
                        </div>
                      )}
                    </Flex>
                  }
                  key={row.id || index}
                >
                  <Card variant="borderless" styles={{ body: { padding: 0 } }}>
                    <Flex vertical wrap="wrap" gap="middle">
                      {filteredColumns.map((col) => {
                        const value = getColumnDisplayValue(row, col)
                        const displayValue = toDisplayString(value)
                        if (displayValue === '-') return null

                        return (
                          <Space
                            direction="horizontal"
                            key={col.field}
                            style={{ width: '100%' }}
                          >
                            <Text strong style={{ minWidth: '120px' }}>
                              {col.headerName}:
                            </Text>
                            <Text style={{ flex: 1, wordBreak: 'break-word' }}>
                              {col.cellRenderer
                                ? col.cellRenderer({ data: row, value: displayValue })
                                : displayValue}
                            </Text>
                          </Space>
                        )
                      })}
                    </Flex>
                  </Card>
                </Panel>
              )
            })}
          </Collapse>

          {pagination && totalRows > pageSize && (
            <Flex
              justify="space-between"
              align="center"
              style={{
                padding: '12px 16px',
                background: theme === 'dark' ? '#222222' : '#f6f6f6',
              }}
            >
              <Button
                disabled={currentPage === 1 || mobileLoading}
                onClick={onPrevPage}
                size="small"
                loading={mobileLoading && currentPage > 1}
              >
                Previous
              </Button>

              <Text
                strong
                style={{
                  fontSize: '13px',
                  color: token.colorTextSecondary,
                }}
              >
                Page {currentPage} of {Math.ceil(totalRows / pageSize)}
              </Text>

              <Button
                disabled={currentPage * pageSize >= totalRows || mobileLoading}
                onClick={onNextPage}
                size="small"
                loading={mobileLoading && currentPage * pageSize < totalRows}
              >
                Next
              </Button>
            </Flex>
          )}
        </div>
      )}
    </div>
  )
}

export default DataGridTableMobileView
