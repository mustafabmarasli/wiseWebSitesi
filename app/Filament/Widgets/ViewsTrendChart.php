<?php

namespace App\Filament\Widgets;

use App\Services\ProductViewRecorder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ViewsTrendChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ürün Görüntülenmeleri (Son 30 Gün)';

    protected ?string $description = 'Aynı ziyaretçinin 30 dakika içindeki tekrar ziyaretleri tek sayılır.';

    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    protected function getData(): array
    {
        $counts = Cache::remember(
            'dashboard_views_trend',
            600,
            fn () => ProductViewRecorder::dailyCounts(30)
        );

        return [
            'datasets' => [
                [
                    'label'           => 'Görüntülenme',
                    'data'            => array_values($counts),
                    'borderColor'     => '#1B4A7A',
                    'backgroundColor' => 'rgba(27, 74, 122, 0.12)',
                    'fill'            => true,
                    'tension'         => 0.35,
                ],
            ],
            'labels' => array_map(
                fn ($day) => Carbon::parse($day)->format('d.m'),
                array_keys($counts)
            ),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['precision' => 0],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
