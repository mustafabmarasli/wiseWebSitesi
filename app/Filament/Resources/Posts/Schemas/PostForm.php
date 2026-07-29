<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Yazı')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        // Slug yalnızca YENİ kayıtta otomatik doldurulur:
                        // yayındaki bir yazının adresini değiştirmek gelen
                        // bağlantıları ve arama sıralamasını kırar.
                        ->afterStateUpdated(function (Set $set, ?string $state, ?Post $record) {
                            if (! $record) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        })
                        ->placeholder('Skleral lens nasıl takılır?'),

                    TextInput::make('slug')
                        ->label('Adres (slug)')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true)
                        ->helperText('Yayına aldıktan sonra DEĞİŞTİRMEYİN — eski bağlantılar kırılır.'),

                    Select::make('channel')
                        ->label('Bölüm')
                        ->options(Post::KANALLAR)
                        ->required()
                        ->default('general')
                        ->helperText('Sağlık seçilirse yazının altına tıbbi uyarı otomatik eklenir.'),

                    Textarea::make('excerpt')
                        ->label('Özet')
                        ->rows(2)
                        ->maxLength(300)
                        ->helperText('Listede ve paylaşımda görünür. Boş bırakılırsa yazının başından alınır.')
                        ->columnSpanFull(),

                    RichEditor::make('body')
                        ->label('İçerik')
                        ->required()
                        ->helperText('Ürüne bağlantı vermeyi unutmayın — rehberin okurunu ürüne götüren şey o bağlantıdır.')
                        ->columnSpanFull(),
                ]),

            Section::make('Kapak Görseli')
                ->description('Önerilen boyut: 1200 × 630 piksel. Paylaşım kartlarında da bu görsel kullanılır.')
                ->columns(2)
                ->schema([
                    FileUpload::make('cover_image')
                        ->label('Kapak')
                        ->image()
                        ->disk('public')
                        ->directory('posts')
                        ->imagePreviewHeight('160')
                        ->maxSize(4096),

                    TextInput::make('cover_alt')
                        ->label('Görsel Açıklaması')
                        ->maxLength(255)
                        ->helperText('Görme engelliler ve arama motorları için.'),
                ]),

            Section::make('Yayın')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Yayında')
                        ->default(false)
                        ->helperText('Kapalıyken yazı sitede görünmez ve site haritasına girmez.'),

                    DateTimePicker::make('published_at')
                        ->label('Yayın Tarihi')
                        ->seconds(false)
                        ->default(now())
                        ->helperText('İleri bir tarih verirseniz yazı o tarihe kadar görünmez.'),
                ]),

            Section::make('Arama Motoru (SEO)')
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Sayfa Başlığı')
                        ->maxLength(70)
                        ->helperText('Boş bırakılırsa yazı başlığı kullanılır.'),

                    Textarea::make('meta_description')
                        ->label('Sayfa Açıklaması')
                        ->rows(2)
                        ->maxLength(200)
                        ->helperText('Boş bırakılırsa özet kullanılır.'),
                ]),
        ]);
    }
}
