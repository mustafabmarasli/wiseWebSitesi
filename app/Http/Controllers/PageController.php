<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display the 30-degree slanted landing page.
     */
    public function landing()
    {
        return view('landing');
    }

    /**
     * Display the Consulting and Foreign Trade Page.
     */
    public function consulting()
    {
        return view('consulting');
    }

    /**
     * Display KVKK Procedure page.
     */
    public function kvkk()
    {
        return view('legal.kvkk');
    }

    /**
     * Display other procedural pages (Distance selling, Privacy, Shipping).
     */
    public function procedural($page)
    {
        $allowedPages = [
            'mesafeli-satis' => 'legal.mesafeli_satis',
            'on-bilgilendirme' => 'legal.on_bilgilendirme',
            'gizlilik-guvenlik' => 'legal.gizlilik_guvenlik',
            'cerez-politikasi' => 'legal.cerez_politikasi',
            'teslimat-iade' => 'legal.teslimat_iade',
            'kullanim-kosullari' => 'legal.kullanim_kosullari'
        ];

        if (!array_key_exists($page, $allowedPages)) {
            abort(404);
        }

        return view($allowedPages[$page]);
    }

    /**
     * Display Contact Page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required'    => 'Ad Soyad alanı zorunludur.',
            'email.required'   => 'E-posta adresi zorunludur.',
            'email.email'      => 'Geçerli bir e-posta adresi giriniz.',
            'subject.required' => 'Konu alanı zorunludur.',
            'message.required' => 'Mesaj alanı zorunludur.',
            'message.max'      => 'Mesajınız en fazla 5000 karakter olabilir.',
        ]);

        // Önce kaydet: e-posta gönderimi başarısız olsa bile mesaj kaybolmaz.
        $contactMessage = ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
        ]);

        try {
            Mail::to(config('mail.admin_address'))->send(new ContactMessageMail($contactMessage));
        } catch (\Exception $e) {
            Log::error('İletişim formu e-postası gönderilemedi (Mesaj ID: ' . $contactMessage->id . '): ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Mesajınız başarıyla iletildi! En kısa sürede sizinle iletişime geçeceğiz.');
    }
}
