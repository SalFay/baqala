<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected string $terminalId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
        $this->terminalId = Str::uuid()->toString();

        Sanctum::actingAs($this->user);
    }

    public function test_can_register_terminal(): void
    {
        $response = $this->postJson('/api/v1/sync/register-terminal', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
            'name' => 'POS Terminal 1',
            'device_info' => 'Chrome on Windows',
            'app_version' => '2.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'terminal_id',
                    'store_id',
                ],
            ]);

        $this->assertDatabaseHas('terminal_registrations', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
        ]);
    }

    public function test_can_bootstrap_data(): void
    {
        // Create test data
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['is_active' => true]);
        $customer = Customer::factory()->create(['status' => 'Active']);

        $response = $this->postJson('/api/v1/sync/bootstrap', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products',
                    'categories',
                    'customers',
                    'settings',
                    'store',
                    'meta' => [
                        'sync_version',
                        'synced_at',
                    ],
                ],
            ]);
    }

    public function test_can_pull_changes(): void
    {
        $response = $this->getJson('/api/v1/sync/pull?' . http_build_query([
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products',
                    'categories',
                    'customers',
                    'inventory',
                    'deleted',
                    'meta',
                ],
            ]);
    }

    public function test_can_pull_changes_since_last_sync(): void
    {
        $lastSyncAt = now()->subHour();

        // Create product after last sync
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/sync/pull?' . http_build_query([
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
            'last_sync_at' => $lastSyncAt->toISOString(),
        ]));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data['products']);
    }

    public function test_can_push_offline_orders(): void
    {
        $offlineId = Str::uuid()->toString();

        $response = $this->postJson('/api/v1/sync/push', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
            'orders' => [
                [
                    'offline_id' => $offlineId,
                    'data' => [
                        'customer_name' => 'Test Customer',
                        'subtotal' => 100,
                        'tax_amount' => 15,
                        'discount' => 0,
                        'total' => 115,
                        'payment_type' => 'cash',
                        'items' => [
                            [
                                'product_id' => Product::factory()->create()->id,
                                'product_name' => 'Test Product',
                                'quantity' => 1,
                                'unit_price' => 100,
                                'cost_price' => 60,
                                'tax_rate' => 15,
                                'tax_amount' => 15,
                                'line_total' => 100,
                            ],
                        ],
                    ],
                    'created_offline_at' => now()->subMinutes(30)->toISOString(),
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'orders',
                    'meta',
                ],
            ]);

        $this->assertDatabaseHas('offline_orders', [
            'offline_id' => $offlineId,
            'status' => 'synced',
        ]);
    }

    public function test_can_get_sync_status(): void
    {
        // Register terminal first
        $this->postJson('/api/v1/sync/register-terminal', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
        ]);

        $response = $this->getJson('/api/v1/sync/status?' . http_build_query([
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'terminal_registered',
                    'terminal_active',
                    'pending_conflicts',
                    'pending_orders',
                    'server_version',
                    'server_time',
                ],
            ]);
    }

    public function test_can_get_pending_conflicts(): void
    {
        $response = $this->getJson('/api/v1/sync/conflicts?' . http_build_query([
            'terminal_id' => $this->terminalId,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_bootstrap_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/sync/bootstrap', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['terminal_id', 'store_id']);
    }

    public function test_bootstrap_validates_store_exists(): void
    {
        $response = $this->postJson('/api/v1/sync/bootstrap', [
            'terminal_id' => $this->terminalId,
            'store_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['store_id']);
    }

    public function test_push_validates_order_data(): void
    {
        $response = $this->postJson('/api/v1/sync/push', [
            'terminal_id' => $this->terminalId,
            'store_id' => $this->store->id,
            'orders' => [
                [
                    // Missing offline_id
                    'data' => [],
                ],
            ],
        ]);

        $response->assertStatus(422);
    }
}
