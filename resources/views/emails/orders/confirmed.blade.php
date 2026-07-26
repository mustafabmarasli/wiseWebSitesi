<x-mail::message>
# Sayın {{ $order->full_name }},

Siparişiniz başarıyla alınmıştır ve hazırlık sürecine başlanmıştır. Bizi tercih ettiğiniz için teşekkür ederiz!

### Sipariş Özeti (Sipariş No: #{{ $order->id }})

<x-mail::table>
| Ürün Adı | Adet | Fiyat | Toplam |
| :--- | :---: | :---: | :---: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2, ',', '.') }} TL | {{ number_format($item->total_price, 2, ',', '.') }} TL |
@endforeach
| **Kargo** | | | **{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 2, ',', '.') . ' TL' : 'Ücretsiz' }}** |
| **Genel Toplam** | | | **{{ number_format($order->total_amount, 2, ',', '.') }} TL** |
</x-mail::table>

### Teslimat ve Fatura Bilgileri
- **Alıcı:** {{ $order->full_name }}
- **Telefon:** {{ $order->masked_phone }}
- **E-posta:** {{ $order->masked_email }}
- **Adres:** {{ $order->address }}
- **Şehir/Posta Kodu:** {{ !empty($order->zip_code) ? $order->zip_code : '' }} {{ $order->city }}
- **Ödeme Yöntemi:** {{ $order->payment_method }}
- **Tahmini Teslim Tarihi:** {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d.m.Y') : '3 İş Günü' }}

@if ($order->user_id)
Siparişinizin durumunu dilediğiniz an üye girişi yaparak "Hesabım > Siparişlerim" sayfasından takip edebilirsiniz.

<x-mail::button :url="route('profile.order-detail', $order->id)">
Siparişi Görüntüle
</x-mail::button>
@else
Aşağıdaki bağlantı size özeldir ve {{ $order->created_at->addDays(7)->format('d.m.Y') }} tarihine kadar geçerlidir.

<x-mail::button :url="\Illuminate\Support\Facades\URL::temporarySignedRoute('payment.success', $order->created_at->addDays(7), ['order' => $order->id])">
Siparişi Görüntüle
</x-mail::button>
@endif

Herhangi bir sorunuz olması durumunda [iletisim@wisesolutions.com.tr](mailto:iletisim@wisesolutions.com.tr) adresinden veya destek hattımızdan bize ulaşabilirsiniz.

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Team**
</x-mail::message>
