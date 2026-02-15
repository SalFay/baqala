<?php

namespace Database\Seeders\BusinessType;

class SanitaryHardwareSeeder extends BaseBusinessTypeSeeder
{
    protected function getBusinessTypeConfig(): array
    {
        return [
            'name' => 'Sanitary & Hardware',
            'name_ar' => 'أدوات صحية وعدد',
            'slug' => 'sanitary-hardware',
            'icon' => 'ToolOutlined',
            'description' => 'Plumbing supplies, fixtures, tools, and building materials',
            'default_attributes' => [
                'product_attributes' => ['dimensions', 'material', 'finish', 'size', 'color', 'brand'],
            ],
            'tax_config' => [
                'default_tax_rate' => 15,
                'tax_inclusive' => false,
            ],
            'receipt_config' => [
                'show_dimensions' => true,
                'show_material' => true,
            ],
            'settings' => [
                'unit_based_pricing' => true,
                'bulk_discount' => true,
            ],
            'is_active' => true,
            'sort_order' => 3,
        ];
    }

    protected function getCategories(): array
    {
        return [
            ['code' => 'SAN-PLUMB', 'name' => 'Plumbing', 'name_ar' => 'السباكة'],
            ['code' => 'SAN-PIPES', 'name' => 'Pipes & Fittings', 'name_ar' => 'المواسير والوصلات', 'parent_code' => 'SAN-PLUMB'],
            ['code' => 'SAN-VALVES', 'name' => 'Valves & Taps', 'name_ar' => 'المحابس والصنابير', 'parent_code' => 'SAN-PLUMB'],
            ['code' => 'SAN-FIXTURES', 'name' => 'Bathroom Fixtures', 'name_ar' => 'تجهيزات الحمام'],
            ['code' => 'SAN-TOILETS', 'name' => 'Toilets & Bidets', 'name_ar' => 'المراحيض والشطافات', 'parent_code' => 'SAN-FIXTURES'],
            ['code' => 'SAN-SINKS', 'name' => 'Sinks & Basins', 'name_ar' => 'المغاسل والأحواض', 'parent_code' => 'SAN-FIXTURES'],
            ['code' => 'SAN-SHOWER', 'name' => 'Showers & Tubs', 'name_ar' => 'الدش والبانيو', 'parent_code' => 'SAN-FIXTURES'],
            ['code' => 'SAN-TOOLS', 'name' => 'Hand Tools', 'name_ar' => 'العدد اليدوية'],
            ['code' => 'SAN-POWER', 'name' => 'Power Tools', 'name_ar' => 'العدد الكهربائية'],
            ['code' => 'SAN-ELEC', 'name' => 'Electrical', 'name_ar' => 'الكهرباء'],
            ['code' => 'SAN-PAINT', 'name' => 'Paint & Supplies', 'name_ar' => 'الدهانات والمستلزمات'],
            ['code' => 'SAN-HARDWARE', 'name' => 'General Hardware', 'name_ar' => 'عدد عامة'],
            ['code' => 'SAN-SAFETY', 'name' => 'Safety Equipment', 'name_ar' => 'معدات السلامة'],
        ];
    }

    protected function getProducts(): array
    {
        return [
            // Pipes & Fittings
            ['sku' => 'SAN-PIPE-PVC-1', 'name' => 'PVC Pipe 1 inch (3m)', 'name_ar' => 'ماسورة بلاستيك 1 انش (3 متر)', 'category_code' => 'SAN-PIPES', 'cost_price' => 8, 'sale_price' => 15, 'material' => 'PVC', 'dimensions' => '1 inch x 3m', 'low_stock_threshold' => 25],
            ['sku' => 'SAN-PIPE-PVC-2', 'name' => 'PVC Pipe 2 inch (3m)', 'name_ar' => 'ماسورة بلاستيك 2 انش (3 متر)', 'category_code' => 'SAN-PIPES', 'cost_price' => 15, 'sale_price' => 28, 'material' => 'PVC', 'dimensions' => '2 inch x 3m', 'low_stock_threshold' => 20],
            ['sku' => 'SAN-PIPE-PPR-20', 'name' => 'PPR Pipe 20mm (4m)', 'name_ar' => 'ماسورة PPR 20 مم (4 متر)', 'category_code' => 'SAN-PIPES', 'cost_price' => 12, 'sale_price' => 22, 'material' => 'PPR', 'dimensions' => '20mm x 4m', 'low_stock_threshold' => 25],
            ['sku' => 'SAN-ELBOW-PVC-1', 'name' => 'PVC Elbow 90° 1 inch', 'name_ar' => 'كوع بلاستيك 90 درجة 1 انش', 'category_code' => 'SAN-PIPES', 'cost_price' => 1.5, 'sale_price' => 3, 'material' => 'PVC', 'low_stock_threshold' => 50],
            ['sku' => 'SAN-TEE-PVC-1', 'name' => 'PVC Tee 1 inch', 'name_ar' => 'تي بلاستيك 1 انش', 'category_code' => 'SAN-PIPES', 'cost_price' => 2, 'sale_price' => 4, 'material' => 'PVC', 'low_stock_threshold' => 50],
            ['sku' => 'SAN-UNION-PPR-20', 'name' => 'PPR Union 20mm', 'name_ar' => 'يونيون PPR 20 مم', 'category_code' => 'SAN-PIPES', 'cost_price' => 5, 'sale_price' => 10, 'material' => 'PPR', 'low_stock_threshold' => 30],
            ['sku' => 'SAN-FLEX-HOSE-50', 'name' => 'Flexible Hose 50cm Stainless', 'name_ar' => 'خرطوم مرن 50 سم ستانلس', 'category_code' => 'SAN-PIPES', 'cost_price' => 8, 'sale_price' => 15, 'material' => 'Stainless Steel', 'low_stock_threshold' => 25],

            // Valves & Taps
            ['sku' => 'SAN-VALVE-GATE-1', 'name' => 'Gate Valve 1 inch Brass', 'name_ar' => 'محبس بوابة 1 انش نحاس', 'category_code' => 'SAN-VALVES', 'cost_price' => 25, 'sale_price' => 45, 'material' => 'Brass', 'low_stock_threshold' => 15],
            ['sku' => 'SAN-VALVE-BALL-1', 'name' => 'Ball Valve 1 inch', 'name_ar' => 'محبس كروي 1 انش', 'category_code' => 'SAN-VALVES', 'cost_price' => 15, 'sale_price' => 28, 'material' => 'Brass', 'low_stock_threshold' => 20],
            ['sku' => 'SAN-STOPCOCK', 'name' => 'Angle Stop Cock Chrome', 'name_ar' => 'محبس زاوية كروم', 'category_code' => 'SAN-VALVES', 'cost_price' => 12, 'sale_price' => 22, 'finish' => 'Chrome', 'low_stock_threshold' => 20],
            ['sku' => 'SAN-MIXER-BASIN', 'name' => 'Basin Mixer Tap Chrome', 'name_ar' => 'خلاط مغسلة كروم', 'category_code' => 'SAN-VALVES', 'cost_price' => 80, 'sale_price' => 149, 'finish' => 'Chrome', 'warranty_months' => 24, 'low_stock_threshold' => 8],
            ['sku' => 'SAN-MIXER-KITCHEN', 'name' => 'Kitchen Sink Mixer Tap', 'name_ar' => 'خلاط مطبخ', 'category_code' => 'SAN-VALVES', 'cost_price' => 100, 'sale_price' => 179, 'finish' => 'Chrome', 'warranty_months' => 24, 'low_stock_threshold' => 8],

            // Toilets & Bidets
            ['sku' => 'SAN-TOILET-WC', 'name' => 'Western Toilet Complete Set', 'name_ar' => 'مرحاض افرنجي طقم كامل', 'category_code' => 'SAN-TOILETS', 'cost_price' => 250, 'sale_price' => 450, 'finish' => 'White', 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'SAN-TOILET-SEAT', 'name' => 'Toilet Seat Soft Close', 'name_ar' => 'قاعدة مرحاض سوفت كلوز', 'category_code' => 'SAN-TOILETS', 'cost_price' => 40, 'sale_price' => 75, 'low_stock_threshold' => 10],
            ['sku' => 'SAN-BIDET-SPRAY', 'name' => 'Bidet Spray Set Chrome', 'name_ar' => 'شطاف كروم طقم', 'category_code' => 'SAN-TOILETS', 'cost_price' => 25, 'sale_price' => 49, 'finish' => 'Chrome', 'low_stock_threshold' => 15],
            ['sku' => 'SAN-FLUSH-VALVE', 'name' => 'Concealed Flush Valve', 'name_ar' => 'سيفون مخفي', 'category_code' => 'SAN-TOILETS', 'cost_price' => 80, 'sale_price' => 145, 'low_stock_threshold' => 8],

            // Sinks & Basins
            ['sku' => 'SAN-BASIN-PEDESTAL', 'name' => 'Pedestal Basin White', 'name_ar' => 'مغسلة بقاعدة ابيض', 'category_code' => 'SAN-SINKS', 'cost_price' => 120, 'sale_price' => 220, 'finish' => 'White', 'low_stock_threshold' => 5],
            ['sku' => 'SAN-BASIN-WALL', 'name' => 'Wall Hung Basin 50cm', 'name_ar' => 'مغسلة معلقة 50 سم', 'category_code' => 'SAN-SINKS', 'cost_price' => 80, 'sale_price' => 149, 'dimensions' => '50cm', 'low_stock_threshold' => 6],
            ['sku' => 'SAN-SINK-SINGLE', 'name' => 'Kitchen Sink Single Bowl SS', 'name_ar' => 'حوض مطبخ فردي ستانلس', 'category_code' => 'SAN-SINKS', 'cost_price' => 100, 'sale_price' => 189, 'material' => 'Stainless Steel', 'low_stock_threshold' => 5],
            ['sku' => 'SAN-SINK-DOUBLE', 'name' => 'Kitchen Sink Double Bowl SS', 'name_ar' => 'حوض مطبخ مزدوج ستانلس', 'category_code' => 'SAN-SINKS', 'cost_price' => 150, 'sale_price' => 279, 'material' => 'Stainless Steel', 'low_stock_threshold' => 4],

            // Showers
            ['sku' => 'SAN-SHOWER-SET', 'name' => 'Shower Set with Rail', 'name_ar' => 'طقم دش مع قضيب', 'category_code' => 'SAN-SHOWER', 'cost_price' => 80, 'sale_price' => 149, 'finish' => 'Chrome', 'warranty_months' => 12, 'low_stock_threshold' => 8],
            ['sku' => 'SAN-SHOWER-HEAD', 'name' => 'Rain Shower Head 8 inch', 'name_ar' => 'رأس دش مطري 8 انش', 'category_code' => 'SAN-SHOWER', 'cost_price' => 45, 'sale_price' => 85, 'dimensions' => '8 inch', 'finish' => 'Chrome', 'low_stock_threshold' => 10],
            ['sku' => 'SAN-SHOWER-MIXER', 'name' => 'Shower Mixer Concealed', 'name_ar' => 'خلاط دش مخفي', 'category_code' => 'SAN-SHOWER', 'cost_price' => 120, 'sale_price' => 220, 'finish' => 'Chrome', 'warranty_months' => 24, 'low_stock_threshold' => 6],

            // Hand Tools
            ['sku' => 'SAN-WRENCH-ADJ-10', 'name' => 'Adjustable Wrench 10 inch', 'name_ar' => 'مفتاح انجليزي 10 انش', 'category_code' => 'SAN-TOOLS', 'cost_price' => 18, 'sale_price' => 35, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-WRENCH-PIPE-14', 'name' => 'Pipe Wrench 14 inch', 'name_ar' => 'مفتاح انابيب 14 انش', 'category_code' => 'SAN-TOOLS', 'cost_price' => 25, 'sale_price' => 48, 'low_stock_threshold' => 10],
            ['sku' => 'SAN-PLIER-COMBO', 'name' => 'Combination Pliers 8 inch', 'name_ar' => 'زردية كومبنيشن 8 انش', 'category_code' => 'SAN-TOOLS', 'cost_price' => 12, 'sale_price' => 25, 'low_stock_threshold' => 15],
            ['sku' => 'SAN-SCREWDRIVER-SET', 'name' => 'Screwdriver Set 8 pieces', 'name_ar' => 'طقم مفكات 8 قطع', 'category_code' => 'SAN-TOOLS', 'cost_price' => 20, 'sale_price' => 39, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-HAMMER-16', 'name' => 'Claw Hammer 16oz', 'name_ar' => 'مطرقة مسمارية 16 اونص', 'category_code' => 'SAN-TOOLS', 'cost_price' => 15, 'sale_price' => 29, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-TAPE-5M', 'name' => 'Measuring Tape 5m', 'name_ar' => 'شريط قياس 5 متر', 'category_code' => 'SAN-TOOLS', 'cost_price' => 5, 'sale_price' => 12, 'low_stock_threshold' => 20],
            ['sku' => 'SAN-LEVEL-60', 'name' => 'Spirit Level 60cm', 'name_ar' => 'ميزان مياه 60 سم', 'category_code' => 'SAN-TOOLS', 'cost_price' => 15, 'sale_price' => 29, 'low_stock_threshold' => 10],

            // Power Tools
            ['sku' => 'SAN-DRILL-CORD', 'name' => 'Corded Drill 13mm 750W', 'name_ar' => 'دريل كهربائي 13 مم 750 واط', 'category_code' => 'SAN-POWER', 'cost_price' => 80, 'sale_price' => 149, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'SAN-DRILL-CORDLESS', 'name' => 'Cordless Drill 18V', 'name_ar' => 'دريل لاسلكي 18 فولت', 'category_code' => 'SAN-POWER', 'cost_price' => 150, 'sale_price' => 279, 'warranty_months' => 12, 'low_stock_threshold' => 4],
            ['sku' => 'SAN-GRINDER-4', 'name' => 'Angle Grinder 4 inch 850W', 'name_ar' => 'صاروخ 4 انش 850 واط', 'category_code' => 'SAN-POWER', 'cost_price' => 70, 'sale_price' => 129, 'warranty_months' => 12, 'low_stock_threshold' => 5],
            ['sku' => 'SAN-JIGSAW', 'name' => 'Jigsaw 600W', 'name_ar' => 'منشار كهربائي 600 واط', 'category_code' => 'SAN-POWER', 'cost_price' => 90, 'sale_price' => 169, 'warranty_months' => 12, 'low_stock_threshold' => 4],

            // Electrical
            ['sku' => 'SAN-WIRE-1.5', 'name' => 'Electrical Wire 1.5mm (100m)', 'name_ar' => 'سلك كهرباء 1.5 مم (100 متر)', 'category_code' => 'SAN-ELEC', 'cost_price' => 80, 'sale_price' => 145, 'low_stock_threshold' => 8],
            ['sku' => 'SAN-WIRE-2.5', 'name' => 'Electrical Wire 2.5mm (100m)', 'name_ar' => 'سلك كهرباء 2.5 مم (100 متر)', 'category_code' => 'SAN-ELEC', 'cost_price' => 120, 'sale_price' => 220, 'low_stock_threshold' => 6],
            ['sku' => 'SAN-SWITCH-SINGLE', 'name' => 'Light Switch Single White', 'name_ar' => 'مفتاح اضاءة فردي ابيض', 'category_code' => 'SAN-ELEC', 'cost_price' => 5, 'sale_price' => 12, 'low_stock_threshold' => 30],
            ['sku' => 'SAN-SOCKET-DOUBLE', 'name' => 'Double Power Socket', 'name_ar' => 'بلاك مزدوج', 'category_code' => 'SAN-ELEC', 'cost_price' => 8, 'sale_price' => 18, 'low_stock_threshold' => 25],
            ['sku' => 'SAN-BREAKER-32', 'name' => 'Circuit Breaker 32A', 'name_ar' => 'قاطع كهرباء 32 امبير', 'category_code' => 'SAN-ELEC', 'cost_price' => 12, 'sale_price' => 25, 'low_stock_threshold' => 15],

            // Paint & Supplies
            ['sku' => 'SAN-PAINT-WHITE-4', 'name' => 'White Paint Matt 4L', 'name_ar' => 'دهان ابيض مط 4 لتر', 'category_code' => 'SAN-PAINT', 'cost_price' => 35, 'sale_price' => 65, 'low_stock_threshold' => 10],
            ['sku' => 'SAN-PAINT-PRIM-4', 'name' => 'Primer Paint 4L', 'name_ar' => 'دهان تحضيري 4 لتر', 'category_code' => 'SAN-PAINT', 'cost_price' => 30, 'sale_price' => 55, 'low_stock_threshold' => 10],
            ['sku' => 'SAN-BRUSH-4', 'name' => 'Paint Brush 4 inch', 'name_ar' => 'فرشاة دهان 4 انش', 'category_code' => 'SAN-PAINT', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 15],
            ['sku' => 'SAN-ROLLER-9', 'name' => 'Paint Roller 9 inch', 'name_ar' => 'رولة دهان 9 انش', 'category_code' => 'SAN-PAINT', 'cost_price' => 12, 'sale_price' => 25, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-PUTTY-5', 'name' => 'Wall Putty 5kg', 'name_ar' => 'معجون حائط 5 كيلو', 'category_code' => 'SAN-PAINT', 'cost_price' => 15, 'sale_price' => 28, 'low_stock_threshold' => 15],

            // General Hardware
            ['sku' => 'SAN-SCREW-BOX', 'name' => 'Screw Assortment Box', 'name_ar' => 'علبة براغي متنوعة', 'category_code' => 'SAN-HARDWARE', 'cost_price' => 20, 'sale_price' => 39, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-NAIL-3', 'name' => 'Nails 3 inch (1kg)', 'name_ar' => 'مسامير 3 انش (1 كيلو)', 'category_code' => 'SAN-HARDWARE', 'cost_price' => 8, 'sale_price' => 15, 'low_stock_threshold' => 20],
            ['sku' => 'SAN-ANCHOR-SET', 'name' => 'Wall Anchors Set', 'name_ar' => 'طقم فيشر جدار', 'category_code' => 'SAN-HARDWARE', 'cost_price' => 10, 'sale_price' => 20, 'low_stock_threshold' => 20],
            ['sku' => 'SAN-TAPE-TEFLON', 'name' => 'Teflon Tape Roll', 'name_ar' => 'شريط تيفلون', 'category_code' => 'SAN-HARDWARE', 'cost_price' => 2, 'sale_price' => 5, 'low_stock_threshold' => 50],
            ['sku' => 'SAN-SILICONE-CLEAR', 'name' => 'Silicone Sealant Clear', 'name_ar' => 'سيليكون شفاف', 'category_code' => 'SAN-HARDWARE', 'cost_price' => 10, 'sale_price' => 20, 'low_stock_threshold' => 20],

            // Safety Equipment
            ['sku' => 'SAN-GLOVES-WORK', 'name' => 'Work Gloves Heavy Duty', 'name_ar' => 'قفازات عمل ثقيلة', 'category_code' => 'SAN-SAFETY', 'cost_price' => 8, 'sale_price' => 18, 'low_stock_threshold' => 20],
            ['sku' => 'SAN-SAFETY-GLASS', 'name' => 'Safety Glasses Clear', 'name_ar' => 'نظارات حماية شفافة', 'category_code' => 'SAN-SAFETY', 'cost_price' => 5, 'sale_price' => 12, 'low_stock_threshold' => 20],
            ['sku' => 'SAN-HARD-HAT', 'name' => 'Hard Hat Yellow', 'name_ar' => 'خوذة سلامة صفراء', 'category_code' => 'SAN-SAFETY', 'cost_price' => 15, 'sale_price' => 29, 'low_stock_threshold' => 12],
            ['sku' => 'SAN-DUST-MASK', 'name' => 'Dust Mask N95 (10 pcs)', 'name_ar' => 'كمامة غبار N95 (10 قطع)', 'category_code' => 'SAN-SAFETY', 'cost_price' => 12, 'sale_price' => 25, 'low_stock_threshold' => 15],
        ];
    }
}
