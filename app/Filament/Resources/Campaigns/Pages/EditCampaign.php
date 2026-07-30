<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use App\Models\Campaign;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Gönderilmiş kampanya düzenlenemez: gönderilen metnin kaydı
     * değiştirilebilirse "ne göndermiştik" sorusunun cevabı kalmaz.
     */
    protected function authorizeAccess(): void
    {
        abort_unless($this->getRecord() instanceof Campaign && $this->getRecord()->isDraft(), 403);
    }
}
