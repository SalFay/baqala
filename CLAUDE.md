# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Core Principles
**ALWAYS apply DRY (Don't Repeat Yourself) and KISS (Keep It Simple Stupid)**
- Reuse components, hooks, helpers - never duplicate code
- Keep controllers thin, move logic to services/repositories
- Check permissions before rendering actions

## Package Manager
**ALWAYS use `yarn` instead of `npm`** for all package management operations.

## Commands

```bash
# Development
yarn dev                          # Start Vite dev server
php artisan serve                 # Start Laravel dev server (if not using Laragon)

# Build
yarn build                        # Production build

# Testing
npx playwright test               # Run all E2E tests
npx playwright test tests/e2e/pos-cart.spec.ts  # Run single E2E test
npx playwright show-report        # View E2E test report
./vendor/bin/phpunit              # Run all PHP tests
./vendor/bin/phpunit --filter=TestName  # Run single PHP test

# Database
php artisan migrate               # Run migrations
php artisan migrate:fresh --seed  # Fresh database with seeders
```

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 11, Spatie Permission, Spatie Activity Log |
| Frontend | React 18, Inertia.js 2, Ant Design 5 + Pro Components |
| State | Recoil (global), useState (local) |
| Tables | AG Grid Enterprise (server-side pagination) |
| Build | Vite (`@` = resources/js, `@css` = resources/css) |
| Testing | Playwright (E2E), PHPUnit (PHP) |

## Architecture

### Frontend Structure (resources/js/)
```
├── app.jsx                    # Single Inertia entry point
├── bootstrap.js               # Axios setup
├── Components/
│   ├── DataGridTable/         # AG Grid wrapper (server-side)
│   ├── GlobalPageHeader.jsx   # Header with breadcrumbs + actions
│   ├── PageContent.jsx        # Page wrapper
│   ├── CustomModal.jsx        # Modal wrapper
│   ├── GlobalFilter.jsx       # Advanced filtering
│   └── Buttons/Button1.jsx    # Unified button
├── Helpers/
│   ├── atom.js                # Recoil atoms (theme, user, permissions, menu)
│   ├── CONSTANT.js            # handleApiError, handleApiSuccess
│   ├── Context/usePermissions.js
│   └── api/*.js               # API endpoint functions
├── Hooks/                     # useIsMobile, useMenuManagement, etc.
├── Layouts/PersistentLayout.jsx  # Main layout (header + sidebar)
└── Pages/                     # Inertia pages (PascalCase folders)
```

### Backend Structure (app/)
```
├── Http/Controllers/          # Thin controllers (Inertia + JSON)
├── Http/Requests/Api/         # Form request validation
├── Services/                  # Business logic orchestration
├── Repositories/              # Data access layer
├── Actions/                   # Single-purpose operations
├── Models/                    # Eloquent models
└── Helpers/                   # Global helper functions
```

## Key Patterns

### Controller Pattern (Thin Controllers)
```php
class CustomerController extends Controller
{
    // Page render (HTML)
    public function index(): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);  // JSON for DataGridTable
        }
        return Inertia::render('Customer/Index');
    }

    // API endpoint (JSON) - called by DataGridTable
    public function listing(Request $request): JsonResponse
    {
        $query = Customer::query()->with(['address']);
        // Apply filters, sorting, pagination...
        return response()->json($data);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Customer created']]
        ]);
    }
}
```

### Page Pattern
```jsx
import { Head, usePage } from '@inertiajs/react'
import PageContent from '@/Components/PageContent.jsx'
import GlobalPageHeader from '@/Components/GlobalPageHeader.jsx'
import DataGridTable from '@/Components/DataGridTable/DataGridTable.jsx'
import usePermissions from '@/Helpers/Context/usePermissions.js'

export default function CustomerListing() {
  const { hasPermission } = usePermissions()
  const [visible, setVisible] = useState(false)
  const [record, setRecord] = useState(null)
  const gridRef = useRef()

  const handleMutation = () => {
    gridRef.current?.api?.purgeServerSideCache()
  }

  const columns = [
    { headerName: 'Name', field: 'name', sortable: true },
    {
      headerName: 'Actions',
      pinned: 'right',
      cellRenderer: (params) => (
        <Dropdown menu={{ items: [
          hasPermission('edit customer') && { key: 'edit', label: 'Edit' }
        ].filter(Boolean) }}>
          <Button icon={<EllipsisOutlined />} />
        </Dropdown>
      )
    }
  ]

  return (
    <>
      <Head title="Customers" />
      <PageContent title="Customers">
        <GlobalPageHeader
          title="Customers"
          actionButtons={[{
            title: 'Add Customer',
            icon: <PlusOutlined />,
            onClick: () => { setRecord(null); setVisible(true) },
            hasPermission: hasPermission('create customer')
          }]}
        />
        <DataGridTable routeName="customers.index" columns={columns} gridRef={gridRef} />
        {visible && <CustomerModal visible={visible} record={record} onUpdate={handleMutation} />}
      </PageContent>
    </>
  )
}
```

### Modal Pattern
```jsx
import CustomModal from '@/Components/CustomModal.jsx'
import { Form, Input, message } from 'antd'
import { handleApiSuccess, handleApiError } from '@/Helpers/CONSTANT.js'
import { createCustomer, updateCustomer } from '@/Helpers/api/customer.js'

const CustomerModal = ({ visible, onCancel, record, onUpdate }) => {
  const [form] = Form.useForm()
  const [loading, setLoading] = useState(false)

  const onFinish = async (values) => {
    setLoading(true)
    try {
      const response = record
        ? await updateCustomer(record.id, values)
        : await createCustomer(values)
      handleApiSuccess(response)
      onUpdate()
      onCancel()
    } catch (error) {
      handleApiError(error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <CustomModal open={visible} onCancel={onCancel} title={record ? 'Edit' : 'Create'}>
      <Form form={form} onFinish={onFinish} layout="vertical" initialValues={record}>
        <Form.Item name="name" label="Name" rules={[{ required: true }]}>
          <Input />
        </Form.Item>
        <Button type="primary" htmlType="submit" loading={loading}>Save</Button>
      </Form>
    </CustomModal>
  )
}
```

### API Helpers Pattern (Helpers/api/*.js)
```js
import axios from 'axios'

export const fetchCustomerListing = () => axios.post(route('customers.listing'))
export const createCustomer = (data) => axios.post(route('customers.store'), data)
export const updateCustomer = (id, data) => axios.put(route('customers.update', id), data)
export const deleteCustomer = (id) => axios.delete(route('customers.destroy', id))
```

### DataGridTable Usage
```jsx
<DataGridTable
  routeName="customers.index"     // Laravel route for server-side data
  columns={columns}               // AG Grid column definitions
  gridRef={gridRef}               // useRef to control grid
  pageSize={20}                   // Default page size
  showSoftDeleted={true}          // Include soft-deleted toggle
/>
```

### Permission Checking
```jsx
import usePermissions from '@/Helpers/Context/usePermissions'

const { hasPermission } = usePermissions()
if (hasPermission('create customer')) { ... }
if (hasPermission(['create customer', 'admin'])) { ... }  // OR logic
```

### Recoil Atoms (Helpers/atom.js)
```js
themeAtom       // 'light' | 'dark'
userAtom        // Current user object
permissionsAtom // Array of permission strings
menuStateAtom   // Sidebar collapse/open state
```

### Error Handling
```js
import { handleApiSuccess, handleApiError } from '@/Helpers/CONSTANT.js'

try {
  const response = await createCustomer(data)
  handleApiSuccess(response)  // Shows success notifications
} catch (error) {
  handleApiError(error)       // Shows error messages
}
```

## Routes (Ziggy)
```jsx
route('customers.index')              // /customers
route('customers.edit', id)           // /customers/{id}/edit
route('customers.store')              // POST /customers
route('customers.update', id)         // PUT /customers/{id}

// Navigation
import { router } from '@inertiajs/react'
router.visit(route('customers.show', id))
```

## Component Reference

| Component | Usage |
|-----------|-------|
| `PersistentLayout` | Main layout wrapper (auto-applied) |
| `PageContent` | Page body wrapper with title |
| `GlobalPageHeader` | Header with breadcrumbs + action buttons |
| `DataGridTable` | AG Grid with server-side pagination |
| `CustomModal` | Styled modal wrapper |
| `Button1` | Unified button component |
| `GlobalFilter` | Advanced filtering UI |


### Coding Principles

**DRY (Don't Repeat Yourself)**
- Extract repeated logic into reusable functions/components/actions
- Use existing helpers (`@/Helpers/`, `@/Utils/`) before creating new ones
- Share constants via `@/Constants/` — never duplicate magic strings/numbers
- Common API patterns → `@/Helpers/api/` | Common UI → `@/Components/`
- If you copy-paste code 3+ times, refactor into shared utility

**KISS (Keep It Simple, Stupid)**
- Solve the immediate problem — no speculative features
- Prefer readable code over clever one-liners
- One function = one job | One component = one purpose
- Avoid deep nesting (max 3 levels) — extract early returns or helpers
- Don't abstract until you have 3+ concrete use cases
- Choose boring tech over novel solutions
- If explanation needed, code is too complex — simplify first

**Apply Both**
- Reuse existing patterns (check `Pages/`, `Components/` for examples)
- New feature? Find similar existing feature, follow its structure
- Question every new file — can existing code handle it?

---
