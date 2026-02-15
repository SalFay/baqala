/**
 * ThemeToggle Component
 *
 * Animated toggle button for switching between light and dark themes.
 * Features sun/moon icon rotation animation with accessibility support.
 */

import { Tooltip, Button } from 'antd';
import { SunOutlined, MoonOutlined } from '@ant-design/icons';
import { motion, AnimatePresence } from 'framer-motion';
import { useTheme } from '@/Hooks/useTheme';

// Animation variants
const iconVariants = {
    initial: {
        scale: 0,
        rotate: -180,
        opacity: 0,
    },
    animate: {
        scale: 1,
        rotate: 0,
        opacity: 1,
        transition: {
            duration: 0.5,
            ease: [0.34, 1.56, 0.64, 1], // spring-like easing
        },
    },
    exit: {
        scale: 0,
        rotate: 180,
        opacity: 0,
        transition: {
            duration: 0.3,
        },
    },
};

export default function ThemeToggle({ size = 'middle', showTooltip = true }) {
    const { isDark, toggleTheme } = useTheme();

    const buttonContent = (
        <Button
            type="text"
            size={size}
            onClick={toggleTheme}
            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
            style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: size === 'large' ? 48 : 40,
                height: size === 'large' ? 48 : 40,
                borderRadius: '50%',
                position: 'relative',
                overflow: 'hidden',
            }}
        >
            <AnimatePresence mode="wait" initial={false}>
                {isDark ? (
                    <motion.span
                        key="moon"
                        variants={iconVariants}
                        initial="initial"
                        animate="animate"
                        exit="exit"
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                        }}
                    >
                        <MoonOutlined
                            style={{
                                fontSize: size === 'large' ? 20 : 18,
                                color: '#ffc53d',
                            }}
                        />
                    </motion.span>
                ) : (
                    <motion.span
                        key="sun"
                        variants={iconVariants}
                        initial="initial"
                        animate="animate"
                        exit="exit"
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                        }}
                    >
                        <SunOutlined
                            style={{
                                fontSize: size === 'large' ? 20 : 18,
                                color: '#faad14',
                            }}
                        />
                    </motion.span>
                )}
            </AnimatePresence>
        </Button>
    );

    if (showTooltip) {
        return (
            <Tooltip
                title={isDark ? 'Light mode' : 'Dark mode'}
                placement="bottom"
            >
                {buttonContent}
            </Tooltip>
        );
    }

    return buttonContent;
}

// Compact version for tight spaces
ThemeToggle.Compact = function CompactThemeToggle() {
    const { isDark, toggleTheme } = useTheme();

    return (
        <button
            onClick={toggleTheme}
            className="theme-toggle"
            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
            style={{
                background: 'none',
                border: 'none',
                cursor: 'pointer',
                padding: 8,
            }}
        >
            <AnimatePresence mode="wait" initial={false}>
                {isDark ? (
                    <motion.div
                        key="moon"
                        initial={{ scale: 0, rotate: -90 }}
                        animate={{ scale: 1, rotate: 0 }}
                        exit={{ scale: 0, rotate: 90 }}
                        transition={{ duration: 0.3 }}
                    >
                        <MoonOutlined style={{ fontSize: 16, color: '#ffc53d' }} />
                    </motion.div>
                ) : (
                    <motion.div
                        key="sun"
                        initial={{ scale: 0, rotate: 90 }}
                        animate={{ scale: 1, rotate: 0 }}
                        exit={{ scale: 0, rotate: -90 }}
                        transition={{ duration: 0.3 }}
                    >
                        <SunOutlined style={{ fontSize: 16, color: '#faad14' }} />
                    </motion.div>
                )}
            </AnimatePresence>
        </button>
    );
};

// Switch-style toggle
ThemeToggle.Switch = function SwitchThemeToggle() {
    const { isDark, toggleTheme } = useTheme();

    return (
        <button
            onClick={toggleTheme}
            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
            style={{
                position: 'relative',
                width: 52,
                height: 28,
                borderRadius: 14,
                border: 'none',
                cursor: 'pointer',
                padding: 0,
                background: isDark
                    ? 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)'
                    : 'linear-gradient(135deg, #87ceeb 0%, #add8e6 100%)',
                transition: 'background 0.3s ease',
                overflow: 'hidden',
            }}
        >
            <motion.div
                animate={{
                    x: isDark ? 24 : 2,
                }}
                transition={{
                    type: 'spring',
                    stiffness: 500,
                    damping: 30,
                }}
                style={{
                    position: 'absolute',
                    top: 2,
                    width: 24,
                    height: 24,
                    borderRadius: '50%',
                    background: isDark ? '#ffc53d' : '#fff',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    boxShadow: '0 2px 4px rgba(0,0,0,0.2)',
                }}
            >
                {isDark ? (
                    <MoonOutlined style={{ fontSize: 12, color: '#1a1a2e' }} />
                ) : (
                    <SunOutlined style={{ fontSize: 12, color: '#faad14' }} />
                )}
            </motion.div>
        </button>
    );
};
