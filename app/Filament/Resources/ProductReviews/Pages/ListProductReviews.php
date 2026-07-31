<?php

namespace App\Filament\Resources\ProductReviews\Pages;

use App\Filament\Resources\ProductReviews\ProductReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    public function getSubheading(): ?string
    {
        return 'Yorumlar yalnızca teslim edilmiş bir siparişte ürünü satın almış müşterilerden gelir. '
            . 'Onaylanmadan hiçbir yorum sitede görünmez.';
    }
}
