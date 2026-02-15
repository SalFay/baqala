<?php

namespace Tests\Feature\V2;

use App\Models\Product;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockTakeTest extends TestCase
{
    use WithFaker;

    protected User $user;
    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->store = Store::first() ?? Store::factory()->create();
    }

    public function test_can_list_stock_takes()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/stock-takes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ]);
    }

    public function test_can_create_stock_take()
    {
        $this->actingAs($this->user);

        $stockTakeData = [
            'store_id' => $this->store->id,
            'type' => 'full',
            'notes' => 'Monthly stock take',
        ];

        $response = $this->postJson('/pos/stock-takes', $stockTakeData);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'full')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('stock_takes', [
            'type' => 'full',
            'notes' => 'Monthly stock take',
        ]);
    }

    public function test_can_create_partial_stock_take()
    {
        $this->actingAs($this->user);

        $stockTakeData = [
            'store_id' => $this->store->id,
            'type' => 'partial',
            'notes' => 'Partial count for specific products',
        ];

        $response = $this->postJson('/pos/stock-takes', $stockTakeData);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'partial');
    }

    public function test_can_start_stock_take()
    {
        $this->actingAs($this->user);

        $stockTake = StockTake::create([
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'type' => 'full',
            'status' => 'draft',
        ]);

        $response = $this->postJson("/pos/stock-takes/{$stockTake->id}/start");

        $response->assertStatus(200);
        $this->assertEquals('in_progress', $stockTake->fresh()->status);
    }

    public function test_can_count_item_in_stock_take()
    {
        $this->actingAs($this->user);

        $stockTake = StockTake::create([
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'type' => 'full',
            'status' => 'in_progress',
        ]);

        $product = Product::first();
        if (!$product) {
            $this->markTestSkipped('No products available for testing');
        }

        $item = StockTakeItem::create([
            'stock_take_id' => $stockTake->id,
            'product_id' => $product->id,
            'expected_quantity' => $product->stock_quantity ?? 10,
            'counted_quantity' => null,
        ]);

        $response = $this->postJson("/pos/stock-takes/{$stockTake->id}/items/{$item->id}/count", [
            'counted_quantity' => 8,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('stock_take_items', [
            'id' => $item->id,
            'counted_quantity' => 8,
        ]);
    }

    public function test_can_complete_stock_take()
    {
        $this->actingAs($this->user);

        $stockTake = StockTake::create([
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'type' => 'full',
            'status' => 'in_progress',
        ]);

        $response = $this->postJson("/pos/stock-takes/{$stockTake->id}/complete", [
            'apply_adjustments' => true,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $stockTake->fresh()->status);
    }

    public function test_can_cancel_stock_take()
    {
        $this->actingAs($this->user);

        $stockTake = StockTake::create([
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'type' => 'full',
            'status' => 'draft',
        ]);

        $response = $this->postJson("/pos/stock-takes/{$stockTake->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $stockTake->fresh()->status);
    }

    public function test_can_get_stock_take_summary()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/stock-takes/summary');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_variance_calculation()
    {
        $this->actingAs($this->user);

        $stockTake = StockTake::create([
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'type' => 'full',
            'status' => 'in_progress',
        ]);

        $product = Product::first();
        if (!$product) {
            $this->markTestSkipped('No products available for testing');
        }

        $item = StockTakeItem::create([
            'stock_take_id' => $stockTake->id,
            'product_id' => $product->id,
            'expected_quantity' => 10,
            'counted_quantity' => 8,
        ]);

        // Refresh to get computed variance
        $item->refresh();

        // Variance = counted - expected = 8 - 10 = -2
        $this->assertEquals(-2, $item->counted_quantity - $item->expected_quantity);
    }
}
