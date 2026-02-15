<?php

namespace Tests\Feature\V2;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use WithFaker;

    protected User $user;
    protected Store $store;
    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->store = Store::first() ?? Store::factory()->create();
        $this->category = ExpenseCategory::first() ?? ExpenseCategory::create([
            'name' => 'Office Supplies',
            'description' => 'Office supplies and stationery',
        ]);
    }

    public function test_can_list_expenses()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_can_create_expense()
    {
        $this->actingAs($this->user);

        $expenseData = [
            'expense_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'amount' => 150.00,
            'description' => 'Office supplies purchase',
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ];

        $response = $this->postJson('/pos/expenses', $expenseData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('expenses', [
            'amount' => 150.00,
            'description' => 'Office supplies purchase',
        ]);
    }

    public function test_can_approve_expense()
    {
        $this->actingAs($this->user);

        $expense = Expense::create([
            'expense_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'amount' => 100.00,
            'description' => 'Test expense',
            'expense_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->postJson("/pos/expenses/{$expense->id}/approve");

        $response->assertStatus(200);
        $this->assertEquals('approved', $expense->fresh()->status);
    }

    public function test_can_reject_expense()
    {
        $this->actingAs($this->user);

        $expense = Expense::create([
            'expense_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'amount' => 100.00,
            'description' => 'Test expense',
            'expense_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->postJson("/pos/expenses/{$expense->id}/reject", [
            'reason' => 'Invalid receipt',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rejected', $expense->fresh()->status);
    }

    public function test_can_mark_expense_as_paid()
    {
        $this->actingAs($this->user);

        $expense = Expense::create([
            'expense_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'amount' => 100.00,
            'description' => 'Test expense',
            'expense_date' => now(),
            'status' => 'approved',
        ]);

        $response = $this->postJson("/pos/expenses/{$expense->id}/paid");

        $response->assertStatus(200);
        $this->assertEquals('paid', $expense->fresh()->status);
    }

    public function test_can_get_expense_categories()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/expenses/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_expense_summary()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/expenses/summary');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_expense_workflow_draft_to_paid()
    {
        $this->actingAs($this->user);

        // Create as draft
        $expense = Expense::create([
            'expense_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'created_by' => $this->user->id,
            'amount' => 200.00,
            'description' => 'Full workflow test',
            'expense_date' => now(),
            'status' => 'draft',
        ]);

        // Submit for approval (update to pending)
        $expense->update(['status' => 'pending']);
        $this->assertEquals('pending', $expense->fresh()->status);

        // Approve
        $this->postJson("/pos/expenses/{$expense->id}/approve");
        $this->assertEquals('approved', $expense->fresh()->status);

        // Mark as paid
        $this->postJson("/pos/expenses/{$expense->id}/paid");
        $this->assertEquals('paid', $expense->fresh()->status);
    }
}
