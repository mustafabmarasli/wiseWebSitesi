<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Select::make('type')
                    ->options(['percent' => 'Yüzde (%)', 'fixed' => 'Sabit Tutar (₺)'])
                    ->default('percent')
                    ->required(),
                TextInput::make('value')
                    ->label('Kupon Değeri')
                    ->required()
                    ->numeric(),
                TextInput::make('max_uses')
                    ->label('Maksimum Toplam Kullanım')
                    ->numeric()
                    ->default(null)
                    ->placeholder('Sınırsız'),
                TextInput::make('max_uses_per_user')
                    ->label('Kullanıcı Başına Maks. Kullanım')
                    ->numeric()
                    ->default(1)
                    ->placeholder('Sınırsız')
                    ->helperText('Her kullanıcı bu kuponu kaç kez kullanabilir? Boş bırakın = sınırsız'),
                TextInput::make('used_count')
                    ->label('Mevcut Kullanım Adedi')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                Toggle::make('active')
                    ->label('Aktif mi?')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->label('Geçerlilik Bitiş Tarihi'),
            ]);
    }
}
