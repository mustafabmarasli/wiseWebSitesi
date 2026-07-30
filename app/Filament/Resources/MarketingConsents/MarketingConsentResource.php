<?php

namespace App\Filament\Resources\MarketingConsents;

use App\Filament\Resources\MarketingConsents\Pages\ListMarketingConsents;
use App\Filament\Resources\MarketingConsents\Tables\MarketingConsentsTable;
use App\Models\MarketingConsent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Ticari elektronik ileti onayları.
 *
 * Yalnızca GÖRÜNTÜLENİR. Onay panelden elle "verilemez": onayın müşteriden
 * alınmış olması gerekir, panelden işaretlemek onay yerine geçmez ve
 * uyuşmazlıkta ispat edilemez.
 */
class MarketingConsentResource extends Resource
{
    protected static ?string $model = MarketingConsent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationLabel = 'İleti Onayları';

    protected static ?string $modelLabel = 'İleti Onayı';

    protected static ?string $pluralModelLabel = 'Ticari İleti Onayları';

    protected static ?int $navigationSort = 26;

    public static function table(Table $table): Table
    {
        return MarketingConsentsTable::configure($table);
    }

    /** Onay yalnızca müşterinin kendi eyleminden doğar. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingConsents::route('/'),
        ];
    }
}
