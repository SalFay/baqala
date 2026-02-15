/**
 * ProSelect - Enhanced Select Component (SparkCRM Pattern)
 *
 * Features:
 * - React Query caching (30-min stale time)
 * - Debounced search (200ms)
 * - Hide selected options
 * - Responsive maxTagCount with ResizeObserver
 * - Tag overflow popover
 * - Auto-select single option
 * - Select All / Clear All for multi-select
 * - Theme awareness
 * - Exclude values support
 */

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Popover, Select, Space, Spin, Tag, theme, Typography } from 'antd';
import { dataToOptions, hexToRgba } from '@/Helpers/transformers';
import { fetchDropdownOptions } from '@/Helpers/api/mix';
import { useRecoilValue } from 'recoil';
import { themeAtom } from '@/Helpers/atoms/uiAtom';
import { debounce } from 'lodash';
import { useQuery, useQueryClient } from '@tanstack/react-query';

const { Text } = Typography;

// Cache configuration
const STALE_TIME = 30 * 60 * 1000; // 30 minutes
const GC_TIME = 60 * 60 * 1000; // 60 minutes

// Stable empty object to prevent unnecessary re-renders
const EMPTY_PARAMS = {};

const TAG_BASE_COLOR = '#cdceca';
const TAG_BACKGROUND_COLOR = hexToRgba(TAG_BASE_COLOR, 0.5);
const TAG_BORDER_COLOR = hexToRgba(TAG_BASE_COLOR, 0.4);

// Estimated tag width for maxTagCount calculation
const ESTIMATED_TAG_PADDING = 24;
const ESTIMATED_CHAR_WIDTH = 7;
const MIN_TAG_WIDTH = 50;

/**
 * ProSelect using Ant Design Select
 */
export default function ProSelect({
    type,
    params = EMPTY_PARAMS,
    options: staticOptions = [],
    onRefresh,
    setOptions,
    variant = 'outlined',
    initialOptions = [],
    readOnly,
    hideSelected = true,
    maxTagCount = 'responsive',
    minTagCount = true,
    tagRender,
    maxTagTextLength = 'responsive',
    selectControl = true,
    autoSelectSingle = false,
    excludeValues = [],
    strictVariant = false,
    wrapperStyle = {},
    enabled = true,
    value,
    onChange,
    mode,
    placeholder = 'Select...',
    ...props
}) {
    const { token } = theme.useToken();
    const selectRef = useRef(null);
    const currentTheme = useRecoilValue(themeAtom);
    const [hovered, setHovered] = useState(false);
    const [selectWidth, setSelectWidth] = useState(0);
    const [searchText, setSearchText] = useState('');
    const [searching, setSearching] = useState(false);
    const [searchResults, setSearchResults] = useState(null);
    const searchIdRef = useRef(0);
    const performSearchRef = useRef(null);
    const queryClient = useQueryClient();

    // Serialize params for stable dependency comparison
    const paramsString = useMemo(() => JSON.stringify(params), [params]);

    // Stable params for query key
    const paramsWithContext = useMemo(
        () => ({
            ...params,
            context: params.context || 'form',
        }),
        [paramsString]
    );

    // Query key for React Query cache
    const queryKey = useMemo(
        () => ['proSelectOptions', type, paramsWithContext],
        [type, paramsWithContext]
    );

    // Use React Query for caching and fetching base options
    const {
        data: cachedOptions = initialOptions,
        isLoading: loading,
        refetch,
    } = useQuery({
        queryKey,
        queryFn: async () => {
            if (type === 'static') {
                return staticOptions;
            }
            const res = await fetchDropdownOptions(type, '', paramsWithContext);
            return dataToOptions(res);
        },
        enabled: !!type && type !== 'static' && enabled,
        staleTime: STALE_TIME,
        gcTime: GC_TIME,
        refetchOnMount: false,
        refetchOnWindowFocus: false,
        retry: 1,
    });

    // Apply excludeValues filter
    const data = useMemo(() => {
        const baseOptions =
            type === 'static'
                ? staticOptions
                : searchResults !== null
                ? searchResults
                : cachedOptions;

        if (excludeValues?.length) {
            return baseOptions.filter((opt) => !excludeValues.includes(opt.value));
        }
        return baseOptions;
    }, [type, staticOptions, cachedOptions, searchResults, excludeValues]);

    // Handle autoSelectSingle
    useEffect(() => {
        if (autoSelectSingle && data.length === 1 && !value && onChange) {
            onChange(data[0].value);
        }
    }, [data, autoSelectSingle, value, onChange]);

    // Pass options to parent if needed
    useEffect(() => {
        if (setOptions && data.length > 0) {
            setOptions(data);
        }
    }, [data, setOptions]);

    // Search function - fetches fresh data for search queries
    const performSearch = useCallback(
        async (text) => {
            if (!text || type === 'static') {
                setSearching(false);
                setSearchResults(null);
                return;
            }

            const currentSearchId = ++searchIdRef.current;
            setSearching(true);

            try {
                const res = await fetchDropdownOptions(type, text, paramsWithContext);

                if (currentSearchId !== searchIdRef.current) {
                    return; // Stale response, ignore it
                }

                const results = dataToOptions(res);
                setSearchResults(results);
            } catch (error) {
                if (currentSearchId === searchIdRef.current) {
                    console.error('Search failed:', error);
                    setSearchResults(null);
                }
            } finally {
                if (currentSearchId === searchIdRef.current) {
                    setSearching(false);
                }
            }
        },
        [type, paramsWithContext]
    );

    // Keep ref updated with latest performSearch
    performSearchRef.current = performSearch;

    // Stable debounced search function
    const debouncedSearch = useMemo(
        () => debounce((text) => performSearchRef.current?.(text), 200),
        []
    );

    // Handle search input
    const handleSearch = useCallback(
        (text) => {
            setSearchText(text);
            if (!text) {
                debouncedSearch.cancel();
                searchIdRef.current++;
                setSearchResults(null);
                setSearching(false);
                return;
            }
            debouncedSearch(text);
        },
        [debouncedSearch]
    );

    // Handle dropdown visibility change
    const handleDropdownVisibleChange = useCallback(
        (open) => {
            if (!open) {
                debouncedSearch.cancel();
                searchIdRef.current++;
                setSearchText('');
                setSearchResults(null);
                setSearching(false);
            }
        },
        [debouncedSearch]
    );

    // Expose the refresh function to the parent
    useEffect(() => {
        if (onRefresh) {
            onRefresh(() => {
                queryClient.invalidateQueries({ queryKey });
                return refetch();
            });
        }
    }, [onRefresh, queryClient, queryKey, refetch]);

    // Cleanup debounced function on unmount
    useEffect(() => {
        return () => {
            debouncedSearch.cancel();
        };
    }, [debouncedSearch]);

    // Track select width for responsive calculations
    useEffect(() => {
        const element = selectRef.current;
        if (!element) return;

        const updateWidth = () => {
            const width = element.offsetWidth || 0;
            setSelectWidth(width);
        };

        updateWidth();

        const observer = new ResizeObserver(updateWidth);
        observer.observe(element);

        return () => observer.disconnect();
    }, []);

    // Memoized computed values
    const isMulti = mode === 'multiple';

    const valueArray = useMemo(
        () => (Array.isArray(value) ? value : []),
        [value]
    );

    const hasValue = useMemo(
        () =>
            isMulti
                ? valueArray.length > 0
                : value !== undefined && value !== null && value !== '',
        [isMulti, valueArray.length, value]
    );

    const allOptions = useMemo(
        () => (type === 'static' ? staticOptions : data),
        [type, staticOptions, data]
    );

    const allSelected = useMemo(
        () =>
            isMulti &&
            valueArray.length > 0 &&
            allOptions.length > 0 &&
            allOptions.every((option) => valueArray.includes(option.value)),
        [isMulti, valueArray, allOptions]
    );

    // Calculate maxTagTextLength
    const calculatedMaxTagTextLength = useMemo(() => {
        if (typeof maxTagTextLength === 'number') return maxTagTextLength;
        return undefined;
    }, [maxTagTextLength]);

    // Calculate maxTagCount based on select width
    const calculatedMaxTagCount = useMemo(() => {
        if (maxTagCount !== 'responsive' || !isMulti) return maxTagCount;
        if (!selectWidth || valueArray.length === 0) return 'responsive';

        const availableWidth = selectWidth - 40;
        let usedWidth = 0;
        let count = 0;

        for (const val of valueArray) {
            const option = allOptions.find((opt) => opt.value === val);
            const label = option?.label || String(val);
            const labelLength = typeof label === 'string' ? label.length : 10;

            const tagWidth = Math.min(
                ESTIMATED_TAG_PADDING + labelLength * ESTIMATED_CHAR_WIDTH,
                typeof calculatedMaxTagTextLength === 'number'
                    ? ESTIMATED_TAG_PADDING + calculatedMaxTagTextLength * ESTIMATED_CHAR_WIDTH
                    : availableWidth
            );

            if (usedWidth + tagWidth + MIN_TAG_WIDTH > availableWidth && count > 0) {
                break;
            }

            usedWidth += tagWidth + 4;
            count++;
        }

        return Math.max(1, count);
    }, [maxTagCount, isMulti, selectWidth, valueArray, allOptions, calculatedMaxTagTextLength]);

    // Hover background color
    const hoverBg = useMemo(
        () => (currentTheme === 'dark' ? token.colorBgBase : '#ffffff'),
        [currentTheme, token.colorBgBase]
    );

    // Determine variant
    const computedVariant = useMemo(() => {
        if (strictVariant) return variant;
        if (variant === 'borderless') {
            return hasValue ? 'borderless' : 'outlined';
        }
        return variant;
    }, [strictVariant, variant, hasValue]);

    // Default tag render function
    const defaultTagRender = useCallback((tagProps) => {
        const { label, closable, onClose } = tagProps;
        return (
            <Tag
                closable={closable}
                onClose={onClose}
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    padding: '2px 8px',
                    cursor: 'default',
                    border: `1px solid ${TAG_BORDER_COLOR}`,
                    backgroundColor: TAG_BACKGROUND_COLOR,
                    maxWidth: '100%',
                    overflow: 'hidden',
                }}
            >
                <span
                    style={{
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                    }}
                >
                    {label}
                </span>
            </Tag>
        );
    }, []);

    // maxTagPlaceholder with popover
    const maxTagPlaceholder = useCallback(
        (omittedValues) => (
            <Popover
                content={
                    <div style={{ maxWidth: '350px', maxHeight: '300px', overflow: 'auto' }}>
                        <Space size={[4, 8]} wrap>
                            {omittedValues.map((item) =>
                                tagRender ? (
                                    <span key={item.value}>
                                        {tagRender({
                                            ...item,
                                            closable: false,
                                            onClose: () =>
                                                onChange(value.filter((val) => val !== item.value)),
                                        })}
                                    </span>
                                ) : (
                                    <Tag
                                        key={item.value}
                                        closable={false}
                                        bordered={false}
                                        style={{ backgroundColor: token.colorBorderSecondary }}
                                    >
                                        {item.label}
                                    </Tag>
                                )
                            )}
                        </Space>
                    </div>
                }
                title="Options"
                trigger="hover"
            >
                +{omittedValues.length} ...
            </Popover>
        ),
        [tagRender, onChange, value, token.colorBorderSecondary]
    );

    // Handle select all or clear all
    const handleSelectControl = useCallback(() => {
        if (allSelected) {
            onChange([]);
        } else {
            const allValues = allOptions.map((option) => option.value);
            onChange(allValues);
        }
    }, [allSelected, allOptions, onChange]);

    // Dropdown render with Select All/Clear All
    const dropdownRender = useCallback(
        (menu) => (
            <div>
                {selectControl && isMulti && (
                    <div
                        style={{
                            padding: '4px 8px',
                            borderBottom: `1px solid ${token.colorBorder}`,
                        }}
                    >
                        <a
                            onClick={(e) => {
                                e.preventDefault();
                                handleSelectControl();
                            }}
                            style={{ display: 'block', cursor: 'pointer', color: token.colorPrimary }}
                        >
                            {allSelected ? 'Clear All' : 'Select All'}
                        </a>
                    </div>
                )}
                {menu}
            </div>
        ),
        [selectControl, isMulti, token.colorBorder, token.colorPrimary, handleSelectControl, allSelected]
    );

    // Option render
    const optionRender = useCallback((option) => {
        const description = option.data?.item?.description || option.data?.description;
        if (description) {
            return (
                <div>
                    <div>{option.label}</div>
                    <Text type="secondary" style={{ fontSize: 12 }}>
                        {description}
                    </Text>
                </div>
            );
        }
        return option.label;
    }, []);

    // Styles
    const selectStyles = useMemo(
        () => ({
            ...props.styles,
            selector: {
                ...props.styles?.selector,
                overflow: 'hidden',
                minWidth: 0,
            },
            selectionOverflow: {
                ...props.styles?.selectionOverflow,
                minWidth: 0,
                overflow: 'hidden',
                maxWidth: '100%',
            },
            selectionItem: {
                ...props.styles?.selectionItem,
                maxWidth: '100%',
                flexShrink: 0,
            },
        }),
        [props.styles]
    );

    // Hover handlers
    const shouldApplyHoverBg = computedVariant === 'borderless' && !readOnly;

    const handleMouseEnter = useCallback(
        (e) => {
            if (shouldApplyHoverBg) {
                e.currentTarget.style.backgroundColor = hoverBg;
            }
            setHovered(true);
        },
        [hoverBg, shouldApplyHoverBg]
    );

    const handleMouseLeave = useCallback(
        (e) => {
            if (shouldApplyHoverBg) {
                e.currentTarget.style.backgroundColor = 'transparent';
            }
            setHovered(false);
        },
        [shouldApplyHoverBg]
    );

    // Wrapper style
    const computedWrapperStyle = useMemo(
        () => ({
            cursor: 'pointer',
            width: '100%',
            minWidth: props.style?.minWidth ?? 0,
            flex: props.style?.flex,
            borderRadius: shouldApplyHoverBg ? token.borderRadius : undefined,
            overflow: shouldApplyHoverBg ? 'hidden' : undefined,
            ...wrapperStyle,
        }),
        [props.style?.flex, props.style?.minWidth, wrapperStyle, shouldApplyHoverBg, token.borderRadius]
    );

    return (
        <div
            ref={selectRef}
            style={computedWrapperStyle}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
        >
            <Select
                variant={computedVariant}
                showSearch
                loading={loading && !searching}
                disabled={readOnly}
                filterOption={false}
                onSearch={handleSearch}
                onOpenChange={handleDropdownVisibleChange}
                options={data}
                value={value}
                onChange={onChange}
                mode={mode}
                placeholder={placeholder}
                notFoundContent={
                    loading || searching ? <Spin size="small" /> : 'No data found'
                }
                tagRender={isMulti ? tagRender || defaultTagRender : undefined}
                maxTagPlaceholder={isMulti ? maxTagPlaceholder : undefined}
                maxTagCount={calculatedMaxTagCount}
                maxTagTextLength={calculatedMaxTagTextLength}
                popupRender={isMulti && selectControl ? dropdownRender : undefined}
                optionRender={optionRender}
                {...props}
                style={{
                    width: '100%',
                    ...props.style,
                }}
                styles={selectStyles}
                suffixIcon={readOnly || !hovered ? null : props?.suffixIcon}
            />
        </div>
    );
}

// Convenience wrappers
ProSelect.Single = (props) => <ProSelect {...props} mode={undefined} />;
ProSelect.Multiple = (props) => <ProSelect {...props} mode="multiple" />;
ProSelect.Tags = (props) => <ProSelect {...props} mode="tags" />;
