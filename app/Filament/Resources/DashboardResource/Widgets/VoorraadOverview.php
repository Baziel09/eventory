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




class VoorraadOverview extends BaseWidget
{
    protected ?string $heading = 'Voorraad & Producten';

    protected static ?int $sort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $recordUrlAttribute = 'slug';

    protected static ?string $recordUrlRoute = 'filament.resources.dashboard-resource.show';

    protected static ?string $recordUrlRouteParameter = 'dashboardResource';

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('voorraadbeheerder')) {
            $lowStockProducts = VendorItemStock::whereHas('vendor.users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->whereColumn('quantity', '<=', 'min_quantity')->count();

            $minQuantityInDanger = VendorItemStock::whereHas('vendor.users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->whereRaw('quantity < min_quantity * 1.5')->count();

            $totalProducts = VendorItemStock::whereHas('vendor.users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->count();
        } else {
            $lowStockProducts = VendorItemStock::whereColumn('quantity', '<=', 'min_quantity')->count();

            $minQuantityInDanger = VendorItemStock::whereRaw('quantity < min_quantity * 1.5')->count();

            $totalProducts = VendorItemStock::count();
        }

        


        return [

            Stat::make('Minimale voorraad in gevaar', $minQuantityInDanger)
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/vendors'",
                ]),

            Stat::make('Laag voorraad', $lowStockProducts)
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/vendor-item-stocks'",
                ]),

            Stat::make('Totaal Producten', $totalProducts)
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'x-on:click' => "window.location.href='/items'",
                ]),
        ];
    }
}
