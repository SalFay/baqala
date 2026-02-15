/**
 * ProDataTable - Enhanced DataTable Component (SparkCRM Pattern)
 *
 * Features:
 * - Debounced search (250ms)
 * - Soft-delete toggle
 * - CSV export with blob download
 * - Permission-based visibility
 * - Alternating row colors
 * - Custom header/cell styling
 * - External data source support (Inertia.js)
 * - React Query integration (optional)
 */

import { useState, useCallback, useMemo, useEffect } from 'react';
import {
    Table,
    Input,
    Button,
    Space,
    Switch,
    Tooltip,
    Flex,
    Typography,
    theme,
} from 'antd';
import {
    SearchOutlined,
    ReloadOutlined,
    DeleteOutlined,
} from '@ant-design/icons';
import { IconFileExport } from '@tabler/icons-react';
import { useRecoilValue } from 'recoil';
import { debounce } from 'lodash';
import axios from 'axios';
import { themeAtom } from '@/Helpers/atoms/uiAtom';

const { Text } = Typography;

export default function ProDataTable({
    columns,
    routeName,
    exportRouteName,
    rowKey = 'id',
    title = '',
    toolBar,
    paginationSize = 25,
    customParams = {},
    showToolbar = true,
    args = {},
    tagType = '',
    handleRowClick = () => {},
    // External data source (for Inertia.js)
    dataSource: externalDataSource,
    loading: externalLoading,
    pagination: externalPagination,
    onChange: externalOnChange,
    // Permission checks
    searchPermission,
    deletePermission,
    exportPermission,
    permissions = {},
    ...props
}) {
    const { token } = theme.useToken();
    const currentTheme = useRecoilValue(themeAtom);
    const isDark = currentTheme === 'dark';

    const [loading, setLoading] = useState(false);
    const [data, setData] = useState([]);
    const [total, setTotal] = useState(0);
    const [tableParams, setTableParams] = useState({ soft_deleted: false });
    const [pagination, setPagination] = useState({
        current: 1,
        pageSize: paginationSize,
        total: 0,
    });

    // Module name for permissions
    const moduleName = routeName ? routeName.split('.')[0].replace(/-/g, ' ') : '';

    // Check permissions
    const hasPermission = useCallback(
        (permission) => {
            if (!permission) return true;
            if (typeof permissions === 'function') {
                return permissions(permission);
            }
            return permissions[permission] === true;
        },
        [permissions]
    );

    const canSearch = hasPermission(searchPermission);
    const canDelete = hasPermission(deletePermission);
    const canExport = hasPermission(exportPermission);

    // Debounced search handler
    const handleSearch = useMemo(
        () =>
            debounce((e) => {
                const searchValue = e.target.value;
                setTableParams((prev) => ({
                    ...prev,
                    search: searchValue,
                }));
                setPagination((prev) => ({ ...prev, current: 1 }));
            }, 250),
        []
    );

    // Debounced soft-delete toggle
    const handleSwitchChange = useMemo(
        () =>
            debounce((checked) => {
                setTableParams((prev) => ({ ...prev, soft_deleted: checked }));
                setPagination((prev) => ({ ...prev, current: 1 }));
            }, 250),
        []
    );

    // Fetch data for internal data source
    const fetchData = useCallback(
        async (params, sort = {}, filter = {}) => {
            if (externalDataSource || !routeName) return;

            try {
                setLoading(true);
                const response = await axios.post(route(routeName, { page: params.current }), {
                    ...args,
                    ...params,
                    sort,
                    filter,
                });
                setData(response.data.data);
                setTotal(response.data.meta?.total || 0);
                setPagination((prev) => ({
                    ...prev,
                    total: response.data.meta?.total || 0,
                }));
            } catch (error) {
                console.error('Failed to fetch data:', error);
            } finally {
                setLoading(false);
            }
        },
        [routeName, args, externalDataSource]
    );

    // Export data functionality
    const exportData = useCallback(
        async (params = {}) => {
            if (!exportRouteName) return;

            try {
                setLoading(true);
                const response = await axios.get(route(exportRouteName), {
                    params,
                    responseType: 'blob',
                });

                const fileName = `${moduleName || 'export'} export.csv`;
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                link.click();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Export failed:', error);
            } finally {
                setLoading(false);
            }
        },
        [exportRouteName, moduleName]
    );

    // Fetch data when params change (for internal data source)
    useEffect(() => {
        if (!externalDataSource && routeName) {
            fetchData({
                ...tableParams,
                current: pagination.current,
                pageSize: pagination.pageSize,
            });
        }
    }, [tableParams, pagination.current, pagination.pageSize, fetchData, externalDataSource, routeName]);

    // Handle tag type change
    useEffect(() => {
        if (tagType) {
            setTableParams((prev) => ({
                ...prev,
                taxonomy: tagType,
            }));
        }
    }, [tagType]);

    // Determine data source
    const tableData = externalDataSource || data;
    const tableTotal = externalDataSource
        ? externalPagination?.total || tableData.length
        : total;
    const isLoading = externalLoading ?? loading;

    // Handle table change
    const handleTableChange = useCallback(
        (newPagination, filters, sorter) => {
            if (!externalDataSource) {
                setPagination({
                    current: newPagination.current,
                    pageSize: newPagination.pageSize,
                    total: newPagination.total,
                });
            }
            externalOnChange?.(newPagination, filters, sorter);
        },
        [externalDataSource, externalOnChange]
    );

    return (
        <div
            style={{
                border: `1px solid ${token.colorBorder}`,
                margin: 14,
                padding: 0,
                borderRadius: '8px',
            }}
        >
            {/* Custom Toolbar */}
            {showToolbar && (
                <Flex
                    justify="space-between"
                    align="center"
                    style={{
                        padding: '8px 16px',
                        borderBottom: `1px solid ${token.colorBorder}`,
                        backgroundColor: token.colorBgLayout,
                    }}
                >
                    <Space>
                        <Text style={{ fontSize: '16px', fontWeight: '500' }}>{title}</Text>
                        {canSearch && (
                            <Input
                                allowClear
                                placeholder="Search..."
                                onChange={handleSearch}
                                size="middle"
                                style={{ width: '200px' }}
                                prefix={<SearchOutlined />}
                            />
                        )}
                    </Space>

                    <Space>
                        {canDelete && (
                            <Switch
                                defaultChecked={false}
                                checkedChildren="Hide Deleted"
                                unCheckedChildren="Show Deleted"
                                onChange={handleSwitchChange}
                            />
                        )}
                        {canExport && exportRouteName && (
                            <Tooltip title={`Export ${moduleName}`}>
                                <Button
                                    icon={<IconFileExport size={18} />}
                                    type="text"
                                    onClick={() => exportData(tableParams)}
                                />
                            </Tooltip>
                        )}
                        {toolBar}
                    </Space>
                </Flex>
            )}

            <Table
                loading={isLoading}
                columns={columns}
                dataSource={tableData}
                rowKey={rowKey}
                pagination={{
                    ...pagination,
                    total: tableTotal,
                    showSizeChanger: true,
                    pageSizeOptions: [10, 25, 50, 100],
                    onChange: (page, pageSize) =>
                        setPagination({ current: page, pageSize, total: tableTotal }),
                    style: {
                        padding: '10px 10px 0px 0px',
                        borderTop: `1px solid ${token.colorBorder}`,
                    },
                    ...(externalPagination || {}),
                }}
                onChange={handleTableChange}
                scroll={{ x: true, scrollToFirstRowOnChange: true }}
                onRow={(record, index) => ({
                    onClick: (e) => {
                        if (e.target.tagName !== 'TD') return;
                        handleRowClick(record, e);
                    },
                    style: {
                        background: index % 2 === 1 ? 'transparent' : token.colorBgLayout,
                    },
                })}
                components={{
                    header: {
                        cell: (cellProps) => (
                            <th
                                {...cellProps}
                                style={{
                                    ...cellProps.style,
                                    backgroundColor: token.colorBgBase || '#f5f5f5',
                                    borderBottom: `1px solid ${token.colorBorder}`,
                                    padding: '7px 6px',
                                }}
                            />
                        ),
                    },
                    body: {
                        row: (rowProps) => (
                            <tr
                                {...rowProps}
                                style={{
                                    ...rowProps.style,
                                    borderBottom: 'none',
                                }}
                            />
                        ),
                        cell: (cellProps) => (
                            <td
                                {...cellProps}
                                style={{
                                    ...cellProps.style,
                                    borderBottom: 'none',
                                    padding: '8px 8px',
                                }}
                            />
                        ),
                    },
                }}
                bordered={false}
                size="large"
                {...props}
            />
        </div>
    );
}

// Export a refresh utility for external use
ProDataTable.refresh = (routeName) => {
    // Dispatch a custom event that components can listen to
    window.dispatchEvent(new CustomEvent('prodatatable-refresh', { detail: { routeName } }));
};
