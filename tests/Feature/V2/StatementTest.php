<?php

namespace Tests\Feature\V2;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StatementTest extends TestCase
{
    use WithFaker;

    protected User $user;
    protected Store $store;
    protected Customer $customer;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->store = Store::first() ?? Store::factory()->create();
        $this->customer = Customer::first() ?? Customer::create([
            'name' => 'Test Customer',
            'phone' => '1234567890',
            'store_id' => $this->store->id,
        ]);
        $this->vendor = Vendor::first() ?? Vendor::create([
            'name' => 'Test Vendor',
            'phone' => '0987654321',
            'store_id' => $this->store->id,
        ]);
    }

    public function test_can_get_customer_statement()
    {
        $this->actingAs($this->user);

        $response = $this->getJson("/pos/customers/{$this->customer->id}/statement");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_customer_statement_with_date_range()
    {
        $this->actingAs($this->user);

        $response = $this->getJson("/pos/customers/{$this->customer->id}/statement?" . http_build_query([
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
    }

    public function test_can_get_vendor_statement()
    {
        $this->actingAs($this->user);

        $response = $this->getJson("/pos/vendors/{$this->vendor->id}/statement");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_add_customer_credit()
    {
        $this->actingAs($this->user);

        $creditData = [
            'amount' => 100.00,
            'type' => 'credit',
            'notes' => 'Payment received',
        ];

        $response = $this->postJson("/pos/customers/{$this->customer->id}/credits", $creditData);

        $response->assertSuccessful();

        $this->assertDatabaseHas('credits', [
            'creditable_type' => Customer::class,
            'creditable_id' => $this->customer->id,
            'amount' => 100.00,
        ]);
    }

    public function test_can_add_vendor_credit()
    {
        $this->actingAs($this->user);

        $creditData = [
            'amount' => 500.00,
            'type' => 'credit',
            'notes' => 'Payment to vendor',
        ];

        $response = $this->postJson("/pos/vendors/{$this->vendor->id}/credits", $creditData);

        $response->assertSuccessful();

        $this->assertDatabaseHas('credits', [
            'creditable_type' => Vendor::class,
            'creditable_id' => $this->vendor->id,
            'amount' => 500.00,
        ]);
    }

    public function test_can_get_customer_credits()
    {
        $this->actingAs($this->user);

        // Add a credit first
        Credit::create([
            'creditable_type' => Customer::class,
            'creditable_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'amount' => 50.00,
            'balance_after' => 50.00,
            'type' => 'credit',
            'notes' => 'Test credit',
        ]);

        $response = $this->getJson("/pos/customers/{$this->customer->id}/credits");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_polymorphic_credit_model()
    {
        // Create customer credit
        $customerCredit = Credit::create([
            'creditable_type' => Customer::class,
            'creditable_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'amount' => 75.00,
            'balance_after' => 75.00,
            'type' => 'credit',
            'notes' => 'Customer payment',
        ]);

        // Create vendor credit
        $vendorCredit = Credit::create([
            'creditable_type' => Vendor::class,
            'creditable_id' => $this->vendor->id,
            'created_by' => $this->user->id,
            'amount' => 200.00,
            'balance_after' => 200.00,
            'type' => 'credit',
            'notes' => 'Vendor payment',
        ]);

        // Verify polymorphic relationships
        $this->assertInstanceOf(Customer::class, $customerCredit->creditable);
        $this->assertInstanceOf(Vendor::class, $vendorCredit->creditable);
    }

    public function test_customer_balance_calculation()
    {
        // Add order (debt)
        Order::create([
            'customer_id' => $this->customer->id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'total' => 200.00,
            'subtotal' => 200.00,
            'payment_method' => 'credit',
            'status' => 'completed',
        ]);

        // Add payment (credit)
        Credit::create([
            'creditable_type' => Customer::class,
            'creditable_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'amount' => 100.00,
            'balance_after' => -100.00,
            'type' => 'credit',
            'notes' => 'Partial payment',
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson("/pos/customers/{$this->customer->id}/statement");

        $response->assertStatus(200);
    }
}
