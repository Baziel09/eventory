<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Category;
use App\Models\VendorItemStock;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VoorraadDoughnut extends ChartWidget
{
    protected static ?string $heading = 'Voorraadstatus Verdeling';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
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

        $data = [$lowStockCount, $warningStockCount, $sufficientStockCount];

        return [
            'labels' => ['Te weinig voorraad', 'Bijna te weinig', 'Voldoende voorraad'],
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(251, 190, 36, 1)',
                        'rgba(52, 211, 153, 1)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(251, 190, 36, 1)',
                        'rgba(52, 211, 153, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}