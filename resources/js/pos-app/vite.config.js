import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  base: '/pos/',
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    proxy: {
      '/pos': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: '../../../public/pos',
    emptyOutDir: true,
    sourcemap: false,
    manifest: true,
    rollupOptions: {
      input: './index.html',
      output: {
        // SparkCRM pattern: avoid ad-blocker triggers
        chunkFileNames: 'assets/c.[name].[hash].js',
        assetFileNames: 'assets/a.[name].[hash][extname]',
        entryFileNames: 'assets/e.[name].[hash].js',
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router-dom'],
          antd: ['antd', '@ant-design/icons'],
          state: ['zustand', '@tanstack/react-query'],
        },
      },
    },
  },
});
