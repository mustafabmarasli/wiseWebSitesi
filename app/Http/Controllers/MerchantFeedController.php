<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Google Merchant Center ürün akışı (RSS 2.0 + g: ad alanı).
 *
 * Merchant Center'da: Ürünler → Akışlar → Yeni akış → "Zamanlanmış getirme"
 * seçilip bu adres verilir. Google günlük olarak kendi çeker; elle dosya
 * yüklemeye gerek kalmaz, stok ve fiyat otomatik güncellenir.
 *
 * Görseli olmayan ürünler akışa GİRMEZ — Google görselsiz ürünü reddeder ve
 * çok sayıda ret hesabın durumunu bozar.
 */
class MerchantFeedController extends Controller
{
    private const CACHE_MINUTES = 60;

    public function index(): Response
    {
        $xml = Cache::remember('merchant-feed.xml', now()->addMinutes(self::CACHE_MINUTES), fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(): string
    {
        $setting  = Setting::current();
        $kalemler = [];

        Product::with('category')
            ->orderBy('id')
            ->chunk(200, function ($products) use (&$kalemler, $setting) {
                foreach ($products as $product) {
                    // Görselsiz ürün Google tarafından reddedilir; hiç göndermeyelim.
                    if (!$product->image_url) {
                        continue;
                    }

                    $kalemler[] = $this->item($product, $setting);
                }
            });

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n"
            . '<channel>' . "\n"
            . '  <title>' . $this->esc(config('app.name')) . '</title>' . "\n"
            . '  <link>' . $this->esc(url('/')) . '</link>' . "\n"
            . '  <description>Ürün akışı</description>' . "\n"
            . implode("\n", $kalemler) . "\n"
            . '</channel>' . "\n"
            . '</rss>';
    }

    private function item(Product $product, Setting $setting): string
    {
        $aciklama = Str::limit(strip_tags($product->description ?: $product->name), 4900, '');
        $fiyat    = number_format((float) $product->price, 2, '.', '') . ' TRY';

        $satirlar = [
            'g:id'           => (string) $product->id,
            'title'          => Str::limit($product->name, 145),
            'description'    => $aciklama,
            'link'           => route('product.detail', $product->slug),
            'g:image_link'   => $product->image_url,
            'g:condition'    => 'new',
            'g:availability' => $product->stock > 0 ? 'in_stock' : 'out_of_stock',
            'g:price'        => $fiyat,
        ];

        // Ürün tanımlayıcısı: Google GTIN ister; yoksa marka + MPN kabul eder.
        // İkisi de yoksa identifier_exists=no yazılmalı, aksi halde ürün reddedilir.
        if (filled($product->gtin)) {
            $satirlar['g:gtin'] = $product->gtin;
        }

        if (filled($product->brand)) {
            $satirlar['g:brand'] = $product->brand;
            $satirlar['g:mpn']   = (string) $product->id;
        }

        if (blank($product->gtin) && blank($product->brand)) {
            $satirlar['g:identifier_exists'] = 'no';
        }

        if ($product->category?->google_product_category) {
            $satirlar['g:google_product_category'] = $product->category->google_product_category;
        }

        if ($product->category) {
            $satirlar['g:product_type'] = $product->category->name;
        }

        // Eski fiyat varsa indirimli gösterim
        if ((float) $product->eski_fiyat > (float) $product->price) {
            $satirlar['g:price']      = number_format((float) $product->eski_fiyat, 2, '.', '') . ' TRY';
            $satirlar['g:sale_price'] = $fiyat;
        }

        $xml = "  <item>\n";

        foreach ($satirlar as $etiket => $deger) {
            $xml .= "    <{$etiket}>" . $this->esc((string) $deger) . "</{$etiket}>\n";
        }

        // Galeri görselleri (Google en fazla 10 ek görsel kabul eder)
        foreach (array_slice($product->additional_image_urls, 0, 10) as $ek) {
            $xml .= '    <g:additional_image_link>' . $this->esc($ek) . "</g:additional_image_link>\n";
        }

        // Kargo: panelden girilen tutar. Ücretsiz kargo limiti aşılıyorsa 0.
        $kargo = $setting->shippingCostFor((float) $product->price);

        $xml .= "    <g:shipping>\n"
            . "      <g:country>TR</g:country>\n"
            . '      <g:price>' . number_format($kargo, 2, '.', '') . " TRY</g:price>\n"
            . "    </g:shipping>\n"
            . "  </item>";

        return $xml;
    }

    private function esc(string $deger): string
    {
        return htmlspecialchars($deger, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
