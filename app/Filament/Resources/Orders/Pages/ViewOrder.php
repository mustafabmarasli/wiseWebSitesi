<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\OrderExporter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        /** @var Order $order */
        $order = $this->getRecord();

        return "Sipariş #{$order->id} — {$order->full_name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Bu Siparişi Excel İndir')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => OrderExporter::download(
                    Collection::make([$this->getRecord()]),
                    'siparis_' . $this->getRecord()->id . '.csv'
                )),
            EditAction::make(),
        ];
    }
}
