<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Item;

class SupplierItemSeeder extends Seeder
{
    public function run()
    {
        $suppliers = Supplier::take(39)->get();

        foreach ($suppliers as $supplier) {
            $items = Item::inRandomOrder()->take(5)->get();

            foreach ($items as $item) {
                DB::table('supplier_item')->updateOrInsert(
                    [
                        'supplier_id' => $supplier->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'cost_price' => rand(5, 50),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
