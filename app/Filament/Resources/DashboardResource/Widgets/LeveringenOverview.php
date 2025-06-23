<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Event;
use App\Models\Item;
use App\Models\Location;
use App\Models\Order;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\VendorItemStock;




class LeveringenOverview extends BaseWidget
{
    protected ?string $heading = 'Inkoop & Leveringen';


    protected static ?int $sort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $recordUrlAttribute = 'slug';

    protected static ?string $recordUrlRoute = 'filament.resources.dashboard-resource.show';

    protected static ?string $recordUrlRouteParameter = 'dashboardResource';

    protected function getStats(): array
    {
        if (auth()->user()->hasRole('voorraadbeheerder')) {
            $pendingOrders = Order::where('status', 'pending')->where('user_id', auth()->id())->count();
            $totalShippedOrders = Order::where('status', 'sent')->where('user_id', auth()->id())->count();
            $totalDeliveredOrders = Order::where('status', 'delivered')->where('user_id', auth()->id())->count();
        } else {
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalShippedOrders = Order::where('status', 'sent')->count();
        $totalDeliveredOrders = Order::where('status', 'delivered')->count();
        }

        return [
            Stat::make('Niet-goedgekeurde bestellingen', $pendingOrders)
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/orders'",
                ]),
            
            Stat::make('Verstuurde bestellingen', $totalShippedOrders)
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/orders'",
                ]),
            

            Stat::make('Geleverde bestellingen', $totalDeliveredOrders)
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/deliveries'",
                ]),

        ];
    }
}
