<x-mail::message>
# Sayın {{ $order->full_name }},

Siparişinizi aldık. Aşağıdaki hesaba **{{ number_format($order->total_amount, 2, ',', '.') }} TL** tutarındaki ödemeyi yaptığınızda siparişiniz hazırlanmaya başlanacaktır.

### Ödeme Bilgileri

<x-mail::table>
| | |
| :--- | :--- |
| **Hesap Adı** | {{ $setting->bank_account_holder }} |
@if ($setting->bank_name)
| **Banka** | {{ $setting->bank_name }} |
@endif
| **IBAN** | {{ $setting->bank_iban }} |
| **Açıklama** | Sipariş No: {{ $order->display_number }} |
| **Tutar** | {{ number_format($order->total_amount, 2, ',', '.') }} TL |
</x-mail::table>

**Önemli:** Havale/EFT açıklamasına mutlaka **Sipariş No: {{ $order->display_number }}** yazınız. Açıklama olmayan ödemelerin eşleştirilmesi gecikebilir.

@if ($setting->bank_transfer_note)
{{ $setting->bank_transfer_note }}
@endif

### Sipariş Özeti (Sipariş No: {{ $order->display_number }})

<x-mail::table>
| Ürün Adı | Adet | Fiyat | Toplam |
| :--- | :---: | :---: | :---: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2, ',', '.') }} TL | {{ number_format($item->total_price, 2, ',', '.') }} TL |
@endforeach
@if ($order->discount_amount > 0)
| **Kupon İndirimi** | | | **-{{ number_format($order->discount_amount, 2, ',', '.') }} TL** |
@endif
@if ($order->bank_transfer_discount > 0)
| **Havale/EFT İndirimi** | | | **-{{ number_format($order->bank_transfer_discount, 2, ',', '.') }} TL** |
@endif
| **Kargo** | | | **{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 2, ',', '.') . ' TL' : 'Ücretsiz' }}** |
| **Ödenecek Tutar** | | | **{{ number_format($order->total_amount, 2, ',', '.') }} TL** |
</x-mail::table>

### Teslimat Bilgileri
- **Alıcı:** {{ $order->full_name }}
- **Telefon:** {{ $order->masked_phone }}
- **Adres:** {{ $order->address }}
- **Şehir/Posta Kodu:** {{ !empty($order->zip_code) ? $order->zip_code : '' }} {{ $order->city }}
- **Ödeme Yöntemi:** {{ $order->payment_method }}

Ödemeniz hesabımıza geçtiğinde size ayrıca bilgi vereceğiz.

Teşekkürler,<br>
{{ config('app.name') }}
</x-mail::message>
