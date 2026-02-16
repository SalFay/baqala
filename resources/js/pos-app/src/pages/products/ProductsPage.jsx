import { useRef, useCallback, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Button,
  Space,
  Tag,
  message,
  Popconfirm,
  Image,
} from 'antd';
import { EditOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import { DataGridTable } from '../../Components/DataGridTable';
import { productService } from '../../api/services/product.service';
import { PRODUCT_TYPE_COLORS, STATUS_COLORS, formatCurrency } from '../../constants';

export default function ProductsPage() {
  const navigate = useNavigate();
  const gridRef = useRef(null);

  // Fetch data for grid
  const fetchData = useCallback(async (params) => {
    const result = await productService.getProducts({
      page: params.page,
      per_page: params.per_page,
      search: params.search,
      ...params.filters,
    });
    return {
      data: result.data || result,
      total: result.total || (result.data?.length || 0),
    };
  }, []);

  // Refresh grid
  const handleRefresh = useCallback(() => {
    if (gridRef.current?.reloadData) {
      gridRef.current.reloadData();
    }
  }, []);

  // Delete product
  const handleDelete = useCallback(async (id) => {
    try {
      await productService.deleteProduct(id);
      message.success('Product deleted successfully');
      handleRefresh();
    } catch (error) {
      message.error(error.response?.data?.message || 'Delete failed');
    }
  }, [handleRefresh]);

  // Column definitions for AG Grid (filterType is used by GlobalFilter)
  const columns = useMemo(() => [
    {
      field: 'image',
      headerName: '',
      minWidth: 70,
      maxWidth: 70,
      sortable: false,
      cellRenderer: (params) => (
        <Image
          src={params.value}
          alt="Product"
          width={40}
          height={40}
          style={{ objectFit: 'cover', borderRadius: 4 }}
          fallback="/assets/no-prod-image.jpg"
          preview={false}
        />
      ),
    },
    {
      field: 'name',
      headerName: 'Product',
      minWidth: 200,
      flex: 2,
      filterType: 'text',
      cellRenderer: (params) => (
        <div>
          <div>{params.value}</div>
          {params.data.sku && (
            <Tag color="default" style={{ fontSize: 10 }}>
              {params.data.sku}
            </Tag>
          )}
        </div>
      ),
    },
    {
      field: 'category',
      headerName: 'Category',
      minWidth: 140,
      flex: 1,
      filterType: 'text',
      valueGetter: (params) => params.data.category?.name || '-',
    },
    {
      field: 'type',
      headerName: 'Type',
      minWidth: 100,
      flex: 1,
      cellRenderer: (params) => (
        <Tag color={PRODUCT_TYPE_COLORS[params.value] || 'blue'}>
          {params.value}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: [
        { value: 'standard', label: 'Standard' },
        { value: 'variable', label: 'Variable' },
        { value: 'service', label: 'Service' },
      ],
    },
    {
      field: 'purchase_price',
      headerName: 'Purchase',
      minWidth: 100,
      flex: 1,
      valueFormatter: (params) => formatCurrency(params.value),
      filterType: 'number',
    },
    {
      field: 'sale_price',
      headerName: 'Sale',
      minWidth: 100,
      flex: 1,
      valueFormatter: (params) => formatCurrency(params.value),
      filterType: 'number',
    },
    {
      field: 'status',
      headerName: 'Status',
      minWidth: 100,
      flex: 1,
      cellRenderer: (params) => (
        <Tag color={STATUS_COLORS[params.value] || 'default'}>
          {params.value}
        </Tag>
      ),
      filterType: 'select',
      filterOptions: [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive' },
      ],
    },
  ], []);

  // Actions column
  const actionsColumn = useMemo(() => ({
    field: 'actions',
    headerName: 'Actions',
    minWidth: 120,
    maxWidth: 120,
    sortable: false,
    cellRenderer: (params) => (
      <Space>
        <Button
          type="text"
          icon={<EyeOutlined />}
          onClick={() => navigate(`/products/${params.data.id}`)}
        />
        <Button
          type="text"
          icon={<EditOutlined />}
          onClick={() => navigate(`/products/${params.data.id}/edit`)}
        />
        <Popconfirm
          title="Delete this product?"
          description="This action cannot be undone."
          onConfirm={() => handleDelete(params.data.id)}
          okText="Yes"
          cancelText="No"
        >
          <Button type="text" danger icon={<DeleteOutlined />} />
        </Popconfirm>
      </Space>
    ),
  }), [navigate, handleDelete]);

  return (
    <div style={{ padding: 24 }}>
      <DataGridTable
        gridRef={gridRef}
        columns={columns}
        actionsColumn={actionsColumn}
        fetchData={fetchData}
        title="Products"
        onAdd={() => navigate('/products/new')}
        addButtonText="Add Product"
        searchPlaceholder="Search products..."
        height={600}
        pageSize={20}
      />
    </div>
  );
}
