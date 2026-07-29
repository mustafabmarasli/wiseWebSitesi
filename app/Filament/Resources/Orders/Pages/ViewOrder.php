<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderFulfiller;
use App\Support\OrderExporter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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

        return "Sipariş {$order->display_number} — {$order->full_name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            // Havale/EFT siparişlerinde ödeme banka hesabına elden geçtiği için
            // sistem bunu kendiliğinden bilemez. Stok düşümü ve kupon sayacı bu
            // onayla işlenir — kart ödemelerindeki callback ile aynı kod yolu.
            Action::make('confirmPayment')
                ->label('Ödeme Geldi, Onayla')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->visible(fn (Order $record): bool => $record->isBankTransfer()
                    && $record->status === OrderStatus::Pending->value)
                ->requiresConfirmation()
                ->modalHeading('Ödemeyi onaylıyor musunuz?')
                ->modalDescription(fn (Order $record): string => number_format((float) $record->total_amount, 2, ',', '.')
                    . ' ₺ tutarındaki havale hesabınıza geçtiyse onaylayın. Sipariş "Ödendi" durumuna geçer, '
                    . 'ürünlerin stoğu düşer ve müşteriye onay e-postası gönderilir. Bu işlem geri alınamaz.')
                ->modalSubmitActionLabel('Evet, ödeme geldi')
                ->action(function (Order $record): void {
                    $fulfiller = new OrderFulfiller();

                    if (! $fulfiller->markPaid($record)) {
                        Notification::make()
                            ->title('Sipariş zaten işlenmiş')
                            ->body('Bu siparişin durumu "Ödeme Bekliyor" değil, bir işlem yapılmadı.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $fulfiller->sendConfirmationMails($record->refresh(), notifyAdmin: false);

                    Notification::make()
                        ->title('Ödeme onaylandı')
                        ->body('Stok düşüldü ve müşteriye bilgilendirme e-postası gönderildi.')
                        ->success()
                        ->send();
                }),

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
