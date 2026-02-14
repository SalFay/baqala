import { atom } from 'recoil';

export const sidebarCollapsedAtom = atom({
    key: 'sidebarCollapsed',
    default: false,
});

export const themeAtom = atom({
    key: 'theme',
    default: 'light',
});

export const loadingAtom = atom({
    key: 'loading',
    default: false,
});

export const notificationAtom = atom({
    key: 'notification',
    default: null,
});

export const currentStoreAtom = atom({
    key: 'currentStore',
    default: null,
});

export const selectedCategoryAtom = atom({
    key: 'selectedCategory',
    default: null,
});
