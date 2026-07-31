<?php

namespace App\Filament\Resources\ProductReviews;

use App\Filament\Resources\ProductReviews\Pages\ListProductReviews;
use App\Filament\Resources\ProductReviews\Tables\ProductReviewsTable;
use App\Models\ProductReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Yalnızca GÖRÜNTÜLENİR ve ONAYLANIR/REDDEDİLİR — panelden yorum
 * OLUŞTURULAMAZ. Yorum yalnızca gerçek bir satın alma+teslimattan doğar
 * (bkz. Product::canBeReviewedBy()); panelden elle eklemek bu güvenceyi
 * anlamsız kılardı.
 */
class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Ürün Yorumları';

    protected static ?string $modelLabel = 'Yorum';

    protected static ?string $pluralModelLabel = 'Ürün Yorumları';

    protected static ?int $navigationSort = 27;

    public static function table(Table $table): Table
    {
        return ProductReviewsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductReviews::route('/'),
        ];
    }

    /** Onay bekleyen sayısı menüde rozet olarak görünür — gözden kaçmasın. */
    public static function getNavigationBadge(): ?string
    {
        $bekleyen = ProductReview::where('status', 'pending')->count();

        return $bekleyen > 0 ? (string) $bekleyen : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
