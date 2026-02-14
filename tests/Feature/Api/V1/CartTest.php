<?php

namespace Tests\Feature\Api\V1;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Store $store;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
        $this->product = Product::factory()->create([
            'sale_price' => 100.00,
            'cost_price' => 60.00,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_view_empty_cart(): void
    {
        $response = $this->getJson('/api/v1/cart?store_id=' . $this->store->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cart',
                'summary',
            ]);
    }

    public function test_can_add_item_to_cart(): void
    {
        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'store_id' => $this->store->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'item',
                'cart',
                'summary',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        // First add an item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $cartItem = $cart->items->first();

        $response = $this->putJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        // First add an item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $cartItem = $cart->items->first();

        $response = $this->deleteJson("/api/v1/cart/items/{$cartItem->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_can_clear_cart(): void
    {
        // Add items
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'store_id' => $this->store->id,
        ]);

        $response = $this->deleteJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cart cleared']);
    }

    public function test_can_set_customer_on_cart(): void
    {
        $customer = Customer::factory()->create();

        // Add item first
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $response = $this->postJson('/api/v1/cart/customer', [
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals($customer->id, $cart->customer_id);
    }

    public function test_can_apply_discount_to_cart(): void
    {
        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $response = $this->postJson('/api/v1/cart/discount', [
            'amount' => 10,
            'type' => 'fixed',
            'reason' => 'Loyalty discount',
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(10, $cart->discount);
        $this->assertEquals('fixed', $cart->discount_type);
    }

    public function test_can_apply_percentage_discount(): void
    {
        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $response = $this->postJson('/api/v1/cart/discount', [
            'amount' => 15,
            'type' => 'percentage',
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(15, $cart->discount);
        $this->assertEquals('percentage', $cart->discount_type);
    }

    public function test_can_remove_discount(): void
    {
        // Add item and discount
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $this->postJson('/api/v1/cart/discount', [
            'amount' => 10,
            'type' => 'fixed',
        ]);

        $response = $this->deleteJson('/api/v1/cart/discount');

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(0, $cart->discount);
        $this->assertNull($cart->discount_type);
    }

    public function test_can_hold_cart(): void
    {
        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $response = $this->postJson('/api/v1/cart/hold', [
            'name' => 'Customer John',
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals('held', $cart->status);
        $this->assertEquals('Customer John', $cart->hold_name);
    }

    public function test_can_get_held_orders(): void
    {
        // Hold a cart
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $this->postJson('/api/v1/cart/hold', [
            'name' => 'Test Hold',
        ]);

        $response = $this->getJson('/api/v1/cart/hold?store_id=' . $this->store->id);

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_checkout_creates_order(): void
    {
        // Add item
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'store_id' => $this->store->id,
        ]);

        $response = $this->postJson('/api/v1/cart/checkout', [
            'payment_type' => 'cash',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'order',
                'receipt',
            ]);

        $this->assertDatabaseHas('orders', [
            'store_id' => $this->store->id,
            'payment_type' => 'cash',
        ]);
    }

    public function test_cannot_checkout_empty_cart(): void
    {
        $response = $this->postJson('/api/v1/cart/checkout', [
            'payment_type' => 'cash',
        ]);

        $response->assertStatus(422);
    }

    public function test_validates_add_item_request(): void
    {
        $response = $this->postJson('/api/v1/cart/items', [
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_validates_invalid_product_id(): void
    {
        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }
}
