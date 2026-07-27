<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ShippingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Site Ayarları';

    protected static ?string $title = 'Site Ayarları';

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
            'announcement_enabled',
            'announcement_title',
            'announcement_text',
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

                Section::make('Site Duyurusu')
                    ->description('Elektronik ve Sağlık sayfaları açıldığında ortada beliren bilgilendirme penceresi. Ziyaretçi kapattığında oturum boyunca tekrar gösterilmez.')
                    ->schema([
                        Toggle::make('announcement_enabled')
                            ->label('Duyuruyu göster')
                            ->helperText('Satışa başlayınca bu düğmeyi kapatmanız yeterli; kod değişikliği gerekmez.')
                            ->live(),

                        TextInput::make('announcement_title')
                            ->label('Başlık')
                            ->maxLength(100)
                            ->placeholder('Yakında Satıştayız'),

                        Textarea::make('announcement_text')
                            ->label('Duyuru Metni')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Sitemiz güncellemeler ve ödeme yöntemi güncellemesi nedeniyle çok yakında ürün satışına başlayacaktır.')
                            ->required(fn ($get) => (bool) $get('announcement_enabled')),
                    ]),
            ]);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            Setting::current()->update([
                'standard_shipping_cost'  => $data['standard_shipping_cost'] ?? 0,
                'free_shipping_threshold' => ($data['free_shipping_threshold'] ?? null) === ''
                    ? null
                    : $data['free_shipping_threshold'],
                'announcement_enabled'    => (bool) ($data['announcement_enabled'] ?? false),
                'announcement_title'      => $data['announcement_title'] ?? null,
                'announcement_text'       => $data['announcement_text'] ?? null,
            ]);

            Notification::make()
                ->title('Ayarlar kaydedildi.')
                ->success()
                ->send();
        } catch (\Illuminate\Database\QueryException $e) {
            Notification::make()
                ->title('Ayarlar kaydedilemedi')
                ->body('Veritabanı hatası: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Kaydet')
                ->action('save'),
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
