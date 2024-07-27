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
            $password = Hash::make( 'admin' );
            $admin_role = Role::where( 'slug', 'admin' )->first();
            $users = [

                [
                    'id'         => 1,
                    'first_name' => 'Super',
                    'last_name'  => 'Admin',
                    'email'      => 'admin@admin.com',
                    'password'   => $password,
                    'role_id'    => $admin_role->id,
                    'phone'      => '+923339471086',
                    'status'     => 'Active',
                    'verified'   => 1
                ]
            ];

            foreach( $users as $user ) {
                User::create( $user );
            }


        }
    }
