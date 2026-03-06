<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class POSSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // POS Settings
            ['key' => 'pos_item_addition_method', 'value' => 'barcode', 'type' => 'select', 'label' => 'Item Addition Method', 'description' => 'Default method for adding items to cart'],
            ['key' => 'pos_auto_print_receipt', 'value' => '0', 'type' => 'boolean', 'label' => 'Auto Print Receipt', 'description' => 'Automatically print receipt after sale'],
            ['key' => 'pos_enable_sounds', 'value' => '1', 'type' => 'boolean', 'label' => 'Enable Sounds', 'description' => 'Play sounds for scan, success, error'],
            ['key' => 'pos_default_customer', 'value' => '', 'type' => 'text', 'label' => 'Default Customer', 'description' => 'Default customer for walk-in sales'],
            ['key' => 'pos_keyboard_shortcuts_enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'Keyboard Shortcuts', 'description' => 'Enable keyboard shortcuts in POS'],
            ['key' => 'pos_show_product_image', 'value' => '1', 'type' => 'boolean', 'label' => 'Show Product Images', 'description' => 'Display product images in POS'],
            ['key' => 'pos_cart_display', 'value' => 'list', 'type' => 'select', 'label' => 'Cart Display Mode', 'description' => 'How to display items in cart'],

            // Currency & Number Settings
            ['key' => 'currency_precision', 'value' => '2', 'type' => 'number', 'label' => 'Currency Precision', 'description' => 'Decimal places for currency amounts'],
            ['key' => 'quantity_precision', 'value' => '3', 'type' => 'number', 'label' => 'Quantity Precision', 'description' => 'Decimal places for quantities'],

            // Invoice Settings
            ['key' => 'financial_year_start', 'value' => '01-01', 'type' => 'text', 'label' => 'Financial Year Start', 'description' => 'MM-DD format'],
            ['key' => 'invoice_scheme', 'value' => 'sequential', 'type' => 'select', 'label' => 'Invoice Numbering Scheme', 'description' => 'How invoice numbers are generated'],
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'type' => 'text', 'label' => 'Invoice Prefix', 'description' => 'Prefix for invoice numbers'],
            ['key' => 'invoice_start_number', 'value' => '1', 'type' => 'number', 'label' => 'Invoice Start Number', 'description' => 'Starting number for invoices'],

            // Stock Settings
            ['key' => 'stock_accounting_method', 'value' => 'FIFO', 'type' => 'select', 'label' => 'Stock Accounting Method', 'description' => 'Method for calculating cost of goods sold'],
            ['key' => 'enable_expiry_tracking', 'value' => '0', 'type' => 'boolean', 'label' => 'Track Expiry Dates', 'description' => 'Enable expiry date tracking for products'],
            ['key' => 'enable_serial_tracking', 'value' => '0', 'type' => 'boolean', 'label' => 'Track Serial Numbers', 'description' => 'Enable serial/IMEI tracking'],
            ['key' => 'enable_batch_tracking', 'value' => '0', 'type' => 'boolean', 'label' => 'Track Batches', 'description' => 'Enable batch/lot tracking'],
            ['key' => 'expiry_alert_days', 'value' => '30', 'type' => 'number', 'label' => 'Expiry Alert Days', 'description' => 'Days before expiry to show alerts'],

            // Business Settings
            ['key' => 'business_type', 'value' => 'retail', 'type' => 'select', 'label' => 'Business Type', 'description' => 'Type of business'],
            ['key' => 'enable_quotations', 'value' => '0', 'type' => 'boolean', 'label' => 'Enable Quotations', 'description' => 'Allow creating quotations'],
            ['key' => 'enable_credit_sales', 'value' => '0', 'type' => 'boolean', 'label' => 'Enable Credit Sales', 'description' => 'Allow credit sales to customers'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
