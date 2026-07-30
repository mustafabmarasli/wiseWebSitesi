<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'kvkk_consent' => 'required|accepted',
        ], [
            'name.required' => 'Ad Soyad alanı zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Lütfen geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresiyle kayıtlı bir hesap zaten var.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre onaylama uyuşmuyor.',
            'kvkk_consent.accepted' => 'Devam etmek için Aydınlatma Metni ve KVKK koşullarını kabul etmelisiniz.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Ticari elektronik ileti onayı KVKK onayından ayrıdır ve isteğe
        // bağlıdır; işaretlenmediyse hiçbir kayıt açılmaz. Onay verilmemiş
        // kişiye pazarlama iletisi göndermek 6563 sayılı kanuna aykırıdır.
        if ($request->boolean('eposta_izni')) {
            \App\Models\MarketingConsent::grant(
                channel: 'email',
                email:   $user->email,
                source:  'register',
                userId:  $user->id,
                ip:      $request->ip(),
            );
        }

        Auth::login($user);

        return redirect()->route('landing')->with('success', 'Hesabınız başarıyla oluşturuldu ve giriş yapıldı.');
    }
}
