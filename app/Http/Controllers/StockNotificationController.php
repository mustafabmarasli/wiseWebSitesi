<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    /**
     * "Stok gelince haber ver" kaydı açar.
     *
     * Yanıt her zaman JSON'dur; düğme sayfayı yenilemeden çalışır.
     */
    public function store(Request $request): JsonResponse
    {
        $veri = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            // Üye girişliyken e-posta hesaptan alınır, formdan beklenmez.
            'email'      => [auth()->check() ? 'nullable' : 'required', 'email', 'max:255'],
        ], [
            'email.required' => 'Lütfen geçerli bir e-posta adresi girin.',
            'email.email'    => 'Girdiğiniz e-posta adresi geçerli görünmüyor.',
        ]);

        $product = Product::findOrFail($veri['product_id']);

        // Stoktaki ürüne bildirim kaydı açmak müşteriyi bekletmek olurdu.
        if ($product->stock > 0) {
            return response()->json([
                'status'  => 'in_stock',
                'message' => 'Bu ürün şu anda stokta. Hemen sipariş verebilirsiniz.',
            ]);
        }

        $email = mb_strtolower(trim(auth()->check() ? auth()->user()->email : $veri['email']));

        $mevcut = StockNotification::where('product_id', $product->id)
            ->where('email', $email)
            ->first();

        if ($mevcut && $mevcut->notified_at === null) {
            return response()->json([
                'status'  => 'already',
                'message' => $email . ' adresi bu ürün için zaten kayıtlı. Stok girişinde size haber vereceğiz.',
            ]);
        }

        // Kayıt varsa (ürün daha önce gelip tekrar tükenmişse) `notified_at`
        // sıfırlanır — aynı müşteri ikinci turda da haber alır.
        StockNotification::updateOrCreate(
            ['product_id' => $product->id, 'email' => $email],
            ['user_id' => auth()->id(), 'notified_at' => null],
        );

        return response()->json([
            'status'  => 'created',
            'message' => 'Kaydınız alındı. Ürün stoklarımıza girdiğinde ' . $email . ' adresine bilgilendirme göndereceğiz.',
        ]);
    }
}
