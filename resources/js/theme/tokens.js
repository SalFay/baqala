/**
 * Baqala POS Design System - Design Tokens
 *
 * Centralized design tokens for consistent styling across the application.
 * These tokens are the single source of truth for colors, spacing, typography, etc.
 */

// Color Palette - Primary (Blue)
export const primary = {
  50: '#e6f7ff',
  100: '#bae7ff',
  200: '#91d5ff',
  300: '#69c0ff',
  400: '#40a9ff',
  500: '#1890ff', // Main primary
  600: '#096dd9',
  700: '#0050b3',
  800: '#003a8c',
  900: '#002766',
};

// Color Palette - Success (Green)
export const success = {
  50: '#f6ffed',
  100: '#d9f7be',
  200: '#b7eb8f',
  300: '#95de64',
  400: '#73d13d',
  500: '#52c41a', // Main success
  600: '#389e0d',
  700: '#237804',
  800: '#135200',
  900: '#092b00',
};

// Color Palette - Warning (Orange)
export const warning = {
  50: '#fff7e6',
  100: '#ffe7ba',
  200: '#ffd591',
  300: '#ffc069',
  400: '#ffa940',
  500: '#fa8c16', // Main warning
  600: '#d46b08',
  700: '#ad4e00',
  800: '#873800',
  900: '#612500',
};

// Color Palette - Error (Red)
export const error = {
  50: '#fff1f0',
  100: '#ffccc7',
  200: '#ffa39e',
  300: '#ff7875',
  400: '#ff4d4f',
  500: '#f5222d', // Main error
  600: '#cf1322',
  700: '#a8071a',
  800: '#820014',
  900: '#5c0011',
};

// Color Palette - Neutral (Gray)
export const neutral = {
  50: '#fafafa',
  100: '#f5f5f5',
  200: '#e8e8e8',
  300: '#d9d9d9',
  400: '#bfbfbf',
  500: '#8c8c8c',
  600: '#595959',
  700: '#434343',
  800: '#262626',
  900: '#141414',
};

// Semantic Colors
export const colors = {
  primary,
  success,
  warning,
  error,
  neutral,

  // Semantic shortcuts
  text: {
    primary: neutral[900],
    secondary: neutral[600],
    tertiary: neutral[500],
    disabled: neutral[400],
    inverse: '#ffffff',
  },

  background: {
    primary: '#ffffff',
    secondary: neutral[50],
    tertiary: neutral[100],
    elevated: '#ffffff',
    overlay: 'rgba(0, 0, 0, 0.45)',
  },

  border: {
    primary: neutral[300],
    secondary: neutral[200],
    light: neutral[100],
  },

  // State colors
  link: primary[500],
  focus: primary[400],
  hover: primary[50],
};

// Dark Mode Colors
export const darkColors = {
  text: {
    primary: 'rgba(255, 255, 255, 0.95)',
    secondary: 'rgba(255, 255, 255, 0.65)',
    tertiary: 'rgba(255, 255, 255, 0.45)',
    disabled: 'rgba(255, 255, 255, 0.25)',
    inverse: neutral[900],
  },

  background: {
    primary: neutral[900],
    secondary: neutral[800],
    tertiary: neutral[700],
    elevated: '#1f1f1f',
    overlay: 'rgba(0, 0, 0, 0.65)',
  },

  border: {
    primary: neutral[700],
    secondary: neutral[800],
    light: neutral[800],
  },
};

// Spacing Scale (4px base unit)
export const spacing = {
  0: '0',
  1: '4px',
  2: '8px',
  3: '12px',
  4: '16px',
  5: '20px',
  6: '24px',
  8: '32px',
  10: '40px',
  12: '48px',
  16: '64px',
  20: '80px',
  24: '96px',
};

// Typography
export const typography = {
  fontFamily: {
    primary: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
    mono: "'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace",
    arabic: "'Noto Naskh Arabic', 'Segoe UI', Tahoma, sans-serif",
  },

  fontSize: {
    xs: '12px',
    sm: '14px',
    base: '16px',
    lg: '18px',
    xl: '20px',
    '2xl': '24px',
    '3xl': '30px',
    '4xl': '36px',
    '5xl': '48px',
  },

  fontWeight: {
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
  },

  lineHeight: {
    tight: 1.25,
    normal: 1.5,
    relaxed: 1.75,
  },
};

// Border Radius
export const borderRadius = {
  none: '0',
  sm: '4px',
  md: '8px',
  lg: '12px',
  xl: '16px',
  '2xl': '24px',
  full: '9999px',
};

// Shadows
export const shadows = {
  none: 'none',
  sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
  md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
  lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
  xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
  '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',

  // Card-specific shadows
  card: '0 2px 8px rgba(0, 0, 0, 0.08)',
  cardHover: '0 8px 24px rgba(0, 0, 0, 0.12)',
  cardActive: '0 4px 12px rgba(0, 0, 0, 0.1)',

  // Component shadows
  dropdown: '0 6px 16px 0 rgba(0, 0, 0, 0.08), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05)',
  modal: '0 6px 16px 0 rgba(0, 0, 0, 0.08), 0 3px 6px -4px rgba(0, 0, 0, 0.12), 0 9px 28px 8px rgba(0, 0, 0, 0.05)',

  // Focus ring
  focus: `0 0 0 2px ${primary[200]}`,
  focusDark: `0 0 0 2px ${primary[700]}`,
};

// Dark mode shadows
export const darkShadows = {
  ...shadows,
  card: '0 2px 8px rgba(0, 0, 0, 0.32)',
  cardHover: '0 8px 24px rgba(0, 0, 0, 0.4)',
  focus: `0 0 0 2px ${primary[700]}`,
};

// Transitions
export const transitions = {
  duration: {
    instant: '0ms',
    fast: '150ms',
    normal: '250ms',
    slow: '350ms',
    slower: '500ms',
  },

  easing: {
    linear: 'linear',
    easeIn: 'cubic-bezier(0.4, 0, 1, 1)',
    easeOut: 'cubic-bezier(0, 0, 0.2, 1)',
    easeInOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
    spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
    bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
  },
};

// Z-Index Scale
export const zIndex = {
  dropdown: 1000,
  sticky: 1020,
  fixed: 1030,
  modalBackdrop: 1040,
  modal: 1050,
  popover: 1060,
  tooltip: 1070,
  toast: 1080,
};

// POS-Specific Tokens
export const pos = {
  // Product card dimensions
  productCard: {
    width: {
      sm: '150px',
      md: '180px',
      lg: '200px',
      xl: '220px',
    },
    height: {
      sm: '180px',
      md: '220px',
      lg: '240px',
      xl: '260px',
    },
    imageHeight: {
      sm: '100px',
      md: '120px',
      lg: '140px',
      xl: '160px',
    },
  },

  // Cart dimensions
  cart: {
    width: {
      collapsed: '80px',
      normal: '400px',
      expanded: '500px',
    },
  },

  // Touch targets (WCAG minimum 44x44)
  touchTarget: {
    min: '44px',
    sm: '48px',
    md: '56px',
    lg: '64px',
  },

  // Button heights
  buttonHeight: {
    sm: '32px',
    md: '44px',
    lg: '56px',
    xl: '72px',
  },

  // Receipt dimensions (80mm thermal paper)
  receipt: {
    width: '280px',
    padding: '12px',
    fontSize: '12px',
    lineHeight: 1.4,
  },
};

// Breakpoints
export const breakpoints = {
  xs: '480px',
  sm: '576px',
  md: '768px',
  lg: '992px',
  xl: '1200px',
  xxl: '1600px',
};

// Media queries
export const media = {
  xs: `@media (max-width: ${breakpoints.xs})`,
  sm: `@media (max-width: ${breakpoints.sm})`,
  md: `@media (max-width: ${breakpoints.md})`,
  lg: `@media (max-width: ${breakpoints.lg})`,
  xl: `@media (max-width: ${breakpoints.xl})`,
  xxl: `@media (max-width: ${breakpoints.xxl})`,

  // Min-width variants
  smUp: `@media (min-width: ${breakpoints.sm})`,
  mdUp: `@media (min-width: ${breakpoints.md})`,
  lgUp: `@media (min-width: ${breakpoints.lg})`,
  xlUp: `@media (min-width: ${breakpoints.xl})`,

  // Touch device detection
  touch: '@media (hover: none) and (pointer: coarse)',
  mouse: '@media (hover: hover) and (pointer: fine)',

  // Print
  print: '@media print',
};

// Export all tokens as a single object for convenience
export const tokens = {
  colors,
  darkColors,
  primary,
  success,
  warning,
  error,
  neutral,
  spacing,
  typography,
  borderRadius,
  shadows,
  darkShadows,
  transitions,
  zIndex,
  pos,
  breakpoints,
  media,
};

export default tokens;
