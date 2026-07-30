<x-mail::message>
{!! $campaign->body !!}

@if ($consent)
<x-mail::subcopy>
Bu iletiyi, kampanya bildirimlerine onay verdiğiniz için alıyorsunuz.
Onayınızı **ücretsiz** olarak geri çekmek için:
[Abonelikten çık]({{ $consent->unsubscribeUrl() }})

Sipariş ve kargo bildirimleri bu onaydan bağımsızdır; çıkış yapsanız da
gönderilmeye devam eder.
</x-mail::subcopy>
@else
<x-mail::subcopy>
Bu bir **deneme gönderimidir**. Gerçek gönderimde bu bölümde alıcıya özel
abonelikten çıkış bağlantısı yer alır.
</x-mail::subcopy>
@endif
</x-mail::message>
