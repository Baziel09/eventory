<?php

namespace Database\Seeders;

use App\Models\VendorItemStock;
use App\Models\Vendor;
use App\Models\Item;
use Illuminate\Database\Seeder;

class VendorItemStockSeeder extends Seeder
{
    public function run()
    {
        $vendors = Vendor::take(5)->get();

        foreach ($vendors as $vendor) {
        $items = Item::inRandomOrder()->take(3)->get();

            foreach ($items as $item) {
                VendorItemStock::firstOrCreate([
                    'vendor_id' => $vendor->id,
                    'item_id' => $item->id,
                    'quantity' => rand(20, 100),
                ]);
            }
        }
    }
}
