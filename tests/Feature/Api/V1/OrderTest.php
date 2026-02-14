<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
        $this->customer = Customer::factory()->create();

        Sanctum::actingAs($this->user);
    }

    protected function createOrder(array $attributes = []): Order
    {
        return Order::factory()
            ->for($this->store)
            ->for($this->user)
            ->create(array_merge([
                'status' => OrderStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
            ], $attributes));
    }

    public function test_can_list_orders(): void
    {
        $this->createOrder();
        $this->createOrder();

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_orders_by_store(): void
    {
        $this->createOrder();
        $otherStore = Store::factory()->create();
        Order::factory()->for($otherStore)->create();

        $response = $this->getJson('/api/v1/orders?store_id=' . $this->store->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_orders_by_status(): void
    {
        $this->createOrder(['status' => OrderStatus::COMPLETED]);
        $this->createOrder(['status' => OrderStatus::CANCELLED]);

        $response = $this->getJson('/api/v1/orders?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_orders_by_date_range(): void
    {
        Order::factory()
            ->for($this->store)
            ->for($this->user)
            ->create(['created_at' => now()->subDays(5)]);

        Order::factory()
            ->for($this->store)
            ->for($this->user)
            ->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/orders?' . http_build_query([
            'from_date' => now()->subDays(2)->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_search_orders(): void
    {
        $order = $this->createOrder(['customer_name' => 'John Doe']);
        $this->createOrder(['customer_name' => 'Jane Smith']);

        $response = $this->getJson('/api/v1/orders?search=John');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_single_order(): void
    {
        $order = $this->createOrder();

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $order->id);
    }

    public function test_can_get_order_receipt(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create();

        OrderItem::factory()
            ->for($order)
            ->for($product)
            ->create();

        $response = $this->getJson("/api/v1/orders/{$order->id}/receipt");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'order',
                'store',
                'items',
                'subtotal',
                'tax',
                'discount',
                'total',
            ]);
    }

    public function test_can_cancel_order(): void
    {
        $order = $this->createOrder(['status' => OrderStatus::COMPLETED]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel", [
            'reason' => 'Customer requested cancellation',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED, $order->status);
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $order = $this->createOrder(['status' => OrderStatus::CANCELLED]);

        $response = $this->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_can_get_today_orders(): void
    {
        $this->createOrder(['created_at' => now()]);
        $this->createOrder(['created_at' => now()->subDays(1)]);

        $response = $this->getJson('/api/v1/orders/today?store_id=' . $this->store->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'orders',
                'stats' => [
                    'total_orders',
                    'completed_orders',
                    'total_sales',
                ],
            ]);
    }

    public function test_can_get_recent_orders(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->createOrder();
        }

        $response = $this->getJson('/api/v1/orders/recent?limit=10&store_id=' . $this->store->id);

        $response->assertStatus(200)
            ->assertJsonCount(10);
    }

    public function test_orders_are_paginated(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->createOrder();
        }

        $response = $this->getJson('/api/v1/orders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 30);
    }
}
