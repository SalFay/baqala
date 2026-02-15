<?php

namespace Database\Seeders\BusinessType;

use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

abstract class BaseBusinessTypeSeeder extends Seeder
{
    protected ?Store $store = null;
    protected ?BusinessType $businessType = null;
    protected array $createdCategories = [];

    /**
     * Get the business type configuration.
     */
    abstract protected function getBusinessTypeConfig(): array;

    /**
     * Get categories for this business type.
     */
    abstract protected function getCategories(): array;

    /**
     * Get products for this business type.
     */
    abstract protected function getProducts(): array;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->store = Store::first();

        $this->createBusinessType();
        $this->createCategories();
        $this->createProducts();

        $this->command->info("Seeded {$this->businessType->name} with " . count($this->getProducts()) . " products.");
    }

    /**
     * Create or update the business type.
     */
    protected function createBusinessType(): void
    {
        $config = $this->getBusinessTypeConfig();

        $this->businessType = BusinessType::updateOrCreate(
            ['slug' => $config['slug']],
            $config
        );

        // Update store's business type if we have a store
        if ($this->store) {
            $this->store->update(['business_type_id' => $this->businessType->id]);
        }
    }

    /**
     * Create categories with optional parent-child relationships.
     */
    protected function createCategories(): void
    {
        $categories = $this->getCategories();

        foreach ($categories as $index => $categoryData) {
            $parentId = null;

            // Handle parent category reference
            if (!empty($categoryData['parent_code'])) {
                $parentId = $this->createdCategories[$categoryData['parent_code']]?->id;
            }

            $category = Category::updateOrCreate(
                ['code' => $categoryData['code']],
                [
                    'name' => $categoryData['name'],
                    'name_ar' => $categoryData['name_ar'] ?? null,
                    'parent_id' => $parentId,
                    'description' => $categoryData['description'] ?? null,
                    'sort_order' => $categoryData['sort_order'] ?? $index,
                    'is_active' => true,
                ]
            );

            $this->createdCategories[$categoryData['code']] = $category;
        }
    }

    /**
     * Create products with inventory.
     */
    protected function createProducts(): void
    {
        $products = $this->getProducts();

        foreach ($products as $productData) {
            // Get category by code
            $categoryId = null;
            if (!empty($productData['category_code'])) {
                $categoryId = $this->createdCategories[$productData['category_code']]?->id
                    ?? Category::where('code', $productData['category_code'])->first()?->id;
            }

            // Build meta from business-specific attributes
            $meta = $productData['meta'] ?? [];
            $businessAttributes = ['imei', 'serial_number', 'warranty_months', 'expiry_date', 'batch_number',
                'prescription_required', 'dimensions', 'material', 'finish', 'weight_type', 'size', 'color',
                'season', 'storage', 'ram', 'modifiers', 'prep_time'];

            foreach ($businessAttributes as $attr) {
                if (isset($productData[$attr])) {
                    $meta[$attr] = $productData[$attr];
                }
            }

            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'store_id' => $this->store?->id,
                    'category_id' => $categoryId,
                    'name' => $productData['name'],
                    'name_ar' => $productData['name_ar'] ?? null,
                    'barcode' => $productData['barcode'] ?? $this->generateBarcode(),
                    'description' => $productData['description'] ?? null,
                    'cost_price' => $productData['cost_price'],
                    'sale_price' => $productData['sale_price'],
                    'compare_price' => $productData['compare_price'] ?? null,
                    'track_inventory' => $productData['track_inventory'] ?? true,
                    'low_stock_threshold' => $productData['low_stock_threshold'] ?? 10,
                    'weight' => $productData['weight'] ?? null,
                    'weight_unit' => $productData['weight_unit'] ?? 'kg',
                    'meta' => !empty($meta) ? $meta : null,
                    'is_active' => true,
                ]
            );

            // Create inventory if store exists
            if ($this->store && ($productData['track_inventory'] ?? true)) {
                $initialStock = $productData['initial_stock'] ?? rand(10, 100);

                StoreInventory::updateOrCreate(
                    [
                        'store_id' => $this->store->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                    ],
                    [
                        'quantity' => $initialStock,
                        'reserved_quantity' => 0,
                        'low_stock_threshold' => $productData['low_stock_threshold'] ?? 10,
                    ]
                );
            }
        }
    }

    /**
     * Generate a random barcode.
     */
    protected function generateBarcode(): string
    {
        return '2' . str_pad(mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    /**
     * Generate SKU with prefix.
     */
    protected function generateSku(string $prefix, int $index): string
    {
        return strtoupper($prefix) . '-' . str_pad($index, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the business type record (useful for import preview).
     */
    public function getBusinessType(): ?BusinessType
    {
        if (!$this->businessType) {
            $config = $this->getBusinessTypeConfig();
            $this->businessType = BusinessType::where('slug', $config['slug'])->first();
        }
        return $this->businessType;
    }

    /**
     * Get preview data without actually seeding.
     */
    public function getPreviewData(): array
    {
        return [
            'business_type' => $this->getBusinessTypeConfig(),
            'categories' => $this->getCategories(),
            'products' => array_map(function ($product) {
                return [
                    'name' => $product['name'],
                    'name_ar' => $product['name_ar'] ?? null,
                    'sku' => $product['sku'],
                    'sale_price' => $product['sale_price'],
                    'cost_price' => $product['cost_price'],
                    'category_code' => $product['category_code'] ?? null,
                ];
            }, array_slice($this->getProducts(), 0, 10)), // Preview first 10
            'total_products' => count($this->getProducts()),
            'total_categories' => count($this->getCategories()),
        ];
    }
}
