<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'E-posta adresi girilmesi zorunludur.',
            'email.email' => 'Lütfen geçerli bir e-posta adresi giriniz.',
            'password.required' => 'Şifre girilmesi zorunludur.',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();

            if ($this->linkPendingGuestOrder()) {
                return redirect()->route('profile.orders')
                    ->with('success', 'Giriş yaptınız ve misafir siparişiniz hesabınıza eklendi.');
            }

            return redirect()->intended(route('landing'))->with('success', 'Başarıyla giriş yaptınız.');
        }

        return back()->withErrors([
            'email' => 'Girdiğiniz bilgiler kayıtlarımızla eşleşmiyor.',
        ])->onlyInput('email');
    }

    /**
     * Misafir olarak verilmiş bir siparişi, giriş yapan hesaba bağlar.
     *
     * Güvenlik: yalnızca kullanıcı hem siparişe erişim hakkını kanıtlamışsa
     * (PaymentController siparişi session'a yazmıştır) hem de hesabın şifresini
     * bildiği için giriş yapabilmişse çalışır. E-posta doğrulaması olmadığından
     * yalnızca e-posta eşleşmesine güvenilmez.
     */
    private function linkPendingGuestOrder(): bool
    {
        $orderId = session()->pull('link_order_after_login');

        if (!$orderId) {
            return false;
        }

        $granted = session()->get('order_access', []);
        if (!in_array($orderId, $granted, true)) {
            return false;
        }

        $user  = Auth::user();
        $order = \App\Models\Order::find($orderId);

        if (!$order || $order->user_id !== null || $order->status !== 'paid') {
            return false;
        }

        if (strcasecmp($order->email, $user->email) !== 0) {
            return false;
        }

        $order->update(['user_id' => $user->id]);

        return true;
    }

    /**
     * Terminate user session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing')->with('success', 'Başarıyla çıkış yapıldı.');
    }
}
