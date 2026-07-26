<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

/**
 * Dosya adından ürün eşleştirerek toplu görsel yükleme.
 *
 * Görseller tarayıcıda WebP'ye çevrilip küçültülerek gönderilir; sunucuda
 * GD/Imagick gerektirmez ve yükleme çok daha hızlı olur.
 */
class BulkImageUpload extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Toplu Görsel Yükle';

    protected static ?string $title = 'Toplu Görsel Yükle';

    protected static ?int $navigationSort = 25;

    protected string $view = 'filament.pages.bulk-image-upload';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    #[Validate(['files.*' => 'image|max:5120'])]
    public array $files = [];

    /** @var array<int, array{name: string, status: string, message: string}> */
    public array $results = [];

    /**
     * auto    = ilk görsel ana görsel, kalanlar galeri
     * main    = hepsi ana görsel (üzerine yazar)
     * gallery = hepsi galeriye eklenir
     */
    public string $mode = 'auto';

    /** Yüklemeden önce ürünün mevcut galerisini boşalt. */
    public bool $replaceGallery = false;

    public function getSubheading(): ?string
    {
        return 'Dosya adı ürünün URL adresiyle (slug) eşleşmelidir. Görseller yüklenmeden önce WebP formatına çevrilip küçültülür.';
    }

    /**
     * Yüklenen dosyaları ürün bazında gruplayıp kaydeder.
     *
     * Aynı ürüne ait birden fazla görsel tek seferde gelebilir; bu yüzden
     * dosyalar önce gruplanır, sonra sıra numarasına göre işlenir.
     */
    public function save(): void
    {
        $this->validate();

        if (empty($this->files)) {
            $this->results = [];

            return;
        }

        /** @var array<int, array{product: Product, files: array}> $groups */
        $groups  = [];
        $results = [];

        foreach ($this->files as $file) {
            $original = $file->getClientOriginalName();
            [$slug, $index] = $this->parseFilename($original);

            $product = Product::where('slug', $slug)->first();

            if (!$product) {
                $results[] = [
                    'name'    => $original,
                    'status'  => 'error',
                    'message' => "\"{$slug}\" adresli ürün bulunamadı.",
                ];

                continue;
            }

            $groups[$product->id]['product']  = $product;
            $groups[$product->id]['files'][]  = ['file' => $file, 'name' => $original, 'index' => $index];
        }

        foreach ($groups as $group) {
            foreach ($this->saveGroup($group['product'], $group['files']) as $result) {
                $results[] = $result;
            }
        }

        $this->results = $results;
        $this->files   = [];

        $ok      = collect($results)->where('status', 'ok')->count();
        $hatali  = count($results) - $ok;

        Notification::make()
            ->title("{$ok} görsel yüklendi" . ($hatali ? ", {$hatali} eşleşmedi" : ''))
            ->status($hatali ? 'warning' : 'success')
            ->send();
    }

    /**
     * Bir ürüne ait görsel grubunu kaydeder.
     *
     * @param  array<int, array{file: UploadedFile, name: string, index: int}>  $files
     * @return array<int, array{name: string, status: string, message: string}>
     */
    private function saveGroup(Product $product, array $files): array
    {
        // Numarasız dosya (index 0) başa gelsin; ana görsel o olacak
        usort($files, fn ($a, $b) => $a['index'] <=> $b['index']);

        $gallery = $this->replaceGallery ? [] : ($product->additional_images ?? []);
        $results = [];
        $ilk     = true;

        foreach ($files as $item) {
            $path = $item['file']->storePublicly('products', 'public');

            $anaGorselOlsun = match ($this->mode) {
                'main'    => true,
                'gallery' => false,
                default   => $ilk,   // auto
            };

            if ($anaGorselOlsun) {
                $product->image_path = $path;
                $hedef = 'ana görsel';
            } else {
                $gallery[] = $path;
                $hedef = 'galeri';
            }

            $results[] = [
                'name'    => $item['name'],
                'status'  => 'ok',
                'message' => "{$product->name} — {$hedef}",
            ];

            $ilk = false;
        }

        $product->additional_images = array_values(array_unique($gallery));
        $product->save();

        return $results;
    }

    /**
     * Dosya adından ürün slug'ı ve sıra numarasını çıkarır.
     *
     * "esp32-devkit.webp"    -> ['esp32-devkit', 0]
     * "esp32-devkit-2.webp"  -> ['esp32-devkit', 2]   (o adla ürün yoksa)
     * "ESP32 DevKit.jpg"     -> ['esp32-devkit', 0]
     *
     * @return array{0: string, 1: int}
     */
    private function parseFilename(string $filename): array
    {
        $slug = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        // Tam eşleşme varsa numara ayıklamaya kalkma:
        // "esp32-c6" gibi adı rakamla biten ürünler bozulmasın.
        if (Product::where('slug', $slug)->exists()) {
            return [$slug, 0];
        }

        if (preg_match('/^(.*)-(\d+)$/', $slug, $m) && Product::where('slug', $m[1])->exists()) {
            return [$m[1], (int) $m[2]];
        }

        return [$slug, 0];
    }

    /**
     * Hangi ürüne hangi dosya adının gerektiğini gösteren liste.
     *
     * Slug'ları ezberlemek mümkün olmadığı için sayfada referans olarak sunulur;
     * görseli olmayanlar başa alınır.
     *
     * @return \Illuminate\Support\Collection<int, array{ad: string, dosya: string, gorselVar: bool}>
     */
    public function getProductReference()
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image_path'])
            ->map(fn (Product $p) => [
                'ad'        => $p->name,
                'dosya'     => $p->slug . '.jpg',
                'gorselVar' => filled($p->image_path),
            ])
            ->sortBy('gorselVar')
            ->values();
    }
}
