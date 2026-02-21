import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'
import { resolve } from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: 'resources/js/app.jsx',
      refresh: true,
    }),
    react(),
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '@css': resolve(__dirname, 'resources/css'),
    },
  },
  build: {
    chunkSizeWarningLimit: 1500,
    rollupOptions: {
      output: {
        chunkFileNames: 'assets/c.[name].[hash].js',
        assetFileNames: 'assets/a.[name].[hash][extname]',
        entryFileNames: 'assets/e.[name].[hash].js',
      },
    },
  },
})
