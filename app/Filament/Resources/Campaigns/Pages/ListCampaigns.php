<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use App\Models\Setting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Yeni Kampanya'),
        ];
    }

    /** Ana şalter kapalıyken bunu görmeden gönderime kalkışılmasın. */
    public function getSubheading(): ?string
    {
        if (! Setting::current()->marketing_sending_enabled) {
            return '⚠ Toplu gönderim KAPALI. Taslak hazırlayabilir, deneme gönderebilirsiniz; '
                . 'gerçek gönderim için Site Ayarları → Bildirimler bölümünden açmanız gerekir.';
        }

        return 'Gönderim yalnızca ilgili kanalda onay vermiş kişilere yapılır. '
            . 'Her iletiye abonelikten çıkış bağlantısı otomatik eklenir.';
    }
}
