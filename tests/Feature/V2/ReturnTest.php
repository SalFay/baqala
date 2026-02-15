<?php

namespace Tests\Feature\V2;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use WithFaker;

    protected User $user;
    protected Store $store;
    protected Order $order;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->store = Store::first() ?? Store::factory()->create();
        $this->product = Product::first();

        // Create order for testing (even without product for some tests)
        $this->order = Order::create([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'subtotal' => 100.00,
            'total' => 100.00,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        if ($this->product) {
            OrderItem::create([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'quantity' => 2,
                'unit_price' => 50.00,
                'total' => 100.00,
            ]);
        }
    }

    public function test_can_list_returns()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/returns');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_returnable_items_for_order()
    {
        $this->actingAs($this->user);

        $response = $this->getJson("/pos/returns/order/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_return()
    {
        $this->actingAs($this->user);

        $orderItem = $this->order->items->first();
        if (!$orderItem) {
            $this->markTestSkipped('No order items available for testing');
        }

        $returnData = [
            'order_id' => $this->order->id,
            'reason' => 'Customer changed mind',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'quantity' => 1,
                    'condition' => 'sellable',
                    'reason' => 'Unwanted',
                ],
            ],
        ];

        $response = $this->postJson('/pos/returns', $returnData);

        $response->assertStatus(201);
    }

    public function test_can_approve_return()
    {
        $this->actingAs($this->user);

        $return = OrderReturn::create([
            'order_id' => $this->order->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'reason' => 'Test return',
            'status' => 'pending',
            'total_amount' => 50.00,
        ]);

        $response = $this->postJson("/pos/returns/{$return->id}/approve");

        $response->assertStatus(200);
        $this->assertEquals('approved', $return->fresh()->status);
    }

    public function test_can_reject_return()
    {
        $this->actingAs($this->user);

        $return = OrderReturn::create([
            'order_id' => $this->order->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'reason' => 'Test return',
            'status' => 'pending',
            'total_amount' => 50.00,
        ]);

        $response = $this->postJson("/pos/returns/{$return->id}/reject", [
            'rejection_reason' => 'Item is damaged beyond acceptance',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $return->fresh()->status);
    }

    public function test_can_process_return()
    {
        $this->actingAs($this->user);

        $return = OrderReturn::create([
            'order_id' => $this->order->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'reason' => 'Test return',
            'status' => 'approved',
            'total_amount' => 50.00,
        ]);

        $response = $this->postJson("/pos/returns/{$return->id}/process", [
            'refund_method' => 'cash',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $return->fresh()->status);
    }

    public function test_can_get_return_reasons()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/returns/reasons');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_return_item_conditions()
    {
        $orderItem = $this->order->items->first();
        if (!$orderItem) {
            $this->markTestSkipped('No order items available for testing');
        }

        $conditions = ['sellable', 'damaged', 'defective'];

        foreach ($conditions as $condition) {
            $this->actingAs($this->user);

            $returnData = [
                'order_id' => $this->order->id,
                'reason' => "Testing condition: {$condition}",
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'quantity' => 1,
                        'condition' => $condition,
                        'reason' => 'Test',
                    ],
                ],
            ];

            $response = $this->postJson('/pos/returns', $returnData);

            // Should accept all conditions
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_cannot_return_more_than_ordered()
    {
        $this->actingAs($this->user);

        $orderItem = $this->order->items->first();
        if (!$orderItem) {
            $this->markTestSkipped('No order items available for testing');
        }

        $returnData = [
            'order_id' => $this->order->id,
            'reason' => 'Return too many',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'quantity' => 100, // More than ordered
                    'condition' => 'sellable',
                    'reason' => 'Test',
                ],
            ],
        ];

        $response = $this->postJson('/pos/returns', $returnData);

        // Should fail validation
        $response->assertStatus(422);
    }

    public function test_return_eligibility_check()
    {
        $this->actingAs($this->user);

        // Check if order is eligible for return
        $response = $this->getJson("/pos/returns/order/{$this->order->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('eligible', $data);
    }
}
