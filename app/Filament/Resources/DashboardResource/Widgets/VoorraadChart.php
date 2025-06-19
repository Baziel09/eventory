<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\VendorItemStock;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class VoorraadChart extends ChartWidget
{
    protected static ?string $heading = 'Voorraad status';

    protected function getType(): string
    {
        // return 'doughnut';
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => false,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'width' => 400,
            'height' => 400,
        ];
    }

    protected function getData(): array
    {
        $lowStockProducts = VendorItemStock::where('quantity', '<=', 10)->count();
        $minQuantityInDanger = VendorItemStock::whereColumn('quantity', '<', 'min_quantity')->count();

        return [
            'labels' => [
                'Te weinig voorraad',
                'Voorraad in gevaar',
            ],
            'datasets' => [
                [
                    'label' => 'Producten',
                    'data' => [
                        $lowStockProducts,
                        $minQuantityInDanger,
                    ],
                    'backgroundColor' => [
                        'rgba(255, 0, 0, 1)',
                        'rgba(255, 205, 86, 1)',
                    ],
                    'borderColor' => 'rgba(0,0,0,0.1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}