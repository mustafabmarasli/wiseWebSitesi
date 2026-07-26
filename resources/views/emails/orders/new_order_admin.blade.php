<x-mail::message>
# Sayın Yönetici,

Sitenizden yeni bir sipariş verildi ve ödemesi başarıyla tamamlandı.

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

### Müşteri ve Teslimat Bilgileri
- **Müşteri Adı Soyadı:** {{ $order->full_name }}
- **Telefon:** {{ $order->phone }}
- **E-posta:** {{ $order->email }}
- **Adres:** {{ $order->address }}
- **Şehir/Posta Kodu:** {{ !empty($order->zip_code) ? $order->zip_code : '' }} {{ $order->city }}
- **Ödeme Yöntemi:** {{ $order->payment_method }}
- **TC Kimlik Numarası:** {{ $order->identity_number ?? 'Belirtilmedi' }}

Detayları görüntülemek ve siparişi yönetmek için yönetim panelini ziyaret edebilirsiniz.

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Otomasyon Sistemi**
</x-mail::message>
