<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Models\Slide;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Görsel')
                ->description('Önerilen boyut: 1600 × 700 piksel. Yatay dikdörtgen olmalı — kare görseller kırpılır.')
                ->schema([
                    FileUpload::make('image_path')
                        ->label('Slayt Görseli')
                        ->image()
                        ->disk('public')
                        ->directory('slides')
                        ->imagePreviewHeight('180')
                        ->maxSize(4096)
                        ->helperText('En fazla 4 MB. Yüklemeden önce görseli 1600×700 boyutuna getirmeniz sayfayı hızlandırır.')
                        ->required(),

                    TextInput::make('image_alt')
                        ->label('Görsel Açıklaması')
                        ->maxLength(255)
                        ->helperText('Görme engelliler ve arama motorları için. Görselde ne olduğunu kısaca yazın.'),
                ]),

            Section::make('İçerik')
                ->columns(2)
                ->schema([
                    Select::make('channel')
                        ->label('Hangi sayfada görünsün')
                        ->options(Slide::KANALLAR)
                        ->required()
                        ->default('electronics'),

                    Select::make('badge_color')
                        ->label('Etiket Rengi')
                        ->options(Slide::RENKLER)
                        ->required()
                        ->default('trendyol'),

                    TextInput::make('badge')
                        ->label('Küçük Etiket')
                        ->maxLength(60)
                        ->placeholder('Gelişmiş Donanım')
                        ->helperText('Başlığın üstündeki renkli rozet. Boş bırakılabilir.'),

                    TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Mikroişlemci Geliştirme Kartları'),

                    Textarea::make('subtitle')
                        ->label('Açıklama')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),
                ]),

            Section::make('Butonlar')
                ->description('Boş bırakılan buton hiç gösterilmez. Adres olarak site içi yol (/iletisim) veya sayfa içi bağlantı (#tum-urunler) yazabilirsiniz.')
                ->columns(2)
                ->schema([
                    TextInput::make('primary_text')
                        ->label('1. Buton Yazısı')
                        ->maxLength(40)
                        ->placeholder('Şimdi Keşfet'),

                    TextInput::make('primary_url')
                        ->label('1. Buton Adresi')
                        ->maxLength(255)
                        ->placeholder('#tum-urunler'),

                    TextInput::make('secondary_text')
                        ->label('2. Buton Yazısı')
                        ->maxLength(40)
                        ->placeholder('Destek Al'),

                    TextInput::make('secondary_url')
                        ->label('2. Buton Adresi')
                        ->maxLength(255)
                        ->placeholder('/iletisim'),
                ]),

            Section::make('Yayın')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Yayında')
                        ->default(true)
                        ->helperText('Kapalıyken slayt sitede görünmez ama silinmez.'),

                    TextInput::make('sort_order')
                        ->label('Sıra')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Küçük sayı önce gösterilir.'),
                ]),
        ]);
    }
}
