<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /** 8 kart → 4x2 tam ızgara, boşluk kalmaz. */
    protected int|array|null $columns = 4;

    public function getPollingInterval(): ?string
    {
        return '300s';
    }

    protected function getStats(): array
    {
        return Cache::remember('dashboard_stats_overview', 600, function () {
        $todayRevenue   = Order::whereIn('status', OrderStatus::paidStatuses())->whereDate('created_at', today())->sum('total_amount');
        $todayOrders    = Order::whereIn('status', OrderStatus::paidStatuses())->whereDate('created_at', today())->count();

        $monthRevenue   = Order::whereIn('status', OrderStatus::paidStatuses())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount');
        $lastMonthRev   = Order::whereIn('status', OrderStatus::paidStatuses())->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('total_amount');
        $revChange      = $lastMonthRev > 0 ? round((($monthRevenue - $lastMonthRev) / $lastMonthRev) * 100, 1) : 0;

        $totalRevenue   = Order::whereIn('status', OrderStatus::paidStatuses())->sum('total_amount');
        $totalOrders    = Order::whereIn('status', OrderStatus::paidStatuses())->count();
        $avgOrder       = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $usersCount     = User::where('is_admin', false)->count();
        $newUsersToday  = User::where('is_admin', false)->whereDate('created_at', today())->count();
        $newUsersWeek   = User::where('is_admin', false)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $lowStockCount  = Product::where('stock', '<=', 5)->count();
        $outOfStock     = Product::where('stock', 0)->count();

        $pendingCount   = Order::where('status', 'pending')->count();
        $shippedCount   = Order::where('status', 'shipped')->count();

        $viewsToday     = ProductView::whereDate('created_at', today())->count();
        $viewsYesterday = ProductView::whereDate('created_at', today()->subDay())->count();
        $viewsChange    = $viewsYesterday > 0
            ? round((($viewsToday - $viewsYesterday) / $viewsYesterday) * 100)
            : null;

        $viewsMonth     = ProductView::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $ordersMonth    = Order::whereIn('status', OrderStatus::paidStatuses())->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $conversion     = $viewsMonth > 0 ? round(($ordersMonth / $viewsMonth) * 100, 2) : 0;

        return [
            Stat::make('Bugünkü Ciro', '₺' . number_format($todayRevenue, 2, ',', '.'))
                ->description("Bugün {$todayOrders} sipariş")
                ->descriptionIcon('heroicon-m-sun')
                ->color('success'),

            Stat::make('Bu Ay Ciro', '₺' . number_format($monthRevenue, 2, ',', '.'))
                ->description($revChange >= 0 ? "+{$revChange}% geçen aya göre" : "{$revChange}% geçen aya göre")
                ->descriptionIcon($revChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revChange >= 0 ? 'success' : 'danger'),

            Stat::make('Toplam Ciro', '₺' . number_format($totalRevenue, 2, ',', '.'))
                ->description("{$totalOrders} başarılı sipariş")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Ort. Sepet Tutarı', '₺' . number_format($avgOrder, 2, ',', '.'))
                ->description('AOV — tüm zamanlar')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Kayıtlı Müşteri', $usersCount)
                ->description("Bugün +{$newUsersToday} | Bu hafta +{$newUsersWeek}")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Bekleyen Sipariş', $pendingCount)
                ->description("{$shippedCount} kargoda")
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Kritik Stok', $lowStockCount . ' ürün')
                ->description("Stok 0: {$outOfStock} ürün")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outOfStock > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success')),

            Stat::make('Bugünkü Görüntülenme', number_format($viewsToday, 0, ',', '.'))
                ->description($viewsChange === null
                    ? 'Dün veri yok'
                    : ($viewsChange >= 0 ? "+{$viewsChange}% düne göre" : "{$viewsChange}% düne göre"))
                ->descriptionIcon($viewsChange !== null && $viewsChange < 0
                    ? 'heroicon-m-arrow-trending-down'
                    : 'heroicon-m-eye')
                ->color($viewsChange !== null && $viewsChange < 0 ? 'warning' : 'info'),

            Stat::make('Dönüşüm Oranı', $conversion . '%')
                ->description("Bu ay {$viewsMonth} görüntülenme → {$ordersMonth} sipariş")
                ->descriptionIcon('heroicon-m-funnel')
                ->color($conversion >= 2 ? 'success' : ($conversion > 0 ? 'warning' : 'gray')),
        ];
        });
    }
}

