<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class NewUsersChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected ?string $heading = 'Yeni Kullanıcı (Son 30 Gün)';
    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    protected function getData(): array
    {
        return Cache::remember('dashboard_new_users_chart', 3600, function () {
            $data = [];
            $labels = [];

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $count = User::where('is_admin', false)->whereDate('created_at', $date)->count();
                $data[] = $count;
                $labels[] = $date->format('d M');
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Yeni Üye',
                        'data' => $data,
                        'fill' => 'start',
                        'backgroundColor' => 'rgba(27, 74, 122, 0.1)',
                        'borderColor' => '#1B4A7A',
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