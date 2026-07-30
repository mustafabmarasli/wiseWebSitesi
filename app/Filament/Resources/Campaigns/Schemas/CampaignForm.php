<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\Campaign;
use App\Models\MarketingConsent;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kampanya')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Kampanya Adı')
                        ->required()
                        ->maxLength(120)
                        ->helperText('Yalnızca panelde görünür, müşteriye gitmez.'),

                    Select::make('channel')
                        ->label('Kanal')
                        ->options(Campaign::KANALLAR)
                        ->required()
                        ->default('email')
                        ->live()
                        // Gönderim yalnızca o kanalda ONAY VERMİŞ kişilere
                        // yapılır; liste elle seçilemez.
                        ->helperText(fn (Get $get): string => 'Şu an bu kanalda onaylı '
                            . MarketingConsent::granted()->channel($get('channel') ?? 'email')->count()
                            . ' alıcı var. Gönderim yalnızca onlara yapılır.'),

                    TextInput::make('subject')
                        ->label('E-posta Konusu')
                        ->maxLength(150)
                        ->visible(fn (Get $get) => $get('channel') === 'email')
                        ->required(fn (Get $get) => $get('channel') === 'email')
                        ->columnSpanFull(),

                    RichEditor::make('body')
                        ->label('İleti Metni')
                        ->required()
                        ->visible(fn (Get $get) => $get('channel') === 'email')
                        ->helperText('Abonelikten çıkış bağlantısı e-postanın altına OTOMATİK eklenir; ayrıca yazmanıza gerek yok.')
                        ->columnSpanFull(),

                    Textarea::make('body')
                        ->label('SMS Metni')
                        ->required()
                        ->rows(4)
                        ->maxLength(400)
                        ->visible(fn (Get $get) => $get('channel') === 'sms')
                        // Türkçe karakter SMS'i UCS-2'ye düşürüyor ve
                        // parça başına 160 yerine 70 karakter kalıyor.
                        ->helperText('Çıkış bağlantısı metnin sonuna otomatik eklenir. Türkçe karakter (ç, ğ, ı, ö, ş, ü) kullanırsanız SMS parça başına 160 değil 70 karakter sayılır ve kontör maliyeti artar.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
