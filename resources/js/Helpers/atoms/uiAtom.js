import { atom, selector } from 'recoil';

/**
 * Sidebar collapsed state
 */
export const sidebarCollapsedAtom = atom({
    key: 'sidebarCollapsed',
    default: false,
});

/**
 * Theme atom with localStorage persistence
 */
export const themeAtom = atom({
    key: 'theme',
    default: 'light',
    effects: [
        ({ setSelf, onSet }) => {
            // Initialize from localStorage on mount
            if (typeof window !== 'undefined') {
                const savedTheme = localStorage.getItem('baqala-theme');
                if (savedTheme && ['light', 'dark'].includes(savedTheme)) {
                    setSelf(savedTheme);
                } else {
                    // Check system preference
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    setSelf(prefersDark ? 'dark' : 'light');
                }
            }

            // Persist to localStorage on change
            onSet((newValue, _, isReset) => {
                if (typeof window !== 'undefined') {
                    if (isReset) {
                        localStorage.removeItem('baqala-theme');
                    } else {
                        localStorage.setItem('baqala-theme', newValue);
                    }
                }
            });
        },
    ],
});

/**
 * Dark mode selector - derived from themeAtom
 */
export const isDarkModeSelector = selector({
    key: 'isDarkMode',
    get: ({ get }) => get(themeAtom) === 'dark',
});

/**
 * Global loading state
 */
export const loadingAtom = atom({
    key: 'loading',
    default: false,
});

/**
 * Global notification state
 */
export const notificationAtom = atom({
    key: 'notification',
    default: null,
});

/**
 * Current store state
 */
export const currentStoreAtom = atom({
    key: 'currentStore',
    default: null,
});

/**
 * Selected category state (for POS)
 */
export const selectedCategoryAtom = atom({
    key: 'selectedCategory',
    default: null,
});

/**
 * Mobile menu open state
 */
export const mobileMenuOpenAtom = atom({
    key: 'mobileMenuOpen',
    default: false,
});

/**
 * Search query state
 */
export const globalSearchAtom = atom({
    key: 'globalSearch',
    default: '',
});

/**
 * Menu open keys - SparkCRM pattern
 */
const getInitialMenuKeys = (key, defaultValue) => {
    if (typeof window === 'undefined') return defaultValue;
    try {
        const saved = localStorage.getItem(key);
        return saved ? JSON.parse(saved) : defaultValue;
    } catch {
        return defaultValue;
    }
};

export const menuOpenKeysAtom = atom({
    key: 'menuOpenKeys',
    default: getInitialMenuKeys('baqala-openMenuKeys', ['dashboard']),
    effects: [
        ({ onSet }) => {
            onSet((newValue) => {
                try {
                    localStorage.setItem('baqala-openMenuKeys', JSON.stringify(newValue));
                } catch (error) {
                    console.error('Failed to save openMenuKeys:', error);
                }
            });
        },
    ],
});

export const menuSelectedKeysAtom = atom({
    key: 'menuSelectedKeys',
    default: getInitialMenuKeys('baqala-selectedMenuKeys', ['/dashboard']),
    effects: [
        ({ onSet }) => {
            onSet((newValue) => {
                try {
                    localStorage.setItem('baqala-selectedMenuKeys', JSON.stringify(newValue));
                } catch (error) {
                    console.error('Failed to save selectedMenuKeys:', error);
                }
            });
        },
    ],
});

/**
 * Mobile detection
 */
export const isMobileAtom = atom({
    key: 'isMobile',
    default: typeof window !== 'undefined' ? window.innerWidth < 768 : false,
});

/**
 * Drawer visible state (mobile menu)
 */
export const drawerVisibleAtom = atom({
    key: 'drawerVisible',
    default: false,
});
