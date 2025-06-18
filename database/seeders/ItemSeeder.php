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
            'Jagermeister',
            'Bacardi Cola',
            'Groninger Pilsner',
            'Heineken',
            'Desperados',
            'Wodka Pure',
            'Jenever',
            'Cointreau',
            'Baileys',
            'Prosecco',
            'Cava',
            'Gin & Tonic',
            'Whiskey Cola',
            'Rum & Cola',
            'Vlaamse Frites',
            'Kipnuggets',
            'Pizza Quattro Formaggi',
            'Tortellini',
            'Gegrilde Vis',
            'Kibbeling',
            'Garnalen',
            'Waldorfsalade',
            'Bananencake',
            'Meringue',
            'Wafel',
            'IJs',
            'Frikadellen',
            'Sloppy Joe',
            'Chili Con Carne',
            'Jagermeister',
            'Bacardi Cola',
            'Groninger Pilsner',
            'Heineken',
            'Desperados',
            'Wodka Pure',
            'Jenever',
            'Cointreau',
            'Baileys',
            'Prosecco',
            'Cava',
            'Gin & Tonic',
            'Whiskey Cola',
            'Rum & Cola',
            'Vlaamse Frites',
            'Kipnuggets',
            'Pizza Quattro Formaggi',
            'Tortellini',
            'Gegrilde Vis',
            'Kibbeling',
            'Garnalen',
            'Waldorfsalade',
            'Bananencake',
            'Meringue',
            'Wafel',
            'IJs',
            'Frikadellen',
            'Sloppy Joe',
            'Chili Con Carne',
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
