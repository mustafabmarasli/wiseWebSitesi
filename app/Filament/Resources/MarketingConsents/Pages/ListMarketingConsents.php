<?php

namespace App\Filament\Resources\MarketingConsents\Pages;

use App\Filament\Resources\MarketingConsents\MarketingConsentResource;
use Filament\Resources\Pages\ListRecords;

class ListMarketingConsents extends ListRecords
{
    protected static string $resource = MarketingConsentResource::class;

    public function getSubheading(): ?string
    {
        return 'Gönderim yapmadan önce onayların İYS\'ye yüklenmiş olması gerekir. '
            . 'Onaysız kişiye pazarlama iletisi göndermek 6563 sayılı kanuna aykırıdır.';
    }
}
