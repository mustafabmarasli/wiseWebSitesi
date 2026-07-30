<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Models\Announcement;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('İçerik')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Neden Bu Fiyatlara Satıyoruz?')
                        ->columnSpanFull(),

                    RichEditor::make('body')
                        ->label('Duyuru Metni')
                        ->helperText('Kalın yazı, madde işareti ve bağlantı kullanabilirsiniz. Uzunluk sınırı yok; çok uzarsa pencere içinde kaydırılır.')
                        ->columnSpanFull(),

                    Select::make('channel')
                        ->label('Nerede görünsün')
                        ->options(Announcement::KANALLAR)
                        ->required()
                        ->default('both'),

                    Select::make('tone')
                        ->label('Tür / Simge')
                        ->options(Announcement::TONLAR)
                        ->required()
                        ->default('info')
                        ->helperText('Uyarı (amber) bir "dikkat" havası verir; misyon veya kampanya metninde Bilgi ya da Kampanya daha uygun.'),
                ]),

            Section::make('Görsel ve Yerleşim')
                ->columns(2)
                ->schema([
                    Select::make('layout')
                        ->label('Yerleşim')
                        ->options(Announcement::YERLESIMLER)
                        ->required()
                        ->default('text')
                        ->live()
                        ->columnSpanFull(),

                    FileUpload::make('image_path')
                        ->label('Görsel')
                        ->image()
                        ->disk('public')
                        ->directory('announcements')
                        ->imagePreviewHeight('160')
                        ->maxSize(4096)
                        // Yerleşim "sadece metin" iken görsel alanı gizli:
                        // yüklenmiş ama hiç gösterilmeyen görsel kafa karıştırır.
                        ->visible(fn (Get $get) => $get('layout') !== 'text')
                        ->helperText('Önerilen: 1000 × 500 piksel yatay görsel. "Yazı görselin üzerinde" seçilirse yazının okunabilmesi için görselin üstüne otomatik karartma uygulanır.'),

                    TextInput::make('image_alt')
                        ->label('Görsel Açıklaması')
                        ->maxLength(255)
                        ->visible(fn (Get $get) => $get('layout') !== 'text')
                        ->helperText('Görme engelliler için. Görselde ne olduğunu kısaca yazın.'),
                ]),

            Section::make('Buton (isteğe bağlı)')
                ->description('İkisini birlikte doldurun; biri boşsa buton çıkmaz ve yalnızca "Anladım" görünür.')
                ->columns(2)
                ->schema([
                    TextInput::make('button_text')
                        ->label('Buton Yazısı')
                        ->maxLength(40)
                        ->placeholder('Ürünleri İncele'),

                    TextInput::make('button_url')
                        ->label('Buton Adresi')
                        ->maxLength(255)
                        ->placeholder('/elektronik')
                        ->helperText('Site içi yol (/elektronik) veya tam adres yazabilirsiniz.'),
                ]),

            Section::make('Yayın')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Yayında')
                        ->default(false)
                        ->helperText('Kapalıyken duyuru sitede görünmez ama silinmez.'),

                    TextInput::make('sort_order')
                        ->label('Sıra')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        // Aynı anda iki pencere açmak üst üste binen pencereler
                        // demek; bu yüzden kuyruk mantığı kullanılıyor.
                        ->helperText('Yayında birden fazla duyuru varsa SIRAYLA açılır: ziyaretçi birincisini kapatınca ikincisi çıkar. Küçük sayı önce gösterilir. Listede satırları sürükleyerek de sıralayabilirsiniz.'),
                ]),
        ]);
    }
}
