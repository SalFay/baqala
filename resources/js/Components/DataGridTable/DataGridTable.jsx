import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { AgGridReact } from 'ag-grid-react';
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-alpine.css';
import {
  ModuleRegistry,
  ClientSideRowModelModule,
  NumberFilterModule,
  TextFilterModule,
  ValidationModule,
} from 'ag-grid-community';
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
  Badge,
} from 'antd';
import {
  FilterOutlined,
  ReloadOutlined,
  SettingOutlined,
  MenuOutlined,
  CloseOutlined,
  DownloadOutlined,
  PlusOutlined,
  SearchOutlined,
} from '@ant-design/icons';
import { useThemeStore } from '../../Helpers/atom';
import GlobalFilter from '../GlobalFilter/GlobalFilter';
import * as XLSX from 'xlsx';

const { Text } = Typography;
const { useToken } = theme;

// Register AG Grid Community modules (required for v35+)
ModuleRegistry.registerModules([
  ClientSideRowModelModule,
  TextFilterModule,
  NumberFilterModule,
  ValidationModule,
]);

/**
 * DataGridTable - AG Grid wrapper component (SparkCRM pattern)
 *
 * Features:
 * - Column visibility with localStorage persistence
 * - Export to Excel
 * - Global filter integration
 * - Mobile responsive with drawer
 * - Theme-aware styling (light/dark)
 * - Soft deleted toggle
 */
const DataGridTable = ({
  gridRef,
  columns = [],
  fetchData,
  pageSize = 20,
  pagination = true,
  height = '70vh',
  title = 'Data',
  onAdd,
  addButtonText = 'Add New',
  searchPlaceholder = 'Search...',
  showSoftDeleted = false,
  filterFields,
  defaultHiddenColumns = [],
  actionsColumn,
  showSearch = true,
  showFilters = true,
  showExport = true,
  showColumnSettings = true,
  showActions = true,
  customParams = {},
  instanceId,
  rowHeight = 48,
}) => {
  const { theme: currentTheme } = useThemeStore();
  const { token } = useToken();
  const themeClass = currentTheme === 'light' ? 'ag-theme-alpine' : 'ag-theme-alpine-dark';
  const moduleName = instanceId || title.toLowerCase().replace(/\s+/g, '-');
  const storageKey = `${moduleName}Filters`;
  const columnVisibilityStorageKey = `${moduleName}ColumnVisibility`;
  const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;

  const internalGridRef = useRef(null);
  const [gridApi, setGridApi] = useState(null);
  const [rowData, setRowData] = useState([]);
  const [totalRows, setTotalRows] = useState(0);
  const [loading, setLoading] = useState(false);
  const [searchInput, setSearchInput] = useState('');
  const searchRef = useRef('');
  const [isSwitchChecked, setIsSwitchChecked] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [isFilterModalVisible, setIsFilterModalVisible] = useState(false);
  const [filterTree, setFilterTree] = useState({});
  const filterTreeRef = useRef({});
  const [currentPage, setCurrentPage] = useState(1);
  const searchTimeoutRef = useRef(null);

  // Column visibility state with localStorage persistence
  const [visibleColumns, setVisibleColumns] = useState(() => {
    try {
      const stored = localStorage.getItem(columnVisibilityStorageKey);
      if (stored) return JSON.parse(stored);
    } catch (error) {
      console.error('Error loading column visibility:', error);
    }
    const initialVisibility = {};
    columns.forEach((col) => {
      initialVisibility[col.field] = !defaultHiddenColumns.includes(col.field);
    });
    return initialVisibility;
  });

  const [allColumnsVisible, setAllColumnsVisible] = useState(() => {
    return Object.values(visibleColumns).every((v) => v);
  });

  // Sync visibleColumns with columns prop
  useEffect(() => {
    setVisibleColumns((prevVisibility) => {
      const newVisibility = { ...prevVisibility };
      let hasChanges = false;

      columns.forEach((col) => {
        if (!(col.field in newVisibility)) {
          newVisibility[col.field] = !defaultHiddenColumns.includes(col.field);
          hasChanges = true;
        }
      });

      Object.keys(newVisibility).forEach((field) => {
        if (!columns.find((col) => col.field === field)) {
          delete newVisibility[field];
          hasChanges = true;
        }
      });

      if (hasChanges) {
        try {
          localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility));
        } catch (error) {
          console.error('Error saving column visibility:', error);
        }
      }

      return hasChanges ? newVisibility : prevVisibility;
    });
  }, [columns, defaultHiddenColumns, columnVisibilityStorageKey]);

  // Load stored filters
  useEffect(() => {
    try {
      const stored = localStorage.getItem(storageKey);
      if (stored) {
        const parsedFilters = JSON.parse(stored);
        if (parsedFilters && Object.keys(parsedFilters).length > 0) {
          setFilterTree(parsedFilters);
          filterTreeRef.current = parsedFilters;
        }
      }
    } catch (error) {
      console.error('Error loading stored filters:', error);
      localStorage.removeItem(storageKey);
    }
  }, [storageKey]);

  // Build filter fields from columns if not provided
  const resolvedFilterFields = useMemo(() => {
    if (filterFields && Object.keys(filterFields).length > 0) return filterFields;

    // Auto-generate filter fields from columns
    return columns
      .filter((col) => col.field && col.field !== 'actions' && col.filterType)
      .map((col) => ({
        field: col.field,
        label: col.headerName || col.field,
        filterType: col.filterType || 'text',
        options: col.filterOptions || [],
      }));
  }, [filterFields, columns]);

  // Check if has filterable fields
  const hasFilterableFields = useMemo(() => {
    if (Array.isArray(resolvedFilterFields)) {
      return resolvedFilterFields.length > 0;
    }
    return (
      Object.keys(resolvedFilterFields.DATES || {}).length > 0 ||
      Object.keys(resolvedFilterFields.SELECTS || {}).length > 0 ||
      Object.keys(resolvedFilterFields.RANGES || {}).length > 0 ||
      Object.keys(resolvedFilterFields.TEXTS || {}).length > 0
    );
  }, [resolvedFilterFields]);

  // Default column settings
  const defaultColDef = useMemo(
    () => ({
      sortable: true,
      resizable: true,
      filter: false,
      flex: 1,
      minWidth: 100,
    }),
    []
  );

  // Filter columns by visibility
  const filteredColumns = useMemo(() => {
    return columns
      .filter((col) => visibleColumns[col.field] !== false && col.field !== 'actions')
      .map((col) => ({ ...col, suppressHeaderMenuButton: true }));
  }, [columns, visibleColumns]);

  // Combine columns with actions column
  const allColumns = useMemo(() => {
    const cols = [...filteredColumns];
    if (actionsColumn && visibleColumns.actions !== false) {
      cols.push(actionsColumn);
    }
    return cols;
  }, [filteredColumns, actionsColumn, visibleColumns]);

  // Fetch data function
  const loadData = useCallback(
    async (page = 1, search = '', filters = null) => {
      if (!fetchData) return;

      setLoading(true);
      try {
        const params = {
          page,
          per_page: pageSize,
          search: search || undefined,
          soft_deleted: isSwitchChecked,
          filterTree: filters || filterTreeRef.current,
          ...customParams,
        };

        const result = await fetchData(params);
        setRowData(result.data || []);
        setTotalRows(result.total || 0);
      } catch (error) {
        console.error('DataGridTable fetch error:', error);
        message.error('Failed to load data');
        setRowData([]);
        setTotalRows(0);
      } finally {
        setLoading(false);
      }
    },
    [fetchData, pageSize, customParams, isSwitchChecked]
  );

  // Initial load
  useEffect(() => {
    loadData(1, '', filterTreeRef.current);
  }, []);

  // Refresh function exposed via ref
  const handleReload = useCallback(
    (resetSearch = false, resetFilters = false) => {
      if (resetSearch) {
        searchRef.current = '';
        setSearchInput('');
      }
      if (resetFilters) {
        filterTreeRef.current = {};
        setFilterTree({});
        localStorage.removeItem(storageKey);
      }
      setCurrentPage(1);
      loadData(1, resetSearch ? '' : searchRef.current, resetFilters ? {} : filterTreeRef.current);
    },
    [loadData, storageKey]
  );

  // Expose reload to parent via ref
  useEffect(() => {
    const ref = gridRef || internalGridRef;
    if (ref) {
      ref.current = {
        reloadData: handleReload,
        api: gridApi,
        clearFilters: () => {
          filterTreeRef.current = {};
          setFilterTree({});
          localStorage.removeItem(storageKey);
          loadData(currentPage, searchRef.current, {});
        },
      };
    }
  }, [gridRef, gridApi, handleReload, storageKey, loadData, currentPage]);

  // Check if has active filters
  const hasActiveFilters = useMemo(() => {
    return filterTree && Object.keys(filterTree).length > 0 && filterTree.conditions?.length > 0;
  }, [filterTree]);

  // Grid ready handler
  const onGridReady = useCallback((params) => {
    setGridApi(params.api);
  }, []);

  // Handle search with debounce
  const handleSearchChange = useCallback(
    (e) => {
      const value = e.target.value;
      setSearchInput(value);
      searchRef.current = value;

      if (searchTimeoutRef.current) {
        clearTimeout(searchTimeoutRef.current);
      }
      searchTimeoutRef.current = setTimeout(() => {
        setCurrentPage(1);
        loadData(1, value, filterTreeRef.current);
      }, 300);
    },
    [loadData]
  );

  // Soft deleted toggle
  const handleSwitchChange = useCallback(
    (checked) => {
      setIsSwitchChecked(checked);
      setCurrentPage(1);
      loadData(1, searchRef.current, filterTreeRef.current);
    },
    [loadData]
  );

  // Filter reset
  const handleFilterReset = useCallback(() => {
    filterTreeRef.current = {};
    setFilterTree({});
    localStorage.removeItem(storageKey);
    setCurrentPage(1);
    loadData(1, searchRef.current, {});
  }, [loadData, storageKey]);

  // Export to Excel
  const handleExport = useCallback(() => {
    if (rowData.length === 0) {
      message.warning('No data to export');
      return;
    }
    try {
      const exportData = rowData.map((row) => {
        const exportRow = {};
        filteredColumns.forEach((col) => {
          if (col.field && col.field !== 'actions') {
            exportRow[col.headerName || col.field] = row[col.field];
          }
        });
        return exportRow;
      });
      const worksheet = XLSX.utils.json_to_sheet(exportData);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, title);
      XLSX.writeFile(workbook, `${title.replace(/\s+/g, '_')}_export.xlsx`);
      message.success('Export completed successfully!');
    } catch (error) {
      console.error('Export error:', error);
      message.error('Export failed');
    }
  }, [rowData, filteredColumns, title]);

  // Handle pagination
  const handlePageChange = useCallback(
    (newPage) => {
      setCurrentPage(newPage);
      loadData(newPage, searchRef.current, filterTreeRef.current);
    },
    [loadData]
  );

  // Column visibility toggle
  const toggleColumnVisibility = useCallback(
    (field) => {
      const newVisibility = { ...visibleColumns, [field]: !visibleColumns[field] };
      setVisibleColumns(newVisibility);
      setAllColumnsVisible(Object.values(newVisibility).every((v) => v));
      try {
        localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility));
      } catch (error) {
        console.error('Error saving column visibility:', error);
      }
    },
    [visibleColumns, columnVisibilityStorageKey]
  );

  const toggleAllColumns = useCallback(
    (checked) => {
      const newVisibility = {};
      columns.forEach((col) => {
        newVisibility[col.field] = checked;
      });
      setVisibleColumns(newVisibility);
      setAllColumnsVisible(checked);
      try {
        localStorage.setItem(columnVisibilityStorageKey, JSON.stringify(newVisibility));
      } catch (error) {
        console.error('Error saving column visibility:', error);
      }
    },
    [columns, columnVisibilityStorageKey]
  );

  const resetColumnVisibility = useCallback(() => {
    const newVisibility = {};
    columns.forEach((col) => {
      newVisibility[col.field] = !defaultHiddenColumns.includes(col.field);
    });
    setVisibleColumns(newVisibility);
    setAllColumnsVisible(defaultHiddenColumns.length === 0);
    localStorage.removeItem(columnVisibilityStorageKey);
  }, [columns, defaultHiddenColumns, columnVisibilityStorageKey]);

  // Calculate pagination info
  const totalPages = Math.ceil(totalRows / pageSize);
  const startRow = totalRows === 0 ? 0 : (currentPage - 1) * pageSize + 1;
  const endRow = Math.min(currentPage * pageSize, totalRows);
  const filterCount = filterTree?.conditions?.length || 0;

  // Column settings content
  const columnSettingsContent = (
    <div style={{ maxHeight: '400px', overflowY: 'auto', width: '250px' }}>
      <div
        style={{
          padding: '8px 12px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
        }}
      >
        <Checkbox checked={allColumnsVisible} onChange={(e) => toggleAllColumns(e.target.checked)}>
          <strong>Column Display</strong>
        </Checkbox>
        <Button type="text" onClick={resetColumnVisibility} size="small">
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
  );

  // Filter badge
  const FilterBadge = ({ onReset }) => (
    <Space>
      {!isMobile && (
        <Text type="secondary" style={{ fontSize: '12px' }}>
          Filters active
        </Text>
      )}
      <Button size="small" icon={<CloseOutlined />} onClick={onReset}>
        Reset
      </Button>
    </Space>
  );

  return (
    <Flex
      vertical
      style={{
        background: currentTheme === 'dark' ? '#191919' : '#f6f6f6',
        borderRadius: '10px',
        padding: isMobile ? '10px' : '10px 20px',
        border: `1px solid ${token.colorBorderSecondary}`,
      }}
    >
      {/* Toolbar */}
      {showActions && (
        <Flex
          justify="space-between"
          align="center"
          style={{ padding: isMobile ? '6px 0' : '10px 0', borderRadius: '8px 8px 0 0' }}
          gap={10}
          wrap={!isMobile}
        >
          {/* Left side - Search */}
          {showSearch && (
            <Input
              value={searchInput}
              onChange={handleSearchChange}
              placeholder={searchPlaceholder}
              prefix={<SearchOutlined />}
              style={{ width: isMobile ? '100%' : 250 }}
              allowClear
              onClear={() => {
                searchRef.current = '';
                setSearchInput('');
                setCurrentPage(1);
                loadData(1, '', filterTreeRef.current);
              }}
            />
          )}

          {/* Right side - Actions */}
          <div>
            <Space wrap>
              {hasActiveFilters && <FilterBadge onReset={handleFilterReset} />}

              {!isMobile ? (
                <>
                  {showFilters && hasFilterableFields && (
                    <Badge count={filterCount} size="small">
                      <Button icon={<FilterOutlined />} onClick={() => setIsFilterModalVisible(true)}>
                        Filter
                      </Button>
                    </Badge>
                  )}
                  <Button icon={<ReloadOutlined />} onClick={() => handleReload(true, true)} loading={loading}>
                    Refresh
                  </Button>
                  {showExport && (
                    <Button icon={<DownloadOutlined />} onClick={handleExport}>
                      Export
                    </Button>
                  )}
                  {showColumnSettings && (
                    <Popover
                      content={columnSettingsContent}
                      title={null}
                      trigger="click"
                      placement="bottomRight"
                    >
                      <Button icon={<SettingOutlined />} />
                    </Popover>
                  )}
                  {onAdd && (
                    <Button type="primary" icon={<PlusOutlined />} onClick={onAdd}>
                      {addButtonText}
                    </Button>
                  )}
                </>
              ) : (
                <>
                  {onAdd && (
                    <Button type="primary" icon={<PlusOutlined />} onClick={onAdd}>
                      Add
                    </Button>
                  )}
                  <Button icon={<MenuOutlined />} onClick={() => setMobileMenuOpen(true)} />
                </>
              )}

              {showSoftDeleted && (
                <Switch
                  checked={isSwitchChecked}
                  onChange={handleSwitchChange}
                  checkedChildren="Hide Deleted"
                  unCheckedChildren="Show Deleted"
                />
              )}
            </Space>
          </div>
        </Flex>
      )}

      {/* Mobile Menu Drawer */}
      <Drawer
        title="Table Options"
        placement="bottom"
        onClose={() => setMobileMenuOpen(false)}
        open={mobileMenuOpen}
        height="auto"
        styles={{ body: { padding: '16px' } }}
      >
        <Space direction="vertical" size="middle" style={{ width: '100%' }}>
          {showFilters && hasFilterableFields && (
            <Button
              block
              icon={<FilterOutlined />}
              onClick={() => {
                setIsFilterModalVisible(true);
                setMobileMenuOpen(false);
              }}
            >
              Apply Filters
            </Button>
          )}
          <Button
            block
            icon={<ReloadOutlined />}
            onClick={() => {
              handleReload(true, true);
              setMobileMenuOpen(false);
            }}
          >
            Reload Table
          </Button>
          {showExport && (
            <Button
              block
              icon={<DownloadOutlined />}
              onClick={() => {
                handleExport();
                setMobileMenuOpen(false);
              }}
            >
              Export to Excel
            </Button>
          )}
          {showColumnSettings && (
            <Popover content={columnSettingsContent} title={null} trigger="click" placement="top">
              <Button block icon={<SettingOutlined />}>
                Column Settings
              </Button>
            </Popover>
          )}
        </Space>
      </Drawer>

      {/* AG Grid */}
      <div style={{ width: '100%', height }}>
        <AgGridReact
          theme="legacy"
          className={themeClass}
          ref={gridRef || internalGridRef}
          rowData={rowData}
          columnDefs={allColumns}
          defaultColDef={defaultColDef}
          rowHeight={rowHeight}
          headerHeight={48}
          onGridReady={onGridReady}
          suppressCellFocus={true}
          animateRows={true}
          domLayout="normal"
          loading={loading}
          overlayNoRowsTemplate={`<span style="padding: 10px; background-color: ${token.colorBgContainer}; border: 1px solid ${token.colorBorder}; border-radius: 4px;">No data found</span>`}
          overlayLoadingTemplate='<span style="padding: 10px;">Loading...</span>'
        />
      </div>

      {/* Pagination */}
      {pagination && (
        <Flex justify="space-between" align="center" style={{ marginTop: 16 }}>
          <Text type="secondary">
            {totalRows === 0 ? 'No entries' : `Showing ${startRow} to ${endRow} of ${totalRows} entries`}
          </Text>
          {totalRows > 0 && (
            <Space>
              <Button disabled={currentPage === 1} onClick={() => handlePageChange(currentPage - 1)}>
                Previous
              </Button>
              <Text>
                Page {currentPage} of {totalPages}
              </Text>
              <Button disabled={currentPage >= totalPages} onClick={() => handlePageChange(currentPage + 1)}>
                Next
              </Button>
            </Space>
          )}
        </Flex>
      )}

      {/* Global Filter Modal */}
      {showFilters && hasFilterableFields && (
        <GlobalFilter
          visible={isFilterModalVisible}
          onCancel={() => setIsFilterModalVisible(false)}
          onApply={(filters) => {
            searchRef.current = '';
            setSearchInput('');
            filterTreeRef.current = filters || {};
            setFilterTree(filters || {});
            setIsFilterModalVisible(false);
            setCurrentPage(1);
            try {
              if (filters && Object.keys(filters).length > 0) {
                localStorage.setItem(storageKey, JSON.stringify(filters));
              } else {
                localStorage.removeItem(storageKey);
              }
            } catch (error) {
              console.error('Error saving filters:', error);
            }
            loadData(1, '', filters || {});
          }}
          filterFields={Array.isArray(resolvedFilterFields) ? resolvedFilterFields : []}
          initialFilters={filterTree}
          title={`Filter ${title}`}
        />
      )}
    </Flex>
  );
};

export default DataGridTable;
