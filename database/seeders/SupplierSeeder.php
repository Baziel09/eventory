<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            ['name' => 'Arcane Supplies Co.', 'email' => 'arcane@example.com', 'phone' => '123-456-7890'],
            ['name' => 'Stellar Provisions', 'email' => 'stellar@example.com', 'phone' => '234-567-8901'],
            ['name' => 'Mythos Materials', 'email' => 'mythos@example.com', 'phone' => '345-678-9012'],
            ['name' => 'Ethereal Emporium', 'email' => 'ethereal@example.com', 'phone' => '456-789-0123'],
            ['name' => 'Timeless Treasures', 'email' => 'timeless@example.com', 'phone' => '567-890-1234'],
            ['name' => 'Cosmic Companions', 'email' => 'cosmic@example.com', 'phone' => '678-901-2345'],
            ['name' => 'Divine Delights', 'email' => 'divine@example.com', 'phone' => '789-012-3456'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate([
                'name' => $s['name'],
                'contact_email' => $s['email'],
                'contact_phone' => $s['phone'],
            ]);
        }
    }
}

