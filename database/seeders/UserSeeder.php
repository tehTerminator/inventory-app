<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a Admin Role
        DB::table('roles')->insert(['name' => 'admin', 'description' => 'A SuperUser']);
        DB::table('roles')->insert(['name' => 'user', 'description' => 'A General User']);
        // Assign the admin role to the first user

        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'mobile' => '9425760707',
            'password' => Hash::make('password'),
        ]);

        DB::table('users')->where('id', 1)->update(['role_id' => '1']);
    }
}
