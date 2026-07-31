@php
    // Yalnızca hâlâ var olan VE bu kullanıcının şu an yorum yazabildiği
    // ürünler listelenir — silinmiş ürün veya zaten yorum yapılmış ürün
    // için kırık/anlamsız bir bağlantı gitmesin.
    $yorumYazilabilirUrunler = $order->items
        ->map(fn ($item) => $item->product)
        ->filter(fn ($product) => $product && $product->canBeReviewedBy($order->user))
        ->unique('id');
@endphp

<x-mail::message>
# Sayın {{ $order->full_name }},

Siparişiniz elinize ulaştı! Umarız ürünlerimizden memnun kalmışsınızdır.

@if ($yorumYazilabilirUrunler->isNotEmpty())
Deneyiminizi diğer müşterilerimizle paylaşmak ister misiniz? Yorumunuz hem
bizim için değerli hem de sizin gibi arayan müşterilere yardımcı oluyor.

<x-mail::table>
| Ürün |  |
| :--- | :---: |
@foreach ($yorumYazilabilirUrunler as $product)
| {{ $product->name }} | [Yorum Yap]({{ route('product.detail', $product->slug) }}#yorumlar) |
@endforeach
</x-mail::table>
@endif

Herhangi bir sorunuz veya sorununuz olması durumunda [iletisim@wisesolutions.com.tr](mailto:iletisim@wisesolutions.com.tr) adresinden bize ulaşabilirsiniz.

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Team**
</x-mail::message>
