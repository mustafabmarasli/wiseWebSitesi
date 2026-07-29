<x-mail::message>
# Sayın Yönetici,

@if ($order->status === \App\Enums\OrderStatus::Pending->value)
Sitenizden yeni bir sipariş verildi. **Ödeme henüz alınmadı** — müşteri havale/EFT ile ödeyecek.

Para hesabınıza geçtiğinde yönetim panelinden **"Ödeme Geldi, Onayla"** düğmesine basın. Stok ancak o zaman düşer ve müşteriye onay e-postası gider. **Onaylamadan kargoya vermeyin.**
@else
Sitenizden yeni bir sipariş verildi ve ödemesi başarıyla tamamlandı.
@endif

### Sipariş Özeti (Sipariş No: {{ $order->display_number }})

<x-mail::table>
| Ürün Adı | Adet | Fiyat | Toplam |
| :--- | :---: | :---: | :---: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2, ',', '.') }} TL | {{ number_format($item->total_price, 2, ',', '.') }} TL |
@endforeach
| **Kargo** | | | **{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 2, ',', '.') . ' TL' : 'Ücretsiz' }}** |
| **Genel Toplam** | | | **{{ number_format($order->total_amount, 2, ',', '.') }} TL** |
</x-mail::table>

### Müşteri ve Teslimat Bilgileri
- **Müşteri Adı Soyadı:** {{ $order->full_name }}
- **Telefon:** {{ $order->phone }}
- **E-posta:** {{ $order->email }}
- **Adres:** {{ $order->address }}
- **Şehir/Posta Kodu:** {{ !empty($order->zip_code) ? $order->zip_code : '' }} {{ $order->city }}
- **Ödeme Yöntemi:** {{ $order->payment_method }}
@if ($order->is_corporate)
- **Fatura Tipi:** Ticari — {{ $order->company_name }} / VD: {{ $order->tax_office }} / VKN: {{ $order->tax_number }}
@endif
@if ($order->coupon_code)
- **Kullanılan Kupon:** {{ $order->coupon_code }} (-{{ number_format((float) $order->discount_amount, 2, ',', '.') }} TL)
@endif

{{-- TC Kimlik No bilerek buraya yazılmıyor: veritabanında şifreli tutulan
     bir veriyi e-posta ile düz metin göndermek KVKK açısından tutarsız olur.
     Fatura için gerektiğinde panelden veya Excel çıktısından görülebilir. --}}
TC Kimlik No dahil tüm bilgiler yönetim panelindeki sipariş detayında yer alır.

<x-mail::button :url="route('filament.admin.resources.orders.view', $order->id)">
Siparişi Panelde Aç
</x-mail::button>

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Otomasyon Sistemi**
</x-mail::message>
