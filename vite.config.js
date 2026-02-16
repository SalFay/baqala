import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/pos-app/src/main.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: false,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js/pos-app/src'),
        },
    },
    build: {
        chunkSizeWarningLimit: 1500,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('antd') || id.includes('@ant-design')) {
                            return 'antd';
                        }
                        if (id.includes('react-dom') || id.includes('react-router')) {
                            return 'vendor';
                        }
                        if (id.includes('zustand') || id.includes('@tanstack')) {
                            return 'state';
                        }
                        if (id.includes('ag-grid')) {
                            return 'aggrid';
                        }
                    }
                },
            },
        },
    },
});
