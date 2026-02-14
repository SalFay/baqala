<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => ['*'],
                'color' => '#1890ff',
                'sort_order' => 0,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'permissions' => [
                    'dashboard.view',
                    'products.view', 'products.create', 'products.edit',
                    'orders.view', 'orders.create',
                    'customers.view', 'customers.create', 'customers.edit',
                    'inventory.view', 'inventory.adjust',
                    'reports.view',
                ],
                'color' => '#52c41a',
                'sort_order' => 1,
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'permissions' => [
                    'pos.access',
                    'orders.view', 'orders.create',
                    'customers.view', 'customers.create',
                    'products.view',
                ],
                'color' => '#faad14',
                'sort_order' => 2,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'permissions' => $role['permissions'],
                    'color' => $role['color'],
                    'sort_order' => $role['sort_order'],
                ]
            );
        }
    }
}
