<x-mail::message>
# Sayın {{ $order->full_name }},

Siparişiniz kargoya verildi ve size doğru yola çıktı! 🚚

### Sipariş No: {{ $order->display_number }}

@if ($order->hasTracking())
- **Kargo Firması:** {{ $order->shipping_method ?: 'Standart Kargo' }}
- **Takip Numarası:** {{ $order->tracking_number }}
@else
- **Kargo Firması:** {{ $order->shipping_method ?: 'Standart Kargo' }}
@endif

@if ($order->tracking_url)
<x-mail::button :url="$order->tracking_url">
Kargomu Takip Et
</x-mail::button>
@endif

@if ($order->user_id)
Siparişinizin güncel durumunu dilediğiniz an üye girişi yaparak "Hesabım > Siparişlerim" sayfasından da görebilirsiniz.

<x-mail::button :url="route('profile.order-detail', $order->id)">
Sipariş Detayını Görüntüle
</x-mail::button>
@endif

Herhangi bir sorunuz olması durumunda [iletisim@wisesolutions.com.tr](mailto:iletisim@wisesolutions.com.tr) adresinden veya destek hattımızdan bize ulaşabilirsiniz.

Teşekkürler,<br>
**Buy WISEly & Wise Solutions Team**
</x-mail::message>
