<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            //PermissionSeeder::class,
            UserSeeder::class,
            
            LocationSeeder::class,
            CategoriesTableSeeder::class,
            UnitsTableSeeder::class,
        ]);
    }
}