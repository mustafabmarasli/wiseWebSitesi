<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Gönderen Bilgileri')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')->label('Ad Soyad'),
                    TextEntry::make('email')->label('E-posta')->copyable(),
                    TextEntry::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i'),
                    TextEntry::make('ip_address')->label('IP Adresi')->placeholder('—'),
                ]),

            Section::make('Mesaj')
                ->schema([
                    TextEntry::make('subject')->label('Konu'),
                    TextEntry::make('message')->label('Mesaj')->prose()->columnSpanFull(),
                ]),
        ]);
    }
}
