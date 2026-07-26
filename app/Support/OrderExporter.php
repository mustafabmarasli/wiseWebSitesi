<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Siparişleri tüm müşteri/fatura/ödeme bilgileriyle CSV'ye aktarır.
 *
 * DİKKAT: Çıktı TC Kimlik No, adres ve telefon gibi kişisel veri içerir (KVKK).
 * Dosyayı paylaşırken ve saklarken dikkatli olun.
 */
class OrderExporter
{
    private const HEADERS = [
        'Sipariş No',
        'Tarih',
        'Durum',
        'Üyelik',
        'Ad',
        'Soyad',
        'E-posta',
        'Telefon',
        'TC Kimlik No',
        'Teslimat Adresi',
        'Teslimat Şehri',
        'Posta Kodu',
        'Fatura Adresi',
        'Fatura Şehri',
        'Fatura Tipi',
        'Firma Adı',
        'Vergi No',
        'Vergi Dairesi',
        'Ürünler',
        'Toplam Ürün Adedi',
        'Ara Toplam',
        'Kupon Kullanıldı mı',
        'Kupon Kodu',
        'İndirim Tutarı',
        'Kargo Yöntemi',
        'Kargo Ücreti',
        'Ödenen Tutar',
        'Para Birimi',
        'Ödeme Yöntemi',
        'iyzico Ödeme ID',
        'iyzico Ödeme Durumu',
        'Tahmini Teslimat',
    ];

    private const STATUS_LABELS = [
        'pending'   => 'Ödeme Bekliyor',
        'paid'      => 'Ödendi / Hazırlanıyor',
        'shipped'   => 'Kargoya Verildi',
        'delivered' => 'Teslim Edildi',
        'failed'    => 'Ödeme Başarısız',
        'review'    => 'İnceleme Gerekiyor',
        'refunded'  => 'İade Edildi',
        'cancelled' => 'İptal Edildi',
    ];

    public static function download(Builder|Collection $orders, ?string $filename = null): StreamedResponse
    {
        $rows = [self::HEADERS];

        $each = function (Order $order) use (&$rows) {
            $rows[] = self::row($order);
        };

        if ($orders instanceof Builder) {
            $orders->with('items')->orderBy('id')->chunk(200, fn ($chunk) => $chunk->each($each));
        } else {
            // Eloquent ya da temel koleksiyon olabilir; loadMissing model üzerinde çağrılır.
            $orders->each(function (Order $order) use ($each) {
                $order->loadMissing('items');
                $each($order);
            });
        }

        return Csv::download(
            $filename ?? 'siparisler_' . now()->format('Y-m-d_H-i-s') . '.csv',
            $rows
        );
    }

    /**
     * @return array<int, string|int|float|null>
     */
    private static function row(Order $order): array
    {
        $items = $order->items;

        $itemSummary = $items
            ->map(fn ($item) => "{$item->product_name} x{$item->quantity}")
            ->implode(' | ');

        $subtotal = $items->sum('total_price');

        return [
            $order->id,
            $order->created_at?->format('d.m.Y H:i'),
            self::STATUS_LABELS[$order->status] ?? $order->status,
            $order->user_id ? 'Üye' : 'Misafir',
            $order->first_name,
            $order->last_name,
            $order->email,
            $order->phone,
            $order->identity_number,
            $order->address,
            $order->city,
            $order->zip_code,
            $order->billing_address,
            $order->billing_city,
            $order->is_corporate ? 'Kurumsal' : 'Bireysel',
            $order->company_name,
            $order->tax_number,
            $order->tax_office,
            $itemSummary,
            $items->sum('quantity'),
            self::money($subtotal),
            $order->coupon_code ? 'Evet' : 'Hayır',
            $order->coupon_code,
            self::money($order->discount_amount),
            $order->shipping_method,
            self::money($order->shipping_cost),
            self::money($order->total_amount),
            $order->currency,
            $order->payment_method,
            $order->iyzico_payment_id,
            $order->iyzico_payment_status,
            $order->estimated_delivery_at?->format('d.m.Y'),
        ];
    }

    /**
     * Excel'in Türkçe yerelde sayı olarak okuması için virgüllü biçim.
     */
    private static function money(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
