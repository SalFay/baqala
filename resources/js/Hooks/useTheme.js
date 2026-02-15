/**
 * useTheme Hook
 *
 * Provides theme state and utilities for dark/light mode switching.
 */

import { useRecoilState, useRecoilValue } from 'recoil';
import { themeAtom, isDarkModeSelector } from '@/Helpers/atoms/uiAtom';
import { colors, darkColors } from '@/theme/tokens';

/**
 * Theme hook for accessing and controlling theme state
 *
 * @returns {Object} Theme state and utilities
 */
export const useTheme = () => {
    const [theme, setTheme] = useRecoilState(themeAtom);
    const isDark = useRecoilValue(isDarkModeSelector);

    /**
     * Toggle between light and dark themes
     */
    const toggleTheme = () => {
        setTheme((prev) => (prev === 'light' ? 'dark' : 'light'));
    };

    /**
     * Set theme to a specific value
     * @param {'light' | 'dark'} newTheme
     */
    const setThemeMode = (newTheme) => {
        if (newTheme === 'light' || newTheme === 'dark') {
            setTheme(newTheme);
        }
    };

    /**
     * Get current color palette based on theme
     */
    const currentColors = isDark ? darkColors : colors;

    /**
     * Get a specific color value
     * @param {string} path - Dot notation path (e.g., 'text.primary', 'background.secondary')
     * @returns {string} Color value
     */
    const getColor = (path) => {
        const keys = path.split('.');
        let value = currentColors;

        for (const key of keys) {
            if (value && typeof value === 'object' && key in value) {
                value = value[key];
            } else {
                return undefined;
            }
        }

        return value;
    };

    /**
     * Common color shortcuts
     */
    const themeColors = {
        // Backgrounds
        bgPrimary: isDark ? '#141414' : '#ffffff',
        bgSecondary: isDark ? '#262626' : '#fafafa',
        bgTertiary: isDark ? '#434343' : '#f5f5f5',
        bgElevated: isDark ? '#1f1f1f' : '#ffffff',

        // Text
        textPrimary: isDark ? 'rgba(255, 255, 255, 0.95)' : '#141414',
        textSecondary: isDark ? 'rgba(255, 255, 255, 0.65)' : '#595959',
        textTertiary: isDark ? 'rgba(255, 255, 255, 0.45)' : '#8c8c8c',

        // Borders
        borderPrimary: isDark ? '#434343' : '#d9d9d9',
        borderSecondary: isDark ? '#262626' : '#e8e8e8',

        // Brand colors (same in both modes)
        primary: '#1890ff',
        success: '#52c41a',
        warning: '#fa8c16',
        error: '#f5222d',
    };

    return {
        theme,
        isDark,
        toggleTheme,
        setTheme: setThemeMode,
        colors: themeColors,
        getColor,
        currentColors,
    };
};

export default useTheme;
