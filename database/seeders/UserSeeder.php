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
            ]
        );
        $admin->assignRole('admin');
        
        $voorraadUser = User::firstOrCreate(
            ['email' => 'voorraad@example.com'],
            [
                'name' => 'Voorraad Beheerder',
                'password' => bcrypt('password'),
            ]
        );
        $voorraadUser->assignRole('voorraadbeheerder');
        
        $vrijwilligerUser = User::firstOrCreate(
            ['email' => 'vrijwilliger@example.com'],
            [
                'name' => 'Vrijwilliger',
                'password' => bcrypt('password'),
            ]
        );
        $vrijwilligerUser->assignRole('vrijwilliger');
    }
}