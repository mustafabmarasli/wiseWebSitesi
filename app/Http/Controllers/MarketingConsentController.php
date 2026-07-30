<?php

namespace App\Http\Controllers;

use App\Models\MarketingConsent;
use Illuminate\Http\Request;

/**
 * Abonelikten çıkış.
 *
 * GİRİŞ GEREKTİRMEZ ve gerektirmemeli: 6563 sayılı kanun çıkışın "kolay ve
 * ücretsiz" olmasını şart koşuyor. E-postadaki bağlantıya tıklayan kişi çoğu
 * zaman oturum açmış değildir; giriş istemek çıkışı zorlaştırmak sayılır.
 */
class MarketingConsentController extends Controller
{
    /** Çıkış sayfası: kişinin tüm kanal onayları listelenir. */
    public function show(string $token)
    {
        $consent = MarketingConsent::where('unsubscribe_token', $token)->firstOrFail();

        return view('marketing.unsubscribe', [
            'consent'  => $consent,
            'onaylar'  => $consent->siblings(),
        ]);
    }

    /**
     * Çıkışı uygular.
     *
     * `kanal` gelirse yalnızca o kanaldan, gelmezse hepsinden çıkarılır.
     * Kayıt SİLİNMEZ: çıkış talebinin de ispatlanabilmesi gerekiyor.
     */
    public function update(Request $request, string $token)
    {
        $consent = MarketingConsent::where('unsubscribe_token', $token)->firstOrFail();

        $kanal = $request->input('kanal');

        $hedefler = $kanal && array_key_exists($kanal, MarketingConsent::KANALLAR)
            ? $consent->siblings()->where('channel', $kanal)
            : $consent->siblings();

        foreach ($hedefler as $kayit) {
            if ($kayit->isGranted()) {
                $kayit->revoke();
            }
        }

        return redirect()
            ->route('marketing.unsubscribe', $token)
            ->with('success', $kanal
                ? MarketingConsent::KANALLAR[$kanal] . ' bildirimlerinden çıkarıldınız.'
                : 'Tüm ticari ileti bildirimlerinden çıkarıldınız.');
    }

    /** Fikir değiştirenler için: aynı sayfadan geri abone olabilme. */
    public function resubscribe(Request $request, string $token)
    {
        $consent = MarketingConsent::where('unsubscribe_token', $token)->firstOrFail();

        $kanal = $request->input('kanal');
        abort_unless($kanal && array_key_exists($kanal, MarketingConsent::KANALLAR), 404);

        MarketingConsent::grant(
            channel: $kanal,
            email:   $consent->email,
            phone:   $consent->phone,
            source:  'unsubscribe',
            userId:  $consent->user_id,
            ip:      $request->ip(),
        );

        return redirect()
            ->route('marketing.unsubscribe', $token)
            ->with('success', MarketingConsent::KANALLAR[$kanal] . ' bildirimlerine yeniden abone oldunuz.');
    }
}
