<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsTableSeeder extends Seeder
{
    public function run()
    {
        $units = [
            ['name' => 'Stukken'],
            ['name' => 'Porties'],
            ['name' => 'Gram'],
            ['name' => 'Kilogram'],
            ['name' => 'Milliliter'],
            ['name' => 'Liter'],
            ['name' => 'Flessen'],
            ['name' => 'Blikjes'],
            ['name' => 'Pakken'],
            ['name' => 'Bakken'],
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
