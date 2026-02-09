<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\LoyaltyTier;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ReturnReason;
use App\Models\Setting;
use App\Models\SettingGroup;
use App\Models\Store;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create default store
        Store::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Store',
                'address' => 'Riyadh, Saudi Arabia',
                'phone' => '+966500000000',
                'is_active' => true,
                'is_warehouse' => false,
            ]
        );

        // Create default tax rate
        TaxRate::firstOrCreate(
            ['name' => 'VAT 15%'],
            [
                'rate' => 15.00,
                'is_default' => true,
                'is_active' => true,
                'description' => 'Saudi Arabia VAT',
            ]
        );

        // Create sample categories
        $categories = ['Beverages', 'Snacks', 'Dairy', 'Bakery', 'Frozen', 'Personal Care', 'Household'];
        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category],
                ['code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category), 0, 4))]
            );
        }

        // Create product attributes
        $sizeAttr = ProductAttribute::firstOrCreate(
            ['slug' => 'size'],
            ['name' => 'Size', 'type' => 'select', 'is_variation' => true]
        );
        foreach (['Small', 'Medium', 'Large', 'XL'] as $size) {
            ProductAttributeValue::firstOrCreate(
                ['product_attribute_id' => $sizeAttr->id, 'slug' => strtolower($size)],
                ['value' => $size]
            );
        }

        $colorAttr = ProductAttribute::firstOrCreate(
            ['slug' => 'color'],
            ['name' => 'Color', 'type' => 'color', 'is_variation' => true]
        );
        $colors = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#00FF00',
            'Black' => '#000000',
            'White' => '#FFFFFF',
        ];
        foreach ($colors as $color => $code) {
            ProductAttributeValue::firstOrCreate(
                ['product_attribute_id' => $colorAttr->id, 'slug' => strtolower($color)],
                ['value' => $color, 'color_code' => $code]
            );
        }

        // Create return reasons
        $reasons = [
            'Defective Product',
            'Wrong Product',
            'Changed Mind',
            'Product Not as Described',
            'Damaged in Transit',
        ];
        foreach ($reasons as $i => $reason) {
            ReturnReason::firstOrCreate(
                ['name' => $reason],
                ['is_active' => true, 'sort_order' => $i]
            );
        }

        // Create loyalty tiers
        $tiers = [
            ['name' => 'Bronze', 'min_points' => 0, 'multiplier' => 1.00, 'discount' => 0, 'color' => '#CD7F32'],
            ['name' => 'Silver', 'min_points' => 1000, 'multiplier' => 1.25, 'discount' => 2, 'color' => '#C0C0C0'],
            ['name' => 'Gold', 'min_points' => 5000, 'multiplier' => 1.50, 'discount' => 5, 'color' => '#FFD700'],
            ['name' => 'Platinum', 'min_points' => 15000, 'multiplier' => 2.00, 'discount' => 10, 'color' => '#E5E4E2'],
        ];
        foreach ($tiers as $i => $tier) {
            LoyaltyTier::firstOrCreate(
                ['name' => $tier['name']],
                [
                    'min_points' => $tier['min_points'],
                    'points_multiplier' => $tier['multiplier'],
                    'discount_percentage' => $tier['discount'],
                    'badge_color' => $tier['color'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }

        // Create setting groups
        $groups = [
            ['name' => 'General', 'slug' => 'general', 'icon' => 'setting'],
            ['name' => 'Tax', 'slug' => 'tax', 'icon' => 'percentage'],
            ['name' => 'Receipt', 'slug' => 'receipt', 'icon' => 'file-text'],
            ['name' => 'Loyalty', 'slug' => 'loyalty', 'icon' => 'crown'],
            ['name' => 'Inventory', 'slug' => 'inventory', 'icon' => 'inbox'],
        ];
        foreach ($groups as $i => $group) {
            SettingGroup::firstOrCreate(
                ['slug' => $group['slug']],
                ['name' => $group['name'], 'icon' => $group['icon'], 'sort_order' => $i]
            );
        }

        // Create default settings
        $generalGroup = SettingGroup::where('slug', 'general')->first();
        $settings = [
            ['key' => 'shop_name', 'value' => 'Baqala POS', 'type' => 'text', 'label' => 'Shop Name'],
            ['key' => 'shop_phone', 'value' => '', 'type' => 'text', 'label' => 'Phone'],
            ['key' => 'shop_email', 'value' => '', 'type' => 'text', 'label' => 'Email'],
            ['key' => 'shop_address', 'value' => '', 'type' => 'text', 'label' => 'Address'],
            ['key' => 'shop_vat_number', 'value' => '', 'type' => 'text', 'label' => 'VAT Number'],
            ['key' => 'currency_code', 'value' => 'SAR', 'type' => 'text', 'label' => 'Currency Code'],
            ['key' => 'currency_symbol', 'value' => 'ر.س', 'type' => 'text', 'label' => 'Currency Symbol'],
        ];
        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key'], 'store_id' => null],
                [
                    'setting_group_id' => $generalGroup?->id,
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'label' => $setting['label'],
                    'is_public' => true,
                ]
            );
        }

        // Create chart of accounts
        $accounts = [
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '1010', 'name' => 'Bank', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],
            ['code' => '1300', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'VAT Payable', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'revenue'],
            ['code' => '4100', 'name' => 'Discount Given', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Operating Expenses', 'type' => 'expense'],
        ];
        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
