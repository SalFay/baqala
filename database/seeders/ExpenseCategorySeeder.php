<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Operating Expenses
            ['code' => 'EXP-RENT', 'name' => 'Rent & Lease', 'name_ar' => 'الإيجار'],
            ['code' => 'EXP-UTIL', 'name' => 'Utilities', 'name_ar' => 'المرافق'],
            ['code' => 'EXP-ELEC', 'name' => 'Electricity', 'name_ar' => 'الكهرباء', 'parent_code' => 'EXP-UTIL'],
            ['code' => 'EXP-WATER', 'name' => 'Water', 'name_ar' => 'المياه', 'parent_code' => 'EXP-UTIL'],
            ['code' => 'EXP-PHONE', 'name' => 'Telephone & Internet', 'name_ar' => 'الهاتف والإنترنت', 'parent_code' => 'EXP-UTIL'],

            // Payroll
            ['code' => 'EXP-SAL', 'name' => 'Salaries & Wages', 'name_ar' => 'الرواتب والأجور'],
            ['code' => 'EXP-BONUS', 'name' => 'Bonuses', 'name_ar' => 'المكافآت', 'parent_code' => 'EXP-SAL'],
            ['code' => 'EXP-GOSI', 'name' => 'GOSI/Insurance', 'name_ar' => 'التأمينات الاجتماعية', 'parent_code' => 'EXP-SAL'],

            // Supplies
            ['code' => 'EXP-SUPP', 'name' => 'Office Supplies', 'name_ar' => 'مستلزمات المكتب'],
            ['code' => 'EXP-CLEAN', 'name' => 'Cleaning Supplies', 'name_ar' => 'مستلزمات التنظيف'],
            ['code' => 'EXP-PACK', 'name' => 'Packaging Materials', 'name_ar' => 'مواد التغليف'],

            // Marketing
            ['code' => 'EXP-MARK', 'name' => 'Marketing & Advertising', 'name_ar' => 'التسويق والإعلان'],
            ['code' => 'EXP-PRINT', 'name' => 'Printing', 'name_ar' => 'الطباعة', 'parent_code' => 'EXP-MARK'],

            // Maintenance
            ['code' => 'EXP-MAINT', 'name' => 'Maintenance & Repairs', 'name_ar' => 'الصيانة والإصلاحات'],
            ['code' => 'EXP-EQUIP', 'name' => 'Equipment Repairs', 'name_ar' => 'صيانة المعدات', 'parent_code' => 'EXP-MAINT'],
            ['code' => 'EXP-BLDG', 'name' => 'Building Maintenance', 'name_ar' => 'صيانة المبنى', 'parent_code' => 'EXP-MAINT'],

            // Transportation
            ['code' => 'EXP-TRANS', 'name' => 'Transportation', 'name_ar' => 'النقل والمواصلات'],
            ['code' => 'EXP-FUEL', 'name' => 'Fuel', 'name_ar' => 'الوقود', 'parent_code' => 'EXP-TRANS'],
            ['code' => 'EXP-VEHICLE', 'name' => 'Vehicle Maintenance', 'name_ar' => 'صيانة المركبات', 'parent_code' => 'EXP-TRANS'],

            // Professional Services
            ['code' => 'EXP-PROF', 'name' => 'Professional Services', 'name_ar' => 'الخدمات المهنية'],
            ['code' => 'EXP-LEGAL', 'name' => 'Legal Fees', 'name_ar' => 'الرسوم القانونية', 'parent_code' => 'EXP-PROF'],
            ['code' => 'EXP-ACCT', 'name' => 'Accounting Fees', 'name_ar' => 'رسوم المحاسبة', 'parent_code' => 'EXP-PROF'],

            // Bank & Fees
            ['code' => 'EXP-BANK', 'name' => 'Bank Charges', 'name_ar' => 'الرسوم البنكية'],
            ['code' => 'EXP-GOV', 'name' => 'Government Fees', 'name_ar' => 'الرسوم الحكومية'],
            ['code' => 'EXP-LIC', 'name' => 'Licenses & Permits', 'name_ar' => 'التراخيص والتصاريح', 'parent_code' => 'EXP-GOV'],

            // Other
            ['code' => 'EXP-MISC', 'name' => 'Miscellaneous', 'name_ar' => 'مصروفات متنوعة'],
            ['code' => 'EXP-HOSP', 'name' => 'Hospitality', 'name_ar' => 'الضيافة'],
            ['code' => 'EXP-TRAIN', 'name' => 'Training & Education', 'name_ar' => 'التدريب والتعليم'],
        ];

        $createdCategories = [];

        foreach ($categories as $index => $categoryData) {
            $parentId = null;

            if (!empty($categoryData['parent_code'])) {
                $parentId = $createdCategories[$categoryData['parent_code']]?->id;
            }

            $category = ExpenseCategory::updateOrCreate(
                ['code' => $categoryData['code']],
                [
                    'name' => $categoryData['name'],
                    'name_ar' => $categoryData['name_ar'] ?? null,
                    'parent_id' => $parentId,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            $createdCategories[$categoryData['code']] = $category;
        }

        $this->command->info('Seeded ' . count($categories) . ' expense categories.');
    }
}
