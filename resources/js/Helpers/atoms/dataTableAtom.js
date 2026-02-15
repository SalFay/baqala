import { atom, selector } from 'recoil';

/**
 * DataTable refresh state atom
 *
 * Stores refresh timestamps for each table to trigger re-fetches.
 * Key is the route name, value is the timestamp.
 */
export const DataTableAtom = atom({
    key: 'dataTableAtom',
    default: {},
});

/**
 * Selector to get refresh timestamp for a specific table
 */
export const getTableRefreshTimestamp = selector({
    key: 'getTableRefreshTimestamp',
    get:
        ({ get }) =>
        (routeName) => {
            const state = get(DataTableAtom);
            return state[routeName] || null;
        },
});

/**
 * DataTable filter state atom
 *
 * Stores filter values for each table.
 */
export const DataTableFiltersAtom = atom({
    key: 'dataTableFiltersAtom',
    default: {},
});

/**
 * DataTable selection state atom
 *
 * Stores selected row keys for each table.
 */
export const DataTableSelectionAtom = atom({
    key: 'dataTableSelectionAtom',
    default: {},
});

/**
 * DataTable pagination state atom
 *
 * Stores pagination state for each table.
 */
export const DataTablePaginationAtom = atom({
    key: 'dataTablePaginationAtom',
    default: {},
});
