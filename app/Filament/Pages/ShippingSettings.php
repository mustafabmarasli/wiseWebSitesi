<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\TelegramNotifier;
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
            'consulting_enabled',
            'new_customer_telegram_enabled',
            'bank_transfer_enabled',
            'bank_transfer_discount_percent',
            'bank_account_holder',
            'bank_name',
            'bank_iban',
            'bank_transfer_note',
            'card_payment_enabled',
            'identity_required_threshold',
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

                Section::make('Ödeme Yöntemleri')
                    ->description('Müşteriye ödeme adımında hangi seçeneklerin sunulacağı. En az biri açık olmalı; ikisi de kapalıysa sipariş alınamaz.')
                    ->schema([
                        Toggle::make('bank_transfer_enabled')
                            ->label('Havale / EFT ile ödeme')
                            ->helperText('Açık olsa bile hesap adı ve IBAN boşsa seçenek müşteriye gösterilmez.')
                            ->live(),

                        TextInput::make('bank_transfer_discount_percent')
                            ->label('Havale / EFT İndirimi')
                            ->helperText('Havale ile ödeyen müşteriye uygulanacak yüzde indirim. İndirim istemiyorsanız 0 yazın. Kupon indirimi düşüldükten sonraki tutar üzerinden hesaplanır, kargo bu indirime dahil değildir.')
                            ->suffix('%')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->required(),

                        TextInput::make('bank_account_holder')
                            ->label('Hesap Adı / Şirket Tam Ünvanı')
                            ->helperText('Müşterinin havale ekranında göreceği ad. Bankadaki hesap ünvanıyla birebir aynı olmalı.')
                            ->maxLength(255)
                            ->placeholder('Wise Solutions ... Ltd. Şti.')
                            ->required(fn ($get) => (bool) $get('bank_transfer_enabled')),

                        TextInput::make('bank_name')
                            ->label('Banka Adı')
                            ->maxLength(255)
                            ->placeholder('Ziraat Bankası'),

                        TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->helperText('TR ile başlayan 26 haneli IBAN. Boşluklu yazabilirsiniz, kaydedilirken temizlenir.')
                            ->maxLength(40)
                            ->placeholder('TR00 0000 0000 0000 0000 0000 00')
                            ->required(fn ($get) => (bool) $get('bank_transfer_enabled'))
                            ->rule('regex:/^TR[0-9 ]{24,32}$/i')
                            ->validationMessages(['regex' => 'IBAN "TR" ile başlamalı ve 26 haneli olmalıdır.']),

                        Textarea::make('bank_transfer_note')
                            ->label('Ek Not (isteğe bağlı)')
                            ->helperText('Havale ekranında ve e-postada banka bilgilerinin altında görünür.')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Ödemeniz hafta içi 16:00\'a kadar yapılırsa aynı gün kargolanır.'),

                        Toggle::make('card_payment_enabled')
                            ->label('Kredi / Banka Kartı ile ödeme (iyzico)')
                            ->helperText('Kapalıyken müşteriye "Çok Yakında" olarak pasif gösterilir. iyzico başvurunuz onaylandığında burayı açmanız yeterli.'),
                    ]),

                Section::make('Fatura ve Kimlik Bilgisi')
                    ->description('Vergi mükellefi olmayan nihai tüketiciye kesilen faturada TC Kimlik No zorunlu değildir; yalnızca tutar fatura düzenleme haddini aştığında gerekir. Bu had her yıl yeniden değerleme ile artar.')
                    ->schema([
                        TextInput::make('identity_required_threshold')
                            ->label('TC Kimlik No İsteme Sınırı')
                            ->helperText('Sipariş tutarı bu değerin üzerindeyse TC Kimlik No zorunlu olur, altındaysa isteğe bağlı. Ticari faturada ve kartla ödemede tutara bakılmaksızın zorunludur. 2025: 9.900 ₺ — 2026: 12.000 ₺. Her siparişte istemek için 0 yazın.')
                            ->prefix('₺')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000000)
                            ->default(12000)
                            ->required(),
                    ]),

                Section::make('Bölümler')
                    ->description('Sitede hangi bölümlerin görüneceğini buradan açıp kapatabilirsiniz.')
                    ->schema([
                        Toggle::make('consulting_enabled')
                            ->label('Danışmanlık ve Dış Ticaret bölümü')
                            ->helperText('Kapalıyken portal sayfasındaki üçüncü bölme, üst menü, alt bilgi ve mobil menüdeki bağlantılar gizlenir; /danismanlik adresi de 404 döner.'),
                    ]),

                Section::make('Bildirimler')
                    ->description('Telegram bildirimleri. Sipariş bildirimi her zaman gider; aşağıdaki isteğe bağlıdır.')
                    ->schema([
                        Toggle::make('new_customer_telegram_enabled')
                            ->label('Yeni müşteri kaydında Telegram bildirimi')
                            ->helperText($this->telegramDurumu()),
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
                'consulting_enabled'      => (bool) ($data['consulting_enabled'] ?? false),
                'new_customer_telegram_enabled' => (bool) ($data['new_customer_telegram_enabled'] ?? false),
                'bank_transfer_enabled'   => (bool) ($data['bank_transfer_enabled'] ?? false),
                'card_payment_enabled'    => (bool) ($data['card_payment_enabled'] ?? false),
                'bank_transfer_discount_percent' => $data['bank_transfer_discount_percent'] ?? 0,
                'bank_account_holder'     => $data['bank_account_holder'] ?? null,
                'bank_name'               => $data['bank_name'] ?? null,
                // IBAN boşluklu girilebilir; kopyala düğmesinin doğru çalışması ve
                // kayıtların tek biçim olması için boşluklar temizlenir.
                'bank_iban'               => filled($data['bank_iban'] ?? null)
                    ? strtoupper(preg_replace('/\s+/', '', $data['bank_iban']))
                    : null,
                'bank_transfer_note'      => $data['bank_transfer_note'] ?? null,
                'identity_required_threshold' => $data['identity_required_threshold'] ?? 0,
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

    /**
     * Telegram hiç yapılandırılmamışken açık bir düğme boşa umut verir;
     * durumu düğmenin altında açıkça yazıyoruz.
     */
    private function telegramDurumu(): string
    {
        if (! (new TelegramNotifier())->isConfigured()) {
            return 'DİKKAT: Telegram bağlantısı kurulmamış (.env içinde bot anahtarı yok). '
                . 'Bu düğmeyi açsanız da bildirim gitmez.';
        }

        return 'Açıkken her yeni üyelikte adınıza Telegram mesajı gelir. '
            . 'Mesajda yalnızca ad ve e-posta yer alır.';
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
