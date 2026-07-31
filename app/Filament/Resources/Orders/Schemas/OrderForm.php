<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
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
                TextInput::make('tracking_number')
                    ->label('Kargo Takip Numarası')
                    ->maxLength(100)
                    ->default(null)
                    ->helperText('Durumu "Kargoya Verildi" yaptığınızda müşteriye bu numarayla birlikte otomatik e-posta gider.'),
                TextInput::make('tracking_url')
                    ->label('Kargo Takip Linki')
                    ->url()
                    ->maxLength(500)
                    ->default(null)
                    // Kargo firmasına göre otomatik link ÜRETİLMEZ — her firmanın
                    // takip adresi farklı formatta. Yanlış tahmin edilen bir URL
                    // müşteriye kırık bağlantı olarak gider.
                    ->helperText('Kargo firmasının takip sayfasındaki tam adresi buraya yapıştırın (ör. https://www.yurticikargo.com/...). Boş bırakılırsa e-postada yalnızca takip numarası gösterilir, buton çıkmaz.')
                    ->columnSpanFull(),
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
                    // Tek kaynak: App\Enums\OrderStatus. Burada elle
                    // tekrarlanan liste, yeni bir durum eklenince
                    // unutulmaya çok müsaitti.
                    ->options(OrderStatus::options())
                    ->default(OrderStatus::Pending->value)
                    ->helperText('"Kargoya Verildi" seçildiğinde müşteriye otomatik e-posta gider.'),
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
