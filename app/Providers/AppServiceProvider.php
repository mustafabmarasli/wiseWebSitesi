<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Stok 0'dan yukarı çıkınca "haber ver" kayıtlarına e-posta gider.
        Product::observe(ProductObserver::class);

        // Yeni müşteri kaydında Telegram bildirimi (panelden açılır/kapanır).
        User::observe(UserObserver::class);

        // Durum "Kargoya Verildi"ye geçince müşteriye takip bilgisiyle e-posta.
        Order::observe(OrderObserver::class);
    }
}
