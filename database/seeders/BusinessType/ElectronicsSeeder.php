<?php

namespace Database\Seeders\BusinessType;

class ElectronicsSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Electronics',
            'name_ar' => 'الكترونيات',
            'slug' => 'electronics',
            'icon' => 'LaptopOutlined',
            'description' => 'TVs, appliances, computers, audio equipment, and electronics accessories',
            'default_attributes' => [
                'product_attributes' => ['warranty_months', 'serial_number', 'model', 'brand', 'power', 'dimensions'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => false,
            ],
            'receipt_config' => [
                'show_serial' => true,
                'show_warranty' => true,
                'show_model' => true,
            ],
            'settings' => [
                'warranty_tracking' => true,
                'serial_number_required' => true,
            ],
            'is_active' => true,
            'sort_order' => 5,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'ELEC-TV', 'name' => 'Televisions', 'name_ar' => 'التلفزيونات'],
            ['code' => 'ELEC-AUDIO', 'name' => 'Audio & Speakers', 'name_ar' => 'الصوتيات والسماعات'],
            ['code' => 'ELEC-COMP', 'name' => 'Computers', 'name_ar' => 'الكمبيوترات'],
            ['code' => 'ELEC-LAPTOP', 'name' => 'Laptops', 'name_ar' => 'اللابتوب', 'parent_code' => 'ELEC-COMP'],
            ['code' => 'ELEC-DESKTOP', 'name' => 'Desktops', 'name_ar' => 'الديسكتوب', 'parent_code' => 'ELEC-COMP'],
            ['code' => 'ELEC-MONITOR', 'name' => 'Monitors', 'name_ar' => 'الشاشات', 'parent_code' => 'ELEC-COMP'],
            ['code' => 'ELEC-PRINT', 'name' => 'Printers & Scanners', 'name_ar' => 'الطابعات والماسحات'],
            ['code' => 'ELEC-GAMING', 'name' => 'Gaming', 'name_ar' => 'الألعاب'],
            ['code' => 'ELEC-KITCHEN', 'name' => 'Kitchen Appliances', 'name_ar' => 'أجهزة المطبخ'],
            ['code' => 'ELEC-HOME', 'name' => 'Home Appliances', 'name_ar' => 'الأجهزة المنزلية'],
            ['code' => 'ELEC-AC', 'name' => 'Air Conditioning', 'name_ar' => 'المكيفات'],
            ['code' => 'ELEC-ACCESS', 'name' => 'Accessories', 'name_ar' => 'الاكسسوارات'],
            ['code' => 'ELEC-STORAGE', 'name' => 'Storage Devices', 'name_ar' => 'أجهزة التخزين'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // TVs
            ['sku' => 'ELEC-TV-SAM-55', 'name' => 'Samsung 55" 4K Smart TV', 'name_ar' => 'تلفزيون سامسونج 55 انش 4K ذكي', 'category_code' => 'ELEC-TV', 'cost_price' => 1500, 'sale_price' => 2199, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-TV-SAM-65', 'name' => 'Samsung 65" 4K Smart TV', 'name_ar' => 'تلفزيون سامسونج 65 انش 4K ذكي', 'category_code' => 'ELEC-TV', 'cost_price' => 2200, 'sale_price' => 3199, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-TV-LG-55-OLED', 'name' => 'LG 55" OLED Smart TV', 'name_ar' => 'تلفزيون ال جي 55 انش OLED ذكي', 'category_code' => 'ELEC-TV', 'cost_price' => 3500, 'sale_price' => 4799, 'warranty_months' => 24, 'low_stock_threshold' => 2],
            ['sku' => 'ELEC-TV-TCL-50', 'name' => 'TCL 50" 4K Smart TV', 'name_ar' => 'تلفزيون تي سي ال 50 انش 4K ذكي', 'category_code' => 'ELEC-TV', 'cost_price' => 900, 'sale_price' => 1399, 'warranty_months' => 24, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-TV-SONY-55', 'name' => 'Sony 55" 4K Smart TV', 'name_ar' => 'تلفزيون سوني 55 انش 4K ذكي', 'category_code' => 'ELEC-TV', 'cost_price' => 2000, 'sale_price' => 2899, 'warranty_months' => 24, 'low_stock_threshold' => 3],

            // Audio
            ['sku' => 'ELEC-SOUND-SAM', 'name' => 'Samsung Soundbar 300W', 'name_ar' => 'ساوند بار سامسونج 300 واط', 'category_code' => 'ELEC-AUDIO', 'cost_price' => 400, 'sale_price' => 649, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'ELEC-SOUND-JBL', 'name' => 'JBL Bar 5.1 Soundbar', 'name_ar' => 'ساوند بار جي بي ال 5.1', 'category_code' => 'ELEC-AUDIO', 'cost_price' => 800, 'sale_price' => 1199, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-SPK-JBL-FLIP', 'name' => 'JBL Flip 6 Bluetooth Speaker', 'name_ar' => 'سماعة جي بي ال فليب 6 بلوتوث', 'category_code' => 'ELEC-AUDIO', 'cost_price' => 250, 'sale_price' => 399, 'warranty_months' => 12, 'low_stock_threshold' => 8],
            ['sku' => 'ELEC-SPK-BOSE', 'name' => 'Bose SoundLink Mini II', 'name_ar' => 'سماعة بوز ساوند لينك ميني 2', 'category_code' => 'ELEC-AUDIO', 'cost_price' => 350, 'sale_price' => 549, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'ELEC-HEAD-SONY', 'name' => 'Sony WH-1000XM5 Headphones', 'name_ar' => 'سماعة سوني WH-1000XM5', 'category_code' => 'ELEC-AUDIO', 'cost_price' => 800, 'sale_price' => 1199, 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Laptops
            ['sku' => 'ELEC-LAP-MAC-AIR', 'name' => 'MacBook Air M3 256GB', 'name_ar' => 'ماك بوك اير M3 256 جيجا', 'category_code' => 'ELEC-LAPTOP', 'cost_price' => 3500, 'sale_price' => 4499, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-LAP-MAC-PRO', 'name' => 'MacBook Pro 14" M3 Pro', 'name_ar' => 'ماك بوك برو 14 انش M3 برو', 'category_code' => 'ELEC-LAPTOP', 'cost_price' => 6000, 'sale_price' => 7499, 'warranty_months' => 12, 'low_stock_threshold' => 2],
            ['sku' => 'ELEC-LAP-HP-15', 'name' => 'HP Pavilion 15 i7 512GB', 'name_ar' => 'اتش بي بافيليون 15 انش i7 512 جيجا', 'category_code' => 'ELEC-LAPTOP', 'cost_price' => 2200, 'sale_price' => 2999, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-LAP-DELL-14', 'name' => 'Dell Inspiron 14 i5 256GB', 'name_ar' => 'ديل انسبايرون 14 انش i5 256 جيجا', 'category_code' => 'ELEC-LAPTOP', 'cost_price' => 1800, 'sale_price' => 2499, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-LAP-LENOVO-15', 'name' => 'Lenovo IdeaPad 15 Ryzen 5', 'name_ar' => 'لينوفو ايديا باد 15 رايزن 5', 'category_code' => 'ELEC-LAPTOP', 'cost_price' => 1500, 'sale_price' => 2099, 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // Monitors
            ['sku' => 'ELEC-MON-SAM-27', 'name' => 'Samsung 27" Curved Monitor', 'name_ar' => 'شاشة سامسونج 27 انش منحنية', 'category_code' => 'ELEC-MONITOR', 'cost_price' => 500, 'sale_price' => 799, 'warranty_months' => 24, 'low_stock_threshold' => 5],
            ['sku' => 'ELEC-MON-LG-24', 'name' => 'LG 24" IPS Monitor', 'name_ar' => 'شاشة ال جي 24 انش IPS', 'category_code' => 'ELEC-MONITOR', 'cost_price' => 350, 'sale_price' => 549, 'warranty_months' => 24, 'low_stock_threshold' => 6],
            ['sku' => 'ELEC-MON-DELL-27-4K', 'name' => 'Dell 27" 4K Monitor', 'name_ar' => 'شاشة ديل 27 انش 4K', 'category_code' => 'ELEC-MONITOR', 'cost_price' => 900, 'sale_price' => 1349, 'warranty_months' => 24, 'low_stock_threshold' => 4],

            // Printers
            ['sku' => 'ELEC-PRT-HP-2775', 'name' => 'HP DeskJet 2775 All-in-One', 'name_ar' => 'طابعة اتش بي ديسك جيت 2775 متعددة', 'category_code' => 'ELEC-PRINT', 'cost_price' => 180, 'sale_price' => 299, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'ELEC-PRT-EPSON-L3150', 'name' => 'Epson EcoTank L3150', 'name_ar' => 'طابعة ابسون ايكو تانك L3150', 'category_code' => 'ELEC-PRINT', 'cost_price' => 400, 'sale_price' => 649, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-PRT-HP-LASER', 'name' => 'HP LaserJet Pro M15w', 'name_ar' => 'طابعة اتش بي ليزر جيت برو M15w', 'category_code' => 'ELEC-PRINT', 'cost_price' => 350, 'sale_price' => 549, 'warranty_months' => 12, 'low_stock_threshold' => 4],

            // Gaming
            ['sku' => 'ELEC-PS5-DISC', 'name' => 'PlayStation 5 Disc Edition', 'name_ar' => 'بلايستيشن 5 نسخة الاسطوانات', 'category_code' => 'ELEC-GAMING', 'cost_price' => 1600, 'sale_price' => 2099, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-PS5-DIG', 'name' => 'PlayStation 5 Digital Edition', 'name_ar' => 'بلايستيشن 5 النسخة الرقمية', 'category_code' => 'ELEC-GAMING', 'cost_price' => 1400, 'sale_price' => 1799, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-XBOX-X', 'name' => 'Xbox Series X', 'name_ar' => 'اكس بوكس سيريس اكس', 'category_code' => 'ELEC-GAMING', 'cost_price' => 1500, 'sale_price' => 1999, 'warranty_months' => 12, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-SWITCH-OLED', 'name' => 'Nintendo Switch OLED', 'name_ar' => 'نينتندو سويتش OLED', 'category_code' => 'ELEC-GAMING', 'cost_price' => 1000, 'sale_price' => 1399, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-PS5-CTRL', 'name' => 'PS5 DualSense Controller', 'name_ar' => 'يد تحكم بلايستيشن 5', 'category_code' => 'ELEC-GAMING', 'cost_price' => 200, 'sale_price' => 299, 'warranty_months' => 12, 'low_stock_threshold' => 8],

            // Kitchen Appliances
            ['sku' => 'ELEC-BLEND-PHILIPS', 'name' => 'Philips Blender 2L 700W', 'name_ar' => 'خلاط فيليبس 2 لتر 700 واط', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 120, 'sale_price' => 199, 'warranty_months' => 24, 'low_stock_threshold' => 6],
            ['sku' => 'ELEC-MIX-KENWOOD', 'name' => 'Kenwood Stand Mixer 5L', 'name_ar' => 'عجانة كينوود 5 لتر', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 600, 'sale_price' => 899, 'warranty_months' => 24, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-MICRO-SAM', 'name' => 'Samsung Microwave 40L', 'name_ar' => 'ميكروويف سامسونج 40 لتر', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 350, 'sale_price' => 549, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-AIRFRY-PHILIPS', 'name' => 'Philips Air Fryer XXL', 'name_ar' => 'قلاية هوائية فيليبس XXL', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 500, 'sale_price' => 799, 'warranty_months' => 24, 'low_stock_threshold' => 5],
            ['sku' => 'ELEC-COFFEE-DELONGHI', 'name' => 'DeLonghi Coffee Machine', 'name_ar' => 'ماكينة قهوة ديلونجي', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 400, 'sale_price' => 649, 'warranty_months' => 24, 'low_stock_threshold' => 4],
            ['sku' => 'ELEC-KETTLE-PHILIPS', 'name' => 'Philips Electric Kettle 1.7L', 'name_ar' => 'غلاية كهربائية فيليبس 1.7 لتر', 'category_code' => 'ELEC-KITCHEN', 'cost_price' => 60, 'sale_price' => 99, 'warranty_months' => 24, 'low_stock_threshold' => 8],

            // Home Appliances
            ['sku' => 'ELEC-WASH-SAM-9KG', 'name' => 'Samsung Washing Machine 9kg', 'name_ar' => 'غسالة سامسونج 9 كيلو', 'category_code' => 'ELEC-HOME', 'cost_price' => 1200, 'sale_price' => 1799, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-FRIDGE-SAM-450', 'name' => 'Samsung Refrigerator 450L', 'name_ar' => 'ثلاجة سامسونج 450 لتر', 'category_code' => 'ELEC-HOME', 'cost_price' => 2000, 'sale_price' => 2999, 'warranty_months' => 24, 'low_stock_threshold' => 2],
            ['sku' => 'ELEC-VAC-DYSON', 'name' => 'Dyson V15 Vacuum Cleaner', 'name_ar' => 'مكنسة دايسون V15', 'category_code' => 'ELEC-HOME', 'cost_price' => 1500, 'sale_price' => 2199, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-IRON-PHILIPS', 'name' => 'Philips Steam Iron 2400W', 'name_ar' => 'مكواة بخار فيليبس 2400 واط', 'category_code' => 'ELEC-HOME', 'cost_price' => 80, 'sale_price' => 149, 'warranty_months' => 24, 'low_stock_threshold' => 6],

            // Air Conditioning
            ['sku' => 'ELEC-AC-SAM-18K', 'name' => 'Samsung Split AC 18000 BTU', 'name_ar' => 'مكيف سامسونج سبليت 18000 وحدة', 'category_code' => 'ELEC-AC', 'cost_price' => 1500, 'sale_price' => 2199, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'ELEC-AC-LG-24K', 'name' => 'LG Split AC 24000 BTU Inverter', 'name_ar' => 'مكيف ال جي سبليت 24000 وحدة انفرتر', 'category_code' => 'ELEC-AC', 'cost_price' => 2200, 'sale_price' => 3199, 'warranty_months' => 24, 'low_stock_threshold' => 2],
            ['sku' => 'ELEC-AC-PORT', 'name' => 'Portable AC 12000 BTU', 'name_ar' => 'مكيف متنقل 12000 وحدة', 'category_code' => 'ELEC-AC', 'cost_price' => 800, 'sale_price' => 1199, 'warranty_months' => 12, 'low_stock_threshold' => 4],

            // Storage Devices
            ['sku' => 'ELEC-SSD-SAM-1TB', 'name' => 'Samsung SSD 1TB NVMe', 'name_ar' => 'هارد اس اس دي سامسونج 1 تيرا', 'category_code' => 'ELEC-STORAGE', 'cost_price' => 200, 'sale_price' => 329, 'warranty_months' => 60, 'low_stock_threshold' => 8],
            ['sku' => 'ELEC-HDD-WD-2TB', 'name' => 'WD External HDD 2TB', 'name_ar' => 'هارد خارجي ويسترن ديجيتال 2 تيرا', 'category_code' => 'ELEC-STORAGE', 'cost_price' => 180, 'sale_price' => 289, 'warranty_months' => 24, 'low_stock_threshold' => 8],
            ['sku' => 'ELEC-USB-SAN-128', 'name' => 'SanDisk USB Flash 128GB', 'name_ar' => 'فلاش سانديسك 128 جيجا', 'category_code' => 'ELEC-STORAGE', 'cost_price' => 30, 'sale_price' => 55, 'warranty_months' => 24, 'low_stock_threshold' => 15],

            // Accessories
            ['sku' => 'ELEC-KB-LOGI-WIRE', 'name' => 'Logitech Wireless Keyboard Mouse', 'name_ar' => 'كيبورد وماوس لوجيتك لاسلكي', 'category_code' => 'ELEC-ACCESS', 'cost_price' => 80, 'sale_price' => 139, 'warranty_months' => 12, 'low_stock_threshold' => 10],
            ['sku' => 'ELEC-MOUSE-LOGI-G', 'name' => 'Logitech G502 Gaming Mouse', 'name_ar' => 'ماوس قيمينق لوجيتك G502', 'category_code' => 'ELEC-ACCESS', 'cost_price' => 150, 'sale_price' => 249, 'warranty_months' => 24, 'low_stock_threshold' => 8],
            ['sku' => 'ELEC-WEBCAM-LOGI', 'name' => 'Logitech C920 HD Webcam', 'name_ar' => 'كاميرا ويب لوجيتك C920', 'category_code' => 'ELEC-ACCESS', 'cost_price' => 150, 'sale_price' => 249, 'warranty_months' => 24, 'low_stock_threshold' => 6],
            ['sku' => 'ELEC-HDMI-2M', 'name' => 'HDMI Cable 2m', 'name_ar' => 'كابل HDMI 2 متر', 'category_code' => 'ELEC-ACCESS', 'cost_price' => 10, 'sale_price' => 25, 'low_stock_threshold' => 20],
            ['sku' => 'ELEC-EXT-6-OUT', 'name' => 'Power Extension 6 Outlets', 'name_ar' => 'توصيلة كهرباء 6 مخارج', 'category_code' => 'ELEC-ACCESS', 'cost_price' => 20, 'sale_price' => 39, 'low_stock_threshold' => 15],
        ];
    }
}
