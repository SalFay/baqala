<?php

namespace Database\Seeders\BusinessType;

class ClothingFashionSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Clothing & Fashion',
            'name_ar' => 'ملابس وأزياء',
            'slug' => 'clothing-fashion',
            'icon' => 'SkinOutlined',
            'description' => 'Men, women, and children clothing, footwear, and fashion accessories',
            'default_attributes' => [
                'product_attributes' => ['size', 'color', 'material', 'season', 'brand', 'gender'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => true,
            ],
            'receipt_config' => [
                'show_size' => true,
                'show_color' => true,
            ],
            'settings' => [
                'size_chart' => true,
                'variant_based' => true,
            ],
            'is_active' => true,
            'sort_order' => 6,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'CLO-MEN', 'name' => 'Men', 'name_ar' => 'رجالي'],
            ['code' => 'CLO-MEN-SHIRT', 'name' => 'Men Shirts', 'name_ar' => 'قمصان رجالي', 'parent_code' => 'CLO-MEN'],
            ['code' => 'CLO-MEN-PANTS', 'name' => 'Men Pants', 'name_ar' => 'بناطيل رجالي', 'parent_code' => 'CLO-MEN'],
            ['code' => 'CLO-MEN-THOBE', 'name' => 'Men Thobes', 'name_ar' => 'ثياب رجالي', 'parent_code' => 'CLO-MEN'],
            ['code' => 'CLO-WOMEN', 'name' => 'Women', 'name_ar' => 'نسائي'],
            ['code' => 'CLO-WMN-DRESS', 'name' => 'Dresses', 'name_ar' => 'فساتين', 'parent_code' => 'CLO-WOMEN'],
            ['code' => 'CLO-WMN-ABAYA', 'name' => 'Abayas', 'name_ar' => 'عبايات', 'parent_code' => 'CLO-WOMEN'],
            ['code' => 'CLO-WMN-TOPS', 'name' => 'Women Tops', 'name_ar' => 'بلوزات نسائية', 'parent_code' => 'CLO-WOMEN'],
            ['code' => 'CLO-KIDS', 'name' => 'Kids', 'name_ar' => 'أطفال'],
            ['code' => 'CLO-KID-BOYS', 'name' => 'Boys Clothing', 'name_ar' => 'ملابس أولاد', 'parent_code' => 'CLO-KIDS'],
            ['code' => 'CLO-KID-GIRLS', 'name' => 'Girls Clothing', 'name_ar' => 'ملابس بنات', 'parent_code' => 'CLO-KIDS'],
            ['code' => 'CLO-FOOT', 'name' => 'Footwear', 'name_ar' => 'الأحذية'],
            ['code' => 'CLO-FOOT-MEN', 'name' => 'Men Shoes', 'name_ar' => 'أحذية رجالي', 'parent_code' => 'CLO-FOOT'],
            ['code' => 'CLO-FOOT-WMN', 'name' => 'Women Shoes', 'name_ar' => 'أحذية نسائي', 'parent_code' => 'CLO-FOOT'],
            ['code' => 'CLO-ACCESS', 'name' => 'Accessories', 'name_ar' => 'اكسسوارات'],
            ['code' => 'CLO-UNDER', 'name' => 'Underwear & Socks', 'name_ar' => 'ملابس داخلية وجوارب'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Men Shirts
            ['sku' => 'CLO-MSH-POLO-WH-M', 'name' => 'Men Polo Shirt White M', 'name_ar' => 'بولو رجالي ابيض مقاس M', 'category_code' => 'CLO-MEN-SHIRT', 'cost_price' => 40, 'sale_price' => 79, 'size' => 'M', 'color' => 'White', 'material' => 'Cotton', 'low_stock_threshold' => 8],
            ['sku' => 'CLO-MSH-POLO-BL-L', 'name' => 'Men Polo Shirt Blue L', 'name_ar' => 'بولو رجالي ازرق مقاس L', 'category_code' => 'CLO-MEN-SHIRT', 'cost_price' => 40, 'sale_price' => 79, 'size' => 'L', 'color' => 'Blue', 'material' => 'Cotton', 'low_stock_threshold' => 8],
            ['sku' => 'CLO-MSH-FORMAL-WH', 'name' => 'Men Formal Shirt White', 'name_ar' => 'قميص رجالي رسمي ابيض', 'category_code' => 'CLO-MEN-SHIRT', 'cost_price' => 60, 'sale_price' => 119, 'color' => 'White', 'material' => 'Cotton Blend', 'low_stock_threshold' => 6],
            ['sku' => 'CLO-MSH-TSHIRT-BK', 'name' => 'Men Basic T-Shirt Black', 'name_ar' => 'تيشيرت رجالي اسود', 'category_code' => 'CLO-MEN-SHIRT', 'cost_price' => 25, 'sale_price' => 49, 'color' => 'Black', 'material' => 'Cotton', 'low_stock_threshold' => 12],
            ['sku' => 'CLO-MSH-TSHIRT-GR', 'name' => 'Men Basic T-Shirt Gray', 'name_ar' => 'تيشيرت رجالي رمادي', 'category_code' => 'CLO-MEN-SHIRT', 'cost_price' => 25, 'sale_price' => 49, 'color' => 'Gray', 'material' => 'Cotton', 'low_stock_threshold' => 12],

            // Men Pants
            ['sku' => 'CLO-MPT-JEANS-BL-32', 'name' => 'Men Jeans Blue Size 32', 'name_ar' => 'جينز رجالي ازرق مقاس 32', 'category_code' => 'CLO-MEN-PANTS', 'cost_price' => 80, 'sale_price' => 159, 'size' => '32', 'color' => 'Blue', 'material' => 'Denim', 'low_stock_threshold' => 6],
            ['sku' => 'CLO-MPT-JEANS-BK-34', 'name' => 'Men Jeans Black Size 34', 'name_ar' => 'جينز رجالي اسود مقاس 34', 'category_code' => 'CLO-MEN-PANTS', 'cost_price' => 80, 'sale_price' => 159, 'size' => '34', 'color' => 'Black', 'material' => 'Denim', 'low_stock_threshold' => 6],
            ['sku' => 'CLO-MPT-FORMAL-NV', 'name' => 'Men Formal Pants Navy', 'name_ar' => 'بنطال رجالي رسمي كحلي', 'category_code' => 'CLO-MEN-PANTS', 'cost_price' => 70, 'sale_price' => 139, 'color' => 'Navy', 'material' => 'Polyester Blend', 'low_stock_threshold' => 6],
            ['sku' => 'CLO-MPT-CHINO-BG', 'name' => 'Men Chino Pants Beige', 'name_ar' => 'بنطال شينو رجالي بيج', 'category_code' => 'CLO-MEN-PANTS', 'cost_price' => 65, 'sale_price' => 129, 'color' => 'Beige', 'material' => 'Cotton', 'low_stock_threshold' => 6],

            // Men Thobes
            ['sku' => 'CLO-MTH-WHITE-52', 'name' => 'Men Thobe White Size 52', 'name_ar' => 'ثوب رجالي ابيض مقاس 52', 'category_code' => 'CLO-MEN-THOBE', 'cost_price' => 100, 'sale_price' => 199, 'size' => '52', 'color' => 'White', 'material' => 'Cotton', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-MTH-WHITE-54', 'name' => 'Men Thobe White Size 54', 'name_ar' => 'ثوب رجالي ابيض مقاس 54', 'category_code' => 'CLO-MEN-THOBE', 'cost_price' => 100, 'sale_price' => 199, 'size' => '54', 'color' => 'White', 'material' => 'Cotton', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-MTH-CREAM-56', 'name' => 'Men Thobe Cream Size 56', 'name_ar' => 'ثوب رجالي كريمي مقاس 56', 'category_code' => 'CLO-MEN-THOBE', 'cost_price' => 110, 'sale_price' => 219, 'size' => '56', 'color' => 'Cream', 'material' => 'Cotton', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-MTH-WINTER-GR', 'name' => 'Men Winter Thobe Gray', 'name_ar' => 'ثوب رجالي شتوي رمادي', 'category_code' => 'CLO-MEN-THOBE', 'cost_price' => 150, 'sale_price' => 299, 'color' => 'Gray', 'season' => 'Winter', 'low_stock_threshold' => 4],

            // Women Dresses
            ['sku' => 'CLO-WDR-CASUAL-BK', 'name' => 'Women Casual Dress Black', 'name_ar' => 'فستان نسائي كاجوال اسود', 'category_code' => 'CLO-WMN-DRESS', 'cost_price' => 90, 'sale_price' => 179, 'color' => 'Black', 'material' => 'Polyester', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-WDR-CASUAL-NV', 'name' => 'Women Casual Dress Navy', 'name_ar' => 'فستان نسائي كاجوال كحلي', 'category_code' => 'CLO-WMN-DRESS', 'cost_price' => 90, 'sale_price' => 179, 'color' => 'Navy', 'material' => 'Polyester', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-WDR-MAXI-FL', 'name' => 'Women Maxi Dress Floral', 'name_ar' => 'فستان ماكسي نسائي ورود', 'category_code' => 'CLO-WMN-DRESS', 'cost_price' => 110, 'sale_price' => 219, 'color' => 'Floral', 'material' => 'Chiffon', 'low_stock_threshold' => 4],

            // Women Abayas
            ['sku' => 'CLO-WAB-PLAIN-BK', 'name' => 'Women Plain Abaya Black', 'name_ar' => 'عباية نسائية سادة سوداء', 'category_code' => 'CLO-WMN-ABAYA', 'cost_price' => 120, 'sale_price' => 249, 'color' => 'Black', 'material' => 'Crepe', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-WAB-EMBR-BK', 'name' => 'Women Embroidered Abaya Black', 'name_ar' => 'عباية نسائية مطرزة سوداء', 'category_code' => 'CLO-WMN-ABAYA', 'cost_price' => 180, 'sale_price' => 349, 'color' => 'Black', 'material' => 'Crepe', 'low_stock_threshold' => 4],
            ['sku' => 'CLO-WAB-OPEN-NV', 'name' => 'Women Open Abaya Navy', 'name_ar' => 'عباية نسائية مفتوحة كحلي', 'category_code' => 'CLO-WMN-ABAYA', 'cost_price' => 150, 'sale_price' => 299, 'color' => 'Navy', 'material' => 'Nida', 'low_stock_threshold' => 4],

            // Women Tops
            ['sku' => 'CLO-WTP-BLOUSE-WH', 'name' => 'Women Blouse White', 'name_ar' => 'بلوزة نسائية بيضاء', 'category_code' => 'CLO-WMN-TOPS', 'cost_price' => 50, 'sale_price' => 99, 'color' => 'White', 'material' => 'Cotton', 'low_stock_threshold' => 6],
            ['sku' => 'CLO-WTP-TUNIC-BG', 'name' => 'Women Tunic Beige', 'name_ar' => 'تونيك نسائي بيج', 'category_code' => 'CLO-WMN-TOPS', 'cost_price' => 60, 'sale_price' => 119, 'color' => 'Beige', 'material' => 'Linen Blend', 'low_stock_threshold' => 5],

            // Boys Clothing
            ['sku' => 'CLO-BOY-TSHIRT-BL', 'name' => 'Boys T-Shirt Blue (8-10)', 'name_ar' => 'تيشيرت ولادي ازرق (8-10)', 'category_code' => 'CLO-KID-BOYS', 'cost_price' => 20, 'sale_price' => 39, 'size' => '8-10', 'color' => 'Blue', 'low_stock_threshold' => 10],
            ['sku' => 'CLO-BOY-JEANS-BL', 'name' => 'Boys Jeans Blue (10-12)', 'name_ar' => 'جينز ولادي ازرق (10-12)', 'category_code' => 'CLO-KID-BOYS', 'cost_price' => 40, 'sale_price' => 79, 'size' => '10-12', 'color' => 'Blue', 'low_stock_threshold' => 8],
            ['sku' => 'CLO-BOY-THOBE-WH', 'name' => 'Boys Thobe White (8-10)', 'name_ar' => 'ثوب ولادي ابيض (8-10)', 'category_code' => 'CLO-KID-BOYS', 'cost_price' => 60, 'sale_price' => 119, 'size' => '8-10', 'color' => 'White', 'low_stock_threshold' => 6],

            // Girls Clothing
            ['sku' => 'CLO-GRL-DRESS-PK', 'name' => 'Girls Dress Pink (6-8)', 'name_ar' => 'فستان بناتي وردي (6-8)', 'category_code' => 'CLO-KID-GIRLS', 'cost_price' => 45, 'sale_price' => 89, 'size' => '6-8', 'color' => 'Pink', 'low_stock_threshold' => 8],
            ['sku' => 'CLO-GRL-SKIRT-NV', 'name' => 'Girls Skirt Navy (8-10)', 'name_ar' => 'تنورة بناتي كحلي (8-10)', 'category_code' => 'CLO-KID-GIRLS', 'cost_price' => 30, 'sale_price' => 59, 'size' => '8-10', 'color' => 'Navy', 'low_stock_threshold' => 8],

            // Men Shoes
            ['sku' => 'CLO-MSH-FORMAL-BK-42', 'name' => 'Men Formal Shoes Black 42', 'name_ar' => 'حذاء رجالي رسمي اسود 42', 'category_code' => 'CLO-FOOT-MEN', 'cost_price' => 120, 'sale_price' => 249, 'size' => '42', 'color' => 'Black', 'material' => 'Leather', 'low_stock_threshold' => 4],
            ['sku' => 'CLO-MSH-FORMAL-BR-43', 'name' => 'Men Formal Shoes Brown 43', 'name_ar' => 'حذاء رجالي رسمي بني 43', 'category_code' => 'CLO-FOOT-MEN', 'cost_price' => 120, 'sale_price' => 249, 'size' => '43', 'color' => 'Brown', 'material' => 'Leather', 'low_stock_threshold' => 4],
            ['sku' => 'CLO-MSH-SNEAK-WH-42', 'name' => 'Men Sneakers White 42', 'name_ar' => 'حذاء رياضي رجالي ابيض 42', 'category_code' => 'CLO-FOOT-MEN', 'cost_price' => 80, 'sale_price' => 159, 'size' => '42', 'color' => 'White', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-MSH-SANDAL-BR-41', 'name' => 'Men Sandals Brown 41', 'name_ar' => 'صندل رجالي بني 41', 'category_code' => 'CLO-FOOT-MEN', 'cost_price' => 50, 'sale_price' => 99, 'size' => '41', 'color' => 'Brown', 'material' => 'Leather', 'low_stock_threshold' => 5],

            // Women Shoes
            ['sku' => 'CLO-WSH-HEELS-BK-38', 'name' => 'Women Heels Black 38', 'name_ar' => 'كعب نسائي اسود 38', 'category_code' => 'CLO-FOOT-WMN', 'cost_price' => 100, 'sale_price' => 199, 'size' => '38', 'color' => 'Black', 'low_stock_threshold' => 4],
            ['sku' => 'CLO-WSH-FLAT-BG-37', 'name' => 'Women Flats Beige 37', 'name_ar' => 'حذاء نسائي فلات بيج 37', 'category_code' => 'CLO-FOOT-WMN', 'cost_price' => 60, 'sale_price' => 119, 'size' => '37', 'color' => 'Beige', 'low_stock_threshold' => 5],
            ['sku' => 'CLO-WSH-SNEAK-PK-38', 'name' => 'Women Sneakers Pink 38', 'name_ar' => 'حذاء رياضي نسائي وردي 38', 'category_code' => 'CLO-FOOT-WMN', 'cost_price' => 70, 'sale_price' => 139, 'size' => '38', 'color' => 'Pink', 'low_stock_threshold' => 5],

            // Accessories
            ['sku' => 'CLO-ACC-BELT-BK', 'name' => 'Men Leather Belt Black', 'name_ar' => 'حزام جلد رجالي اسود', 'category_code' => 'CLO-ACCESS', 'cost_price' => 30, 'sale_price' => 65, 'color' => 'Black', 'material' => 'Leather', 'low_stock_threshold' => 10],
            ['sku' => 'CLO-ACC-BELT-BR', 'name' => 'Men Leather Belt Brown', 'name_ar' => 'حزام جلد رجالي بني', 'category_code' => 'CLO-ACCESS', 'cost_price' => 30, 'sale_price' => 65, 'color' => 'Brown', 'material' => 'Leather', 'low_stock_threshold' => 10],
            ['sku' => 'CLO-ACC-SHEMAGH-WH', 'name' => 'Shemagh White Red', 'name_ar' => 'شماغ ابيض احمر', 'category_code' => 'CLO-ACCESS', 'cost_price' => 25, 'sale_price' => 55, 'color' => 'White/Red', 'low_stock_threshold' => 15],
            ['sku' => 'CLO-ACC-GHUTRA-WH', 'name' => 'Ghutra White', 'name_ar' => 'غترة بيضاء', 'category_code' => 'CLO-ACCESS', 'cost_price' => 20, 'sale_price' => 45, 'color' => 'White', 'low_stock_threshold' => 15],
            ['sku' => 'CLO-ACC-WALLET-BK', 'name' => 'Men Leather Wallet Black', 'name_ar' => 'محفظة جلد رجالي سوداء', 'category_code' => 'CLO-ACCESS', 'cost_price' => 35, 'sale_price' => 79, 'color' => 'Black', 'material' => 'Leather', 'low_stock_threshold' => 8],
            ['sku' => 'CLO-ACC-SCARF-MIX', 'name' => 'Women Scarf Mixed Colors', 'name_ar' => 'وشاح نسائي الوان مختلطة', 'category_code' => 'CLO-ACCESS', 'cost_price' => 25, 'sale_price' => 55, 'color' => 'Mixed', 'material' => 'Silk', 'low_stock_threshold' => 10],
            ['sku' => 'CLO-ACC-BAG-WMN-BK', 'name' => 'Women Handbag Black', 'name_ar' => 'حقيبة يد نسائية سوداء', 'category_code' => 'CLO-ACCESS', 'cost_price' => 80, 'sale_price' => 169, 'color' => 'Black', 'material' => 'Faux Leather', 'low_stock_threshold' => 5],

            // Underwear & Socks
            ['sku' => 'CLO-UND-BOXER-3PK', 'name' => 'Men Boxers 3-Pack', 'name_ar' => 'بوكسر رجالي 3 قطع', 'category_code' => 'CLO-UNDER', 'cost_price' => 30, 'sale_price' => 59, 'low_stock_threshold' => 12],
            ['sku' => 'CLO-UND-UNDERSHIRT-3', 'name' => 'Men Undershirt White 3-Pack', 'name_ar' => 'فانيلة رجالي بيضاء 3 قطع', 'category_code' => 'CLO-UNDER', 'cost_price' => 25, 'sale_price' => 49, 'color' => 'White', 'low_stock_threshold' => 12],
            ['sku' => 'CLO-UND-SOCKS-6PK', 'name' => 'Men Socks 6-Pack Black', 'name_ar' => 'جوارب رجالي 6 قطع اسود', 'category_code' => 'CLO-UNDER', 'cost_price' => 20, 'sale_price' => 39, 'color' => 'Black', 'low_stock_threshold' => 15],
            ['sku' => 'CLO-UND-SOCKS-WH-6', 'name' => 'Men Socks 6-Pack White', 'name_ar' => 'جوارب رجالي 6 قطع ابيض', 'category_code' => 'CLO-UNDER', 'cost_price' => 20, 'sale_price' => 39, 'color' => 'White', 'low_stock_threshold' => 15],
        ];
    }
}
