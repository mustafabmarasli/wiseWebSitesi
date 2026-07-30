<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
// DİKKAT: `Filament\Forms\Set` DEĞİL — Filament v5'te taşındı. Eski yol
// closure imzasında TypeError'a düşüyor; panelde ürün adı yazıp alandan
// çıkınca "yüklenirken hata oluştu" görünüyordu.
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('brand')
                    ->label('Marka')
                    ->maxLength(255)
                    ->helperText('Google Merchant için. Örn: Espressif, DFRobot, DMV. Boş bırakılırsa akışta "tanımlayıcı yok" olarak işaretlenir.'),
                TextInput::make('gtin')
                    ->label('Barkod (GTIN/EAN)')
                    ->maxLength(50)
                    ->helperText('Varsa ürünün barkodu. Google en güçlü eşleştirmeyi bununla yapar; yoksa boş bırakın.'),
                RichEditor::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                KeyValue::make('features')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₺'),
                TextInput::make('eski_fiyat')
                    ->numeric()
                    ->default(null)
                    ->prefix('₺'),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image_path')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->label('Ana Görsel'),
                FileUpload::make('additional_images')
                    ->image()
                    ->multiple()
                    ->disk('public')
                    ->directory('products')
                    ->label('Ek Görseller (Galeri)'),
                TextInput::make('rating')
                    ->numeric()
                    ->nullable()
                    ->default(0.0)
                    ->dehydrateStateUsing(fn ($state) => $state ?? 0.0),
                Toggle::make('is_featured')
                    ->label('Vitrinde Göster')
                    ->default(false),
                TextInput::make('satis_sayisi')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('meta_title')
                    ->default(null),
                Textarea::make('meta_description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
