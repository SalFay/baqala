# Baqala POS - Claude Instructions

## Core Principles
**ALWAYS apply DRY (Don't Repeat Yourself) and KISS (Keep It Simple Stupid)**
- Remove duplicate code
- Keep solutions minimal and straightforward
- Avoid over-engineering
- Reuse existing code and patterns

## Package Manager
**ALWAYS use `yarn` instead of `npm`** for all package management operations.

## Project Structure (SparkCRM Pattern)
```
resources/js/
├── app.jsx              # Single entry point (Inertia)
├── bootstrap.js         # Bootstrap/axios setup
├── ziggy.js             # Laravel routes helper
├── Components/          # Shared components
├── Helpers/             # Helper functions & Recoil atoms
├── Hooks/               # Custom React hooks
├── Layouts/             # PersistentLayout (main layout)
└── Pages/               # Inertia pages (PascalCase folders)
    ├── Auth/Login.jsx
    ├── Dashboard/Index.jsx
    ├── Products/Index.jsx
    └── ...
```

## Commands
```bash
# Install dependencies
yarn install

# Development server
yarn dev

# Build for production
yarn build
```

## Key Patterns (SparkCRM-inspired)
- **No TypeScript** - Use JavaScript/JSX only
- **Single entry point** - `resources/js/app.jsx` with Inertia
- **Inertia routing** - No React Router, use `router` from `@inertiajs/react`
- **Recoil for state** - Global state management
- **Ant Design** - Primary UI framework
- **Session auth** - `withCredentials: true` for CSRF
- **PersistentLayout** - Default layout for all pages

## Inertia Page Pattern
```jsx
import { Head, usePage } from '@inertiajs/react'
import { Typography } from 'antd'

export default function PageName() {
  const { data } = usePage().props  // Data from controller

  return (
    <>
      <Head title="Page Title" />
      {/* Page content */}
    </>
  )
}
```

## Routes
- All routes use Inertia::render() from controllers
- API routes: `/pos/*` (JSON for data fetching)
- Page routes: Return Inertia responses
