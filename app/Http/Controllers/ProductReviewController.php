<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * Yorum gönderir.
     *
     * Yetki denetimi burada TEKRAR yapılır (yalnızca formu gizlemek yetmez) —
     * `Product::canBeReviewedBy()` ile aynı kural; aksi hâlde biri isteği
     * elle POST ederek satın almadığı bir ürüne yorum yazabilirdi.
     */
    public function store(Request $request, Product $product)
    {
        abort_unless($product->canBeReviewedBy($request->user()), 403,
            'Bu ürüne yorum yazabilmek için ürünü satın almış ve teslim almış olmanız gerekir.');

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.required'  => 'Lütfen bir puan seçin.',
            'comment.required' => 'Lütfen yorumunuzu yazın.',
            'comment.min'      => 'Yorumunuz en az 10 karakter olmalıdır.',
        ]);

        // Hangi teslim edilmiş siparişten hak kazandığı — ispat kaydı.
        $orderId = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', $request->user()->id)
                ->where('status', \App\Enums\OrderStatus::Delivered->value))
            ->latest('id')
            ->value('order_id');

        ProductReview::create([
            'product_id' => $product->id,
            'user_id'    => $request->user()->id,
            'order_id'   => $orderId,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'],
            'status'     => 'pending',
        ]);

        return redirect()
            ->route('product.detail', $product->slug)
            ->with('success', 'Yorumunuz alındı. İncelendikten sonra sayfada yayınlanacaktır — teşekkür ederiz!');
    }
}
