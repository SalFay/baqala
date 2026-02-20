import './bootstrap'
import '../css/app.css'

import { createRoot } from 'react-dom/client'
import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { RecoilRoot } from 'recoil'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import ThemeProvider from '@/Components/ThemeProvider'

const appName = import.meta.env.VITE_APP_NAME || 'Baqala POS'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
      staleTime: 5 * 60 * 1000,
    },
  },
})

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async (name) => {
    const page = await resolvePageComponent(
      `./Pages/${name}.jsx`,
      import.meta.glob('./Pages/**/*.jsx')
    )

    if (!page.default.layout) {
      const PersistentLayout = (await import('./Layouts/PersistentLayout.jsx')).default
      page.default.layout = (page) => <PersistentLayout children={page} />
    }

    return page
  },
  setup({ el, App, props }) {
    const root = createRoot(el)
    root.render(
      <QueryClientProvider client={queryClient}>
        <RecoilRoot>
          <ThemeProvider>
            <App {...props} />
          </ThemeProvider>
        </RecoilRoot>
      </QueryClientProvider>
    )
  },
  progress: {
    color: '#1890ff',
  },
})
