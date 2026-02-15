<?php

namespace Tests\Feature\V2;

use App\Models\BusinessType;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BusinessTypeTest extends TestCase
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

    public function test_can_list_business_types()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/business-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description'],
                ],
            ]);
    }

    public function test_can_get_current_business_type()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/pos/business-types/current');

        $response->assertStatus(200);
    }

    public function test_can_seed_business_types()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/pos/business-types/seed');

        $response->assertStatus(200);

        // Should have business types in database
        $this->assertGreaterThan(0, BusinessType::count());
    }

    public function test_can_preview_business_type()
    {
        $this->actingAs($this->user);

        $businessType = BusinessType::first();
        if (!$businessType) {
            // Seed first
            $this->postJson('/pos/business-types/seed');
            $businessType = BusinessType::first();
        }

        if (!$businessType) {
            $this->markTestSkipped('No business types available');
        }

        $response = $this->getJson("/pos/business-types/{$businessType->id}/preview");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'business_type',
                    'categories',
                    'products',
                ],
            ]);
    }

    public function test_can_apply_business_type()
    {
        $this->actingAs($this->user);

        $businessType = BusinessType::first();
        if (!$businessType) {
            $this->postJson('/pos/business-types/seed');
            $businessType = BusinessType::first();
        }

        if (!$businessType) {
            $this->markTestSkipped('No business types available');
        }

        $response = $this->postJson("/pos/business-types/{$businessType->id}/apply");

        $response->assertStatus(200);
    }

    public function test_business_types_have_required_fields()
    {
        $this->actingAs($this->user);

        // Seed business types
        $this->postJson('/pos/business-types/seed');

        $businessTypes = BusinessType::all();

        foreach ($businessTypes as $type) {
            $this->assertNotEmpty($type->name);
            $this->assertNotEmpty($type->slug);
        }
    }

    public function test_grocery_business_type_exists()
    {
        $this->actingAs($this->user);

        $this->postJson('/pos/business-types/seed');

        $grocery = BusinessType::where('slug', 'grocery-supermarket')->first();

        $this->assertNotNull($grocery);
        $this->assertEquals('Grocery & Supermarket', $grocery->name);
    }

    public function test_pharmacy_business_type_exists()
    {
        $this->actingAs($this->user);

        $this->postJson('/pos/business-types/seed');

        $pharmacy = BusinessType::where('slug', 'medical-pharmacy')->first();

        $this->assertNotNull($pharmacy);
    }

    public function test_store_can_have_business_type()
    {
        $this->actingAs($this->user);

        $this->postJson('/pos/business-types/seed');

        $businessType = BusinessType::first();

        $this->store->update(['business_type_id' => $businessType->id]);

        $this->assertEquals($businessType->id, $this->store->fresh()->business_type_id);
    }
}
