<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sipariş Özeti')
                ->columns(4)
                ->schema([
                    TextEntry::make('order_number')->label('Sipariş No')->copyable()->weight('bold'),
                    TextEntry::make('created_at')->label('Sipariş Tarihi')->dateTime('d.m.Y H:i'),
                    TextEntry::make('status')
                        ->label('Durum')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending'   => 'Ödeme Bekliyor',
                            'paid'      => 'Ödendi / Hazırlanıyor',
                            'shipped'   => 'Kargoya Verildi',
                            'delivered' => 'Teslim Edildi',
                            'failed'    => 'Ödeme Başarısız',
                            'review'    => 'İnceleme Gerekiyor',
                            'refunded'  => 'İade Edildi',
                            'cancelled' => 'İptal Edildi',
                            default     => $state,
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'paid', 'delivered' => 'success',
                            'shipped'           => 'info',
                            'pending'           => 'warning',
                            default             => 'danger',
                        }),
                    TextEntry::make('user_id')
                        ->label('Müşteri Tipi')
                        ->badge()
                        ->state(fn (Order $r): string => $r->user_id ? 'Üye' : 'Misafir')
                        ->color(fn (Order $r): string => $r->user_id ? 'success' : 'gray'),
                ]),

            Section::make('Müşteri Bilgileri')
                ->columns(3)
                ->schema([
                    TextEntry::make('full_name')->label('Ad Soyad'),
                    TextEntry::make('email')->label('E-posta')->copyable(),
                    TextEntry::make('phone')->label('Telefon')->copyable(),
                    TextEntry::make('identity_number')
                        ->label('TC Kimlik No')
                        ->copyable()
                        ->placeholder('—')
                        ->helperText('Veritabanında şifreli saklanır.'),
                ]),

            Section::make('Teslimat Adresi')
                ->columns(3)
                ->schema([
                    TextEntry::make('province.name')->label('İl')->placeholder('—'),
                    TextEntry::make('district.name')->label('İlçe')->placeholder('—'),
                    TextEntry::make('neighborhood.name')->label('Mahalle')->placeholder('—'),
                    TextEntry::make('address')->label('Tam Adres')->columnSpan(3),
                    TextEntry::make('zip_code')->label('Posta Kodu')->placeholder('—'),
                    TextEntry::make('shipping_method')->label('Kargo Yöntemi')->placeholder('—'),
                ]),

            Section::make('Fatura Bilgileri')
                ->columns(3)
                ->schema([
                    TextEntry::make('is_corporate')
                        ->label('Fatura Tipi')
                        ->badge()
                        ->state(fn (Order $r): string => $r->is_corporate ? 'Kurumsal' : 'Bireysel')
                        ->color(fn (Order $r): string => $r->is_corporate ? 'info' : 'gray'),
                    TextEntry::make('company_name')->label('Firma Adı')->placeholder('—'),
                    TextEntry::make('tax_number')->label('Vergi No')->placeholder('—'),
                    TextEntry::make('tax_office')->label('Vergi Dairesi')->placeholder('—'),
                    
                    TextEntry::make('billingProvince.name')->label('Fatura İl')->placeholder('Teslimat adresiyle aynı'),
                    TextEntry::make('billingDistrict.name')->label('Fatura İlçe')->placeholder('Teslimat adresiyle aynı'),
                    TextEntry::make('billingNeighborhood.name')->label('Fatura Mahalle')->placeholder('Teslimat adresiyle aynı'),
                    TextEntry::make('billing_address')->label('Fatura Adresi')->placeholder('Teslimat adresiyle aynı')->columnSpan(3),
                ]),

            Section::make('Sipariş Edilen Ürünler')
                ->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->columns(4)
                        ->schema([
                            TextEntry::make('product_name')->label('Ürün')->columnSpan(2),
                            TextEntry::make('quantity')->label('Adet'),
                            TextEntry::make('unit_price')->label('Birim Fiyat')->money('TRY'),
                            TextEntry::make('total_price')->label('Satır Toplamı')->money('TRY')->columnSpan(4),
                        ]),
                ]),

            Section::make('Ödeme Dökümü')
                ->columns(3)
                ->schema([
                    TextEntry::make('items_subtotal')
                        ->label('Ara Toplam')
                        ->state(fn (Order $r): string => number_format((float) $r->items->sum('total_price'), 2, ',', '.') . ' ₺'),

                    TextEntry::make('coupon_code')
                        ->label('Kupon')
                        ->badge()
                        ->state(fn (Order $r): string => $r->coupon_code ?: 'Kullanılmadı')
                        ->color(fn (Order $r): string => $r->coupon_code ? 'success' : 'gray'),

                    TextEntry::make('discount_amount')
                        ->label('İndirim')
                        ->state(fn (Order $r): string => '-' . number_format((float) $r->discount_amount, 2, ',', '.') . ' ₺')
                        ->color(fn (Order $r): string => (float) $r->discount_amount > 0 ? 'danger' : 'gray'),

                    TextEntry::make('shipping_cost')
                        ->label('Kargo Ücreti')
                        ->state(fn (Order $r): string => (float) $r->shipping_cost > 0
                            ? number_format((float) $r->shipping_cost, 2, ',', '.') . ' ₺'
                            : 'Ücretsiz')
                        ->color(fn (Order $r): string => (float) $r->shipping_cost > 0 ? 'warning' : 'success'),

                    TextEntry::make('total_amount')
                        ->label('Ödenen Tutar')
                        ->money('TRY')
                        ->weight('bold')
                        ->size('lg')
                        ->color('success'),

                    TextEntry::make('estimated_delivery_at')->label('Tahmini Teslimat')->date('d.m.Y')->placeholder('—'),
                ]),

            Section::make('Ödeme Kaydı')
                ->columns(3)
                ->collapsed()
                ->schema([
                    TextEntry::make('payment_method')->label('Ödeme Yöntemi')->placeholder('—'),
                    TextEntry::make('iyzico_payment_id')->label('iyzico Ödeme ID')->copyable()->placeholder('—'),
                    TextEntry::make('iyzico_payment_status')->label('iyzico Durumu')->placeholder('—'),
                    TextEntry::make('iyzico_conversation_id')->label('Conversation ID')->copyable()->placeholder('—'),
                    TextEntry::make('currency')->label('Para Birimi'),
                    TextEntry::make('updated_at')->label('Son Güncelleme')->dateTime('d.m.Y H:i'),
                ]),
        ]);
    }
}
