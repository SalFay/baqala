import { useState, useCallback, useMemo, useRef, useEffect } from 'react';
import { Select, Spin, Space, Button, Divider } from 'antd';
import { useQuery } from '@tanstack/react-query';
import axios from '../api/axios';
import debounce from 'lodash/debounce';

interface ProSelectOption {
  value: number | string;
  label: string;
  description?: string;
  [key: string]: unknown;
}

interface ProSelectProps {
  type: string;
  params?: Record<string, unknown>;
  placeholder?: string;
  mode?: 'multiple' | 'tags';
  value?: number | string | (number | string)[];
  onChange?: (value: number | string | (number | string)[] | undefined) => void;
  disabled?: boolean;
  allowClear?: boolean;
  showSearch?: boolean;
  style?: React.CSSProperties;
  className?: string;
  context?: 'form' | 'filter';
  excludeValues?: (number | string)[];
  selectControl?: boolean;
  maxTagCount?: number | 'responsive';
  initialOptions?: ProSelectOption[];
  onRefresh?: (refetch: () => void) => void;
  enabled?: boolean;
}

// API helper to fetch dropdown options
async function fetchOptions(
  type: string,
  search: string = '',
  params: Record<string, unknown> = {}
): Promise<ProSelectOption[]> {
  const response = await axios.post('/dropdown', {
    type,
    q: search,
    ...params,
  });
  return response.data;
}

// Transform API response to Select options format
function dataToOptions(data: unknown[]): ProSelectOption[] {
  if (!Array.isArray(data)) return [];

  return data.map((item: Record<string, unknown>) => ({
    value: item.value ?? item.id,
    label: item.label ?? item.name ?? String(item.id),
    description: item.description ?? item.email ?? item.code,
    ...item,
  })) as ProSelectOption[];
}

export default function ProSelect({
  type,
  params = {},
  placeholder = 'Select...',
  mode,
  value,
  onChange,
  disabled = false,
  allowClear = true,
  showSearch = true,
  style,
  className,
  context = 'form',
  excludeValues = [],
  selectControl = false,
  maxTagCount = 'responsive',
  initialOptions = [],
  onRefresh,
  enabled = true,
}: ProSelectProps) {
  const [searchText, setSearchText] = useState('');
  const [searchResults, setSearchResults] = useState<ProSelectOption[] | null>(null);
  const [searching, setSearching] = useState(false);
  const searchIdRef = useRef(0);
  const performSearchRef = useRef<((text: string) => void) | null>(null);

  // Build params with context
  const paramsWithContext = useMemo(
    () => ({ ...params, context }),
    [params, context]
  );

  // React Query for initial/cached options
  const {
    data: cachedOptions = initialOptions,
    isLoading: loading,
    refetch,
  } = useQuery({
    queryKey: ['proSelectOptions', type, paramsWithContext],
    queryFn: async () => {
      const res = await fetchOptions(type, '', paramsWithContext);
      return dataToOptions(res);
    },
    enabled: !!type && enabled,
    staleTime: 30 * 60 * 1000, // 30 minutes
    gcTime: 60 * 60 * 1000, // 60 minutes (formerly cacheTime)
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    retry: 1,
  });

  // Expose refetch to parent
  useEffect(() => {
    if (onRefresh) {
      onRefresh(refetch);
    }
  }, [onRefresh, refetch]);

  // Perform search with race condition prevention
  const performSearch = useCallback(
    async (text: string) => {
      const currentSearchId = ++searchIdRef.current;
      setSearching(true);

      try {
        const res = await fetchOptions(type, text, paramsWithContext);

        // Prevent stale responses
        if (currentSearchId !== searchIdRef.current) return;

        setSearchResults(dataToOptions(res));
      } catch (error) {
        console.error('ProSelect search error:', error);
        setSearchResults(null);
      } finally {
        if (currentSearchId === searchIdRef.current) {
          setSearching(false);
        }
      }
    },
    [type, paramsWithContext]
  );

  // Keep ref updated
  useEffect(() => {
    performSearchRef.current = performSearch;
  }, [performSearch]);

  // Debounced search (200ms)
  const debouncedSearch = useMemo(
    () =>
      debounce((text: string) => {
        performSearchRef.current?.(text);
      }, 200),
    []
  );

  // Handle search
  const handleSearch = useCallback(
    (text: string) => {
      setSearchText(text);
      if (!text) {
        debouncedSearch.cancel();
        setSearchResults(null);
        return;
      }
      debouncedSearch(text);
    },
    [debouncedSearch]
  );

  // Cleanup debounce on unmount
  useEffect(() => {
    return () => {
      debouncedSearch.cancel();
    };
  }, [debouncedSearch]);

  // Get options to display (search results or cached)
  const displayOptions = useMemo(() => {
    const options = searchResults ?? cachedOptions;

    // Filter out excluded values
    if (excludeValues.length > 0) {
      return options.filter(
        (opt) => !excludeValues.includes(opt.value as number | string)
      );
    }

    return options;
  }, [searchResults, cachedOptions, excludeValues]);

  // Handle select all (for multiple mode)
  const handleSelectAll = useCallback(() => {
    if (mode === 'multiple' && onChange) {
      const allValues = displayOptions.map((opt) => opt.value);
      onChange(allValues as (number | string)[]);
    }
  }, [mode, onChange, displayOptions]);

  // Handle clear all
  const handleClearAll = useCallback(() => {
    if (onChange) {
      onChange(mode === 'multiple' ? [] : undefined);
    }
  }, [mode, onChange]);

  // Dropdown render for select control buttons
  const dropdownRender = useCallback(
    (menu: React.ReactNode) => {
      if (!selectControl || mode !== 'multiple') {
        return menu;
      }

      return (
        <>
          <Space
            style={{ padding: '4px 8px', width: '100%', justifyContent: 'space-between' }}
          >
            <Button type="link" size="small" onClick={handleSelectAll}>
              Select All
            </Button>
            <Button type="link" size="small" onClick={handleClearAll}>
              Clear
            </Button>
          </Space>
          <Divider style={{ margin: '4px 0' }} />
          {menu}
        </>
      );
    },
    [selectControl, mode, handleSelectAll, handleClearAll]
  );

  return (
    <Select
      style={{ minWidth: 150, ...style }}
      className={className}
      placeholder={placeholder}
      mode={mode}
      value={value}
      onChange={onChange}
      disabled={disabled}
      allowClear={allowClear}
      showSearch={showSearch}
      filterOption={false}
      onSearch={showSearch ? handleSearch : undefined}
      loading={loading || searching}
      notFoundContent={
        loading || searching ? <Spin size="small" /> : 'No results'
      }
      maxTagCount={maxTagCount}
      dropdownRender={dropdownRender}
      options={displayOptions.map((opt) => ({
        value: opt.value,
        label: opt.description ? (
          <div>
            <div>{opt.label}</div>
            <div style={{ fontSize: 12, color: '#888' }}>{opt.description}</div>
          </div>
        ) : (
          opt.label
        ),
      }))}
    />
  );
}
