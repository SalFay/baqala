/**
 * Ant Design Theme Configuration
 *
 * Dynamic theme configuration for light and dark modes using design tokens.
 */

import { theme } from 'antd';
import { primary, success, warning, error, neutral, shadows, borderRadius, transitions } from './tokens';

/**
 * Get Ant Design theme configuration
 * @param {boolean} isDark - Whether to use dark mode
 * @returns {Object} Ant Design theme configuration
 */
export const getTheme = (isDark = false) => ({
  algorithm: isDark ? theme.darkAlgorithm : theme.defaultAlgorithm,
  token: {
    // Primary colors
    colorPrimary: primary[500],
    colorSuccess: success[500],
    colorWarning: warning[500],
    colorError: error[500],
    colorInfo: primary[500],

    // Typography
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
    fontSize: 14,

    // Border radius - touch-friendly
    borderRadius: 8,
    borderRadiusSM: 6,
    borderRadiusLG: 12,

    // Control sizes - touch-friendly (44px minimum for WCAG)
    controlHeight: 44,
    controlHeightSM: 36,
    controlHeightLG: 56,

    // Motion
    motionDurationFast: '150ms',
    motionDurationMid: '250ms',
    motionDurationSlow: '350ms',
    motionEaseInOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
    motionEaseOut: 'cubic-bezier(0, 0, 0.2, 1)',

    // Background colors
    colorBgContainer: isDark ? neutral[800] : '#ffffff',
    colorBgElevated: isDark ? neutral[700] : '#ffffff',
    colorBgLayout: isDark ? neutral[900] : neutral[50],
    colorBgSpotlight: isDark ? neutral[700] : neutral[100],

    // Text colors
    colorText: isDark ? 'rgba(255, 255, 255, 0.95)' : neutral[900],
    colorTextSecondary: isDark ? 'rgba(255, 255, 255, 0.65)' : neutral[600],
    colorTextTertiary: isDark ? 'rgba(255, 255, 255, 0.45)' : neutral[500],
    colorTextDisabled: isDark ? 'rgba(255, 255, 255, 0.25)' : neutral[400],

    // Border colors
    colorBorder: isDark ? neutral[700] : neutral[300],
    colorBorderSecondary: isDark ? neutral[800] : neutral[200],

    // Link color
    colorLink: primary[500],
    colorLinkHover: primary[400],
    colorLinkActive: primary[600],

    // Shadows
    boxShadow: isDark ? shadows.card.replace('rgba(0, 0, 0, 0.08)', 'rgba(0, 0, 0, 0.32)') : shadows.card,
    boxShadowSecondary: isDark ? shadows.cardHover.replace('rgba(0, 0, 0, 0.12)', 'rgba(0, 0, 0, 0.4)') : shadows.cardHover,
  },
  components: {
    Button: {
      controlHeight: 44,
      controlHeightLG: 56,
      controlHeightSM: 36,
      paddingContentHorizontal: 20,
      borderRadius: 8,
      primaryShadow: 'none',
      defaultShadow: 'none',
    },
    Input: {
      controlHeight: 44,
      controlHeightLG: 56,
      controlHeightSM: 36,
      paddingInline: 16,
      borderRadius: 8,
    },
    InputNumber: {
      controlHeight: 44,
      controlHeightLG: 56,
      controlHeightSM: 36,
      paddingInline: 16,
      borderRadius: 8,
    },
    Select: {
      controlHeight: 44,
      controlHeightLG: 56,
      controlHeightSM: 36,
      borderRadius: 8,
    },
    Card: {
      borderRadiusLG: 12,
      boxShadow: shadows.card,
      paddingLG: 24,
    },
    Modal: {
      borderRadiusLG: 12,
      boxShadow: shadows.modal,
    },
    Table: {
      borderRadius: 8,
      headerBg: isDark ? neutral[800] : neutral[50],
      rowHoverBg: isDark ? neutral[700] : primary[50],
      headerColor: isDark ? 'rgba(255, 255, 255, 0.95)' : neutral[900],
    },
    Menu: {
      itemBorderRadius: 8,
      itemHeight: 44,
      subMenuItemBg: 'transparent',
      itemSelectedBg: isDark ? primary[900] : primary[50],
      itemSelectedColor: primary[500],
      itemHoverBg: isDark ? neutral[700] : neutral[100],
    },
    Layout: {
      headerBg: isDark ? neutral[800] : '#ffffff',
      siderBg: isDark ? neutral[800] : '#ffffff',
      bodyBg: isDark ? neutral[900] : neutral[50],
    },
    Tabs: {
      itemSelectedColor: primary[500],
      inkBarColor: primary[500],
      itemHoverColor: primary[400],
    },
    Tag: {
      borderRadiusSM: 6,
    },
    Badge: {
      statusSize: 8,
    },
    Tooltip: {
      borderRadius: 8,
    },
    Dropdown: {
      borderRadiusLG: 12,
      boxShadowSecondary: shadows.dropdown,
    },
    Message: {
      borderRadiusLG: 8,
    },
    Notification: {
      borderRadiusLG: 12,
    },
    Drawer: {
      borderRadiusLG: 0,
    },
    List: {
      itemPadding: '12px 0',
    },
    Divider: {
      colorSplit: isDark ? neutral[700] : neutral[200],
    },
    Radio: {
      buttonSolidCheckedBg: primary[500],
      buttonSolidCheckedHoverBg: primary[600],
    },
    Skeleton: {
      gradientFromColor: isDark ? neutral[700] : neutral[200],
      gradientToColor: isDark ? neutral[600] : neutral[100],
    },
  },
});

/**
 * Get CSS variables for the current theme
 * @param {boolean} isDark - Whether to use dark mode
 * @returns {Object} CSS variables object
 */
export const getThemeVars = (isDark = false) => ({
  '--color-primary': primary[500],
  '--color-primary-light': primary[100],
  '--color-primary-dark': primary[700],
  '--color-success': success[500],
  '--color-warning': warning[500],
  '--color-error': error[500],

  '--color-bg-primary': isDark ? neutral[900] : '#ffffff',
  '--color-bg-secondary': isDark ? neutral[800] : neutral[50],
  '--color-bg-tertiary': isDark ? neutral[700] : neutral[100],
  '--color-bg-elevated': isDark ? '#1f1f1f' : '#ffffff',

  '--color-text-primary': isDark ? 'rgba(255, 255, 255, 0.95)' : neutral[900],
  '--color-text-secondary': isDark ? 'rgba(255, 255, 255, 0.65)' : neutral[600],
  '--color-text-tertiary': isDark ? 'rgba(255, 255, 255, 0.45)' : neutral[500],

  '--color-border-primary': isDark ? neutral[700] : neutral[300],
  '--color-border-secondary': isDark ? neutral[800] : neutral[200],

  '--shadow-card': isDark ? '0 2px 8px rgba(0, 0, 0, 0.32)' : shadows.card,
  '--shadow-card-hover': isDark ? '0 8px 24px rgba(0, 0, 0, 0.4)' : shadows.cardHover,
  '--shadow-dropdown': shadows.dropdown,
  '--shadow-modal': shadows.modal,

  '--border-radius-sm': borderRadius.sm,
  '--border-radius-md': borderRadius.md,
  '--border-radius-lg': borderRadius.lg,

  '--transition-fast': transitions.duration.fast,
  '--transition-normal': transitions.duration.normal,
  '--transition-slow': transitions.duration.slow,
});

export default getTheme;
