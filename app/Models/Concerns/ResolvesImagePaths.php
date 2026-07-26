<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Görsel yollarını iki farklı kaynaktan çözer.
 *
 * Veritabanında iki format bir arada bulunuyor:
 *  - "img/esp8266d1/1.jpg"  → seeder'dan gelen, public/ altındaki statik dosyalar
 *  - "products/01KY....jpg" → admin panelinden yüklenen, public diskteki dosyalar
 *
 * Görüntüleme tarafı bu ayrımı bilmek zorunda kalmasın diye çözüm burada yapılır.
 */
trait ResolvesImagePaths
{
    /**
     * Ana görselin tam URL'i. Görsel yoksa null döner.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveImageUrl($this->image_path);
    }

    /**
     * Galeri görsellerinin URL listesi (çözülemeyenler atlanır).
     *
     * @return array<int, string>
     */
    public function getAdditionalImageUrlsAttribute(): array
    {
        return collect($this->additional_images ?? [])
            ->map(fn ($path) => $this->resolveImageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Tek bir yolu URL'e çevirir; dosya gerçekten yoksa null döner.
     */
    public function resolveImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Zaten tam URL olarak kaydedilmişse dokunma
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // public/ altındaki statik dosyalar (seeder verisi)
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Panelden yüklenenler: public disk (storage/app/public)
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }
}
