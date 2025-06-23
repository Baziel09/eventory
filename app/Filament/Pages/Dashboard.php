<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Filament\Resources\DashboardResource\Widgets\LeveringenOverview; 
use App\Filament\Resources\DashboardResource\Widgets\VoorraadOverview;
use App\Filament\Resources\DashboardResource\Widgets\LeveringenChart;
use App\Filament\Resources\DashboardResource\Widgets\VoorraadChart;
use App\Filament\Resources\DashboardResource\Widgets\VoorraadDoughnut;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            LeveringenOverview::class,
            LeveringenChart::class,
            VoorraadOverview::class,
            VoorraadChart::class,
            VoorraadDoughnut::class
        ];
    }
}