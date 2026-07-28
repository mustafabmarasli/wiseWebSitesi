<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('channel')
                    ->options([
                        'electronics' => 'Elektronik',
                        'health' => 'Sağlık',
                    ])
                    ->required()
                    ->default('electronics')
                    ->label('Kanal / Bölüm'),
                TextInput::make('google_product_category')
                    ->label('Google Ürün Kategorisi')
                    ->maxLength(20)
                    ->helperText('Google taksonomi kimliği. Örn: Elektronik bileşenler için 3853. Boş bırakılırsa Google kendi tahmin eder. Liste: support.google.com/merchants/answer/6324436')
                    ->placeholder('3853'),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->image()
                    ->disk('public')
                    ->directory('categories'),
            ]);
    }
}
