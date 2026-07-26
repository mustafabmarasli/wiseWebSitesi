<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\Csv;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CouponStatsWidget extends TableWidget
{
    protected static ?int $sort = 12;

    protected static ?string $heading = 'Kupon Kullanım İstatistikleri';

    protected int | string | array $columnSpan = 'full';

    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->statsQuery())
            ->defaultSort('total_uses', 'desc')
            ->columns([
                TextColumn::make('coupon_code')
                    ->label('Kupon Kodu')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_uses')
                    ->label('Kullanım Sayısı')
                    ->sortable(),
                TextColumn::make('total_discount')
                    ->label('Toplam İndirim')
                    ->sortable()
                    ->money('TRY'),
            ])
            ->headerActions([
                Action::make('export-csv')
                    ->label('CSV İndir')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn (): StreamedResponse => $this->exportToCsv()),
            ]);
    }

    /**
     * Kupon bazında toplanmış sipariş istatistikleri.
     */
    private function statsQuery()
    {
        return Order::query()
            ->whereNotNull('coupon_code')
            ->whereIn('status', ['paid', 'shipped', 'delivered'])
            ->select(
                DB::raw('MIN(id) as id'),
                'coupon_code',
                DB::raw('count(*) as total_uses'),
                DB::raw('sum(discount_amount) as total_discount'),
            )
            ->groupBy('coupon_code');
    }

    public function exportToCsv(): StreamedResponse
    {
        $rows = [['Kupon Kodu', 'Kullanım Sayısı', 'Toplam İndirim (TL)']];

        foreach ($this->statsQuery()->get() as $row) {
            $rows[] = [$row->coupon_code, $row->total_uses, $row->total_discount];
        }

        return Csv::download('kupon_istatistikleri_' . now()->format('Y-m-d') . '.csv', $rows);
    }
}
