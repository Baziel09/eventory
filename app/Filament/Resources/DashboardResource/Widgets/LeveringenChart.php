<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class LeveringenChart extends ChartWidget
{
    protected static ?string $heading = 'Bestellingen status';

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
        $statuses = ['pending', 'confirmed', 'sent', 'delivered'];

        $counts = Order::selectRaw('status, COUNT(*) as count')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $data = array_map(fn($status) => $counts[$status] ?? 0, $statuses);

        return [
            'labels' => ['In afwachting', 'Bevestigd', 'Verzonden', 'Geleverd'],
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(251, 190, 36, 1)',
                        'rgba(52, 211, 153, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                    ],
                    'borderColor' => [
                        'rgba(251, 190, 36, 1)',
                        'rgba(52, 211, 153, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}