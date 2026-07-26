<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AOVTrendChart extends ChartWidget
{
    protected static ?int $sort = 6;
    protected ?string $heading = 'Ortalama Sepet Tutarı Trendi (AOV - Son 6 Ay)';

    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    protected function getData(): array
    {
        return Cache::remember('dashboard_aov_chart', 3600, function () {
            $data = [];
            $labels = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $year = $date->year;
                $month = $date->month;

                $monthlyAvg = Order::whereIn('status', ['paid', 'shipped', 'delivered'])
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->avg('total_amount') ?? 0;

                $data[] = round((float) $monthlyAvg, 2);
                $labels[] = $date->translatedFormat('F Y');
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Ortalama Sepet (₺)',
                        'data' => $data,
                        'fill' => 'start',
                        'backgroundColor' => 'rgba(45, 212, 191, 0.1)',
                        'borderColor' => '#2DD4BF',
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}