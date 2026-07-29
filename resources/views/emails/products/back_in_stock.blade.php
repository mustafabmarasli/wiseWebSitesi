<x-mail::message>
# Beklediğiniz ürün stokta!

**{{ $product->name }}** ürünü stoklarımıza yeniden girdi. Bu e-postayı, ürün
tükendiğinde "Stok Gelince Haber Ver" bildirimi oluşturduğunuz için alıyorsunuz.

@if ($product->price > 0)
**Güncel fiyat:** {{ number_format($product->price, 2, ',', '.') }} TL
@endif

Stok adedi sınırlı olabilir; siparişler geldiği sırayla karşılanır.

<x-mail::button :url="route('product.detail', $product->slug)">
Ürüne Git
</x-mail::button>

Herhangi bir sorunuz olması durumunda [iletisim@wisesolutions.com.tr](mailto:iletisim@wisesolutions.com.tr) adresinden bize ulaşabilirsiniz.

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Team**

<x-mail::subcopy>
Bu bildirim tek seferliktir; başka bir e-posta göndermeyeceğiz ve adresiniz
pazarlama amacıyla kullanılmaz.
</x-mail::subcopy>
</x-mail::message>
