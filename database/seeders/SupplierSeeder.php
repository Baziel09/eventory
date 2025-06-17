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
            ['name' => 'Galactic Goods', 'email' => 'galactic@example.com', 'phone' => '890-123-4567'],
            ['name' => 'Starlight Supplies', 'email' => 'starlight@example.com', 'phone' => '901-234-5678'],
            ['name' => 'Astral Artifacts', 'email' => 'astral@example.com', 'phone' => '012-345-6789'],
            ['name' => 'Eclipse Essentials', 'email' => 'eclipse@example.com', 'phone' => '123-456-7891'],
            ['name' => 'Lunar Luxuries', 'email' => 'lunar@example.com', 'phone' => '234-567-8902'],
            ['name' => 'Solar Souvenirs', 'email' => 'solar@example.com', 'phone' => '345-678-9013'],
            ['name' => 'Meteoric Merchandise', 'email' => 'meteoric@example.com', 'phone' => '456-789-0124'],
            ['name' => 'Orbital Offerings', 'email' => 'orbital@example.com', 'phone' => '567-890-1235'],
            ['name' => 'Planetary Provisions', 'email' => 'planetary@example.com', 'phone' => '678-901-2346'],
            ['name' => 'Stellar Stores', 'email' => 'stellarstores@example.com', 'phone' => '789-012-3457'],
            ['name' => 'Comet Collections', 'email' => 'comet@example.com', 'phone' => '890-123-4568'],
            ['name' => 'Asteroid Accessories', 'email' => 'asteroid@example.com', 'phone' => '901-234-5679'],
            ['name' => 'Interstellar Impressions', 'email' => 'interstellar@example.com', 'phone' => '012-345-6790'],
            ['name' => 'Galaxy Gifts', 'email' => 'galaxy@example.com', 'phone' => '123-456-7892'],
            ['name' => 'Constellation Creations', 'email' => 'constellation@example.com', 'phone' => '234-567-8903'],
            ['name' => 'Space Station Shop', 'email' => 'spacestation@example.com', 'phone' => '345-678-9014'],
            ['name' => 'Astronautical Attire', 'email' => 'astronautical@example.com', 'phone' => '456-789-0125'],
            ['name' => 'Celestial Curios', 'email' => 'celestial@example.com', 'phone' => '567-890-1236'],
            ['name' => 'Moonlight Market', 'email' => 'moonlight@example.com', 'phone' => '678-901-2347'],
            ['name' => 'Coca-Cola', 'email' => 'coca-cola@example.com', 'phone' => '789-012-3458'],

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

