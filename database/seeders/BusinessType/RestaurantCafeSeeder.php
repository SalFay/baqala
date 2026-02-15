<?php

namespace Database\Seeders\BusinessType;

class RestaurantCafeSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Restaurant & Cafe',
            'name_ar' => 'مطعم وكافيه',
            'slug' => 'restaurant-cafe',
            'icon' => 'CoffeeOutlined',
            'description' => 'Hot and cold drinks, food items, combos, and cafe services',
            'default_attributes' => [
                'product_attributes' => ['modifiers', 'prep_time', 'calories', 'allergens', 'spicy_level'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => true,
            ],
            'receipt_config' => [
                'show_prep_time' => false,
                'kitchen_print' => true,
                'table_number' => true,
            ],
            'settings' => [
                'table_management' => true,
                'kitchen_display' => true,
                'modifiers_support' => true,
            ],
            'is_active' => true,
            'sort_order' => 7,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'REST-HOT', 'name' => 'Hot Drinks', 'name_ar' => 'مشروبات ساخنة'],
            ['code' => 'REST-COFFEE', 'name' => 'Coffee', 'name_ar' => 'القهوة', 'parent_code' => 'REST-HOT'],
            ['code' => 'REST-TEA', 'name' => 'Tea', 'name_ar' => 'الشاي', 'parent_code' => 'REST-HOT'],
            ['code' => 'REST-COLD', 'name' => 'Cold Drinks', 'name_ar' => 'مشروبات باردة'],
            ['code' => 'REST-JUICE', 'name' => 'Fresh Juices', 'name_ar' => 'عصائر طازجة', 'parent_code' => 'REST-COLD'],
            ['code' => 'REST-SMOOTH', 'name' => 'Smoothies', 'name_ar' => 'سموذي', 'parent_code' => 'REST-COLD'],
            ['code' => 'REST-FOOD', 'name' => 'Food', 'name_ar' => 'الطعام'],
            ['code' => 'REST-BREAK', 'name' => 'Breakfast', 'name_ar' => 'الفطور', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-SAND', 'name' => 'Sandwiches', 'name_ar' => 'ساندويتشات', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-BURGER', 'name' => 'Burgers', 'name_ar' => 'برجر', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-PIZZA', 'name' => 'Pizza', 'name_ar' => 'بيتزا', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-PASTA', 'name' => 'Pasta', 'name_ar' => 'باستا', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-SALAD', 'name' => 'Salads', 'name_ar' => 'سلطات', 'parent_code' => 'REST-FOOD'],
            ['code' => 'REST-DESSERT', 'name' => 'Desserts', 'name_ar' => 'حلويات'],
            ['code' => 'REST-SIDES', 'name' => 'Sides', 'name_ar' => 'إضافات'],
            ['code' => 'REST-COMBOS', 'name' => 'Combos', 'name_ar' => 'وجبات'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Coffee
            ['sku' => 'REST-ESP-SINGLE', 'name' => 'Espresso Single', 'name_ar' => 'اسبريسو سنجل', 'category_code' => 'REST-COFFEE', 'cost_price' => 3, 'sale_price' => 10, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-ESP-DOUBLE', 'name' => 'Espresso Double', 'name_ar' => 'اسبريسو دبل', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 14, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-AMER-S', 'name' => 'Americano Small', 'name_ar' => 'امريكانو صغير', 'category_code' => 'REST-COFFEE', 'cost_price' => 3, 'sale_price' => 12, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-AMER-L', 'name' => 'Americano Large', 'name_ar' => 'امريكانو كبير', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 16, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-LATTE-S', 'name' => 'Caffe Latte Small', 'name_ar' => 'كافيه لاتيه صغير', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 16, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-LATTE-L', 'name' => 'Caffe Latte Large', 'name_ar' => 'كافيه لاتيه كبير', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 20, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-CAPP-S', 'name' => 'Cappuccino Small', 'name_ar' => 'كابتشينو صغير', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 16, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-CAPP-L', 'name' => 'Cappuccino Large', 'name_ar' => 'كابتشينو كبير', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 20, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-MOCHA-S', 'name' => 'Caffe Mocha Small', 'name_ar' => 'كافيه موكا صغير', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 18, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-MOCHA-L', 'name' => 'Caffe Mocha Large', 'name_ar' => 'كافيه موكا كبير', 'category_code' => 'REST-COFFEE', 'cost_price' => 6, 'sale_price' => 22, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-FLAT-WHITE', 'name' => 'Flat White', 'name_ar' => 'فلات وايت', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 18, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-MACCHIATO', 'name' => 'Macchiato', 'name_ar' => 'ماكياتو', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 14, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-SPANISH', 'name' => 'Spanish Latte', 'name_ar' => 'سبانش لاتيه', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 20, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-ICED-AMER', 'name' => 'Iced Americano', 'name_ar' => 'ايس امريكانو', 'category_code' => 'REST-COFFEE', 'cost_price' => 4, 'sale_price' => 16, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-ICED-LATTE', 'name' => 'Iced Latte', 'name_ar' => 'ايس لاتيه', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 18, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-ICED-MOCHA', 'name' => 'Iced Mocha', 'name_ar' => 'ايس موكا', 'category_code' => 'REST-COFFEE', 'cost_price' => 6, 'sale_price' => 20, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-V60', 'name' => 'V60 Pour Over', 'name_ar' => 'V60 بور اوفر', 'category_code' => 'REST-COFFEE', 'cost_price' => 5, 'sale_price' => 22, 'prep_time' => 5, 'track_inventory' => false],

            // Tea
            ['sku' => 'REST-TEA-BLACK', 'name' => 'Black Tea', 'name_ar' => 'شاي اسود', 'category_code' => 'REST-TEA', 'cost_price' => 2, 'sale_price' => 8, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-TEA-GREEN', 'name' => 'Green Tea', 'name_ar' => 'شاي اخضر', 'category_code' => 'REST-TEA', 'cost_price' => 2, 'sale_price' => 10, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-TEA-KARAK', 'name' => 'Karak Tea', 'name_ar' => 'شاي كرك', 'category_code' => 'REST-TEA', 'cost_price' => 3, 'sale_price' => 10, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-TEA-MINT', 'name' => 'Mint Tea', 'name_ar' => 'شاي نعناع', 'category_code' => 'REST-TEA', 'cost_price' => 2, 'sale_price' => 10, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-HOT-CHOC', 'name' => 'Hot Chocolate', 'name_ar' => 'هوت شوكليت', 'category_code' => 'REST-HOT', 'cost_price' => 4, 'sale_price' => 18, 'prep_time' => 4, 'track_inventory' => false],

            // Fresh Juices
            ['sku' => 'REST-JUICE-ORG', 'name' => 'Fresh Orange Juice', 'name_ar' => 'عصير برتقال طازج', 'category_code' => 'REST-JUICE', 'cost_price' => 5, 'sale_price' => 16, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-JUICE-APP', 'name' => 'Fresh Apple Juice', 'name_ar' => 'عصير تفاح طازج', 'category_code' => 'REST-JUICE', 'cost_price' => 5, 'sale_price' => 16, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-JUICE-MIX', 'name' => 'Fresh Mixed Juice', 'name_ar' => 'عصير كوكتيل طازج', 'category_code' => 'REST-JUICE', 'cost_price' => 6, 'sale_price' => 18, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-JUICE-LEMON', 'name' => 'Fresh Lemonade', 'name_ar' => 'ليموناضة طازجة', 'category_code' => 'REST-JUICE', 'cost_price' => 4, 'sale_price' => 14, 'prep_time' => 4, 'track_inventory' => false],
            ['sku' => 'REST-MOJITO', 'name' => 'Virgin Mojito', 'name_ar' => 'موهيتو', 'category_code' => 'REST-JUICE', 'cost_price' => 5, 'sale_price' => 18, 'prep_time' => 5, 'track_inventory' => false],

            // Smoothies
            ['sku' => 'REST-SMOOTH-BERRY', 'name' => 'Mixed Berry Smoothie', 'name_ar' => 'سموذي توت مشكل', 'category_code' => 'REST-SMOOTH', 'cost_price' => 7, 'sale_price' => 22, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-SMOOTH-MANGO', 'name' => 'Mango Smoothie', 'name_ar' => 'سموذي مانجو', 'category_code' => 'REST-SMOOTH', 'cost_price' => 7, 'sale_price' => 22, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-SMOOTH-BANANA', 'name' => 'Banana Smoothie', 'name_ar' => 'سموذي موز', 'category_code' => 'REST-SMOOTH', 'cost_price' => 6, 'sale_price' => 20, 'prep_time' => 5, 'track_inventory' => false],

            // Breakfast
            ['sku' => 'REST-EGG-SCRAM', 'name' => 'Scrambled Eggs with Toast', 'name_ar' => 'بيض مخفوق مع توست', 'category_code' => 'REST-BREAK', 'cost_price' => 8, 'sale_price' => 22, 'prep_time' => 10, 'track_inventory' => false],
            ['sku' => 'REST-EGG-FRIED', 'name' => 'Fried Eggs with Toast', 'name_ar' => 'بيض مقلي مع توست', 'category_code' => 'REST-BREAK', 'cost_price' => 8, 'sale_price' => 22, 'prep_time' => 10, 'track_inventory' => false],
            ['sku' => 'REST-OMELETTE', 'name' => 'Cheese Omelette', 'name_ar' => 'اومليت جبن', 'category_code' => 'REST-BREAK', 'cost_price' => 10, 'sale_price' => 28, 'prep_time' => 12, 'track_inventory' => false],
            ['sku' => 'REST-FOUL', 'name' => 'Foul Medames', 'name_ar' => 'فول مدمس', 'category_code' => 'REST-BREAK', 'cost_price' => 6, 'sale_price' => 18, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-PANCAKE', 'name' => 'Pancakes with Maple Syrup', 'name_ar' => 'بانكيك مع شراب القيقب', 'category_code' => 'REST-BREAK', 'cost_price' => 8, 'sale_price' => 26, 'prep_time' => 12, 'track_inventory' => false],
            ['sku' => 'REST-FRENCH-TST', 'name' => 'French Toast', 'name_ar' => 'فرنش توست', 'category_code' => 'REST-BREAK', 'cost_price' => 7, 'sale_price' => 22, 'prep_time' => 10, 'track_inventory' => false],

            // Sandwiches
            ['sku' => 'REST-CLUB-SAND', 'name' => 'Club Sandwich', 'name_ar' => 'كلوب ساندويتش', 'category_code' => 'REST-SAND', 'cost_price' => 12, 'sale_price' => 32, 'prep_time' => 12, 'track_inventory' => false],
            ['sku' => 'REST-CHICKEN-SAND', 'name' => 'Grilled Chicken Sandwich', 'name_ar' => 'ساندويتش دجاج مشوي', 'category_code' => 'REST-SAND', 'cost_price' => 10, 'sale_price' => 28, 'prep_time' => 12, 'track_inventory' => false],
            ['sku' => 'REST-BEEF-SAND', 'name' => 'Beef Steak Sandwich', 'name_ar' => 'ساندويتش ستيك لحم', 'category_code' => 'REST-SAND', 'cost_price' => 14, 'sale_price' => 38, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-TUNA-SAND', 'name' => 'Tuna Sandwich', 'name_ar' => 'ساندويتش تونة', 'category_code' => 'REST-SAND', 'cost_price' => 8, 'sale_price' => 24, 'prep_time' => 10, 'track_inventory' => false],
            ['sku' => 'REST-FALAFEL-SAND', 'name' => 'Falafel Sandwich', 'name_ar' => 'ساندويتش فلافل', 'category_code' => 'REST-SAND', 'cost_price' => 6, 'sale_price' => 18, 'prep_time' => 10, 'track_inventory' => false],

            // Burgers
            ['sku' => 'REST-BURG-CLASSIC', 'name' => 'Classic Beef Burger', 'name_ar' => 'برجر لحم كلاسيك', 'category_code' => 'REST-BURGER', 'cost_price' => 12, 'sale_price' => 32, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-BURG-CHEESE', 'name' => 'Cheese Burger', 'name_ar' => 'تشيز برجر', 'category_code' => 'REST-BURGER', 'cost_price' => 14, 'sale_price' => 36, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-BURG-CHICKEN', 'name' => 'Crispy Chicken Burger', 'name_ar' => 'برجر دجاج مقرمش', 'category_code' => 'REST-BURGER', 'cost_price' => 11, 'sale_price' => 30, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-BURG-DBL', 'name' => 'Double Beef Burger', 'name_ar' => 'دبل برجر لحم', 'category_code' => 'REST-BURGER', 'cost_price' => 18, 'sale_price' => 45, 'prep_time' => 18, 'track_inventory' => false],
            ['sku' => 'REST-BURG-MUSHROOM', 'name' => 'Mushroom Swiss Burger', 'name_ar' => 'برجر فطر وجبن سويسري', 'category_code' => 'REST-BURGER', 'cost_price' => 16, 'sale_price' => 42, 'prep_time' => 18, 'track_inventory' => false],

            // Pizza
            ['sku' => 'REST-PIZZA-MARG-M', 'name' => 'Margherita Pizza Medium', 'name_ar' => 'بيتزا مارغريتا وسط', 'category_code' => 'REST-PIZZA', 'cost_price' => 15, 'sale_price' => 38, 'prep_time' => 20, 'track_inventory' => false],
            ['sku' => 'REST-PIZZA-MARG-L', 'name' => 'Margherita Pizza Large', 'name_ar' => 'بيتزا مارغريتا كبير', 'category_code' => 'REST-PIZZA', 'cost_price' => 20, 'sale_price' => 52, 'prep_time' => 25, 'track_inventory' => false],
            ['sku' => 'REST-PIZZA-PEPP-M', 'name' => 'Pepperoni Pizza Medium', 'name_ar' => 'بيتزا بيبروني وسط', 'category_code' => 'REST-PIZZA', 'cost_price' => 18, 'sale_price' => 45, 'prep_time' => 20, 'track_inventory' => false],
            ['sku' => 'REST-PIZZA-CHICK-M', 'name' => 'Chicken Pizza Medium', 'name_ar' => 'بيتزا دجاج وسط', 'category_code' => 'REST-PIZZA', 'cost_price' => 20, 'sale_price' => 48, 'prep_time' => 20, 'track_inventory' => false],
            ['sku' => 'REST-PIZZA-VEG-M', 'name' => 'Vegetable Pizza Medium', 'name_ar' => 'بيتزا خضار وسط', 'category_code' => 'REST-PIZZA', 'cost_price' => 16, 'sale_price' => 42, 'prep_time' => 20, 'track_inventory' => false],

            // Pasta
            ['sku' => 'REST-PASTA-ALF', 'name' => 'Chicken Alfredo Pasta', 'name_ar' => 'باستا الفريدو بالدجاج', 'category_code' => 'REST-PASTA', 'cost_price' => 14, 'sale_price' => 38, 'prep_time' => 18, 'track_inventory' => false],
            ['sku' => 'REST-PASTA-ARRAB', 'name' => 'Penne Arrabiata', 'name_ar' => 'بيني ارابياتا', 'category_code' => 'REST-PASTA', 'cost_price' => 12, 'sale_price' => 32, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-PASTA-BOLOG', 'name' => 'Spaghetti Bolognese', 'name_ar' => 'سباغيتي بولونيز', 'category_code' => 'REST-PASTA', 'cost_price' => 14, 'sale_price' => 36, 'prep_time' => 18, 'track_inventory' => false],
            ['sku' => 'REST-PASTA-CARBO', 'name' => 'Carbonara Pasta', 'name_ar' => 'باستا كاربونارا', 'category_code' => 'REST-PASTA', 'cost_price' => 15, 'sale_price' => 40, 'prep_time' => 18, 'track_inventory' => false],

            // Salads
            ['sku' => 'REST-SALAD-CAESAR', 'name' => 'Caesar Salad', 'name_ar' => 'سلطة سيزر', 'category_code' => 'REST-SALAD', 'cost_price' => 10, 'sale_price' => 28, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-SALAD-GREEK', 'name' => 'Greek Salad', 'name_ar' => 'سلطة يونانية', 'category_code' => 'REST-SALAD', 'cost_price' => 8, 'sale_price' => 24, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-SALAD-FATTOUSH', 'name' => 'Fattoush Salad', 'name_ar' => 'سلطة فتوش', 'category_code' => 'REST-SALAD', 'cost_price' => 8, 'sale_price' => 22, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-SALAD-CHICKEN', 'name' => 'Grilled Chicken Salad', 'name_ar' => 'سلطة دجاج مشوي', 'category_code' => 'REST-SALAD', 'cost_price' => 14, 'sale_price' => 36, 'prep_time' => 12, 'track_inventory' => false],

            // Desserts
            ['sku' => 'REST-DESSERT-CAKE', 'name' => 'Chocolate Cake Slice', 'name_ar' => 'قطعة كيك شوكولاتة', 'category_code' => 'REST-DESSERT', 'cost_price' => 6, 'sale_price' => 18, 'prep_time' => 2, 'track_inventory' => false],
            ['sku' => 'REST-DESSERT-CHEESE', 'name' => 'Cheesecake Slice', 'name_ar' => 'قطعة تشيز كيك', 'category_code' => 'REST-DESSERT', 'cost_price' => 7, 'sale_price' => 22, 'prep_time' => 2, 'track_inventory' => false],
            ['sku' => 'REST-DESSERT-TIRAMISU', 'name' => 'Tiramisu', 'name_ar' => 'تيراميسو', 'category_code' => 'REST-DESSERT', 'cost_price' => 8, 'sale_price' => 24, 'prep_time' => 2, 'track_inventory' => false],
            ['sku' => 'REST-DESSERT-KUNAFA', 'name' => 'Kunafa with Cream', 'name_ar' => 'كنافة بالقشطة', 'category_code' => 'REST-DESSERT', 'cost_price' => 8, 'sale_price' => 26, 'prep_time' => 5, 'track_inventory' => false],
            ['sku' => 'REST-DESSERT-ICE', 'name' => 'Ice Cream 2 Scoops', 'name_ar' => 'ايس كريم 2 كرة', 'category_code' => 'REST-DESSERT', 'cost_price' => 5, 'sale_price' => 16, 'prep_time' => 2, 'track_inventory' => false],

            // Sides
            ['sku' => 'REST-SIDE-FRIES', 'name' => 'French Fries', 'name_ar' => 'بطاطس مقلية', 'category_code' => 'REST-SIDES', 'cost_price' => 4, 'sale_price' => 12, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-SIDE-ONION', 'name' => 'Onion Rings', 'name_ar' => 'حلقات البصل', 'category_code' => 'REST-SIDES', 'cost_price' => 5, 'sale_price' => 14, 'prep_time' => 8, 'track_inventory' => false],
            ['sku' => 'REST-SIDE-HUMMUS', 'name' => 'Hummus', 'name_ar' => 'حمص', 'category_code' => 'REST-SIDES', 'cost_price' => 4, 'sale_price' => 14, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-SIDE-MUTABBAL', 'name' => 'Mutabbal', 'name_ar' => 'متبل', 'category_code' => 'REST-SIDES', 'cost_price' => 4, 'sale_price' => 14, 'prep_time' => 3, 'track_inventory' => false],
            ['sku' => 'REST-SIDE-CHEESE-STIX', 'name' => 'Cheese Sticks', 'name_ar' => 'اصابع الجبن', 'category_code' => 'REST-SIDES', 'cost_price' => 6, 'sale_price' => 18, 'prep_time' => 8, 'track_inventory' => false],

            // Combos
            ['sku' => 'REST-COMBO-BURG', 'name' => 'Burger Combo (Burger + Fries + Drink)', 'name_ar' => 'وجبة برجر (برجر + بطاطس + مشروب)', 'category_code' => 'REST-COMBOS', 'cost_price' => 18, 'sale_price' => 45, 'prep_time' => 18, 'track_inventory' => false],
            ['sku' => 'REST-COMBO-CHICK', 'name' => 'Chicken Combo (Sandwich + Fries + Drink)', 'name_ar' => 'وجبة دجاج (ساندويتش + بطاطس + مشروب)', 'category_code' => 'REST-COMBOS', 'cost_price' => 16, 'sale_price' => 42, 'prep_time' => 15, 'track_inventory' => false],
            ['sku' => 'REST-COMBO-BREAK', 'name' => 'Breakfast Combo (Eggs + Toast + Coffee)', 'name_ar' => 'وجبة فطور (بيض + توست + قهوة)', 'category_code' => 'REST-COMBOS', 'cost_price' => 14, 'sale_price' => 35, 'prep_time' => 12, 'track_inventory' => false],
        ];
    }
}
