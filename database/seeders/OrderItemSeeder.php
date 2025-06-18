<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run()
    {
        $orders = Order::take(15)->get();
        foreach ($orders as $order) {

            $items =$order->supplier->items;

            foreach ($items as $item) {
                OrderItem::firstOrCreate([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => rand(5, 20),
                ]);
            }
        }
    }
}
