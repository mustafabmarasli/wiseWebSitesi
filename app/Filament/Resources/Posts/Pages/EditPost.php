<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Yazıyı sitede görmek için: taslakken sayfa 404 döner,
            // bu yüzden düğme yalnızca yayındaki yazılarda çıkar.
            Action::make('siteDeGor')
                ->label('Sitede Gör')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('blog.show', $this->record->slug))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->is_published
                    && $this->record->published_at
                    && ! $this->record->published_at->isFuture()),

            DeleteAction::make(),
        ];
    }
}
