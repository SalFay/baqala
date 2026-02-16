import { useRef, useCallback, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Button,
  Tag,
} from 'antd';
import { EyeOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import { DataGridTable } from '../../Components/DataGridTable';
import { orderService } from '../../api/services/order.service';
import {
  ORDER_STATUS_COLORS,
  PAYMENT_STATUS_COLORS,
  DATE_FORMATS,
  formatCurrency,
} from '../../constants';

export default function OrdersPage() {
  const navigate = useNavigate();
  const gridRef = useRef(null);

  // Fetch data for grid
  const fetchData = useCallback(async (params) => {
    // Build filters from GlobalFilter conditions
    const apiParams = {
      page: params.page,
      per_page: params.per_page,
      search: params.search,
    };

    // Handle filter conditions
    if (params.filters?.conditions) {
      params.filters.conditions.forEach(condition => {
        if (condition.field === 'status') {
          apiParams.status = condition.value;
        } else if (condition.field === 'payment_status') {
          apiParams.payment_status = condition.value;
        } else if (condition.field === 'created_at') {
          if (condition.operator === 'between' && condition.value) {
            apiParams.from_date = condition.value[0]?.format?.(DATE_FORMATS.API) || condition.value[0];
            apiParams.to_date = condition.value[1]?.format?.(DATE_FORMATS.API) || condition.value[1];
          } else if (condition.operator === 'after') {
            apiParams.from_date = condition.value;
          } else if (condition.operator === 'before') {
            apiParams.to_date = condition.value;
          }
        }
      });
    }

    const result = await orderService.getOrders(apiParams);
    return {
      data: result.data || result,
      total: result.total || (result.data?.length || 0),
    };
  }, []);

  // Column definitions for AG Grid (filterType is used by GlobalFilter)
  const columns = useMemo(() => [
    {
      field: 'order_number',
      headerName: 'Order #',
      minWidth: 120,
      flex: 1,
      filterType: 'text',
      cellRenderer: (params) => (
        <Button
          type="link"
          style={{ padding: 0 }}
          onClick={() => navigate(`/orders/${params.data.id}`)}
        >
          {params.value}
        </Button>
      ),
    },
    {
      field: 'customer',
      headerName: 'Customer',
      minWidth: 180,
      flex: 2,
      filterType: 'text',
      valueGetter: (params) => params.data.customer?.full_name || 'Walk-in',
    },
    {
      field: 'status',
      headerName: 'Status',
      minWidth: 120,
      flex: 1,
      cellRenderer: (params) => (
        <Tag color={ORDER_STATUS_COLORS[params.value] || 'default'}>
          {params.value?.toUpperCase()}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: [
        { value: 'pending', label: 'Pending' },
        { value: 'completed', label: 'Completed' },
        { value: 'cancelled', label: 'Cancelled' },
        { value: 'refunded', label: 'Refunded' },
      ],
    },
    {
      field: 'payment_status',
      headerName: 'Payment',
      minWidth: 120,
      flex: 1,
      cellRenderer: (params) => (
        <Tag color={PAYMENT_STATUS_COLORS[params.value] || 'default'}>
          {params.value?.toUpperCase()}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: [
        { value: 'pending', label: 'Pending' },
        { value: 'paid', label: 'Paid' },
        { value: 'partial', label: 'Partial' },
        { value: 'refunded', label: 'Refunded' },
      ],
    },
    {
      field: 'total',
      headerName: 'Total',
      minWidth: 120,
      flex: 1,
      valueFormatter: (params) => formatCurrency(params.value),
      filterType: 'number',
    },
    {
      field: 'created_at',
      headerName: 'Date',
      minWidth: 160,
      flex: 1,
      valueFormatter: (params) => dayjs(params.value).format(DATE_FORMATS.DISPLAY_WITH_TIME),
      filterType: 'date',
    },
  ], [navigate]);

  // Actions column
  const actionsColumn = useMemo(() => ({
    field: 'actions',
    headerName: '',
    minWidth: 60,
    maxWidth: 60,
    sortable: false,
    cellRenderer: (params) => (
      <Button
        type="text"
        icon={<EyeOutlined />}
        onClick={() => navigate(`/orders/${params.data.id}`)}
      />
    ),
  }), [navigate]);

  return (
    <div style={{ padding: 24 }}>
      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        actionsColumn={actionsColumn}
        fetchData={fetchData}
        title="Orders"
        searchPlaceholder="Search orders..."
        height={600}
        pageSize={20}
      />
    </div>
  );
}
