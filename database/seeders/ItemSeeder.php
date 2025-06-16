<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            'Enchanted Lanterns',
            'Celestial Scrolls',
            'Phantom Ribbons',
            'Chrono Stones',
            'Luminous Dust',
            'Arcane Amulets',
            'Ethereal Wands',
            'Divine Orbs',
            'Cosmic Shards',
            'Celestial Rings',
            'Timeless Relics',
            'Eternal Talismans',
            'Cosmic Keys',
            'Eternal Scrolls',
            'Cosmic Orbs',
            'Eternal Wands',
            'Cosmic Amulets',
            'Eternal Rings',
            'Timeless Keys',
            'Celestial Orbs',
        ];

        foreach ($items as $item) {
            $category = Category::inRandomOrder()->first();
            $unit = Unit::inRandomOrder()->first();

            if ($category && $unit) {
                Item::firstOrCreate([
                    'name' => $item,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                ]);
            }
        }
    }
}
