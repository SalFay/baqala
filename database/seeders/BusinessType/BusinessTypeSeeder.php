<?php

namespace Database\Seeders\BusinessType;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

/**
 * Seeds all business types without their products.
 * Use individual seeders (MobileShopSeeder, etc.) to seed products.
 */
class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $businessTypes = [
            [
                'name' => 'Mobile Shop',
                'name_ar' => 'محل الجوالات',
                'slug' => 'mobile-shop',
                'icon' => 'MobileOutlined',
                'description' => 'Mobile phones, tablets, accessories, and repair services',
                'default_attributes' => ['product_attributes' => ['imei', 'serial_number', 'storage', 'ram', 'color', 'warranty_months']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => false],
                'receipt_config' => ['show_imei' => true, 'show_warranty' => true],
                'settings' => ['track_imei' => true, 'warranty_tracking' => true],
                'sort_order' => 1,
            ],
            [
                'name' => 'Medical & Pharmacy',
                'name_ar' => 'صيدلية ومستلزمات طبية',
                'slug' => 'medical-pharmacy',
                'icon' => 'MedicineBoxOutlined',
                'description' => 'Medicines, medical equipment, first aid, and personal care products',
                'default_attributes' => ['product_attributes' => ['expiry_date', 'batch_number', 'prescription_required', 'dosage', 'manufacturer']],
                'tax_config' => ['default_tax_rate' => 0, 'tax_inclusive' => true],
                'receipt_config' => ['show_expiry' => true, 'show_batch' => true, 'show_prescription_warning' => true],
                'settings' => ['track_expiry' => true, 'require_prescription' => true, 'fifo_inventory' => true],
                'sort_order' => 2,
            ],
            [
                'name' => 'Sanitary & Hardware',
                'name_ar' => 'أدوات صحية وعدد',
                'slug' => 'sanitary-hardware',
                'icon' => 'ToolOutlined',
                'description' => 'Plumbing supplies, fixtures, tools, and building materials',
                'default_attributes' => ['product_attributes' => ['dimensions', 'material', 'finish', 'size', 'color', 'brand']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => false],
                'receipt_config' => ['show_dimensions' => true, 'show_material' => true],
                'settings' => ['unit_based_pricing' => true, 'bulk_discount' => true],
                'sort_order' => 3,
            ],
            [
                'name' => 'Grocery & Supermarket',
                'name_ar' => 'بقالة وسوبرماركت',
                'slug' => 'grocery-supermarket',
                'icon' => 'ShoppingCartOutlined',
                'description' => 'Fresh produce, dairy, beverages, frozen foods, and household items',
                'default_attributes' => ['product_attributes' => ['expiry_date', 'weight_type', 'unit', 'origin', 'brand']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => true],
                'receipt_config' => ['show_expiry' => false, 'show_weight' => true],
                'settings' => ['track_expiry' => true, 'fifo_inventory' => true, 'weight_based_items' => true],
                'sort_order' => 4,
            ],
            [
                'name' => 'Electronics',
                'name_ar' => 'الكترونيات',
                'slug' => 'electronics',
                'icon' => 'LaptopOutlined',
                'description' => 'TVs, appliances, computers, audio equipment, and electronics accessories',
                'default_attributes' => ['product_attributes' => ['warranty_months', 'serial_number', 'model', 'brand', 'power', 'dimensions']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => false],
                'receipt_config' => ['show_serial' => true, 'show_warranty' => true, 'show_model' => true],
                'settings' => ['warranty_tracking' => true, 'serial_number_required' => true],
                'sort_order' => 5,
            ],
            [
                'name' => 'Clothing & Fashion',
                'name_ar' => 'ملابس وأزياء',
                'slug' => 'clothing-fashion',
                'icon' => 'SkinOutlined',
                'description' => 'Men, women, and children clothing, footwear, and fashion accessories',
                'default_attributes' => ['product_attributes' => ['size', 'color', 'material', 'season', 'brand', 'gender']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => true],
                'receipt_config' => ['show_size' => true, 'show_color' => true],
                'settings' => ['size_chart' => true, 'variant_based' => true],
                'sort_order' => 6,
            ],
            [
                'name' => 'Restaurant & Cafe',
                'name_ar' => 'مطعم وكافيه',
                'slug' => 'restaurant-cafe',
                'icon' => 'CoffeeOutlined',
                'description' => 'Hot and cold drinks, food items, combos, and cafe services',
                'default_attributes' => ['product_attributes' => ['modifiers', 'prep_time', 'calories', 'allergens', 'spicy_level']],
                'tax_config' => ['default_tax_rate' => 15, 'tax_inclusive' => true],
                'receipt_config' => ['show_prep_time' => false, 'kitchen_print' => true, 'table_number' => true],
                'settings' => ['table_management' => true, 'kitchen_display' => true, 'modifiers_support' => true],
                'sort_order' => 7,
            ],
        ];

        foreach ($businessTypes as $type) {
            BusinessType::updateOrCreate(
                ['slug' => $type['slug']],
                array_merge($type, ['is_active' => true])
            );
        }

        $this->command->info('Seeded ' . count($businessTypes) . ' business types.');
    }
}
