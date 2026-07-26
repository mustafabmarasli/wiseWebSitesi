<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AllDataExportWidget extends Widget
{
    protected static ?int $sort = 13;

    protected int|string|array $columnSpan = 'full';
    protected string $heading = 'Tüm Verileri Dışa Aktar';
    protected string $view = 'filament.widgets.all-data-export';

    public function getViewData(): array
    {
        return [
            'stats' => Cache::remember('export_all_stats', 3600, function () {
                $paidStatuses = OrderStatus::paidStatuses();
                
                $healthCategories = \App\Models\Category::whereIn('slug', ['lens-aksesuarlari', 'dmv-urunleri'])->pluck('id');
                $electronicsCategories = \App\Models\Category::whereNotIn('slug', ['lens-aksesuarlari', 'dmv-urunleri'])->pluck('id');
                
                return [
                    'total_revenue' => Order::whereIn('status', $paidStatuses)->sum('total_amount'),
                    'total_orders' => Order::whereIn('status', $paidStatuses)->count(),
                    'total_users' => User::where('is_admin', false)->count(),
                    'total_products' => Product::count(),
                    'avg_order_value' => Order::whereIn('status', $paidStatuses)->avg('total_amount') ?? 0,
                    'health_revenue' => OrderItem::whereHas('order', fn($q) => $q->whereIn('status', $paidStatuses))
                        ->whereHas('product', fn($q) => $q->whereIn('category_id', $healthCategories))
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereIn('orders.status', $paidStatuses)
                        ->sum('order_items.total_price'),
                    'electronics_revenue' => OrderItem::whereHas('order', fn($q) => $q->whereIn('status', $paidStatuses))
                        ->whereHas('product', fn($q) => $q->whereIn('category_id', $electronicsCategories))
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereIn('orders.status', $paidStatuses)
                        ->sum('order_items.total_price'),
                    'low_stock_count' => Product::where('stock', '<=', 5)->count(),
                    'out_of_stock' => Product::where('stock', 0)->count(),
                    'coupon_usage_count' => Order::whereNotNull('coupon_code')->whereIn('status', $paidStatuses)->count(),
                    'monthly_orders' => Order::whereIn('status', $paidStatuses)->whereMonth('created_at', now()->month)->count(),
                ];
            }),
        ];
    }

    public function exportAll(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $paidStatuses = OrderStatus::paidStatuses();

        $stats = [];
        $stats[] = ['Metrik', 'Değer'];
        $stats[] = ['Toplam Ciro', Order::whereIn('status', $paidStatuses)->sum('total_amount')];
        $stats[] = ['Toplam Sipariş', Order::whereIn('status', $paidStatuses)->count()];
        $stats[] = ['Toplam Müşteri', User::where('is_admin', false)->count()];
        $stats[] = ['Toplam Ürün', Product::count()];
        $stats[] = ['Ortalama Sepet', Order::whereIn('status', $paidStatuses)->avg('total_amount') ?? 0];
        $stats[] = ['Bekleyen Sipariş', Order::where('status', 'pending')->count()];
        $stats[] = ['Kritik Stok', Product::where('stock', '<=', 5)->count()];
        $stats[] = ['Tükenen Ürün', Product::where('stock', 0)->count()];
        $stats[] = ['Bugünkü Sipariş', Order::whereIn('status', $paidStatuses)->whereDate('created_at', today())->count()];
        $stats[] = ['Bu Ay Sipariş', Order::whereIn('status', $paidStatuses)->whereMonth('created_at', now()->month)->count()];

        $stats[] = [];
        $stats[] = ['En Çok Satan 10 Ürün'];
        $stats[] = ['Ürün Adı', 'Satış Sayısı', 'Toplam Gelir'];
        $topProducts = Product::orderBy('satis_sayisi', 'desc')->take(10)->get();
        foreach ($topProducts as $p) {
            $stats[] = [$p->name, $p->satis_sayisi, $p->satis_sayisi * $p->price];
        }

        $stats[] = [];
        $stats[] = ['Kupon Kullanımı'];
        $stats[] = ['Kupon Kodu', 'Kullanım Sayısı', 'Toplam İndirim'];
        $coupons = Order::whereNotNull('coupon_code')->whereIn('status', $paidStatuses)
            ->select('coupon_code', DB::raw('count(*) as total_uses'), DB::raw('sum(discount_amount) as total_discount'))
            ->groupBy('coupon_code')->orderByDesc('total_uses')->get();
        foreach ($coupons as $c) {
            $stats[] = [$c->coupon_code, $c->total_uses, $c->total_discount];
        }

        return \App\Support\Csv::download(
            'wisesolutions_rapor_' . now()->format('Y-m-d_H-i-s') . '.csv',
            $stats
        );
    }
}