<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $cashierRole = Role::where('slug', 'cashier')->first();

        $users = [
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
                'role_id' => $adminRole?->id,
                'phone' => '+923339471086',
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Store',
                'last_name' => 'Manager',
                'email' => 'manager@demo.com',
                'password' => Hash::make('manager'),
                'role_id' => $managerRole?->id,
                'phone' => '+966500000001',
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Main',
                'last_name' => 'Cashier',
                'email' => 'cashier@demo.com',
                'password' => Hash::make('cashier'),
                'role_id' => $cashierRole?->id,
                'phone' => '+966500000002',
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
