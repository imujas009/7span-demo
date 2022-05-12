<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        $data = array(
        			'role_id' => 1, //admin role
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
			        'email' => 'admin@admin.com',
			        'password' => Hash::make('password'),
        		);
        User::insert($data);
    }
}
