<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use App\Models\Province;
use Illuminate\Validation\ValidationException;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Kullanıcı Profil Kontrol Paneli (Hesabım)
     */
    public function index()
    {
        $user = auth()->user();
        $addresses = $user->addresses()->latest()->get();
        return view('profile.index', compact('user', 'addresses'));
    }

    /**
     * Kullanıcı Bilgilerini Güncelle (İsim, E-posta ve İletişim Tercihleri)
     */
    public function updateInfo(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Hesap bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Şifre Güncelleme
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'Mevcut şifreniz hatalı.',
            'password.confirmed' => 'Şifre onayınız uyuşmuyor.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Şifreniz başarıyla güncellendi.');
    }

    /**
     * Yeni Adres Kaydet
     */
    public function storeAddress(Request $request)
    {
        $validated = $this->validateAddress($request);

        auth()->user()->addresses()->create($validated);

        return redirect()->back()->with('success', 'Adresiniz başarıyla kaydedildi.');
    }

    /**
     * Adres formunun doğrulama kuralları.
     *
     * `$request->all()` yerine yalnızca doğrulanmış alanlar kaydedilir; aksi
     * halde forma eklenen her alan doğrudan modele geçerdi.
     *
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request): array
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:100',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string|max:500',
            'province_id'     => 'nullable|integer|exists:provinces,id',
            'district_id'     => 'nullable|integer|exists:districts,id',
            'neighborhood_id' => 'nullable|integer|exists:neighborhoods,id',
            'city'            => 'nullable|string|max:100',
            'zip_code'        => 'nullable|string|max:20',
        ], [
            'title.required'      => 'Adres başlığı zorunludur.',
            'first_name.required' => 'Ad alanı zorunludur.',
            'last_name.required'  => 'Soyad alanı zorunludur.',
            'phone.required'      => 'Telefon zorunludur.',
            'address.required'    => 'Adres alanı zorunludur.',
        ]);

        // `city` eski kayıtlarla uyum için tutuluyor; il seçildiyse ondan doldurulur.
        if (filled($validated['province_id'] ?? null)) {
            $validated['city'] = Province::find($validated['province_id'])?->name ?? ($validated['city'] ?? null);
        }

        if (blank($validated['city'] ?? null)) {
            throw ValidationException::withMessages([
                'province_id' => 'İl seçimi zorunludur.',
            ]);
        }

        return $validated;
    }

    /**
     * Adres Güncelle
     */
    public function updateAddress(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->update($this->validateAddress($request));

        return redirect()->back()->with('success', 'Adresiniz başarıyla güncellendi.');
    }

    /**
     * Adres Sil
     */
    public function deleteAddress(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return redirect()->back()->with('success', 'Adresiniz silindi.');
    }

    /**
     * Favori Ürünleri Listele
     */
    public function favoritesList()
    {
        $products = auth()->user()->favoriteProducts()->with('category')->latest()->get();
        return view('profile.favorites', compact('products'));
    }

    /**
     * Favori Ekle / Çıkar (Toggle)
     */
    public function toggleFavorite(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);
        $user = auth()->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
            $message = 'Ürün favorilerinizden çıkarıldı.';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            $status = 'added';
            $message = 'Ürün favorilerinize eklendi.';
        }

        // `ajax()` YALNIZCA `X-Requested-With` başlığına bakar; `Accept:
        // application/json` gönderen bir fetch çağrısı yönlendirme alıp
        // sessizce sayfayı yeniliyordu. `expectsJson()` ikisini de kapsar.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
            ]);
        }

        // Ürün kartlarındaki kalp hâlâ normal form gönderimi yapıyor.
        return redirect()->back()->with('success', $message);
    }

    /**
     * Kullanıcının geçmiş siparişlerini listele.
     */
    public function orders()
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('profile.orders', compact('orders'));
    }

    /**
     * Sipariş detayını göster.
     */
    public function orderDetail(Order $order)
    {
        // Yetki kontrolü: Sadece sipariş sahibi veya admin görebilir
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Bu siparişi görüntüleme yetkiniz yok.');
        }

        $order->loadMissing('items');

        return view('profile.order_detail', compact('order'));
    }
}
