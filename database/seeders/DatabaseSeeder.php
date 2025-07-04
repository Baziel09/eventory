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
            
            
            LocationSeeder::class,
            CategoriesTableSeeder::class,
            UnitsTableSeeder::class,
            ItemSeeder::class,
            SupplierSeeder::class,
            SupplierItemSeeder::class,
            VendorSeeder::class,
            VendorItemStockSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
        ]);
    }
}