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
        $user = User::factory()->Create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');
        
        $voorraadUser = User::factory()->Create([
            'name' => 'Voorraad Beheerder',
            'email' => 'voorraad@example.com',
            'password' => bcrypt('password'),
        ]);

        $voorraadUser->assignRole('voorraadbeheerder');
        
        $vrijwilligerUser = User::factory()->Create([
            'name' => 'Lezer User',
            'email' => 'vrijwilliger@example.com',
            'password' => bcrypt('password'),
        ]);
        $vrijwilligerUser->assignRole('vrijwilliger');
    }
}