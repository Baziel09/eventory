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
        $suppliers = Supplier::take(7)->get();

        foreach ($suppliers as $supplier) {
            $items = Item::inRandomOrder()->take(3)->get();

            foreach ($items as $item) {
                DB::table('supplier_item')->updateOrInsert(
                    [
                        'supplier_id' => $supplier->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'cost_price' => rand(10, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
