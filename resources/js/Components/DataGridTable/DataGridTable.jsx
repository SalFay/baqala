import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { AgGridReact } from 'ag-grid-react'
import 'ag-grid-community/styles/ag-grid.css'
import 'ag-grid-community/styles/ag-theme-alpine.css'
import axios from 'axios'
import {
  ModuleRegistry,
  NumberFilterModule,
  TextFilterModule,
  TooltipModule,
  ValidationModule,
} from 'ag-grid-community'
import {
  AdvancedFilterModule,
  ColumnMenuModule,
  ContextMenuModule,
  ExcelExportModule,
  PaginationModule,
  ServerSideRowModelModule,
} from 'ag-grid-enterprise'
import {
  Button,
  Checkbox,
  Drawer,
  Flex,
  Input,
  message,
  Popover,
  Space,
  Switch,
  theme,
  Typography,
} from 'antd'
import { IconFileExport, IconFilter, IconMenu2, IconReload, IconSettings, IconX } from '@tabler/icons-react'
import usePermissions from '@/Helpers/Context/usePermissions.js'
import { useRecoilValue } from 'recoil'
import { themeAtom } from '@/Helpers/atom.js'
import { handleApiError } from '@/Helpers/CONSTANT.js'
import GlobalFilter from '@/Components/GlobalFilter.jsx'
import useIsMobile from '@/Hooks/useIsMobile.js'
import Button1 from '@/Components/Buttons/Button1.jsx'
import '@css/ag-grid-custom.scss'
import DataGridTableMobileView from '@/Components/DataGridTable/DataGridTableMobileView.jsx'

const { Text } = Typography

ModuleRegistry.registerModules([
  PaginationModule,
  TextFilterModule,
  NumberFilterModule,
  AdvancedFilterModule,
  ColumnMenuModule,
  ContextMenuModule,
  ServerSideRowModelModule,
  ExcelExportModule,
  ValidationModule,
  TooltipModule,
])

const { useToken } = theme

// Stable empty function to prevent re-renders when setIsActive is not provided
const noop = () => {}

const DataGridTable = ({
  showResetFilter = true,
  gridRef,
  routeName,
  columns = [],
  setIsActive = noop,
  pageSize = 20,
  pagination = true,
  paginationAutoPageSize = false,
  suppressPaginationPanel = false,
  enableCellTextSelection = true,
  rowHeight = undefined,
  customParams = {},
  args,
  showSoftDeleted = true,
  height = undefined,
  width = undefined,
  filterFields,
  taskView = false,
  compactView = false,
  showModal,
  defaultFilter,
  instanceId,
  defaultHiddenColumns = [],
  showActions = true
}) => {
  // Validate required props
  if (!routeName) {
    console.error('DataGridTable: routeName prop is required')
  }
  if (!columns || columns.length === 0) {
    console.warn('DataGridTable: columns prop is empty or not provided')
  }

  const currentTheme = useRecoilValue(themeAtom)
  const { token } = useToken()
  const themeClass = currentTheme === 'light' ? 'ag-theme-alpine' : 'ag-theme-alpine-dark'
  const { hasPermission } = usePermissions()
  const moduleName = routeName ? routeName.split('.')[0].replace(/-/g, ' ') : 'data'
  const storageKey = instanceId ? `${instanceId}Filters` : `${moduleName}Filters`
  const columnVisibilityStorageKey = instanceId ? `${instanceId}ColumnVisibility` : `${moduleName}ColumnVisibility`
  const isMobile = useIsMobile()
  const [mobileLoading, setMobileLoading] = useState(false)
  const [gridApi, setGridApi] = useState(null)
  const [searchInput, setSearchInput] = useState('')
  const searchRef = useRef('')
  const [isSwitchChecked, setIsSwitchChecked] = useState(false)
  const [exportLoading, setExportLoading] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const [visibleColumns, setVisibleColumns] = useState(() => {
    try {
      const stored = localStorage.getItem(columnVisibilityStorageKey)
      if (stored) {
        return JSON.parse(stored)
      }
    } catch (error) {
      console.error('Error loading column visibility from localStorage:', error)
    }

    const initialVisibility = {}
    columns.forEach((col) => {
      initialVisibility[col.field] = !defaultHiddenColumns.includes(col.field)
    })
    return initialVisibility
  })
  const [allColumnsVisible, setAllColumnsVisible] = useState(() => {
    try {
      const stored = localStorage.getItem(columnVisibilityStorageKey)
      if (stored) {
        const parsedVisibility = JSON.parse(stored)
        return Object.values(parsedVisibility).every(v => v)
      }
    } catch (error) {
      console.error('Error checking column visibility:', error)
    }
    return defaultHiddenColumns.length === 0
  })
  const [isFilterModalVisible, setIsFilterModalVisible] = useState(false)
  const [filterTree, setFilterTree] = useState(defaultFilter ? defaultFilter : {})
  const [rowData, setRowData] = useState([])
  const [totalRows, setTotalRows] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const isInitialMount = useRef(true)
  const filterTreeRef = useRef({})
  const switchRef = useRef(false)
  const [showResetingFilter, setShowResetingFilter] = useState(showResetFilter)

  // Sync visibleColumns with columns prop
  useEffect(() => {
    setVisibleColumns(prevVisibility => {
      const newVisibility = { ...prevVisibility }
      let hasChanges = false

      columns.forEach(col => {
        if (!(col.field in newVisibility)) {
          newVisibility[col.field] = !defaultHiddenColumns.includes(col.field)
          hasChanges = true
        }
      })

      Object.keys(newVisibility).forEach(field => {
        if (!columns.find(col => col.field === field)) {
          delete newVisibility[field]
          hasChanges = true
        }
      })

      if (hasChanges) {
        try {
          localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility))
        } catch (error) {
          console.error('Error saving column visibility to localStorage:', error)
        }
      }

      return hasChanges ? newVisibility : prevVisibility
    })
  }, [columns, defaultHiddenColumns, columnVisibilityStorageKey])

  useEffect(() => {
    const loadStoredFilters = () => {
      try {
        let parsedFilters = null
        if (defaultFilter) {
          parsedFilters = defaultFilter
        } else if (args) {
          const nestedFormat = {
            type: 'AND',
            conditions: [
              {
                type: 'AND',
                conditions: args.map((c) => ({
                  field: c.column,
                  operator: c.operator === '=' ? 'is' : c.operator,
                  value: [String(c.value)],
                })),
              },
            ],
          }
          parsedFilters = nestedFormat
        } else {
          const stored = localStorage.getItem(storageKey)
          if (stored) {
            parsedFilters = JSON.parse(stored)
          }
        }

        if (parsedFilters && Object.keys(parsedFilters).length > 0) {
          setFilterTree(parsedFilters)
          filterTreeRef.current = parsedFilters
        }
      } catch (error) {
        console.error('Error loading stored filters:', error)
        localStorage.removeItem(storageKey)
      }
    }

    loadStoredFilters()
  }, [storageKey, args, defaultFilter])

  const hasActiveFilters = useCallback((filterTree) => {
    const refHasFilters = filterTreeRef.current &&
      Object.keys(filterTreeRef.current).length > 0 &&
      filterTreeRef.current.conditions?.length > 0

    const stateHasFilters = filterTree &&
      Object.keys(filterTree).length > 0 &&
      filterTree.conditions?.length > 0

    if (refHasFilters || stateHasFilters) {
      return true
    }

    try {
      const storedFilters = localStorage.getItem(storageKey)
      if (storedFilters) {
        const parsedFilters = JSON.parse(storedFilters)
        return parsedFilters &&
          Object.keys(parsedFilters).length > 0 &&
          parsedFilters.conditions?.length > 0
      }
    } catch {
      return false
    }
    return false
  }, [storageKey])

  const defaultColDef = useMemo(() => {
    return {
      flex: 1,
      minWidth: 100,
    }
  }, [])

  const filteredColumns = useMemo(() => {
    const columnsWithSuppressedMenu = columns.map(col => ({
      ...col,
      suppressHeaderMenuButton: true,
    }))
    return columnsWithSuppressedMenu.filter(col => visibleColumns[col.field] && col.field !== 'actions')
  }, [columns, visibleColumns])

  const actionsColumn = useMemo(() => {
    return columns.find(col => col.field === 'actions')
  }, [columns])
  const showActionsColumn = !!actionsColumn && visibleColumns.actions !== false

  const hasFilterableFields = useMemo(() => {
    const hasFilterFields = filterFields && (
      Object.keys(filterFields.DATES || {}).length > 0 ||
      Object.keys(filterFields.SELECTS || {}).length > 0 ||
      Object.keys(filterFields.RANGES || {}).length > 0
    )
    const hasFilterableColumns = columns.some((col) => col.context?.filterType)
    return hasFilterFields || hasFilterableColumns
  }, [filterFields, columns])

  const fetchData = useCallback(async (page = currentPage, sortModel = [], filterModel) => {
    if (isMobile) {
      setMobileLoading(true)
    }
    try {
      const response = await axios.post(route(routeName), {
        ...args,
        current: page,
        pageSize: pageSize,
        sort: sortModel,
        filter: filterModel,
        search: searchRef.current,
        soft_deleted: switchRef.current,
        ...customParams,
        filterTree: filterTreeRef.current,
      })
      const { data, total } = response.data
      setRowData(data || [])
      setTotalRows(total || 0)
      return { data, total }
    } catch (error) {
      handleApiError(error)
      return { data: [], total: 0 }
    } finally {
      if (isMobile) {
        setMobileLoading(false)
      }
    }
  }, [routeName, pageSize, customParams, args, isMobile, currentPage])

  const createDataSource = useCallback(() => {
    return {
      getRows: async (params) => {
        const page = params.request.startRow / pageSize + 1
        const filterModel = params.request.filterModel
        const sortModel = params.request.sortModel
        params.api.setGridOption("loading", true)

        const { data, total } = await fetchData(page, sortModel, filterModel)
        params.api.setGridOption("loading", false)
        if (!data || data.length === 0 || total === 0) {
          params.api.showNoRowsOverlay()
          params.success({ rowData: [], rowCount: 0 })
        } else {
          params.api.setGridOption("loading", false)
          params.success({ rowData: data, rowCount: total })
        }
      },
    }
  }, [fetchData, pageSize])

  const onGridReady = useCallback((params) => {
    setGridApi(params.api)
    if (params.api) {
      const datasource = createDataSource()
      params.api.setGridOption('serverSideDatasource', datasource)
    }
  }, [createDataSource])

  const refreshGrid = useCallback(() => {
    if (isMobile) {
      fetchData(currentPage, [])
    } else if (gridApi) {
      const datasource = createDataSource()
      gridApi.setGridOption('serverSideDatasource', datasource)
      gridApi.refreshServerSide({ purge: true })
    }
  }, [gridApi, createDataSource, isMobile, currentPage, fetchData])

  const handleExport = async () => {
    setExportLoading(true)
    try {
      const exportParams = {
        ...args,
        sort: [],
        filter: {},
        search: searchRef.current || '',
        soft_deleted: switchRef.current || false,
        ...customParams,
        filterTree: filterTreeRef.current || {},
        export: true,
      }
      const response = await axios.post(route(routeName), exportParams, {
        responseType: 'blob',
        headers: {
          'Accept': 'text/csv,application/octet-stream',
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
      const blob = new Blob([response.data], {
        type: response.headers['content-type'] || 'text/csv',
      })
      let filename = 'export.csv'
      const contentDisposition = response.headers['content-disposition']
      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)
        if (filenameMatch && filenameMatch[1]) {
          filename = filenameMatch[1].replace(/['"]/g, '')
        }
      }
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = filename
      link.style.display = 'none'
      document.body.appendChild(link)
      link.click()
      setTimeout(() => {
        window.URL.revokeObjectURL(url)
        document.body.removeChild(link)
      }, 100)
      message.success('Export completed successfully!')
    } catch (error) {
      console.error('Export error:', error)
      message.error('Export failed. Please try again.')
      handleApiError(error)
    } finally {
      setExportLoading(false)
    }
  }

  const handleSearchChange = (event) => {
    const value = event.target.value
    setSearchInput(value)
    searchRef.current = value
    setShowResetingFilter(true)
    setCurrentPage(1)
    refreshGrid()
  }

  const handleSwitchChange = (checked) => {
    setIsSwitchChecked(checked)
    switchRef.current = checked
    setIsActive(!checked)
    setCurrentPage(1)
    refreshGrid()
  }

  const handleFilterReset = () => {
    filterTreeRef.current = {}
    setFilterTree({})
    localStorage.removeItem(storageKey)
    localStorage.removeItem(`${moduleName}Filters`)

    if (gridApi && !isMobile) {
      gridApi.setFilterModel(null)
    }

    setCurrentPage(1)
    setShowResetingFilter(false)
    refreshGrid()
  }

  const handleReload = useCallback((resetSearch = false, resetFilters = false) => {
    if (resetSearch) {
      searchRef.current = ''
      setSearchInput('')
    }
    if (resetFilters) {
      filterTreeRef.current = {}
      setFilterTree({})
      localStorage.removeItem(storageKey)
      if (gridApi && !isMobile) {
        gridApi.setFilterModel(null)
      }
    }
    switchRef.current = false
    setIsSwitchChecked(false)
    setIsActive(true)
    setCurrentPage(1)
    refreshGrid()
  }, [gridApi, storageKey, refreshGrid, setIsActive, isMobile])

  useEffect(() => {
    if (gridRef && gridRef.current) {
      gridRef.current.reloadData = handleReload
      gridRef.current.api = gridApi
    }
  }, [gridRef, gridApi, handleReload])

  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false
      if (isMobile) {
        fetchData(1)
      } else if (gridApi) {
        refreshGrid()
      }
    }
  }, [gridApi, refreshGrid, isMobile, fetchData])

  const toggleColumnVisibility = (field) => {
    const newVisibility = {
      ...visibleColumns,
      [field]: !visibleColumns[field],
    }
    setVisibleColumns(newVisibility)
    const allVisible = Object.values(newVisibility).every(v => v)
    setAllColumnsVisible(allVisible)

    try {
      localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility))
    } catch (error) {
      console.error('Error saving column visibility to localStorage:', error)
    }
  }

  const toggleAllColumns = (checked) => {
    const newVisibility = {}
    columns.forEach(col => {
      newVisibility[col.field] = checked
    })
    setVisibleColumns(newVisibility)
    setAllColumnsVisible(checked)

    try {
      localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility))
    } catch (error) {
      console.error('Error saving column visibility to localStorage:', error)
    }
  }

  const resetColumnVisibility = () => {
    const newVisibility = {}
    columns.forEach(col => {
      newVisibility[col.field] = !defaultHiddenColumns.includes(col.field)
    })
    setVisibleColumns(newVisibility)
    setAllColumnsVisible(defaultHiddenColumns.length === 0)

    try {
      localStorage.removeItem(columnVisibilityStorageKey)
    } catch (error) {
      console.error('Error removing column visibility from localStorage:', error)
    }
  }

  const columnSettingsContent = (
    <div style={{ maxHeight: '400px', overflowY: 'auto', width: '250px' }}>
      <div style={{
        padding: '8px 12px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
      }}>
        <Checkbox
          checked={allColumnsVisible}
          onChange={(e) => toggleAllColumns(e.target.checked)}
        >
          <strong>Column Display</strong>
        </Checkbox>
        <Button
          type="text"
          onClick={resetColumnVisibility}
          size="small"
          title="Reset to default"
        >
          Reset
        </Button>
      </div>
      <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
        {columns.map((column) => (
          <div key={column.field} style={{ margin: '4px 12px' }}>
            <Checkbox
              checked={visibleColumns[column.field]}
              onChange={() => toggleColumnVisibility(column.field)}
            >
              {column.headerName || column.field}
            </Checkbox>
          </div>
        ))}
      </div>
    </div>
  )

  const FilterBadge = ({ onReset }) => (
    <Space>
      {!isMobile &&
        <Text type="secondary" style={{ fontSize: '12px' }}>
          Showing data based on your filters
        </Text>
      }
      <Button
        size="small"
        icon={<IconX size={16} />}
        onClick={onReset}
      >
        Reset
      </Button>
    </Space>
  )

  const handleMobilePrev = useCallback(() => {
    setCurrentPage((prev) => prev - 1)
    fetchData(currentPage - 1)
  }, [fetchData, currentPage])

  const handleMobileNext = useCallback(() => {
    setCurrentPage((prev) => prev + 1)
    fetchData(currentPage + 1)
  }, [fetchData, currentPage])

  return (
    <>
      <Flex
        vertical={true}
        style={{
          background: currentTheme === 'dark' ? '#191919' : '#f6f6f6',
          borderRadius: '10px',
          padding: '10px 20px',
          border: `1px solid ${token.colorBorderSecondary}`,
          margin: isMobile ? '10px' : 0
        }}
      >
        {showActions &&
          <Flex
            justify={'space-between'}
            align={'center'}
            style={{
              padding: isMobile ? '6px 0' : '10px 0',
              borderRadius: '8px 8px 0 0',
            }}
            gap={10}
            wrap={!isMobile && true}
          >
            <Input
              id="searchInput"
              value={searchInput}
              onChange={handleSearchChange}
              placeholder="Quick Search..."
              style={{ width: 250 }}
              allowClear
              onClear={() => {
                searchRef.current = ''
                setSearchInput('')
                setCurrentPage(1)
                refreshGrid()
              }}
            />
            <div>
              <Space>
                {!isMobile ? (
                  <>
                    {showResetingFilter && hasActiveFilters(filterTree) && (
                      <FilterBadge onReset={handleFilterReset} />
                    )}
                    {hasFilterableFields && (
                      <Button1
                        icon={<IconFilter size={16} />}
                        onClick={() => setIsFilterModalVisible(true)}
                        title="Filters"
                      />
                    )}
                    <Button1
                      icon={<IconReload size={16} />}
                      onClick={() => handleReload(true, true)}
                      title="Refresh"
                    />
                    {hasPermission(`access export ${moduleName}`) && (
                      <Button1
                        icon={<IconFileExport size={16} />}
                        onClick={handleExport}
                        loading={exportLoading}
                        title="Export to Excel"
                      />
                    )}
                    <Popover
                      content={columnSettingsContent}
                      title={null}
                      trigger="click"
                      placement="bottomRight"
                      overlayStyle={{ padding: 0 }}
                    >
                      <Button1
                        icon={<IconSettings size={16} />}
                        title="Column Settings"
                      />
                    </Popover>
                  </>
                ) : (
                  <Button1
                    icon={<IconMenu2 size={16} />}
                    onClick={() => setMobileMenuOpen(true)}
                    title="Options"
                  />
                )}
                {showSoftDeleted && hasPermission(`access deleted ${moduleName}`) && (
                  <Switch
                    checked={isSwitchChecked}
                    onChange={handleSwitchChange}
                    title="Toggle Option"
                    checkedChildren="Hide Deleted"
                    unCheckedChildren="Show Deleted"
                  />
                )}
              </Space>
            </div>
          </Flex>
        }

        <Drawer
          title="Table Options"
          placement="bottom"
          onClose={() => setMobileMenuOpen(false)}
          open={mobileMenuOpen}
          height="auto"
          styles={{
            body: { padding: '16px' },
          }}
        >
          <Space direction="vertical" size="middle" style={{ width: '100%' }}>
            {hasFilterableFields && (
              <Button
                block
                icon={<IconFilter size={16} />}
                onClick={() => {
                  setIsFilterModalVisible(true)
                  setMobileMenuOpen(false)
                }}
              >
                Apply Filters
              </Button>
            )}
            <Button
              block
              icon={<IconReload size={16} />}
              onClick={() => {
                handleReload(true, true)
                setMobileMenuOpen(false)
              }}
            >
              Reload Table
            </Button>
            {hasPermission(`access export ${moduleName}`) && (
              <Button
                block
                icon={<IconFileExport size={16} />}
                onClick={() => {
                  handleExport()
                  setMobileMenuOpen(false)
                }}
                loading={exportLoading}
              >
                Export to Excel
              </Button>
            )}
            <Popover
              content={columnSettingsContent}
              title={null}
              trigger="click"
              placement="top"
              overlayStyle={{ padding: 0 }}
              onOpenChange={(visible) => {
                if (!visible) setMobileMenuOpen(false)
              }}
            >
              <Button
                block
                icon={<IconSettings size={16} />}
              >
                Column Settings
              </Button>
            </Popover>
          </Space>
        </Drawer>

        {isMobile ? (
          <DataGridTableMobileView
            rowData={rowData}
            filteredColumns={filteredColumns}
            actionsColumn={actionsColumn}
            showActionsColumn={showActionsColumn}
            token={token}
            theme={currentTheme}
            pagination={pagination}
            totalRows={totalRows}
            pageSize={pageSize}
            currentPage={currentPage}
            mobileLoading={mobileLoading}
            onPrevPage={handleMobilePrev}
            onNextPage={handleMobileNext}
          />
        ) : (
          <div style={{ width: '100%', height: height ? height : '76vh' }}>
            <AgGridReact
              theme={'legacy'}
              className={themeClass}
              ref={gridRef}
              defaultColDef={defaultColDef}
              rowHeight={rowHeight}
              columnDefs={filteredColumns.concat(actionsColumn ? [actionsColumn] : [])}
              pagination={pagination}
              paginationPageSize={pageSize}
              rowModelType="serverSide"
              cacheBlockSize={pageSize}
              paginationAutoPageSize={paginationAutoPageSize}
              onGridReady={onGridReady}
              suppressPaginationPanel={suppressPaginationPanel}
              enableCellTextSelection={enableCellTextSelection}
              overlayNoRowsTemplate={`<span style="padding: 10px; background-color: ${token.colorBorderSecondary}; border: 1px solid ${token.colorBorder};">No data found</span>`}
            />
          </div>
        )}

        {hasFilterableFields && (
          <GlobalFilter
            visible={isFilterModalVisible}
            handleCancel={() => setIsFilterModalVisible(false)}
            onApplyFilters={(filters) => {
              searchRef.current = ''
              setSearchInput('')
              filterTreeRef.current = filters
              setFilterTree(filters)
              setIsFilterModalVisible(false)
              setShowResetingFilter(true)
              setCurrentPage(1)
              try {
                if (filters && Object.keys(filters).length > 0) {
                  localStorage.setItem(storageKey, JSON.stringify(filters))
                } else {
                  localStorage.removeItem(storageKey)
                }
              } catch (error) {
                console.error('Error saving filters to localStorage:', error)
              }
              refreshGrid()
            }}
            columns={columns}
            filterFields={filterFields}
            moduleName={moduleName}
            storageKey={storageKey}
            initialFilters={filterTree}
          />
        )}
      </Flex>
    </>
  )
}

export default DataGridTable
