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
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    // Kategorisiz ürün sitede hiçbir yerde görünmez: anasayfa
                    // ve arama kategoriye göre filtreliyor, detay sayfası
                    // kategori adını yazıyor.
                    ->helperText('Zorunlu — ürünün hangi mağazada (Elektronik / Sağlık) görüneceğini kategori belirler.'),

                TextInput::make('name')
                    ->label('Ürün Adı')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->label('Adres (slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Zorunlu — ürünün adresi (/urun/...). Yayındaki bir ürünün slug\'ını DEĞİŞTİRMEYİN, eski bağlantılar ve arama sıralaması kırılır.'),
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
                    ->label('Fiyat')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₺')
                    // Sepet ve sipariş hesabı bu alan üzerinden yürüyor;
                    // boş kalırsa ürün sepete eklenemez.
                    ->helperText('Zorunlu — satışta geçerli fiyat. Sepet ve sipariş toplamı bunu kullanır.'),

                TextInput::make('eski_fiyat')
                    ->label('Eski Fiyat')
                    ->numeric()
                    ->minValue(0)
                    ->default(null)
                    ->prefix('₺')
                    ->helperText('İsteğe bağlı. Fiyattan BÜYÜK yazarsanız üstü çizili gösterilir ve indirim yüzdesi hesaplanır. İndirim yoksa boş bırakın.'),

                TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Zorunlu — 0 yazarsanız ürün "Tükendi" görünür ve "Stok Gelince Haber Ver" düğmesi çıkar. Boş bırakılamaz çünkü stok karşılaştırmaları sayı bekliyor.'),
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
                // Zorunluluk KALDIRILDI: bu bir sayaç ama sistem onu hiçbir
                // yerde artırmıyor, elle giriliyor. Yöneticiyi her kayıtta
                // sayı yazmaya zorlamanın bir karşılığı yoktu.
                TextInput::make('satis_sayisi')
                    ->label('Satış Sayısı')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->dehydrateStateUsing(fn ($state) => $state ?? 0)
                    ->helperText('Anasayfadaki "Popüler Ürünler" sıralamasını belirler. Otomatik artmaz, elle girilir. Boş bırakılırsa 0 sayılır.'),
                TextInput::make('meta_title')
                    ->default(null),
                Textarea::make('meta_description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
