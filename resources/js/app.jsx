import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { RecoilRoot } from 'recoil';
import { ConfigProvider } from 'antd';

const appName = import.meta.env.VITE_APP_NAME || 'Baqala POS';

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
                <ConfigProvider
                    theme={{
                        token: {
                            colorPrimary: '#1890ff',
                            borderRadius: 6,
                        },
                    }}
                >
                    <App {...props} />
                </ConfigProvider>
            </RecoilRoot>
        );
    },
    progress: {
        color: '#1890ff',
    },
});
