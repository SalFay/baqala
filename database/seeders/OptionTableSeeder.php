<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SettingGroup;
use Illuminate\Database\Seeder;

class OptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generalGroup = SettingGroup::where('slug', 'general')->first();

        $options = [
            ['key' => 'redirect_role_admin', 'value' => 'admin', 'label' => 'Admin Redirect'],
            ['key' => 'title', 'value' => 'Point of Sale', 'label' => 'System Title'],
            ['key' => 'logo', 'value' => 'logo.png', 'label' => 'Logo'],
            ['key' => 'address', 'value' => 'Mingora', 'label' => 'Address'],
            ['key' => 'phone', 'value' => '03339471086', 'label' => 'Phone'],
        ];

        foreach ($options as $option) {
            Setting::firstOrCreate(
                ['key' => $option['key'], 'store_id' => null],
                [
                    'setting_group_id' => $generalGroup?->id,
                    'value' => $option['value'],
                    'type' => 'text',
                    'label' => $option['label'],
                    'is_public' => true,
                ]
            );
        }
    }
}
