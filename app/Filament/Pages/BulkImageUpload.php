<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public bool $asGallery = false;

    public function getSubheading(): ?string
    {
        return 'Dosya adı ürünün URL adresiyle (slug) eşleşmelidir. Görseller yüklenmeden önce WebP formatına çevrilip küçültülür.';
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
            // Görseli eksik olanlar önce görünsün
            ->sortBy('gorselVar')
            ->values();
    }

    /**
     * Yüklenen dosyaları ürünlerle eşleştirip kaydeder.
     */
    public function save(): void
    {
        $this->validate();

        if (empty($this->files)) {
            $this->results = [];

            return;
        }

        $results = [];

        foreach ($this->files as $file) {
            $results[] = $this->handleFile($file);
        }

        $this->results = $results;
        $this->files   = [];

        $basarili = collect($results)->where('status', 'ok')->count();
        $basarisiz = count($results) - $basarili;

        \Filament\Notifications\Notification::make()
            ->title("{$basarili} görsel yüklendi" . ($basarisiz ? ", {$basarisiz} eşleşmedi" : ''))
            ->status($basarisiz ? 'warning' : 'success')
            ->send();
    }

    /**
     * @return array{name: string, status: string, message: string}
     */
    private function handleFile(UploadedFile $file): array
    {
        $original = $file->getClientOriginalName();
        $slug     = $this->slugFromFilename($original);

        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return [
                'name'    => $original,
                'status'  => 'error',
                'message' => "\"{$slug}\" adresli ürün bulunamadı.",
            ];
        }

        $path = $file->storePublicly('products', 'public');

        if ($this->asGallery) {
            $gallery   = $product->additional_images ?? [];
            $gallery[] = $path;
            $product->update(['additional_images' => array_values(array_unique($gallery))]);
            $hedef = 'galeriye eklendi';
        } else {
            $product->update(['image_path' => $path]);
            $hedef = 'ana görsel olarak ayarlandı';
        }

        return [
            'name'    => $original,
            'status'  => 'ok',
            'message' => "{$product->name} — {$hedef}",
        ];
    }

    /**
     * Dosya adından ürün slug'ı çıkarır.
     *
     * "esp32-devkit.webp"    -> esp32-devkit
     * "esp32-devkit-2.webp"  -> esp32-devkit   (galeri için numaralı dosyalar)
     * "ESP32 DevKit.jpg"     -> esp32-devkit
     */
    private function slugFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $slug = Str::slug($name);

        // Sonundaki "-2", "-3" gibi sıra numaralarını at; tam eşleşme yoksa dene
        if (!Product::where('slug', $slug)->exists()) {
            $withoutIndex = preg_replace('/-\d+$/', '', $slug);

            if ($withoutIndex && Product::where('slug', $withoutIndex)->exists()) {
                return $withoutIndex;
            }
        }

        return $slug;
    }
}
