<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ShippingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Kargo Ayarları';

    protected static ?string $title = 'Kargo Ayarları';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.shipping-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::current()->only([
            'standard_shipping_cost',
            'free_shipping_threshold',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Kargo Ücreti')
                    ->description('Buradaki tutar ödeme adımında sipariş toplamına eklenir.')
                    ->schema([
                        TextInput::make('standard_shipping_cost')
                            ->label('Standart Kargo Ücreti')
                            ->helperText('0 yazarsanız kargo her siparişte ücretsiz olur.')
                            ->prefix('₺')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100000)
                            ->required()
                            ->default(0),

                        TextInput::make('free_shipping_threshold')
                            ->label('Ücretsiz Kargo Alt Limiti')
                            ->helperText('Bu tutarın üzerindeki siparişlerde kargo ücreti alınmaz. Kampanyayı kapatmak için boş bırakın. Karşılaştırma indirim sonrası tutar üzerinden yapılır.')
                            ->prefix('₺')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000000)
                            ->nullable(),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::current()->update([
            'standard_shipping_cost'  => $data['standard_shipping_cost'] ?? 0,
            'free_shipping_threshold' => ($data['free_shipping_threshold'] ?? null) === ''
                ? null
                : $data['free_shipping_threshold'],
        ]);

        Notification::make()
            ->title('Kargo ayarları kaydedildi.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Kaydet')
                ->submit('save'),
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        $setting = Setting::current();
        $cost    = (float) $setting->standard_shipping_cost;

        if ($cost <= 0) {
            return 'Şu an kargo tüm siparişlerde ücretsiz.';
        }

        $threshold = $setting->free_shipping_threshold;

        if ($threshold === null) {
            return 'Şu an her siparişten ' . number_format($cost, 2, ',', '.') . ' ₺ kargo ücreti alınıyor.';
        }

        return 'Şu an ' . number_format($cost, 2, ',', '.') . ' ₺ kargo ücreti alınıyor; '
            . number_format((float) $threshold, 2, ',', '.') . ' ₺ üzeri siparişlerde ücretsiz.';
    }
}
