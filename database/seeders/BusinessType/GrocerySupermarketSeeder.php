<?php

namespace Database\Seeders\BusinessType;

class GrocerySupermarketSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Grocery & Supermarket',
            'name_ar' => 'بقالة وسوبرماركت',
            'slug' => 'grocery-supermarket',
            'icon' => 'ShoppingCartOutlined',
            'description' => 'Fresh produce, dairy, beverages, frozen foods, and household items',
            'default_attributes' => [
                'product_attributes' => ['expiry_date', 'weight_type', 'unit', 'origin', 'brand'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => true,
            ],
            'receipt_config' => [
                'show_expiry' => false,
                'show_weight' => true,
            ],
            'settings' => [
                'track_expiry' => true,
                'fifo_inventory' => true,
                'weight_based_items' => true,
            ],
            'is_active' => true,
            'sort_order' => 4,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'GRO-FRESH', 'name' => 'Fresh Produce', 'name_ar' => 'الخضار والفواكه الطازجة'],
            ['code' => 'GRO-VEG', 'name' => 'Vegetables', 'name_ar' => 'الخضروات', 'parent_code' => 'GRO-FRESH'],
            ['code' => 'GRO-FRUIT', 'name' => 'Fruits', 'name_ar' => 'الفواكه', 'parent_code' => 'GRO-FRESH'],
            ['code' => 'GRO-DAIRY', 'name' => 'Dairy & Eggs', 'name_ar' => 'الألبان والبيض'],
            ['code' => 'GRO-MEAT', 'name' => 'Meat & Poultry', 'name_ar' => 'اللحوم والدواجن'],
            ['code' => 'GRO-BAKERY', 'name' => 'Bakery', 'name_ar' => 'المخبوزات'],
            ['code' => 'GRO-BEV', 'name' => 'Beverages', 'name_ar' => 'المشروبات'],
            ['code' => 'GRO-WATER', 'name' => 'Water & Juices', 'name_ar' => 'المياه والعصائر', 'parent_code' => 'GRO-BEV'],
            ['code' => 'GRO-SODA', 'name' => 'Soft Drinks', 'name_ar' => 'المشروبات الغازية', 'parent_code' => 'GRO-BEV'],
            ['code' => 'GRO-SNACKS', 'name' => 'Snacks & Chips', 'name_ar' => 'الوجبات الخفيفة والشيبس'],
            ['code' => 'GRO-CANNED', 'name' => 'Canned Goods', 'name_ar' => 'المعلبات'],
            ['code' => 'GRO-RICE', 'name' => 'Rice & Grains', 'name_ar' => 'الأرز والحبوب'],
            ['code' => 'GRO-OIL', 'name' => 'Cooking Oil & Ghee', 'name_ar' => 'الزيوت والسمن'],
            ['code' => 'GRO-SPICE', 'name' => 'Spices & Seasonings', 'name_ar' => 'البهارات والتوابل'],
            ['code' => 'GRO-FROZEN', 'name' => 'Frozen Foods', 'name_ar' => 'الأطعمة المجمدة'],
            ['code' => 'GRO-CLEAN', 'name' => 'Cleaning Supplies', 'name_ar' => 'مواد التنظيف'],
            ['code' => 'GRO-PERSONAL', 'name' => 'Personal Care', 'name_ar' => 'العناية الشخصية'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Vegetables
            ['sku' => 'GRO-TOM-1KG', 'name' => 'Tomatoes (1kg)', 'name_ar' => 'طماطم (1 كيلو)', 'category_code' => 'GRO-VEG', 'cost_price' => 3, 'sale_price' => 5, 'weight_type' => 'per_kg', 'low_stock_threshold' => 20],
            ['sku' => 'GRO-ONION-1KG', 'name' => 'Onions (1kg)', 'name_ar' => 'بصل (1 كيلو)', 'category_code' => 'GRO-VEG', 'cost_price' => 2, 'sale_price' => 4, 'weight_type' => 'per_kg', 'low_stock_threshold' => 25],
            ['sku' => 'GRO-POTATO-1KG', 'name' => 'Potatoes (1kg)', 'name_ar' => 'بطاطس (1 كيلو)', 'category_code' => 'GRO-VEG', 'cost_price' => 2.5, 'sale_price' => 4.5, 'weight_type' => 'per_kg', 'low_stock_threshold' => 25],
            ['sku' => 'GRO-CUCUMBER', 'name' => 'Cucumber (1kg)', 'name_ar' => 'خيار (1 كيلو)', 'category_code' => 'GRO-VEG', 'cost_price' => 3, 'sale_price' => 5, 'weight_type' => 'per_kg', 'low_stock_threshold' => 20],
            ['sku' => 'GRO-LETTUCE', 'name' => 'Lettuce (head)', 'name_ar' => 'خس (رأس)', 'category_code' => 'GRO-VEG', 'cost_price' => 2, 'sale_price' => 4, 'weight_type' => 'per_piece', 'low_stock_threshold' => 15],
            ['sku' => 'GRO-CARROT-1KG', 'name' => 'Carrots (1kg)', 'name_ar' => 'جزر (1 كيلو)', 'category_code' => 'GRO-VEG', 'cost_price' => 3, 'sale_price' => 5.5, 'weight_type' => 'per_kg', 'low_stock_threshold' => 20],

            // Fruits
            ['sku' => 'GRO-APPLE-1KG', 'name' => 'Red Apples (1kg)', 'name_ar' => 'تفاح أحمر (1 كيلو)', 'category_code' => 'GRO-FRUIT', 'cost_price' => 5, 'sale_price' => 9, 'weight_type' => 'per_kg', 'low_stock_threshold' => 15],
            ['sku' => 'GRO-BANANA-1KG', 'name' => 'Bananas (1kg)', 'name_ar' => 'موز (1 كيلو)', 'category_code' => 'GRO-FRUIT', 'cost_price' => 3, 'sale_price' => 6, 'weight_type' => 'per_kg', 'low_stock_threshold' => 20],
            ['sku' => 'GRO-ORANGE-1KG', 'name' => 'Oranges (1kg)', 'name_ar' => 'برتقال (1 كيلو)', 'category_code' => 'GRO-FRUIT', 'cost_price' => 4, 'sale_price' => 7, 'weight_type' => 'per_kg', 'low_stock_threshold' => 15],
            ['sku' => 'GRO-MANGO-1KG', 'name' => 'Mangoes (1kg)', 'name_ar' => 'مانجو (1 كيلو)', 'category_code' => 'GRO-FRUIT', 'cost_price' => 8, 'sale_price' => 14, 'weight_type' => 'per_kg', 'low_stock_threshold' => 12],
            ['sku' => 'GRO-GRAPE-500G', 'name' => 'Grapes (500g)', 'name_ar' => 'عنب (500 جرام)', 'category_code' => 'GRO-FRUIT', 'cost_price' => 6, 'sale_price' => 10, 'weight_type' => 'per_pack', 'low_stock_threshold' => 15],

            // Dairy & Eggs
            ['sku' => 'GRO-MILK-ALMARAI-1L', 'name' => 'Almarai Full Cream Milk 1L', 'name_ar' => 'حليب المراعي كامل الدسم 1 لتر', 'category_code' => 'GRO-DAIRY', 'cost_price' => 4, 'sale_price' => 6.5, 'low_stock_threshold' => 25],
            ['sku' => 'GRO-MILK-NADA-1L', 'name' => 'Nada Full Cream Milk 1L', 'name_ar' => 'حليب ندى كامل الدسم 1 لتر', 'category_code' => 'GRO-DAIRY', 'cost_price' => 3.5, 'sale_price' => 6, 'low_stock_threshold' => 25],
            ['sku' => 'GRO-LABAN-1L', 'name' => 'Almarai Laban 1L', 'name_ar' => 'لبن المراعي 1 لتر', 'category_code' => 'GRO-DAIRY', 'cost_price' => 3.5, 'sale_price' => 6, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-YOGURT-170G', 'name' => 'Almarai Yogurt 170g', 'name_ar' => 'روب المراعي 170 جرام', 'category_code' => 'GRO-DAIRY', 'cost_price' => 1.5, 'sale_price' => 2.5, 'low_stock_threshold' => 40],
            ['sku' => 'GRO-CHEESE-SLICE', 'name' => 'Puck Cheese Slices (10 pcs)', 'name_ar' => 'جبنة بوك شرائح (10 قطع)', 'category_code' => 'GRO-DAIRY', 'cost_price' => 8, 'sale_price' => 13, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-CHEESE-FETA', 'name' => 'Puck Feta Cheese 200g', 'name_ar' => 'جبنة بوك فيتا 200 جرام', 'category_code' => 'GRO-DAIRY', 'cost_price' => 6, 'sale_price' => 10, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-EGGS-30', 'name' => 'Fresh Eggs (30 pcs)', 'name_ar' => 'بيض طازج (30 حبة)', 'category_code' => 'GRO-DAIRY', 'cost_price' => 15, 'sale_price' => 22, 'low_stock_threshold' => 10],
            ['sku' => 'GRO-BUTTER-400G', 'name' => 'Lurpak Butter 400g', 'name_ar' => 'زبدة لورباك 400 جرام', 'category_code' => 'GRO-DAIRY', 'cost_price' => 18, 'sale_price' => 28, 'low_stock_threshold' => 10],

            // Meat & Poultry
            ['sku' => 'GRO-CHICKEN-1KG', 'name' => 'Fresh Whole Chicken (1kg)', 'name_ar' => 'دجاج كامل طازج (1 كيلو)', 'category_code' => 'GRO-MEAT', 'cost_price' => 12, 'sale_price' => 18, 'weight_type' => 'per_kg', 'low_stock_threshold' => 10],
            ['sku' => 'GRO-CHICKEN-BREAST', 'name' => 'Chicken Breast (1kg)', 'name_ar' => 'صدور دجاج (1 كيلو)', 'category_code' => 'GRO-MEAT', 'cost_price' => 18, 'sale_price' => 28, 'weight_type' => 'per_kg', 'low_stock_threshold' => 8],
            ['sku' => 'GRO-BEEF-1KG', 'name' => 'Fresh Beef (1kg)', 'name_ar' => 'لحم بقري طازج (1 كيلو)', 'category_code' => 'GRO-MEAT', 'cost_price' => 35, 'sale_price' => 55, 'weight_type' => 'per_kg', 'low_stock_threshold' => 8],
            ['sku' => 'GRO-LAMB-1KG', 'name' => 'Fresh Lamb (1kg)', 'name_ar' => 'لحم ضأن طازج (1 كيلو)', 'category_code' => 'GRO-MEAT', 'cost_price' => 45, 'sale_price' => 65, 'weight_type' => 'per_kg', 'low_stock_threshold' => 6],

            // Bakery
            ['sku' => 'GRO-BREAD-WHITE', 'name' => 'White Bread Sliced', 'name_ar' => 'خبز توست ابيض', 'category_code' => 'GRO-BAKERY', 'cost_price' => 3, 'sale_price' => 5, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-BREAD-BROWN', 'name' => 'Brown Bread Sliced', 'name_ar' => 'خبز توست بني', 'category_code' => 'GRO-BAKERY', 'cost_price' => 3.5, 'sale_price' => 6, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-SAMOON-6', 'name' => 'Arabic Bread (6 pcs)', 'name_ar' => 'صمون عربي (6 قطع)', 'category_code' => 'GRO-BAKERY', 'cost_price' => 2, 'sale_price' => 3.5, 'low_stock_threshold' => 25],
            ['sku' => 'GRO-CROISSANT-4', 'name' => 'Croissant (4 pcs)', 'name_ar' => 'كرواسون (4 قطع)', 'category_code' => 'GRO-BAKERY', 'cost_price' => 5, 'sale_price' => 9, 'low_stock_threshold' => 15],

            // Water & Juices
            ['sku' => 'GRO-WATER-500ML', 'name' => 'Safi Water 500ml', 'name_ar' => 'مياه صافي 500 مل', 'category_code' => 'GRO-WATER', 'cost_price' => 0.5, 'sale_price' => 1, 'low_stock_threshold' => 100],
            ['sku' => 'GRO-WATER-1.5L', 'name' => 'Safi Water 1.5L', 'name_ar' => 'مياه صافي 1.5 لتر', 'category_code' => 'GRO-WATER', 'cost_price' => 1, 'sale_price' => 2, 'low_stock_threshold' => 50],
            ['sku' => 'GRO-WATER-5L', 'name' => 'Safi Water 5L', 'name_ar' => 'مياه صافي 5 لتر', 'category_code' => 'GRO-WATER', 'cost_price' => 3, 'sale_price' => 5, 'low_stock_threshold' => 25],
            ['sku' => 'GRO-JUICE-ALMARAI-1L', 'name' => 'Almarai Orange Juice 1L', 'name_ar' => 'عصير المراعي برتقال 1 لتر', 'category_code' => 'GRO-WATER', 'cost_price' => 5, 'sale_price' => 8, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-JUICE-APPLE-1L', 'name' => 'Almarai Apple Juice 1L', 'name_ar' => 'عصير المراعي تفاح 1 لتر', 'category_code' => 'GRO-WATER', 'cost_price' => 5, 'sale_price' => 8, 'low_stock_threshold' => 20],

            // Soft Drinks
            ['sku' => 'GRO-PEPSI-330ML', 'name' => 'Pepsi 330ml Can', 'name_ar' => 'بيبسي 330 مل علبة', 'category_code' => 'GRO-SODA', 'cost_price' => 1.5, 'sale_price' => 2.5, 'low_stock_threshold' => 50],
            ['sku' => 'GRO-COKE-330ML', 'name' => 'Coca-Cola 330ml Can', 'name_ar' => 'كوكا كولا 330 مل علبة', 'category_code' => 'GRO-SODA', 'cost_price' => 1.5, 'sale_price' => 2.5, 'low_stock_threshold' => 50],
            ['sku' => 'GRO-SPRITE-330ML', 'name' => 'Sprite 330ml Can', 'name_ar' => 'سبرايت 330 مل علبة', 'category_code' => 'GRO-SODA', 'cost_price' => 1.5, 'sale_price' => 2.5, 'low_stock_threshold' => 50],
            ['sku' => 'GRO-PEPSI-1L', 'name' => 'Pepsi 1L Bottle', 'name_ar' => 'بيبسي 1 لتر', 'category_code' => 'GRO-SODA', 'cost_price' => 3, 'sale_price' => 5, 'low_stock_threshold' => 30],
            ['sku' => 'GRO-REDBULL', 'name' => 'Red Bull Energy 250ml', 'name_ar' => 'ريد بول 250 مل', 'category_code' => 'GRO-SODA', 'cost_price' => 5, 'sale_price' => 8, 'low_stock_threshold' => 25],

            // Snacks
            ['sku' => 'GRO-LAYS-160G', 'name' => 'Lays Classic Chips 160g', 'name_ar' => 'ليز كلاسيك 160 جرام', 'category_code' => 'GRO-SNACKS', 'cost_price' => 4, 'sale_price' => 7, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-DORITOS-180G', 'name' => 'Doritos Nacho Cheese 180g', 'name_ar' => 'دوريتوس ناتشو تشيز 180 جرام', 'category_code' => 'GRO-SNACKS', 'cost_price' => 5, 'sale_price' => 8, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-PRINGLES-165G', 'name' => 'Pringles Original 165g', 'name_ar' => 'برينجلز اورجينال 165 جرام', 'category_code' => 'GRO-SNACKS', 'cost_price' => 6, 'sale_price' => 10, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-NUTS-MIX-200G', 'name' => 'Mixed Nuts 200g', 'name_ar' => 'مكسرات مشكلة 200 جرام', 'category_code' => 'GRO-SNACKS', 'cost_price' => 12, 'sale_price' => 20, 'low_stock_threshold' => 15],

            // Rice & Grains
            ['sku' => 'GRO-RICE-BASMATI-5KG', 'name' => 'Basmati Rice 5kg', 'name_ar' => 'أرز بسمتي 5 كيلو', 'category_code' => 'GRO-RICE', 'cost_price' => 35, 'sale_price' => 55, 'low_stock_threshold' => 10],
            ['sku' => 'GRO-RICE-MASRY-5KG', 'name' => 'Egyptian Rice 5kg', 'name_ar' => 'أرز مصري 5 كيلو', 'category_code' => 'GRO-RICE', 'cost_price' => 25, 'sale_price' => 40, 'low_stock_threshold' => 12],
            ['sku' => 'GRO-FLOUR-2KG', 'name' => 'All Purpose Flour 2kg', 'name_ar' => 'دقيق متعدد الاستخدام 2 كيلو', 'category_code' => 'GRO-RICE', 'cost_price' => 6, 'sale_price' => 10, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-SUGAR-2KG', 'name' => 'White Sugar 2kg', 'name_ar' => 'سكر ابيض 2 كيلو', 'category_code' => 'GRO-RICE', 'cost_price' => 8, 'sale_price' => 12, 'low_stock_threshold' => 15],

            // Cooking Oil
            ['sku' => 'GRO-OIL-CORN-1.5L', 'name' => 'Shams Corn Oil 1.5L', 'name_ar' => 'زيت شمس ذرة 1.5 لتر', 'category_code' => 'GRO-OIL', 'cost_price' => 15, 'sale_price' => 24, 'low_stock_threshold' => 12],
            ['sku' => 'GRO-OIL-VEG-1.5L', 'name' => 'Afia Vegetable Oil 1.5L', 'name_ar' => 'زيت عافية 1.5 لتر', 'category_code' => 'GRO-OIL', 'cost_price' => 14, 'sale_price' => 22, 'low_stock_threshold' => 12],
            ['sku' => 'GRO-OIL-OLIVE-500ML', 'name' => 'Extra Virgin Olive Oil 500ml', 'name_ar' => 'زيت زيتون بكر 500 مل', 'category_code' => 'GRO-OIL', 'cost_price' => 25, 'sale_price' => 42, 'low_stock_threshold' => 10],
            ['sku' => 'GRO-GHEE-900G', 'name' => 'Goody Vegetable Ghee 900g', 'name_ar' => 'سمن جودي نباتي 900 جرام', 'category_code' => 'GRO-OIL', 'cost_price' => 12, 'sale_price' => 20, 'low_stock_threshold' => 12],

            // Canned Goods
            ['sku' => 'GRO-TUNA-170G', 'name' => 'Americana Tuna Chunk 170g', 'name_ar' => 'تونة امريكانا قطع 170 جرام', 'category_code' => 'GRO-CANNED', 'cost_price' => 6, 'sale_price' => 10, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-BEANS-400G', 'name' => 'Heinz Baked Beans 400g', 'name_ar' => 'فول هاينز 400 جرام', 'category_code' => 'GRO-CANNED', 'cost_price' => 5, 'sale_price' => 8, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-CORN-340G', 'name' => 'Green Giant Sweet Corn 340g', 'name_ar' => 'ذرة جرين جاينت 340 جرام', 'category_code' => 'GRO-CANNED', 'cost_price' => 5, 'sale_price' => 9, 'low_stock_threshold' => 18],
            ['sku' => 'GRO-TOMATO-PASTE', 'name' => 'Tomato Paste 400g', 'name_ar' => 'معجون طماطم 400 جرام', 'category_code' => 'GRO-CANNED', 'cost_price' => 3, 'sale_price' => 5.5, 'low_stock_threshold' => 25],

            // Frozen Foods
            ['sku' => 'GRO-FRIES-1KG', 'name' => 'McCain French Fries 1kg', 'name_ar' => 'بطاطس ماكين 1 كيلو', 'category_code' => 'GRO-FROZEN', 'cost_price' => 12, 'sale_price' => 18, 'low_stock_threshold' => 12],
            ['sku' => 'GRO-NUGGETS-500G', 'name' => 'Al Kabeer Chicken Nuggets 500g', 'name_ar' => 'ناجتس الكبير 500 جرام', 'category_code' => 'GRO-FROZEN', 'cost_price' => 15, 'sale_price' => 25, 'low_stock_threshold' => 10],
            ['sku' => 'GRO-ICECREAM-2L', 'name' => 'Baskin Robbins Vanilla 2L', 'name_ar' => 'ايس كريم باسكن روبنز فانيلا 2 لتر', 'category_code' => 'GRO-FROZEN', 'cost_price' => 35, 'sale_price' => 55, 'low_stock_threshold' => 6],

            // Cleaning Supplies
            ['sku' => 'GRO-TIDE-3KG', 'name' => 'Tide Detergent 3kg', 'name_ar' => 'تايد مسحوق 3 كيلو', 'category_code' => 'GRO-CLEAN', 'cost_price' => 25, 'sale_price' => 42, 'low_stock_threshold' => 10],
            ['sku' => 'GRO-FAIRY-900ML', 'name' => 'Fairy Dish Soap 900ml', 'name_ar' => 'فيري سائل غسيل 900 مل', 'category_code' => 'GRO-CLEAN', 'cost_price' => 8, 'sale_price' => 14, 'low_stock_threshold' => 15],
            ['sku' => 'GRO-CLOROX-1L', 'name' => 'Clorox Bleach 1L', 'name_ar' => 'كلوركس مبيض 1 لتر', 'category_code' => 'GRO-CLEAN', 'cost_price' => 5, 'sale_price' => 9, 'low_stock_threshold' => 18],
            ['sku' => 'GRO-TISSUE-200', 'name' => 'Fine Tissue Box (200 sheets)', 'name_ar' => 'مناديل فاين (200 ورقة)', 'category_code' => 'GRO-CLEAN', 'cost_price' => 5, 'sale_price' => 9, 'low_stock_threshold' => 20],
            ['sku' => 'GRO-TOILET-6', 'name' => 'Fine Toilet Rolls (6 pcs)', 'name_ar' => 'مناديل حمام فاين (6 قطع)', 'category_code' => 'GRO-CLEAN', 'cost_price' => 12, 'sale_price' => 20, 'low_stock_threshold' => 15],
        ];
    }
}
