<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Bier'],
            ['name' => 'Wijn'],
            ['name' => 'Frisdrank'],
            ['name' => 'Sterke drank'],
            ['name' => 'Cocktails'],
            ['name' => 'Warme dranken'],
            ['name' => 'Snacks'],
            ['name' => 'Warm Eten'],
            ['name' => 'Koel Eten'],
            ['name' => 'Groenten'],
            ['name' => 'Vlees'],
            ['name' => 'Vis'],
            ['name' => 'Vegetarisch'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}