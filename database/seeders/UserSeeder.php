<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'phone' => '0612345678',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');
        
        $voorraadUser = User::firstOrCreate(
            ['email' => 'voorraad@example.com'],
            [
                'name' => 'Voorraad Beheerder',
                'password' => bcrypt('password'),
                'phone' => '0687654321',
                'is_active' => true,
            ]
        );
        $voorraadUser->assignRole('voorraadbeheerder');
        
        $vrijwilligerUser = User::firstOrCreate(
            ['email' => 'vrijwilliger@example.com'],
            [
                'name' => 'Vrijwilliger',
                'password' => bcrypt('password'),
                'phone' => '0678901234',
                'is_active' => true,
            ]
        );
        $vrijwilligerUser->assignRole('vrijwilliger');
    }
}
