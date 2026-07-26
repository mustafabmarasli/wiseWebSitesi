<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderConfirmedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    /**
     * Sipariş sonuç sayfalarının imzalı bağlantısı ne kadar geçerli kalsın.
     */
    private const RESULT_LINK_TTL_DAYS = 7;

    /**
     * iyzico'dan dönen callback'i işle.
     * CSRF koruması devre dışı (routes/web.php'de withoutMiddleware ile).
     */
    public function callback(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return redirect()->route('landing')
                ->with('error', 'Geçersiz ödeme yanıtı.');
        }

        // Siparişi bul
        $order = Order::where('iyzico_token', $token)->first();

        if (!$order) {
            return redirect()->route('landing')
                ->with('error', 'Sipariş bulunamadı.');
        }

        // Bu tarayıcıya siparişin sonuç sayfasını görme izni ver.
        $this->grantOrderAccess($order);

        // IDEMPOTENCY: callback tekrar tetiklenirse stok/kupon/e-posta ikinci kez işlenmesin.
        if ($order->status !== 'pending') {
            return $this->redirectToResult($order);
        }

        // iyzico'dan ödeme durumunu sorgula
        $options = new \Iyzipay\Options();
        $options->setApiKey(config('iyzico.api_key'));
        $options->setSecretKey(config('iyzico.secret_key'));
        $options->setBaseUrl(config('iyzico.base_url'));

        $retrieveRequest = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        $retrieveRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $retrieveRequest->setConversationId($order->iyzico_conversation_id);
        $retrieveRequest->setToken($token);

        $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($retrieveRequest, $options);

        $isSuccessful = $checkoutForm->getStatus() === 'success'
            && $checkoutForm->getPaymentStatus() === 'SUCCESS';

        if (!$isSuccessful) {
            $order->update([
                'status'                => 'failed',
                'iyzico_payment_status' => $checkoutForm->getPaymentStatus() ?? 'FAILED',
            ]);

            return $this->redirectToResult($order);
        }

        // TUTAR DOĞRULAMASI: iyzico'nun tahsil ettiği tutar siparişteki tutarla eşleşmeli.
        $paidPrice = (float) $checkoutForm->getPaidPrice();
        $expected  = (float) $order->total_amount;

        if (abs($paidPrice - $expected) > 0.01) {
            Log::critical('Ödeme tutarı uyuşmazlığı', [
                'order_id'   => $order->id,
                'expected'   => $expected,
                'paid_price' => $paidPrice,
                'payment_id' => $checkoutForm->getPaymentId(),
            ]);

            $order->update([
                'status'                => 'review',
                'iyzico_payment_id'     => $checkoutForm->getPaymentId(),
                'iyzico_payment_status' => $checkoutForm->getPaymentStatus(),
            ]);

            return redirect()->route('landing')->with(
                'error',
                'Ödemeniz alındı ancak tutarda bir uyuşmazlık tespit edildi. Ekibimiz sizinle iletişime geçecek.'
            );
        }

        // Ödeme başarılı — stok ve kupon sayacı tek transaction içinde güncellenir.
        $order->loadMissing('items');

        DB::transaction(function () use ($order, $checkoutForm) {
            $order->update([
                'status'                => 'paid',
                'iyzico_payment_id'     => $checkoutForm->getPaymentId(),
                'iyzico_payment_status' => $checkoutForm->getPaymentStatus(),
            ]);

            // Stok düşümü koşullu ve atomiktir: iki müşteri son ürünü aynı anda
            // satın alırsa ikincisinin UPDATE'i 0 satır etkiler ve stok negatife
            // düşmez. Ödeme zaten alındığı için sipariş iptal edilmez, operasyon
            // ekibinin görebilmesi için loglanır.
            foreach ($order->items as $item) {
                if (!$item->product_id) {
                    continue;
                }

                $affected = Product::where('id', $item->product_id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);

                if ($affected === 0) {
                    Log::warning('Stok yetersiz kaldı, sipariş fazla satış içeriyor', [
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                    ]);
                }
            }

            if ($order->coupon_code) {
                Coupon::where('code', $order->coupon_code)->increment('used_count');
            }
        });

        // E-posta gönderimi (başarısız olsa bile sipariş geçerlidir)
        try {
            Mail::to($order->email)->send(new OrderConfirmedMail($order));
            Mail::to(config('mail.admin_address'))->send(new AdminNewOrderMail($order));
        } catch (\Exception $e) {
            Log::error('Sipariş e-postası gönderilemedi (Sipariş ID: ' . $order->id . '): ' . $e->getMessage());
        }

        // Sepeti ve kuponu temizle
        session()->forget(['cart', 'coupon']);

        return $this->redirectToResult($order);
    }

    /**
     * Başarılı ödeme sayfası.
     */
    public function success(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

        if ($order->status !== 'paid') {
            return redirect()->route('landing');
        }

        $order->loadMissing('items');

        return view('payment.success', compact('order'));
    }

    /**
     * Başarısız ödeme sayfası.
     */
    public function failed(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

        if (!in_array($order->status, ['failed', 'pending'])) {
            return redirect()->route('landing');
        }

        $order->loadMissing('items');

        return view('payment.failed', compact('order'));
    }

    /**
     * Misafir siparişi sonrası şifre belirleyerek üye ol.
     */
    public function registerFromOrder(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

        // Sipariş başarılı mı ve henüz bir hesaba bağlı değil mi kontrol et
        if ($order->status !== 'paid' || $order->user_id !== null) {
            return redirect()->route('landing');
        }

        // Bu e-posta adresiyle zaten kayıtlı biri varsa, hesabın şifresini bilmeden
        // giriş yaptırmıyoruz. Kullanıcı giriş yaptıktan sonra sipariş eşleştirilir.
        if (User::where('email', $order->email)->exists()) {
            session(['link_order_after_login' => $order->id]);

            return redirect()->route('login')->with(
                'info',
                'Bu e-posta adresiyle kayıtlı bir hesap zaten var. Giriş yaptığınızda siparişiniz otomatik olarak hesabınıza eklenecek.'
            );
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Şifre alanı zorunludur.',
            'password.min'       => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        $user = DB::transaction(function () use ($order, $request) {
            $user = User::create([
                'name'     => $order->first_name . ' ' . $order->last_name,
                'email'    => $order->email,
                'password' => Hash::make($request->password),
            ]);

            $order->update(['user_id' => $user->id]);

            Address::create([
                'user_id'    => $user->id,
                'title'      => 'Sipariş Adresim',
                'first_name' => $order->first_name,
                'last_name'  => $order->last_name,
                'phone'      => $order->phone,
                'address'    => $order->address,
                'city'       => $order->city,
                'zip_code'   => $order->zip_code,
            ]);

            return $user;
        });

        auth()->login($user);
        $request->session()->regenerate();

        return redirect()->route('profile.index')->with(
            'success',
            'Hesabınız başarıyla oluşturuldu ve giriş yapıldı! Siparişinizi "Hesabım > Siparişlerim" sayfasından takip edebilirsiniz.'
        );
    }

    /**
     * Sipariş sonuç sayfasına imzalı bağlantı ile yönlendir.
     */
    private function redirectToResult(Order $order)
    {
        $route = $order->status === 'paid' ? 'payment.success' : 'payment.failed';

        return redirect()->to(
            URL::temporarySignedRoute($route, now()->addDays(self::RESULT_LINK_TTL_DAYS), ['order' => $order->id])
        );
    }

    /**
     * Bu tarayıcı oturumuna siparişi görüntüleme izni ver.
     */
    private function grantOrderAccess(Order $order): void
    {
        $granted = session()->get('order_access', []);

        if (!in_array($order->id, $granted, true)) {
            $granted[] = $order->id;
            session()->put('order_access', $granted);
        }
    }

    /**
     * Siparişe erişim yetkisi üç yoldan biriyle kanıtlanabilir:
     *  1) Siparişin sahibi olarak giriş yapmış olmak,
     *  2) Ödemeyi başlatan tarayıcı oturumuna sahip olmak,
     *  3) Geçerli imzalı bağlantıya sahip olmak (e-postadaki link).
     */
    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        if (auth()->check() && $order->user_id === auth()->id()) {
            return;
        }

        if (in_array($order->id, session()->get('order_access', []), true)) {
            return;
        }

        if ($request->hasValidSignature()) {
            $this->grantOrderAccess($order);
            return;
        }

        abort(403, 'Bu siparişi görüntüleme yetkiniz yok.');
    }
}
