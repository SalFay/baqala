import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { RecoilRoot, useRecoilValue } from 'recoil';
import { ConfigProvider, App as AntApp } from 'antd';
import { useEffect } from 'react';
import { getTheme, getThemeVars } from './theme/antdTheme';
import { isDarkModeSelector } from './Helpers/atoms/uiAtom';

const appName = import.meta.env.VITE_APP_NAME || 'Baqala POS';

/**
 * ThemeProvider component that wraps the app with dynamic theming
 */
function ThemeProvider({ children }) {
    const isDark = useRecoilValue(isDarkModeSelector);

    // Apply theme class and CSS variables to document
    useEffect(() => {
        const root = document.documentElement;

        // Toggle dark mode class
        if (isDark) {
            root.classList.add('dark');
            root.classList.remove('light');
        } else {
            root.classList.add('light');
            root.classList.remove('dark');
        }

        // Apply CSS variables
        const vars = getThemeVars(isDark);
        Object.entries(vars).forEach(([key, value]) => {
            root.style.setProperty(key, value);
        });

        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', isDark ? '#141414' : '#ffffff');
        }
    }, [isDark]);

    return (
        <ConfigProvider theme={getTheme(isDark)}>
            <AntApp>
                {children}
            </AntApp>
        </ConfigProvider>
    );
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <RecoilRoot>
                <ThemeProvider>
                    <App {...props} />
                </ThemeProvider>
            </RecoilRoot>
        );
    },
    progress: {
        color: '#1890ff',
    },
});
