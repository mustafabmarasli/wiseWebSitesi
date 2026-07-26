<x-mail::message>
# Yeni İletişim Formu Mesajı

Web sitesindeki iletişim formundan yeni bir mesaj alındı.

- **Gönderen:** {{ $contactMessage->name }}
- **E-posta:** {{ $contactMessage->email }}
- **Konu:** {{ $contactMessage->subject }}
- **Tarih:** {{ $contactMessage->created_at->format('d.m.Y H:i') }}

**Mesaj:**

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

Bu e-postayı doğrudan yanıtlarsanız yanıtınız {{ $contactMessage->email }} adresine gider.

<x-mail::button :url="route('filament.admin.resources.contact-messages.view', $contactMessage->id)">
Panelde Görüntüle
</x-mail::button>

**Buy WISEly & Wise Solutions**
</x-mail::message>
