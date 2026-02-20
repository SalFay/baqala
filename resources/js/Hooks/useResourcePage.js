import { useState, useCallback, useMemo } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { message } from 'antd';
import { DEFAULT_PAGE_SIZE } from '../constants';

/**
 * Generic hook for CRUD resource pages
 * Handles common patterns: search, pagination, create, update, delete
 *
 * @param {Object} options
 * @param {string} options.resourceKey - Query key for React Query (e.g., 'customers')
 * @param {Function} options.listFn - Function to fetch list data
 * @param {Function} options.createFn - Function to create item
 * @param {Function} options.updateFn - Function to update item (receives id, data)
 * @param {Function} options.deleteFn - Function to delete item (receives id)
 * @param {string} options.resourceName - Human readable name for messages (e.g., 'Customer')
 * @param {Object} options.defaultFilters - Default filter values
 * @param {number} options.pageSize - Items per page
 * @param {boolean} options.paginated - Whether the resource is paginated
 */
export function useResourcePage({
  resourceKey,
  listFn,
  createFn,
  updateFn,
  deleteFn,
  resourceName = 'Item',
  defaultFilters = {},
  pageSize = DEFAULT_PAGE_SIZE,
  paginated = true,
}) {
  const queryClient = useQueryClient();

  // State
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState(defaultFilters);
  const [modalOpen, setModalOpen] = useState(false);
  const [detailModal, setDetailModal] = useState(null);
  const [editingItem, setEditingItem] = useState(null);

  // Build query params
  const queryParams = useMemo(() => ({
    search: search || undefined,
    page: paginated ? page : undefined,
    per_page: paginated ? pageSize : undefined,
    ...filters,
  }), [search, page, pageSize, filters, paginated]);

  // Main data query
  const {
    data,
    isLoading,
    error: queryError,
    refetch,
  } = useQuery({
    queryKey: [resourceKey, queryParams],
    queryFn: () => listFn(queryParams),
  });

  // Create mutation
  const createMutation = useMutation({
    mutationFn: createFn,
    onSuccess: () => {
      message.success(`${resourceName} created successfully`);
      queryClient.invalidateQueries({ queryKey: [resourceKey] });
      closeModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || `Failed to create ${resourceName.toLowerCase()}`);
    },
  });

  // Update mutation
  const updateMutation = useMutation({
    mutationFn: ({ id, data }) => updateFn(id, data),
    onSuccess: () => {
      message.success(`${resourceName} updated successfully`);
      queryClient.invalidateQueries({ queryKey: [resourceKey] });
      closeModal();
    },
    onError: (error) => {
      message.error(error.response?.data?.message || `Failed to update ${resourceName.toLowerCase()}`);
    },
  });

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: deleteFn,
    onSuccess: () => {
      message.success(`${resourceName} deleted successfully`);
      queryClient.invalidateQueries({ queryKey: [resourceKey] });
    },
    onError: (error) => {
      message.error(error.response?.data?.message || `Failed to delete ${resourceName.toLowerCase()}`);
    },
  });

  // Modal handlers
  const openCreateModal = useCallback(() => {
    setEditingItem(null);
    setModalOpen(true);
  }, []);

  const openEditModal = useCallback((item) => {
    setEditingItem(item);
    setModalOpen(true);
  }, []);

  const closeModal = useCallback(() => {
    setModalOpen(false);
    setEditingItem(null);
  }, []);

  const openDetailModal = useCallback((item) => {
    setDetailModal(item);
  }, []);

  const closeDetailModal = useCallback(() => {
    setDetailModal(null);
  }, []);

  // Submit handler
  const handleSubmit = useCallback((values) => {
    if (editingItem) {
      updateMutation.mutate({ id: editingItem.id, data: values });
    } else {
      createMutation.mutate(values);
    }
  }, [editingItem, createMutation, updateMutation]);

  // Delete handler
  const handleDelete = useCallback((id) => {
    deleteMutation.mutate(id);
  }, [deleteMutation]);

  // Search handler with page reset
  const handleSearch = useCallback((value) => {
    setSearch(value);
    setPage(1);
  }, []);

  // Filter handler with page reset
  const handleFilterChange = useCallback((newFilters) => {
    setFilters((prev) => ({ ...prev, ...newFilters }));
    setPage(1);
  }, []);

  // Extract data based on whether it's paginated or not
  const items = useMemo(() => {
    if (!data) return [];
    if (paginated && data.data) return data.data;
    return Array.isArray(data) ? data : [];
  }, [data, paginated]);

  // Pagination config for Ant Design Table
  const paginationConfig = useMemo(() => {
    if (!paginated) return false;
    return {
      current: data?.current_page || page,
      total: data?.total,
      pageSize: data?.per_page || pageSize,
      onChange: setPage,
      showSizeChanger: false,
      showTotal: (total) => `Total ${total} ${resourceName.toLowerCase()}${total !== 1 ? 's' : ''}`,
    };
  }, [paginated, data, page, pageSize, resourceName]);

  return {
    // Data
    items,
    rawData: data,
    isLoading,
    queryError,

    // Search & Filters
    search,
    setSearch: handleSearch,
    filters,
    setFilters: handleFilterChange,

    // Pagination
    page,
    setPage,
    paginationConfig,

    // Modal state
    modalOpen,
    editingItem,
    detailModal,

    // Modal actions
    openCreateModal,
    openEditModal,
    closeModal,
    openDetailModal,
    closeDetailModal,

    // CRUD actions
    handleSubmit,
    handleDelete,
    refetch,

    // Mutation states for loading indicators
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
    isMutating: createMutation.isPending || updateMutation.isPending,
  };
}

export default useResourcePage;
