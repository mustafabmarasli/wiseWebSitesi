<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        if (!$this->isConfigured()) {
            return redirect()->route('login')->with(
                'error',
                'Google ile giriş şu anda kullanılamıyor. Lütfen e-posta ve şifrenizle giriş yapın.'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Google OAuth anahtarları tanımlı mı?
     */
    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        if (!$this->isConfigured()) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                // If user doesn't exist, create it with a random password
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            }
            
            Auth::login($user, true);
            
            return redirect()->route('landing')->with('success', 'Google hesabınız ile başarıyla giriş yapıldı.');
            
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google ile giriş yapılırken bir hata oluştu. Lütfen tekrar deneyin.'
            ]);
        }
    }
}
