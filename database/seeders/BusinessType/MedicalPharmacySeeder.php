<?php

namespace Database\Seeders\BusinessType;

class MedicalPharmacySeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Medical & Pharmacy',
            'name_ar' => 'صيدلية ومستلزمات طبية',
            'slug' => 'medical-pharmacy',
            'icon' => 'MedicineBoxOutlined',
            'description' => 'Medicines, medical equipment, first aid, and personal care products',
            'default_attributes' => [
                'product_attributes' => ['expiry_date', 'batch_number', 'prescription_required', 'dosage', 'manufacturer'],
            ],
            'tax_config' => [
                'default_tax_rate' => 0,
                'tax_inclusive' => true,
            ],
            'receipt_config' => [
                'show_expiry' => true,
                'show_batch' => true,
                'show_prescription_warning' => true,
            ],
            'settings' => [
                'track_expiry' => true,
                'require_prescription' => true,
                'fifo_inventory' => true,
            ],
            'is_active' => true,
            'sort_order' => 2,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'MED-RX', 'name' => 'Prescription Medicines', 'name_ar' => 'أدوية بوصفة طبية'],
            ['code' => 'MED-OTC', 'name' => 'Over-the-Counter', 'name_ar' => 'أدوية بدون وصفة'],
            ['code' => 'MED-PAIN', 'name' => 'Pain Relief', 'name_ar' => 'مسكنات الألم', 'parent_code' => 'MED-OTC'],
            ['code' => 'MED-COLD', 'name' => 'Cold & Flu', 'name_ar' => 'البرد والانفلونزا', 'parent_code' => 'MED-OTC'],
            ['code' => 'MED-DIGEST', 'name' => 'Digestive Health', 'name_ar' => 'صحة الجهاز الهضمي', 'parent_code' => 'MED-OTC'],
            ['code' => 'MED-ALLERGY', 'name' => 'Allergy & Sinus', 'name_ar' => 'الحساسية والجيوب', 'parent_code' => 'MED-OTC'],
            ['code' => 'MED-VIT', 'name' => 'Vitamins & Supplements', 'name_ar' => 'الفيتامينات والمكملات'],
            ['code' => 'MED-BABY', 'name' => 'Baby Care', 'name_ar' => 'العناية بالأطفال'],
            ['code' => 'MED-SKIN', 'name' => 'Skin Care', 'name_ar' => 'العناية بالبشرة'],
            ['code' => 'MED-HAIR', 'name' => 'Hair Care', 'name_ar' => 'العناية بالشعر'],
            ['code' => 'MED-ORAL', 'name' => 'Oral Care', 'name_ar' => 'العناية بالفم'],
            ['code' => 'MED-EQUIP', 'name' => 'Medical Equipment', 'name_ar' => 'الأجهزة الطبية'],
            ['code' => 'MED-FIRST', 'name' => 'First Aid', 'name_ar' => 'الإسعافات الأولية'],
            ['code' => 'MED-DIAB', 'name' => 'Diabetes Care', 'name_ar' => 'العناية بالسكري'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Pain Relief
            ['sku' => 'MED-PANADOL-500', 'name' => 'Panadol Extra 500mg (24 tablets)', 'name_ar' => 'بنادول اكسترا 500 ملجم (24 حبة)', 'category_code' => 'MED-PAIN', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],
            ['sku' => 'MED-ADVIL-200', 'name' => 'Advil Ibuprofen 200mg (20 tablets)', 'name_ar' => 'ادفيل ايبوبروفين 200 ملجم (20 حبة)', 'category_code' => 'MED-PAIN', 'cost_price' => 10, 'sale_price' => 18, 'low_stock_threshold' => 20],
            ['sku' => 'MED-ASPIRIN-100', 'name' => 'Aspirin 100mg (30 tablets)', 'name_ar' => 'أسبرين 100 ملجم (30 حبة)', 'category_code' => 'MED-PAIN', 'cost_price' => 5, 'sale_price' => 12, 'low_stock_threshold' => 25],
            ['sku' => 'MED-VOLT-GEL', 'name' => 'Voltaren Gel 50g', 'name_ar' => 'فولتارين جل 50 جرام', 'category_code' => 'MED-PAIN', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 15],
            ['sku' => 'MED-TYLENOL-650', 'name' => 'Tylenol Extra Strength 650mg (50 tablets)', 'name_ar' => 'تايلنول اكسترا 650 ملجم (50 حبة)', 'category_code' => 'MED-PAIN', 'cost_price' => 20, 'sale_price' => 35, 'low_stock_threshold' => 15],

            // Cold & Flu
            ['sku' => 'MED-PANADOL-CF', 'name' => 'Panadol Cold & Flu (24 tablets)', 'name_ar' => 'بنادول كولد اند فلو (24 حبة)', 'category_code' => 'MED-COLD', 'cost_price' => 12, 'sale_price' => 22, 'low_stock_threshold' => 20],
            ['sku' => 'MED-STREPSILS', 'name' => 'Strepsils Honey & Lemon (24 lozenges)', 'name_ar' => 'ستربسلز عسل وليمون (24 حبة)', 'category_code' => 'MED-COLD', 'cost_price' => 8, 'sale_price' => 16, 'low_stock_threshold' => 25],
            ['sku' => 'MED-OTRIVIN-SP', 'name' => 'Otrivin Nasal Spray 10ml', 'name_ar' => 'اوتريفين بخاخ أنف 10 مل', 'category_code' => 'MED-COLD', 'cost_price' => 10, 'sale_price' => 19, 'low_stock_threshold' => 20],
            ['sku' => 'MED-VICKS-VAP', 'name' => 'Vicks VapoRub 50g', 'name_ar' => 'فيكس فابوراب 50 جرام', 'category_code' => 'MED-COLD', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],
            ['sku' => 'MED-COUGH-SYR', 'name' => 'Prospan Cough Syrup 100ml', 'name_ar' => 'بروسبان شراب السعال 100 مل', 'category_code' => 'MED-COLD', 'cost_price' => 18, 'sale_price' => 32, 'low_stock_threshold' => 15],

            // Digestive Health
            ['sku' => 'MED-GAVISCON', 'name' => 'Gaviscon Double Action 150ml', 'name_ar' => 'جافيسكون دبل اكشن 150 مل', 'category_code' => 'MED-DIGEST', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 15],
            ['sku' => 'MED-NEXIUM-20', 'name' => 'Nexium 20mg (14 tablets)', 'name_ar' => 'نكسيوم 20 ملجم (14 حبة)', 'category_code' => 'MED-DIGEST', 'cost_price' => 25, 'sale_price' => 45, 'low_stock_threshold' => 10],
            ['sku' => 'MED-IMODIUM', 'name' => 'Imodium 2mg (6 capsules)', 'name_ar' => 'ايموديوم 2 ملجم (6 كبسولات)', 'category_code' => 'MED-DIGEST', 'cost_price' => 10, 'sale_price' => 18, 'low_stock_threshold' => 15],
            ['sku' => 'MED-DULCO-10', 'name' => 'Dulcolax 10mg (10 tablets)', 'name_ar' => 'دولكولاكس 10 ملجم (10 حبات)', 'category_code' => 'MED-DIGEST', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],

            // Allergy
            ['sku' => 'MED-ZYRTEC-10', 'name' => 'Zyrtec 10mg (20 tablets)', 'name_ar' => 'زيرتك 10 ملجم (20 حبة)', 'category_code' => 'MED-ALLERGY', 'cost_price' => 18, 'sale_price' => 32, 'low_stock_threshold' => 15],
            ['sku' => 'MED-CLARITIN', 'name' => 'Claritin 10mg (10 tablets)', 'name_ar' => 'كلاريتين 10 ملجم (10 حبات)', 'category_code' => 'MED-ALLERGY', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 15],
            ['sku' => 'MED-TELFAST-180', 'name' => 'Telfast 180mg (15 tablets)', 'name_ar' => 'تلفاست 180 ملجم (15 حبة)', 'category_code' => 'MED-ALLERGY', 'cost_price' => 25, 'sale_price' => 42, 'low_stock_threshold' => 12],

            // Vitamins & Supplements
            ['sku' => 'MED-VITC-1000', 'name' => 'Vitamin C 1000mg (30 tablets)', 'name_ar' => 'فيتامين سي 1000 ملجم (30 حبة)', 'category_code' => 'MED-VIT', 'cost_price' => 12, 'sale_price' => 25, 'low_stock_threshold' => 20],
            ['sku' => 'MED-VITD-5000', 'name' => 'Vitamin D3 5000 IU (60 softgels)', 'name_ar' => 'فيتامين د3 5000 وحدة (60 كبسولة)', 'category_code' => 'MED-VIT', 'cost_price' => 20, 'sale_price' => 38, 'low_stock_threshold' => 15],
            ['sku' => 'MED-OMEGA3', 'name' => 'Omega-3 Fish Oil 1000mg (60 softgels)', 'name_ar' => 'أوميجا 3 زيت السمك 1000 ملجم (60 كبسولة)', 'category_code' => 'MED-VIT', 'cost_price' => 25, 'sale_price' => 45, 'low_stock_threshold' => 15],
            ['sku' => 'MED-MULTI-MEN', 'name' => 'Centrum Men Multivitamin (30 tablets)', 'name_ar' => 'سنتروم للرجال (30 حبة)', 'category_code' => 'MED-VIT', 'cost_price' => 30, 'sale_price' => 55, 'low_stock_threshold' => 12],
            ['sku' => 'MED-MULTI-WMN', 'name' => 'Centrum Women Multivitamin (30 tablets)', 'name_ar' => 'سنتروم للنساء (30 حبة)', 'category_code' => 'MED-VIT', 'cost_price' => 30, 'sale_price' => 55, 'low_stock_threshold' => 12],
            ['sku' => 'MED-IRON-65', 'name' => 'Iron Supplement 65mg (30 tablets)', 'name_ar' => 'مكمل الحديد 65 ملجم (30 حبة)', 'category_code' => 'MED-VIT', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 15],
            ['sku' => 'MED-CALC-600', 'name' => 'Calcium + Vitamin D 600mg (60 tablets)', 'name_ar' => 'كالسيوم + فيتامين د 600 ملجم (60 حبة)', 'category_code' => 'MED-VIT', 'cost_price' => 18, 'sale_price' => 35, 'low_stock_threshold' => 15],

            // Baby Care
            ['sku' => 'MED-BABY-PAMP-M', 'name' => 'Pampers Premium Medium (52 diapers)', 'name_ar' => 'بامبرز بريميوم ميديوم (52 حفاضة)', 'category_code' => 'MED-BABY', 'cost_price' => 45, 'sale_price' => 75, 'low_stock_threshold' => 10],
            ['sku' => 'MED-BABY-PAMP-L', 'name' => 'Pampers Premium Large (44 diapers)', 'name_ar' => 'بامبرز بريميوم لارج (44 حفاضة)', 'category_code' => 'MED-BABY', 'cost_price' => 45, 'sale_price' => 75, 'low_stock_threshold' => 10],
            ['sku' => 'MED-BABY-FORM', 'name' => 'S-26 Gold Stage 1 (400g)', 'name_ar' => 'اس-26 جولد مرحلة 1 (400 جرام)', 'category_code' => 'MED-BABY', 'cost_price' => 35, 'sale_price' => 55, 'low_stock_threshold' => 8],
            ['sku' => 'MED-BABY-WIPES', 'name' => 'Johnson Baby Wipes (72 wipes)', 'name_ar' => 'مناديل جونسون للأطفال (72 منديل)', 'category_code' => 'MED-BABY', 'cost_price' => 10, 'sale_price' => 18, 'low_stock_threshold' => 15],
            ['sku' => 'MED-BABY-SHAM', 'name' => 'Johnson Baby Shampoo 500ml', 'name_ar' => 'شامبو جونسون للأطفال 500 مل', 'category_code' => 'MED-BABY', 'cost_price' => 12, 'sale_price' => 22, 'low_stock_threshold' => 12],

            // Skin Care
            ['sku' => 'MED-NIVEA-CREAM', 'name' => 'Nivea Cream 150ml', 'name_ar' => 'كريم نيفيا 150 مل', 'category_code' => 'MED-SKIN', 'cost_price' => 10, 'sale_price' => 18, 'low_stock_threshold' => 15],
            ['sku' => 'MED-VASELINE', 'name' => 'Vaseline Original 250ml', 'name_ar' => 'فازلين اصلي 250 مل', 'category_code' => 'MED-SKIN', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 15],
            ['sku' => 'MED-SUN-SPF50', 'name' => 'Neutrogena Sunscreen SPF50 88ml', 'name_ar' => 'واقي شمس نيوتروجينا SPF50 88 مل', 'category_code' => 'MED-SKIN', 'cost_price' => 25, 'sale_price' => 45, 'low_stock_threshold' => 10],
            ['sku' => 'MED-CETAPHIL', 'name' => 'Cetaphil Gentle Cleanser 236ml', 'name_ar' => 'سيتافيل منظف لطيف 236 مل', 'category_code' => 'MED-SKIN', 'cost_price' => 28, 'sale_price' => 48, 'low_stock_threshold' => 10],

            // Oral Care
            ['sku' => 'MED-SENSODYNE', 'name' => 'Sensodyne Toothpaste 100ml', 'name_ar' => 'معجون سنسوداين 100 مل', 'category_code' => 'MED-ORAL', 'cost_price' => 12, 'sale_price' => 22, 'low_stock_threshold' => 15],
            ['sku' => 'MED-COLGATE-TW', 'name' => 'Colgate Total Whitening 100ml', 'name_ar' => 'كولجيت توتال وايتنينج 100 مل', 'category_code' => 'MED-ORAL', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],
            ['sku' => 'MED-LISTERINE', 'name' => 'Listerine Cool Mint 500ml', 'name_ar' => 'ليسترين كول مينت 500 مل', 'category_code' => 'MED-ORAL', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 12],
            ['sku' => 'MED-ORALB-TB', 'name' => 'Oral-B Toothbrush Medium', 'name_ar' => 'فرشاة اورال بي وسط', 'category_code' => 'MED-ORAL', 'cost_price' => 5, 'sale_price' => 12, 'low_stock_threshold' => 25],

            // Medical Equipment
            ['sku' => 'MED-BP-MONITOR', 'name' => 'Omron Blood Pressure Monitor', 'name_ar' => 'جهاز قياس ضغط الدم اومرون', 'category_code' => 'MED-EQUIP', 'cost_price' => 120, 'sale_price' => 189, 'warranty_months' => 24, 'low_stock_threshold' => 3],
            ['sku' => 'MED-THERMO-DIG', 'name' => 'Digital Thermometer', 'name_ar' => 'ميزان حرارة رقمي', 'category_code' => 'MED-EQUIP', 'cost_price' => 15, 'sale_price' => 29, 'warranty_months' => 12, 'low_stock_threshold' => 10],
            ['sku' => 'MED-PULSE-OX', 'name' => 'Fingertip Pulse Oximeter', 'name_ar' => 'جهاز قياس الاكسجين', 'category_code' => 'MED-EQUIP', 'cost_price' => 30, 'sale_price' => 55, 'warranty_months' => 12, 'low_stock_threshold' => 8],
            ['sku' => 'MED-NEBULIZER', 'name' => 'Compact Nebulizer Machine', 'name_ar' => 'جهاز البخار الكمام', 'category_code' => 'MED-EQUIP', 'cost_price' => 80, 'sale_price' => 145, 'warranty_months' => 12, 'low_stock_threshold' => 5],

            // First Aid
            ['sku' => 'MED-BAND-ASST', 'name' => 'Band-Aid Assorted (100 pieces)', 'name_ar' => 'لصقات باند ايد متنوعة (100 قطعة)', 'category_code' => 'MED-FIRST', 'cost_price' => 12, 'sale_price' => 22, 'low_stock_threshold' => 15],
            ['sku' => 'MED-DETTOL-100', 'name' => 'Dettol Antiseptic 100ml', 'name_ar' => 'ديتول مطهر 100 مل', 'category_code' => 'MED-FIRST', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],
            ['sku' => 'MED-COTTON-50', 'name' => 'Medical Cotton Roll 50g', 'name_ar' => 'قطن طبي 50 جرام', 'category_code' => 'MED-FIRST', 'cost_price' => 5, 'sale_price' => 10, 'low_stock_threshold' => 25],
            ['sku' => 'MED-BANDAGE-5', 'name' => 'Elastic Bandage 5cm x 4m', 'name_ar' => 'رباط ضاغط 5 سم × 4 متر', 'category_code' => 'MED-FIRST', 'cost_price' => 4, 'sale_price' => 8, 'low_stock_threshold' => 30],
            ['sku' => 'MED-FA-KIT', 'name' => 'First Aid Kit Complete', 'name_ar' => 'حقيبة إسعافات أولية كاملة', 'category_code' => 'MED-FIRST', 'cost_price' => 35, 'sale_price' => 65, 'low_stock_threshold' => 8],

            // Diabetes Care
            ['sku' => 'MED-GLUCO-METER', 'name' => 'Accu-Chek Glucose Meter', 'name_ar' => 'جهاز قياس السكر اكيو تشيك', 'category_code' => 'MED-DIAB', 'cost_price' => 80, 'sale_price' => 149, 'warranty_months' => 24, 'low_stock_threshold' => 5],
            ['sku' => 'MED-GLUCO-STRIP', 'name' => 'Accu-Chek Test Strips (50 strips)', 'name_ar' => 'شرائط اكيو تشيك (50 شريحة)', 'category_code' => 'MED-DIAB', 'cost_price' => 45, 'sale_price' => 75, 'low_stock_threshold' => 10],
            ['sku' => 'MED-INSULIN-SYR', 'name' => 'Insulin Syringes (100 pieces)', 'name_ar' => 'حقن انسولين (100 قطعة)', 'category_code' => 'MED-DIAB', 'cost_price' => 25, 'sale_price' => 45, 'low_stock_threshold' => 8],
            ['sku' => 'MED-LANCETS', 'name' => 'Lancets (100 pieces)', 'name_ar' => 'إبر الوخز (100 قطعة)', 'category_code' => 'MED-DIAB', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 10],
        ];
    }
}
