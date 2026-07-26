<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductView;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Görüntülenme İstatistikleri')
                ->description('Aynı ziyaretçinin 30 dakika içindeki tekrar ziyaretleri tek sayılır.')
                ->columns(4)
                ->schema([
                    TextEntry::make('views_today')
                        ->label('Bugün')
                        ->state(fn (Product $r): string => number_format($this->viewsSince($r, 0), 0, ',', '.')),

                    TextEntry::make('views_7')
                        ->label('Son 7 Gün')
                        ->state(fn (Product $r): string => number_format($this->viewsSince($r, 6), 0, ',', '.')),

                    TextEntry::make('views_30')
                        ->label('Son 30 Gün')
                        ->state(fn (Product $r): string => number_format($this->viewsSince($r, 29), 0, ',', '.')),

                    TextEntry::make('view_count')
                        ->label('Toplam Görüntülenme')
                        ->badge()
                        ->color('info')
                        ->state(fn (Product $r): string => number_format((int) $r->view_count, 0, ',', '.')),

                    TextEntry::make('unique_visitors')
                        ->label('Tekil Ziyaretçi')
                        ->state(fn (Product $r): string => number_format(
                            ProductView::where('product_id', $r->id)->distinct('visitor_hash')->count('visitor_hash'),
                            0, ',', '.'
                        )),

                    TextEntry::make('member_views')
                        ->label('Üye Görüntülemesi')
                        ->state(fn (Product $r): string => number_format(
                            ProductView::where('product_id', $r->id)->whereNotNull('user_id')->count(),
                            0, ',', '.'
                        )),

                    TextEntry::make('satis_sayisi')
                        ->label('Satış Adedi')
                        ->state(fn (Product $r): string => number_format((int) $r->satis_sayisi, 0, ',', '.')),

                    TextEntry::make('conversion_rate')
                        ->label('Dönüşüm Oranı')
                        ->badge()
                        ->state(fn (Product $r): string => $r->conversion_rate === null ? '—' : $r->conversion_rate . '%')
                        ->color(fn (Product $r): string => match (true) {
                            $r->conversion_rate === null => 'gray',
                            $r->conversion_rate >= 5     => 'success',
                            $r->conversion_rate >= 1     => 'warning',
                            default                      => 'danger',
                        })
                        ->helperText('Görüntülenme başına satış'),
                ]),

            Section::make('Son 14 Gün')
                ->schema([
                    TextEntry::make('daily_breakdown')
                        ->hiddenLabel()
                        ->html()
                        ->state(fn (Product $r): string => $this->dailyBars($r)),
                ]),

            Section::make('Ürün Bilgileri')
                ->columns(3)
                ->schema([
                    TextEntry::make('category.name')->label('Kategori')->placeholder('—'),
                    TextEntry::make('price')->label('Fiyat')->money('TRY'),
                    TextEntry::make('stock')
                        ->label('Stok')
                        ->badge()
                        ->color(fn (int $state): string => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),
                    TextEntry::make('slug')->label('URL')->copyable()->columnSpan(3),
                ]),
        ]);
    }

    private function viewsSince(Product $product, int $daysAgo): int
    {
        return ProductView::where('product_id', $product->id)
            ->where('created_at', '>=', now()->subDays($daysAgo)->startOfDay())
            ->count();
    }

    /**
     * Son 14 günün görüntülenmesini basit bir çubuk grafik olarak çizer.
     */
    private function dailyBars(Product $product): string
    {
        $rows = ProductView::where('product_id', $product->id)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day')
            ->all();

        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date        = now()->subDays($i);
            $key         = $date->format('Y-m-d');
            $days[$key]  = ['label' => $date->format('d.m'), 'count' => (int) ($rows[$key] ?? 0)];
        }

        $max = max(1, max(array_column($days, 'count')));

        if (array_sum(array_column($days, 'count')) === 0) {
            return '<p class="text-sm text-gray-500 dark:text-gray-400">Son 14 günde görüntülenme kaydı yok.</p>';
        }

        $bars = '';
        foreach ($days as $day) {
            $height = max(4, (int) round(($day['count'] / $max) * 100));
            $bars .= sprintf(
                '<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.35rem;min-width:0">
                    <span style="font-size:.7rem;font-weight:700;color:#6b7280">%d</span>
                    <div style="width:100%%;height:100px;display:flex;align-items:flex-end">
                        <div title="%s: %d görüntülenme" style="width:100%%;height:%d%%;background:#1B4A7A;border-radius:4px 4px 0 0"></div>
                    </div>
                    <span style="font-size:.65rem;color:#9ca3af;white-space:nowrap">%s</span>
                </div>',
                $day['count'],
                $day['label'],
                $day['count'],
                $height,
                $day['label']
            );
        }

        return '<div style="display:flex;gap:.4rem;align-items:flex-end">' . $bars . '</div>';
    }
}
