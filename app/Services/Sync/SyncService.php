<?php

namespace App\Services\Sync;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethods;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Models\TaxRate;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncService
{
    protected array $entityClasses = [
        'products' => Product::class,
        'categories' => Category::class,
        'customers' => Customer::class,
        'tax_rates' => TaxRate::class,
    ];

    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Bootstrap sync - full data download for initial sync.
     */
    public function bootstrap(string $terminalId, int $storeId, ?array $entities = null): array
    {
        $startTime = microtime(true);

        // Register/update terminal
        $this->registerTerminal($terminalId, $storeId, []);

        // Default entities if not specified
        $entities = $entities ?? ['products', 'categories', 'customers', 'settings', 'tax_rates', 'payment_methods'];

        $data = [];

        if (in_array('products', $entities)) {
            $data['products'] = $this->getProducts($storeId);
        }

        if (in_array('categories', $entities)) {
            $data['categories'] = $this->getCategories();
        }

        if (in_array('customers', $entities)) {
            $data['customers'] = $this->getCustomers($storeId);
        }

        if (in_array('settings', $entities)) {
            $data['settings'] = $this->getSettings($storeId);
        }

        if (in_array('tax_rates', $entities)) {
            $data['tax_rates'] = $this->getTaxRates();
        }

        if (in_array('payment_methods', $entities)) {
            $data['payment_methods'] = $this->getPaymentMethods();
        }

        // Get store info
        $data['store'] = Store::find($storeId);

        // Meta information
        $data['meta'] = [
            'sync_version' => $this->getCurrentSyncVersion(),
            'synced_at' => now()->toISOString(),
            'duration_ms' => round((microtime(true) - $startTime) * 1000),
        ];

        // Log sync
        $this->logSync($terminalId, 'bootstrap', 'completed', [
            'records_sent' => $this->countRecords($data),
            'duration_ms' => $data['meta']['duration_ms'],
        ]);

        return $data;
    }

    /**
     * Pull changes since last sync (delta sync).
     */
    public function pull(string $terminalId, int $storeId, ?string $lastSyncAt, array $entityVersions = []): array
    {
        $startTime = microtime(true);

        $lastSync = $lastSyncAt ? \Carbon\Carbon::parse($lastSyncAt) : null;

        $changes = [];

        // Products updated since last sync
        $changes['products'] = $this->getProductChanges($storeId, $lastSync);

        // Categories updated since last sync
        $changes['categories'] = $this->getCategoryChanges($lastSync);

        // Customers updated since last sync
        $changes['customers'] = $this->getCustomerChanges($lastSync);

        // Settings updated since last sync
        $changes['settings'] = $this->getSettingChanges($storeId, $lastSync);

        // Inventory updates
        $changes['inventory'] = $this->getInventoryChanges($storeId, $lastSync);

        // Deleted records
        $changes['deleted'] = $this->getDeletedRecords($lastSync);

        // Update terminal last sync
        $this->updateTerminalLastSync($terminalId);

        $changes['meta'] = [
            'sync_version' => $this->getCurrentSyncVersion(),
            'synced_at' => now()->toISOString(),
            'duration_ms' => round((microtime(true) - $startTime) * 1000),
            'has_more' => false,
        ];

        // Log sync
        $this->logSync($terminalId, 'pull', 'completed', [
            'records_received' => $this->countRecords($changes),
            'duration_ms' => $changes['meta']['duration_ms'],
        ]);

        return $changes;
    }

    /**
     * Push changes from client to server.
     */
    public function push(string $terminalId, int $storeId, array $data): array
    {
        $startTime = microtime(true);

        $results = [
            'orders' => [],
            'customers' => [],
            'conflicts' => [],
        ];

        DB::beginTransaction();

        try {
            // Process offline orders
            if (!empty($data['orders'])) {
                foreach ($data['orders'] as $orderData) {
                    $result = $this->processOfflineOrder($terminalId, $storeId, $orderData);
                    $results['orders'][] = $result;
                }
            }

            // Process customers created offline
            if (!empty($data['customers'])) {
                foreach ($data['customers'] as $customerData) {
                    $result = $this->processOfflineCustomer($terminalId, $customerData);
                    $results['customers'][] = $result;
                }
            }

            DB::commit();

            $results['meta'] = [
                'synced_at' => now()->toISOString(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000),
                'success' => true,
            ];

            // Log sync
            $this->logSync($terminalId, 'push', 'completed', [
                'records_sent' => count($data['orders'] ?? []) + count($data['customers'] ?? []),
                'conflicts_count' => count($results['conflicts']),
                'duration_ms' => $results['meta']['duration_ms'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $results['meta'] = [
                'synced_at' => now()->toISOString(),
                'success' => false,
                'error' => $e->getMessage(),
            ];

            $this->logSync($terminalId, 'push', 'failed', [
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $results;
    }

    /**
     * Get sync status for a terminal.
     */
    public function getStatus(string $terminalId, int $storeId): array
    {
        $terminal = DB::table('terminal_registrations')
            ->where('terminal_id', $terminalId)
            ->first();

        $pendingConflicts = DB::table('sync_conflicts')
            ->where('terminal_id', $terminalId)
            ->whereNull('resolution')
            ->count();

        $pendingOrders = DB::table('offline_orders')
            ->where('terminal_id', $terminalId)
            ->where('status', 'pending')
            ->count();

        $lastSync = DB::table('sync_logs')
            ->where('terminal_id', $terminalId)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->first();

        return [
            'terminal_registered' => $terminal !== null,
            'terminal_active' => $terminal?->is_active ?? false,
            'last_sync_at' => $terminal?->last_sync_at,
            'pending_conflicts' => $pendingConflicts,
            'pending_orders' => $pendingOrders,
            'last_sync' => $lastSync ? [
                'operation' => $lastSync->operation,
                'status' => $lastSync->status,
                'completed_at' => $lastSync->created_at,
            ] : null,
            'server_version' => $this->getCurrentSyncVersion(),
            'server_time' => now()->toISOString(),
        ];
    }

    /**
     * Register or update a terminal.
     */
    public function registerTerminal(string $terminalId, int $storeId, array $data): object
    {
        $terminal = DB::table('terminal_registrations')
            ->updateOrInsert(
                ['terminal_id' => $terminalId],
                [
                    'store_id' => $storeId,
                    'user_id' => auth()->id(),
                    'name' => $data['name'] ?? null,
                    'device_info' => $data['device_info'] ?? null,
                    'app_version' => $data['app_version'] ?? null,
                    'last_seen_at' => now(),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );

        return DB::table('terminal_registrations')
            ->where('terminal_id', $terminalId)
            ->first();
    }

    /**
     * Resolve a sync conflict.
     */
    public function resolveConflict(string $conflictId, string $resolution, ?array $resolvedData = null): array
    {
        $conflict = DB::table('sync_conflicts')
            ->where('conflict_id', $conflictId)
            ->first();

        if (!$conflict) {
            throw new \InvalidArgumentException('Conflict not found');
        }

        if ($conflict->resolution) {
            throw new \InvalidArgumentException('Conflict already resolved');
        }

        $finalData = match ($resolution) {
            'client_wins' => json_decode($conflict->client_data, true),
            'server_wins' => json_decode($conflict->server_data, true),
            'manual' => $resolvedData,
            default => throw new \InvalidArgumentException('Invalid resolution type'),
        };

        DB::table('sync_conflicts')
            ->where('conflict_id', $conflictId)
            ->update([
                'resolution' => $resolution,
                'resolved_data' => json_encode($finalData),
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);

        return [
            'conflict_id' => $conflictId,
            'resolution' => $resolution,
            'resolved_at' => now()->toISOString(),
        ];
    }

    /**
     * Get pending conflicts for a terminal.
     */
    public function getPendingConflicts(string $terminalId): array
    {
        return DB::table('sync_conflicts')
            ->where('terminal_id', $terminalId)
            ->whereNull('resolution')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => [
                'conflict_id' => $c->conflict_id,
                'entity_type' => $c->entity_type,
                'entity_id' => $c->entity_id,
                'client_data' => json_decode($c->client_data, true),
                'server_data' => json_decode($c->server_data, true),
                'created_at' => $c->created_at,
            ])
            ->toArray();
    }

    // Private helper methods

    protected function getProducts(int $storeId): array
    {
        return Product::query()
            ->with(['category', 'variants'])
            ->where('is_active', true)
            ->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            })
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'name_ar' => $p->name_ar,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'type' => $p->type?->value,
                'category_id' => $p->category_id,
                'cost_price' => (float) $p->cost_price,
                'sale_price' => (float) $p->sale_price,
                'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
                'track_inventory' => $p->track_inventory,
                'low_stock_threshold' => $p->low_stock_threshold,
                'image_url' => $p->image_url,
                'stock_quantity' => $p->getStockQuantity($storeId),
                'variants' => $p->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'cost_price' => (float) $v->cost_price,
                    'sale_price' => (float) $v->sale_price,
                    'stock_quantity' => $v->getStockQuantity($storeId),
                ])->toArray(),
                'updated_at' => $p->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getCategories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'parent_id' => $c->parent_id,
                'image' => $c->image,
                'sort_order' => $c->sort_order,
                'updated_at' => $c->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getCustomers(int $storeId): array
    {
        return Customer::query()
            ->with('loyalty')
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'first_name' => $c->first_name,
                'last_name' => $c->last_name,
                'full_name' => $c->full_name,
                'business_name' => $c->business_name,
                'email' => $c->email,
                'phone' => $c->phone,
                'loyalty_card_number' => $c->loyalty_card_number,
                'credit_balance' => (float) $c->credit_balance,
                'credit_limit' => (float) $c->credit_limit,
                'loyalty_points' => $c->loyalty_points,
                'updated_at' => $c->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getSettings(int $storeId): array
    {
        $store = Store::find($storeId);

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'phone' => $store->phone,
                'email' => $store->email,
                'tax_number' => $store->tax_number,
                'currency' => $store->currency ?? 'SAR',
            ],
            'pos' => [
                'tax_rate' => (float) Setting::get('default_tax_rate', 15),
                'tax_inclusive' => (bool) Setting::get('tax_inclusive', false),
                'allow_negative_inventory' => (bool) Setting::get('allow_negative_inventory', false),
                'loyalty_points_value' => (float) Setting::get('loyalty_points_value', 0.01),
                'points_per_currency' => (int) Setting::get('points_per_currency', 1),
                'receipt_footer' => Setting::get('receipt_footer', ''),
            ],
        ];
    }

    protected function getTaxRates(): array
    {
        return TaxRate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'rate' => (float) $t->rate,
                'is_default' => $t->is_default,
            ])
            ->toArray();
    }

    protected function getPaymentMethods(): array
    {
        return PaymentMethods::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ])
            ->toArray();
    }

    protected function getProductChanges(int $storeId, ?\Carbon\Carbon $since): array
    {
        $query = Product::query()
            ->with(['category', 'variants'])
            ->where('is_active', true)
            ->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        return $query->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'name_ar' => $p->name_ar,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'type' => $p->type?->value,
                'category_id' => $p->category_id,
                'cost_price' => (float) $p->cost_price,
                'sale_price' => (float) $p->sale_price,
                'track_inventory' => $p->track_inventory,
                'stock_quantity' => $p->getStockQuantity($storeId),
                'updated_at' => $p->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getCategoryChanges(?\Carbon\Carbon $since): array
    {
        $query = Category::query()->where('is_active', true);

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        return $query->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'parent_id' => $c->parent_id,
                'sort_order' => $c->sort_order,
                'updated_at' => $c->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getCustomerChanges(?\Carbon\Carbon $since): array
    {
        $query = Customer::query()->where('status', 'Active');

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        return $query->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'first_name' => $c->first_name,
                'last_name' => $c->last_name,
                'full_name' => $c->full_name,
                'phone' => $c->phone,
                'loyalty_points' => $c->loyalty_points,
                'credit_balance' => (float) $c->credit_balance,
                'updated_at' => $c->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getSettingChanges(int $storeId, ?\Carbon\Carbon $since): array
    {
        // Settings don't have updated_at tracking, return all if within window
        if (!$since || $since->diffInHours(now()) > 1) {
            return $this->getSettings($storeId);
        }
        return [];
    }

    protected function getInventoryChanges(int $storeId, ?\Carbon\Carbon $since): array
    {
        $query = StoreInventory::query()
            ->with('product')
            ->where('store_id', $storeId);

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        return $query->get()
            ->map(fn($i) => [
                'product_id' => $i->product_id,
                'product_variant_id' => $i->product_variant_id,
                'quantity' => $i->quantity,
                'updated_at' => $i->updated_at->toISOString(),
            ])
            ->toArray();
    }

    protected function getDeletedRecords(?\Carbon\Carbon $since): array
    {
        if (!$since) {
            return [];
        }

        return [
            'products' => Product::onlyTrashed()
                ->where('deleted_at', '>', $since)
                ->pluck('id')
                ->toArray(),
            'categories' => Category::onlyTrashed()
                ->where('deleted_at', '>', $since)
                ->pluck('id')
                ->toArray(),
            'customers' => Customer::onlyTrashed()
                ->where('deleted_at', '>', $since)
                ->pluck('id')
                ->toArray(),
        ];
    }

    protected function processOfflineOrder(string $terminalId, int $storeId, array $orderData): array
    {
        $offlineId = $orderData['offline_id'];
        $data = $orderData['data'];

        // Check if already processed
        $existing = DB::table('offline_orders')
            ->where('offline_id', $offlineId)
            ->first();

        if ($existing && $existing->status === 'synced') {
            return [
                'offline_id' => $offlineId,
                'status' => 'already_synced',
                'order_id' => $existing->order_id,
            ];
        }

        try {
            // Create the order from offline data
            $order = Order::create([
                'store_id' => $storeId,
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
                'payment_type' => $data['payment_type'] ?? 'cash',
                'date' => $orderData['created_offline_at'],
                'status' => $data['status'] ?? 'completed',
                'payment_status' => $data['payment_status'] ?? 'paid',
                'sub_total' => $data['subtotal'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'total' => $data['total'] ?? 0,
                'customer_name' => $data['customer_name'] ?? null,
                'cashier_name' => $data['cashier_name'] ?? null,
                'notes' => 'Synced from offline - Terminal: ' . $terminalId,
            ]);

            // Create order items
            foreach ($data['items'] ?? [] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'sku' => $item['sku'] ?? '',
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }

            // Record offline order
            DB::table('offline_orders')->updateOrInsert(
                ['offline_id' => $offlineId],
                [
                    'terminal_id' => $terminalId,
                    'store_id' => $storeId,
                    'order_id' => $order->id,
                    'order_data' => json_encode($data),
                    'status' => 'synced',
                    'created_offline_at' => $orderData['created_offline_at'],
                    'synced_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return [
                'offline_id' => $offlineId,
                'status' => 'synced',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ];

        } catch (\Exception $e) {
            DB::table('offline_orders')->updateOrInsert(
                ['offline_id' => $offlineId],
                [
                    'terminal_id' => $terminalId,
                    'store_id' => $storeId,
                    'order_data' => json_encode($data),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => DB::raw('retry_count + 1'),
                    'created_offline_at' => $orderData['created_offline_at'],
                    'updated_at' => now(),
                ]
            );

            return [
                'offline_id' => $offlineId,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function processOfflineCustomer(string $terminalId, array $customerData): array
    {
        $offlineId = $customerData['offline_id'];
        $data = $customerData['data'];

        try {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'business_name' => $data['business_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            return [
                'offline_id' => $offlineId,
                'status' => 'synced',
                'customer_id' => $customer->id,
            ];

        } catch (\Exception $e) {
            return [
                'offline_id' => $offlineId,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function updateTerminalLastSync(string $terminalId): void
    {
        DB::table('terminal_registrations')
            ->where('terminal_id', $terminalId)
            ->update([
                'last_sync_at' => now(),
                'last_seen_at' => now(),
            ]);
    }

    protected function logSync(string $terminalId, string $operation, string $status, array $data = []): void
    {
        DB::table('sync_logs')->insert([
            'terminal_id' => $terminalId,
            'operation' => $operation,
            'status' => $status,
            'records_sent' => $data['records_sent'] ?? 0,
            'records_received' => $data['records_received'] ?? 0,
            'conflicts_count' => $data['conflicts_count'] ?? 0,
            'duration_ms' => $data['duration_ms'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function countRecords(array $data): int
    {
        $count = 0;
        foreach ($data as $key => $value) {
            if (is_array($value) && $key !== 'meta') {
                $count += count($value);
            }
        }
        return $count;
    }

    protected function getCurrentSyncVersion(): int
    {
        return (int) DB::table('sync_versions')
            ->max('version') ?? 1;
    }
}
