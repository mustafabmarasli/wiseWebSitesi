<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class ChannelPieChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected ?string $heading = 'Kanal Bazlı Satış Dağılımı (Tüm Zamanlar)';

    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    protected function getData(): array
    {
        return Cache::remember('dashboard_channel_pie', 3600, function () {
            $healthCategories = Category::whereIn('slug', ['lens-aksesuarlari', 'dmv-urunleri'])->pluck('id');
            $electronicsCategories = Category::whereNotIn('slug', ['lens-aksesuarlari', 'dmv-urunleri'])->pluck('id');

            $healthRevenue = OrderItem::whereHas('order', function ($q) {
                    $q->whereIn('status', ['paid', 'shipped', 'delivered']);
                })
                ->whereHas('product', function ($q) use ($healthCategories) {
                    $q->whereIn('category_id', $healthCategories);
                })
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['paid', 'shipped', 'delivered'])
                ->sum('order_items.total_price');

            $electronicsRevenue = OrderItem::whereHas('order', function ($q) {
                    $q->whereIn('status', ['paid', 'shipped', 'delivered']);
                })
                ->whereHas('product', function ($q) use ($electronicsCategories) {
                    $q->whereIn('category_id', $electronicsCategories);
                })
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['paid', 'shipped', 'delivered'])
                ->sum('order_items.total_price');

            return [
                'datasets' => [
                    [
                        'label' => 'Kanal Bazlı Gelir',
                        'data' => [round($healthRevenue, 2), round($electronicsRevenue, 2)],
                        'backgroundColor' => ['#10B981', '#1B4A7A'],
                        'borderColor' => ['#059669', '#14385C'],
                    ],
                ],
                'labels' => ['Sağlık & Lens', 'Elektronik'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}