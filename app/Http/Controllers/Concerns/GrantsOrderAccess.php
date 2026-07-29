<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;

/**
 * Sipariş sonuç sayfalarına oturum bazlı erişim izni.
 *
 * Hem ödemeyi başlatan (CartController) hem sonucu işleyen
 * (PaymentController) taraf aynı oturum anahtarını kullanmak zorunda;
 * anahtar iki yerde ayrı yazılsaydı biri değiştiğinde misafir müşteri
 * kendi siparişini göremez hâle gelirdi.
 */
trait GrantsOrderAccess
{
    private const ORDER_ACCESS_KEY = 'order_access';

    /** Bu tarayıcı oturumuna siparişi görüntüleme izni ver. */
    protected function grantOrderAccess(Order $order): void
    {
        $granted = session()->get(self::ORDER_ACCESS_KEY, []);

        if (! in_array($order->id, $granted, true)) {
            $granted[] = $order->id;
            session()->put(self::ORDER_ACCESS_KEY, $granted);
        }
    }

    /** Oturumun bu siparişe erişim izni var mı? */
    protected function hasOrderAccess(Order $order): bool
    {
        return in_array($order->id, session()->get(self::ORDER_ACCESS_KEY, []), true);
    }
}
