<?php

namespace Database\Seeders\BusinessType;

class MobileShopSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Mobile Shop',
            'name_ar' => 'محل الجوالات',
            'slug' => 'mobile-shop',
            'icon' => 'MobileOutlined',
            'description' => 'Mobile phones, tablets, accessories, and repair services',
            'default_attributes' => [
                'product_attributes' => ['imei', 'serial_number', 'storage', 'ram', 'color', 'warranty_months'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => false,
            ],
            'receipt_config' => [
                'show_imei' => true,
                'show_warranty' => true,
            ],
            'settings' => [
                'track_imei' => true,
                'warranty_tracking' => true,
            ],
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'MOB-PHONES', 'name' => 'Mobile Phones', 'name_ar' => 'الهواتف المحمولة'],
            ['code' => 'MOB-TABLETS', 'name' => 'Tablets', 'name_ar' => 'الأجهزة اللوحية'],
            ['code' => 'MOB-ACCESS', 'name' => 'Accessories', 'name_ar' => 'الإكسسوارات'],
            ['code' => 'MOB-CASES', 'name' => 'Cases & Covers', 'name_ar' => 'الأغطية والحافظات', 'parent_code' => 'MOB-ACCESS'],
            ['code' => 'MOB-CHARGERS', 'name' => 'Chargers & Cables', 'name_ar' => 'الشواحن والكابلات', 'parent_code' => 'MOB-ACCESS'],
            ['code' => 'MOB-AUDIO', 'name' => 'Audio & Earphones', 'name_ar' => 'السماعات', 'parent_code' => 'MOB-ACCESS'],
            ['code' => 'MOB-SCREEN', 'name' => 'Screen Protectors', 'name_ar' => 'واقيات الشاشة', 'parent_code' => 'MOB-ACCESS'],
            ['code' => 'MOB-POWER', 'name' => 'Power Banks', 'name_ar' => 'شواحن متنقلة', 'parent_code' => 'MOB-ACCESS'],
            ['code' => 'MOB-SMART', 'name' => 'Smart Watches', 'name_ar' => 'الساعات الذكية'],
            ['code' => 'MOB-SIM', 'name' => 'SIM Cards & Services', 'name_ar' => 'شرائح وخدمات'],
            ['code' => 'MOB-REPAIR', 'name' => 'Repair Services', 'name_ar' => 'خدمات الصيانة'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Flagship Phones
            ['sku' => 'MOB-IP15PM-256', 'name' => 'iPhone 15 Pro Max 256GB', 'name_ar' => 'ايفون 15 برو ماكس 256 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 4200, 'sale_price' => 4999, 'storage' => '256GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-IP15PM-512', 'name' => 'iPhone 15 Pro Max 512GB', 'name_ar' => 'ايفون 15 برو ماكس 512 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 4800, 'sale_price' => 5599, 'storage' => '512GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-IP15P-128', 'name' => 'iPhone 15 Pro 128GB', 'name_ar' => 'ايفون 15 برو 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 3600, 'sale_price' => 4299, 'storage' => '128GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-IP15-128', 'name' => 'iPhone 15 128GB', 'name_ar' => 'ايفون 15 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 2800, 'sale_price' => 3399, 'storage' => '128GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-IP14-128', 'name' => 'iPhone 14 128GB', 'name_ar' => 'ايفون 14 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 2200, 'sale_price' => 2699, 'storage' => '128GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Samsung
            ['sku' => 'MOB-S24U-256', 'name' => 'Samsung Galaxy S24 Ultra 256GB', 'name_ar' => 'سامسونج جالكسي S24 الترا 256 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 4000, 'sale_price' => 4799, 'storage' => '256GB', 'ram' => '12GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-S24P-256', 'name' => 'Samsung Galaxy S24+ 256GB', 'name_ar' => 'سامسونج جالكسي S24 بلس 256 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 3200, 'sale_price' => 3899, 'storage' => '256GB', 'ram' => '12GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-S24-128', 'name' => 'Samsung Galaxy S24 128GB', 'name_ar' => 'سامسونج جالكسي S24 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 2400, 'sale_price' => 2999, 'storage' => '128GB', 'ram' => '8GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-A54-128', 'name' => 'Samsung Galaxy A54 128GB', 'name_ar' => 'سامسونج جالكسي A54 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 1200, 'sale_price' => 1599, 'storage' => '128GB', 'ram' => '8GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-A34-128', 'name' => 'Samsung Galaxy A34 128GB', 'name_ar' => 'سامسونج جالكسي A34 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 900, 'sale_price' => 1199, 'storage' => '128GB', 'ram' => '6GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Xiaomi / Budget Phones
            ['sku' => 'MOB-XM14-256', 'name' => 'Xiaomi 14 256GB', 'name_ar' => 'شاومي 14 256 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 2200, 'sale_price' => 2799, 'storage' => '256GB', 'ram' => '12GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-RN13-128', 'name' => 'Redmi Note 13 Pro 128GB', 'name_ar' => 'ريدمي نوت 13 برو 128 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 700, 'sale_price' => 999, 'storage' => '128GB', 'ram' => '8GB', 'warranty_months' => 12, 'low_stock_threshold' => 8],
            ['sku' => 'MOB-POCO-256', 'name' => 'POCO X6 Pro 256GB', 'name_ar' => 'بوكو X6 برو 256 جيجا', 'category_code' => 'MOB-PHONES', 'cost_price' => 1000, 'sale_price' => 1399, 'storage' => '256GB', 'ram' => '8GB', 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Tablets
            ['sku' => 'MOB-IPADP-128', 'name' => 'iPad Pro 11" M4 128GB', 'name_ar' => 'ايباد برو 11 انش M4 128 جيجا', 'category_code' => 'MOB-TABLETS', 'cost_price' => 3000, 'sale_price' => 3699, 'storage' => '128GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-IPAD10-64', 'name' => 'iPad 10th Gen 64GB', 'name_ar' => 'ايباد الجيل العاشر 64 جيجا', 'category_code' => 'MOB-TABLETS', 'cost_price' => 1200, 'sale_price' => 1599, 'storage' => '64GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-TABS9-128', 'name' => 'Samsung Galaxy Tab S9 128GB', 'name_ar' => 'سامسونج جالكسي تاب S9 128 جيجا', 'category_code' => 'MOB-TABLETS', 'cost_price' => 2200, 'sale_price' => 2699, 'storage' => '128GB', 'warranty_months' => 12, 'low_stock_threshold' => 3],

            // Cases & Covers
            ['sku' => 'MOB-CS-IP15PM', 'name' => 'iPhone 15 Pro Max Silicone Case', 'name_ar' => 'غطاء سيليكون ايفون 15 برو ماكس', 'category_code' => 'MOB-CASES', 'cost_price' => 15, 'sale_price' => 39, 'low_stock_threshold' => 20],
            ['sku' => 'MOB-CS-IP15P', 'name' => 'iPhone 15 Pro Clear Case', 'name_ar' => 'غطاء شفاف ايفون 15 برو', 'category_code' => 'MOB-CASES', 'cost_price' => 10, 'sale_price' => 29, 'low_stock_threshold' => 20],
            ['sku' => 'MOB-CS-S24U', 'name' => 'Samsung S24 Ultra Leather Case', 'name_ar' => 'غطاء جلد سامسونج S24 الترا', 'category_code' => 'MOB-CASES', 'cost_price' => 20, 'sale_price' => 49, 'low_stock_threshold' => 15],
            ['sku' => 'MOB-CS-UNIV', 'name' => 'Universal Phone Pouch', 'name_ar' => 'حقيبة جوال عالمية', 'category_code' => 'MOB-CASES', 'cost_price' => 8, 'sale_price' => 25, 'low_stock_threshold' => 25],

            // Chargers & Cables
            ['sku' => 'MOB-CHG-20W', 'name' => 'Apple 20W USB-C Charger', 'name_ar' => 'شاحن ابل 20 واط', 'category_code' => 'MOB-CHARGERS', 'cost_price' => 40, 'sale_price' => 79, 'warranty_months' => 6, 'low_stock_threshold' => 15],
            ['sku' => 'MOB-CHG-65W', 'name' => 'Samsung 65W Super Fast Charger', 'name_ar' => 'شاحن سامسونج 65 واط سريع', 'category_code' => 'MOB-CHARGERS', 'cost_price' => 50, 'sale_price' => 99, 'warranty_months' => 6, 'low_stock_threshold' => 15],
            ['sku' => 'MOB-CBL-USBC', 'name' => 'USB-C to USB-C Cable 1m', 'name_ar' => 'كابل USB-C الى USB-C 1 متر', 'category_code' => 'MOB-CHARGERS', 'cost_price' => 10, 'sale_price' => 29, 'low_stock_threshold' => 25],
            ['sku' => 'MOB-CBL-LTN', 'name' => 'Lightning Cable 1m Original', 'name_ar' => 'كابل لايتننج 1 متر اصلي', 'category_code' => 'MOB-CHARGERS', 'cost_price' => 25, 'sale_price' => 59, 'low_stock_threshold' => 20],
            ['sku' => 'MOB-CHG-CAR', 'name' => 'Dual USB Car Charger 30W', 'name_ar' => 'شاحن سيارة USB مزدوج 30 واط', 'category_code' => 'MOB-CHARGERS', 'cost_price' => 15, 'sale_price' => 39, 'low_stock_threshold' => 20],

            // Audio & Earphones
            ['sku' => 'MOB-AIRPODS-P2', 'name' => 'AirPods Pro 2nd Gen', 'name_ar' => 'ايربودز برو الجيل الثاني', 'category_code' => 'MOB-AUDIO', 'cost_price' => 700, 'sale_price' => 949, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-AIRPODS-3', 'name' => 'AirPods 3rd Gen', 'name_ar' => 'ايربودز الجيل الثالث', 'category_code' => 'MOB-AUDIO', 'cost_price' => 500, 'sale_price' => 699, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-BUDS2P', 'name' => 'Samsung Galaxy Buds2 Pro', 'name_ar' => 'سامسونج جالكسي بودز 2 برو', 'category_code' => 'MOB-AUDIO', 'cost_price' => 400, 'sale_price' => 599, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'MOB-EAR-BT', 'name' => 'Bluetooth Earphones Generic', 'name_ar' => 'سماعات بلوتوث', 'category_code' => 'MOB-AUDIO', 'cost_price' => 30, 'sale_price' => 79, 'warranty_months' => 3, 'low_stock_threshold' => 15],

            // Screen Protectors
            ['sku' => 'MOB-SP-IP15PM', 'name' => 'iPhone 15 Pro Max Tempered Glass', 'name_ar' => 'زجاج حماية ايفون 15 برو ماكس', 'category_code' => 'MOB-SCREEN', 'cost_price' => 5, 'sale_price' => 25, 'low_stock_threshold' => 30],
            ['sku' => 'MOB-SP-S24U', 'name' => 'Samsung S24 Ultra Screen Protector', 'name_ar' => 'حماية شاشة سامسونج S24 الترا', 'category_code' => 'MOB-SCREEN', 'cost_price' => 8, 'sale_price' => 35, 'low_stock_threshold' => 25],
            ['sku' => 'MOB-SP-UNIV', 'name' => 'Universal Screen Protector Kit', 'name_ar' => 'طقم حماية شاشة عالمي', 'category_code' => 'MOB-SCREEN', 'cost_price' => 3, 'sale_price' => 15, 'low_stock_threshold' => 40],

            // Power Banks
            ['sku' => 'MOB-PB-10K', 'name' => 'Anker PowerCore 10000mAh', 'name_ar' => 'انكر باور كور 10000 مللي', 'category_code' => 'MOB-POWER', 'cost_price' => 60, 'sale_price' => 99, 'warranty_months' => 12, 'low_stock_threshold' => 10],
            ['sku' => 'MOB-PB-20K', 'name' => 'Anker PowerCore 20000mAh', 'name_ar' => 'انكر باور كور 20000 مللي', 'category_code' => 'MOB-POWER', 'cost_price' => 100, 'sale_price' => 149, 'warranty_months' => 12, 'low_stock_threshold' => 8],
            ['sku' => 'MOB-PB-MAG', 'name' => 'MagSafe Battery Pack', 'name_ar' => 'بطارية ماجسيف', 'category_code' => 'MOB-POWER', 'cost_price' => 200, 'sale_price' => 299, 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Smart Watches
            ['sku' => 'MOB-AW9-45', 'name' => 'Apple Watch Series 9 45mm', 'name_ar' => 'ابل واتش سيريس 9 45 مم', 'category_code' => 'MOB-SMART', 'cost_price' => 1200, 'sale_price' => 1599, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-AWSE-44', 'name' => 'Apple Watch SE 2nd Gen 44mm', 'name_ar' => 'ابل واتش SE الجيل الثاني 44 مم', 'category_code' => 'MOB-SMART', 'cost_price' => 800, 'sale_price' => 1099, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'MOB-GW6-44', 'name' => 'Samsung Galaxy Watch 6 44mm', 'name_ar' => 'سامسونج جالكسي واتش 6 44 مم', 'category_code' => 'MOB-SMART', 'cost_price' => 900, 'sale_price' => 1199, 'warranty_months' => 12, 'low_stock_threshold' => 3],

            // SIM Cards & Services
            ['sku' => 'MOB-SIM-STC', 'name' => 'STC Prepaid SIM Card', 'name_ar' => 'شريحة STC مسبقة الدفع', 'category_code' => 'MOB-SIM', 'cost_price' => 10, 'sale_price' => 20, 'track_inventory' => false],
            ['sku' => 'MOB-SIM-MOBILY', 'name' => 'Mobily Prepaid SIM Card', 'name_ar' => 'شريحة موبايلي مسبقة الدفع', 'category_code' => 'MOB-SIM', 'cost_price' => 10, 'sale_price' => 20, 'track_inventory' => false],
            ['sku' => 'MOB-SIM-ZAIN', 'name' => 'Zain Prepaid SIM Card', 'name_ar' => 'شريحة زين مسبقة الدفع', 'category_code' => 'MOB-SIM', 'cost_price' => 10, 'sale_price' => 20, 'track_inventory' => false],
            ['sku' => 'MOB-RCH-50', 'name' => 'Prepaid Recharge 50 SAR', 'name_ar' => 'شحن رصيد 50 ريال', 'category_code' => 'MOB-SIM', 'cost_price' => 47, 'sale_price' => 50, 'track_inventory' => false],
            ['sku' => 'MOB-RCH-100', 'name' => 'Prepaid Recharge 100 SAR', 'name_ar' => 'شحن رصيد 100 ريال', 'category_code' => 'MOB-SIM', 'cost_price' => 95, 'sale_price' => 100, 'track_inventory' => false],

            // Repair Services
            ['sku' => 'MOB-REP-SCRN', 'name' => 'Screen Replacement Service', 'name_ar' => 'خدمة تبديل الشاشة', 'category_code' => 'MOB-REPAIR', 'cost_price' => 0, 'sale_price' => 150, 'track_inventory' => false],
            ['sku' => 'MOB-REP-BATT', 'name' => 'Battery Replacement Service', 'name_ar' => 'خدمة تبديل البطارية', 'category_code' => 'MOB-REPAIR', 'cost_price' => 0, 'sale_price' => 100, 'track_inventory' => false],
            ['sku' => 'MOB-REP-PORT', 'name' => 'Charging Port Repair', 'name_ar' => 'إصلاح منفذ الشحن', 'category_code' => 'MOB-REPAIR', 'cost_price' => 0, 'sale_price' => 80, 'track_inventory' => false],
        ];
    }
}
