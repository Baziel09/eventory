<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Category;
use App\Models\VendorItemStock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VoorraadChart extends ChartWidget
{
    protected static ?string $heading = 'Voorraadstatus Overzicht';

    protected static ?string $maxHeight = '400px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
        //     'responsive' => true,
        //     'maintainAspectRatio' => false,
        //     'plugins' => [
        //         'legend' => [
        //             'display' => true,
        //             'position' => 'bottom',
        //         ],
        //         'tooltip' => [
        //             'enabled' => true,
        //             'callbacks' => [
        //                 'label' => 'function(context) {
        //                     const label = context.label || "";
        //                     const value = context.parsed.y;
        //                     return label + ": " + value + " producten";
        //                 }'
        //             ],
        //         ],
        //     ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 10,
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $user = auth()->user();

        if ($user->hasRole('voorraadbeheerder')) {
            $query = VendorItemStock::whereHas('vendor.users', function ($subQuery) use ($user) {
                $subQuery->where('users.id', $user->id);
            });
        } else {
            $query = VendorItemStock::query();
        }

        // Stocks below min
        $lowStockCount = (clone $query)->whereColumn('quantity', '<=', 'min_quantity')->count();

        // Stocks between min and 1.5 * min
        $warningStockCount = (clone $query)
            ->whereRaw('quantity > min_quantity AND quantity < min_quantity * 1.5')
            ->count();

        // Stocks above 1.5 * min (sufficient)
        $sufficientStockCount = (clone $query)
            ->whereRaw('quantity >= min_quantity * 1.5')
            ->count();

        return [
            'labels' => [
                'Te weinig voorraad (' . $lowStockCount . ')',
                'Bijna te weinig (' . $warningStockCount . ')',
                'Voldoende voorraad (' . $sufficientStockCount . ')',
            ],
            'datasets' => [
                [
                    'label' => 'Aantal producten',
                    'data' => [
                        $lowStockCount,
                        $warningStockCount,
                        $sufficientStockCount,
                    ],
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.9)',   // Red
                        'rgba(245, 158, 11, 0.9)',  // Yellow
                        'rgba(34, 197, 94, 0.9)',   // Green
                    ],
                    'borderColor' => [
                        'rgba(239, 68, 68, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(34, 197, 94, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}