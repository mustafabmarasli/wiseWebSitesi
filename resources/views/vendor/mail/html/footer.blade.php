<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{-- Logo mutlak URL ile verilir: e-posta istemcisi siteden bagimsiz calisir.
     Dosya adinda bosluk/parantez olmayan bir kopya kullaniliyor; ozgun ad
     bazi istemcilerde bozuluyordu. --}}
<img src="{{ url('img/logo.jpg') }}" alt="{{ config('app.name') }}" width="120"
     style="width: 120px; max-width: 120px; height: auto; display: block; margin: 0 auto 16px; border: 0;">

{{ Illuminate\Mail\Markdown::parse($slot) }}

<p style="margin: 12px 0 0; font-size: 12px; color: #b0adc5; line-height: 1.5;">
<a href="{{ url('/') }}" style="color: #b0adc5; text-decoration: underline;">wisesolutions.com.tr</a>
&nbsp;·&nbsp;
<a href="mailto:{{ config('mail.from.address') }}" style="color: #b0adc5; text-decoration: underline;">{{ config('mail.from.address') }}</a>
</p>
</td>
</tr>
</table>
</td>
</tr>
