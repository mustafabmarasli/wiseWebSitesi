<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Aylık Satış Analizi (Son 6 Ay)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('F');
            $year = $date->year;
            $month = $date->month;

            $monthlyRevenue = Order::whereIn('status', OrderStatus::paidStatuses())
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount');

            $data[] = (float) $monthlyRevenue;
            $labels[] = $monthName;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Aylık Satış Tutarı (₺)',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
