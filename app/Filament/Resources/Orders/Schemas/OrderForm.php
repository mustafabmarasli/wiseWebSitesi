<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->default(null),
                TextInput::make('first_name')
                    ->disabled()
                    ->required(),
                TextInput::make('last_name')
                    ->disabled()
                    ->required(),
                TextInput::make('email')
                    ->label('E-posta Adresi')
                    ->email()
                    ->disabled()
                    ->required(),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->disabled()
                    ->required(),
                Textarea::make('address')
                    ->label('Teslimat Adresi')
                    ->disabled()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->label('Şehir')
                    ->disabled()
                    ->required(),
                TextInput::make('zip_code')
                    ->label('Posta Kodu')
                    ->disabled()
                    ->default(null),
                TextInput::make('identity_number')
                    ->label('TC Kimlik No')
                    ->disabled()
                    ->default(null),
                TextInput::make('payment_method')
                    ->label('Ödeme Yöntemi')
                    ->disabled()
                    ->default('iyzico'),
                TextInput::make('shipping_method')
                    ->label('Kargo Yöntemi')
                    ->required()
                    ->default('Standart Kargo'),
                TextInput::make('shipping_cost')
                    ->label('Kargo Ücreti')
                    ->disabled()
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('₺'),
                DateTimePicker::make('estimated_delivery_at')
                    ->label('Tahmini Teslimat Tarihi'),
                TextInput::make('total_amount')
                    ->label('Toplam Tutar')
                    ->disabled()
                    ->required()
                    ->numeric()
                    ->prefix('₺'),
                TextInput::make('currency')
                    ->disabled()
                    ->required()
                    ->default('TRY'),
                Select::make('status')
                    ->label('Sipariş Durumu')
                    ->options([
                        'pending' => 'Ödeme Bekliyor',
                        'paid' => 'Ödendi / Hazırlanıyor',
                        'shipped' => 'Kargoya Verildi',
                        'delivered' => 'Teslim Edildi',
                        'failed' => 'Ödeme Başarısız',
                        'review' => 'İnceleme Gerekiyor (Tutar Uyuşmazlığı)',
                        'refunded' => 'İade Edildi',
                        'cancelled' => 'İptal Edildi',
                    ])
                    ->default('pending'),
                TextInput::make('iyzico_payment_id')
                    ->label('iyzico Ödeme ID')
                    ->disabled()
                    ->default(null),
                TextInput::make('iyzico_conversation_id')
                    ->label('iyzico İşlem ID')
                    ->disabled()
                    ->default(null),
                TextInput::make('iyzico_payment_status')
                    ->label('iyzico Durum')
                    ->disabled()
                    ->default(null),
                TextInput::make('coupon_code')
                    ->label('Kullanılan Kupon')
                    ->disabled()
                    ->default(null),
                TextInput::make('discount_amount')
                    ->label('İndirim Tutarı')
                    ->disabled()
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('₺'),
            ]);
    }
}
