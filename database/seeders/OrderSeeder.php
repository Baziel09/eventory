<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\Supplier;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $vendors = Vendor::take(5)->get();
        $suppliers = Supplier::take(5)->get();
        $user = User::first();

        $statuses = ['pending', 'confirmed', 'sent', 'cancelled', 'delivered'];

        foreach ($suppliers as $supplier) {
            foreach ($vendors as $vendor) {
                Order::firstOrCreate([
                    'vendor_id' => $vendor->id,
                    'supplier_id' => $supplier->id,
                    'user_id' => $user?->id,
                    'ordered_at' => now(),
                    'status' => Arr::random($statuses),
                ]);
            }
        }

        $deliveredOrders = Order::where('status', 'delivered')->get();

        foreach ($deliveredOrders as $order) {
            Delivery::firstOrCreate([
                'delivered_at' => now(),
                'order_id' => $order->id,
                'user_id' => $user?->id
            ]);
        }

    }
}