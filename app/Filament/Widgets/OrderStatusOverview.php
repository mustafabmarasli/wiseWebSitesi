<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class OrderStatusOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Sipariş Durum Özeti';
    public function getPollingInterval(): ?string
    {
        return '300s';
    }

    protected function getStats(): array
    {
        return Cache::remember('dashboard_order_status_stats', 600, function () {
            $total = Order::count();
            $paid = Order::where('status', 'paid')->count();
            $shipped = Order::where('status', 'shipped')->count();
            $delivered = Order::where('status', 'delivered')->count();
            $pending = Order::where('status', 'pending')->count();
            $failed = Order::where('status', 'failed')->count();

            return [
                Stat::make('Toplam Sipariş', $total)
                    ->description('Tüm zamanlar')
                    ->descriptionIcon('heroicon-m-shopping-bag')
                    ->color('info'),

                Stat::make('Ödendi / Hazırlanıyor', $paid)
                    ->description('Onaylanmış')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),

                Stat::make('Kargoda', $shipped)
                    ->description('Yolda')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('warning'),

                Stat::make('Teslim Edildi', $delivered)
                    ->description('Tamamlanmış')
                    ->descriptionIcon('heroicon-m-home')
                    ->color('primary'),

                Stat::make('Beklemede / İşlemde', $pending)
                    ->description('Onay bekliyor')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($pending > 0 ? 'warning' : 'success'),

                Stat::make('Başarısız / İptal', $failed)
                    ->description('Ödeme hatası')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color($failed > 0 ? 'danger' : 'success'),
            ];
        });
    }
}