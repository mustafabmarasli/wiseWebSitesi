<?php

use App\Enums\OrderStatus;
use App\Mail\ReviewInviteMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function reviewProduct(array $overrides = []): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name'        => 'Yorum Urunu',
        'slug'        => 'yorum-urunu-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 5,
        'rating'      => 4.5,
    ], $overrides));
}

/** Kullanıcı için teslim edilmiş, ürünü içeren bir sipariş oluşturur. */
function deliveredOrderWithProduct(User $user, Product $product, string $status = 'delivered'): Order
{
    $order = Order::create([
        'user_id' => $user->id,
        'first_name' => 'Test', 'last_name' => 'Kullanici',
        'email' => $user->email, 'phone' => '05551112233',
        'address' => 'Test Sokak', 'city' => 'Istanbul',
        'total_amount' => $product->price, 'currency' => 'TRY', 'status' => $status,
    ]);

    OrderItem::create([
        'order_id' => $order->id, 'product_id' => $product->id,
        'product_name' => $product->name, 'quantity' => 1,
        'unit_price' => $product->price, 'total_price' => $product->price,
    ]);

    return $order;
}

it('misafir yorum yazamaz', function () {
    $product = reviewProduct();

    $this->post(route('product.review.store', $product), ['rating' => 5, 'comment' => 'Cok iyi bir urun, tavsiye ederim.'])
        ->assertRedirect(route('login'));

    expect(ProductReview::count())->toBe(0);
});

it('urunu almamis uye yorum yazamaz', function () {
    $user = User::factory()->create();
    $product = reviewProduct();

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 5, 'comment' => 'Cok iyi bir urun, tavsiye ederim.'])
        ->assertForbidden();

    expect(ProductReview::count())->toBe(0);
});

it('siparisi sadece kargoda olan uye yorum yazamaz', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product, status: 'shipped');

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 5, 'comment' => 'Cok iyi bir urun, tavsiye ederim.'])
        ->assertForbidden();
});

it('teslim edilmis siparisteki urune yorum yazilabilir', function () {
    $user = User::factory()->create(['name' => 'Ali Yilmaz']);
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 4, 'comment' => 'Urun gayet iyi, tavsiye ederim.'])
        ->assertRedirect(route('product.detail', $product->slug));

    $yorum = ProductReview::first();

    expect($yorum->product_id)->toBe($product->id)
        ->and($yorum->user_id)->toBe($user->id)
        ->and($yorum->rating)->toBe(4)
        ->and($yorum->status)->toBe('pending');
});

it('ayni urune ikinci kez yorum yazilamaz', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);

    $this->actingAs($user)->post(route('product.review.store', $product), [
        'rating' => 5, 'comment' => 'Ilk yorum, cok begendim.',
    ]);

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 3, 'comment' => 'Ikinci yorum denemesi.'])
        ->assertForbidden();

    expect(ProductReview::count())->toBe(1);
});

it('puan araligi disinda deger reddedilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 6, 'comment' => 'Gecerli uzunlukta yorum metni.'])
        ->assertSessionHasErrors('rating');
});

it('cok kisa yorum reddedilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);

    $this->actingAs($user)
        ->post(route('product.review.store', $product), ['rating' => 5, 'comment' => 'kisa'])
        ->assertSessionHasErrors('comment');
});

it('onaylanmamis yorum urun sayfasinda gorunmez', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);
    ProductReview::create([
        'product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $user->orders()->value('id') ?? Order::first()->id,
        'rating' => 5, 'comment' => 'Onay bekleyen yorum metni burada.', 'status' => 'pending',
    ]);

    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertDontSee('Onay bekleyen yorum metni burada.');
});

it('onaylanmis yorum urun sayfasinda gorunur', function () {
    $user = User::factory()->create(['name' => 'Ayse Demir']);
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product);
    ProductReview::create([
        'product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $order->id,
        'rating' => 5, 'comment' => 'Harika bir urun, herkese tavsiye ederim.', 'status' => 'approved', 'approved_at' => now(),
    ]);

    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('Harika bir urun, herkese tavsiye ederim.')
        ->assertSee('Doğrulanmış Alışveriş')
        // Tam ad DEĞİL, maskeli gösterim.
        ->assertSee('Ayse D.')
        ->assertDontSee('Ayse Demir');
});

it('gercek ortalama ve sayi dogru hesaplanir', function () {
    $urun = reviewProduct();
    $u1 = User::factory()->create(); $o1 = deliveredOrderWithProduct($u1, $urun);
    $u2 = User::factory()->create(); $o2 = deliveredOrderWithProduct($u2, $urun);

    ProductReview::create(['product_id' => $urun->id, 'user_id' => $u1->id, 'order_id' => $o1->id, 'rating' => 5, 'comment' => 'Cok iyi bir deneyim yasadim.', 'status' => 'approved']);
    ProductReview::create(['product_id' => $urun->id, 'user_id' => $u2->id, 'order_id' => $o2->id, 'rating' => 3, 'comment' => 'Idare eder, fena degil.', 'status' => 'approved']);

    $urun->refresh();

    expect($urun->real_reviews_count)->toBe(2)
        ->and($urun->real_average_rating)->toBe(4.0)
        ->and($urun->hasRealReviews())->toBeTrue();
});

it('reddedilen yorum ortalamaya girmez', function () {
    $urun = reviewProduct();
    $u1 = User::factory()->create(); $o1 = deliveredOrderWithProduct($u1, $urun);

    ProductReview::create(['product_id' => $urun->id, 'user_id' => $u1->id, 'order_id' => $o1->id, 'rating' => 1, 'comment' => 'Reddedilecek olan yorum budur.', 'status' => 'rejected']);

    expect($urun->hasRealReviews())->toBeFalse()
        ->and($urun->real_average_rating)->toBeNull();
});

it('gercek yorum yokken sahte degerlendirme sayisi basilmaz', function () {
    // Eskiden her üründe sabit "(24 Değerlendirme)" yazıyordu.
    $urun = reviewProduct(['rating' => 4.5]);

    $this->get(route('product.detail', $urun->slug))
        ->assertOk()
        ->assertDontSee('24 Değerlendirme')
        ->assertDontSee('Değerlendirme)');
});

it('gercek yorum varken dogru sayi basilir', function () {
    $urun = reviewProduct();
    $u1 = User::factory()->create(); $o1 = deliveredOrderWithProduct($u1, $urun);
    ProductReview::create(['product_id' => $urun->id, 'user_id' => $u1->id, 'order_id' => $o1->id, 'rating' => 5, 'comment' => 'Gercekten cok memnun kaldim.', 'status' => 'approved']);

    $this->get(route('product.detail', $urun->slug))
        ->assertOk()
        ->assertSee('(1 Değerlendirme)', false);
});

it('aggregate rating semasi yalnizca gercek yorumla basilir', function () {
    $urunYorumsuz = reviewProduct(['rating' => 4.8]);
    $this->get(route('product.detail', $urunYorumsuz->slug))
        ->assertOk()
        ->assertDontSee('"@type":"AggregateRating"', false);

    $urunYorumlu = reviewProduct();
    $u1 = User::factory()->create(); $o1 = deliveredOrderWithProduct($u1, $urunYorumlu);
    ProductReview::create(['product_id' => $urunYorumlu->id, 'user_id' => $u1->id, 'order_id' => $o1->id, 'rating' => 5, 'comment' => 'Semaya girecek gercek bir yorum.', 'status' => 'approved']);

    $this->get(route('product.detail', $urunYorumlu->slug))
        ->assertOk()
        ->assertSee('"@type":"AggregateRating"', false);
});

it('satin alan uyeye yorum formu gosterilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    deliveredOrderWithProduct($user, $product);

    $this->actingAs($user)
        ->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('id="review-form"', false);
});

it('almamis uyeye yorum formu gosterilmez', function () {
    $user = User::factory()->create();
    $product = reviewProduct();

    $this->actingAs($user)
        ->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertDontSee('id="review-form"', false);
});

it('daha once yorum yapan uyeye tesekkur mesaji gosterilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product);
    ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $order->id, 'rating' => 5, 'comment' => 'Daha once yazdigim yorum budur.', 'status' => 'pending']);

    $this->actingAs($user)
        ->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('daha önce yorum yaptınız')
        ->assertDontSee('id="review-form"', false);
});

it('teslim edildiginde yorum daveti gider', function () {
    Mail::fake();

    $user = User::factory()->create();
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product, status: 'shipped');

    $order->update(['status' => 'delivered']);

    Mail::assertSent(ReviewInviteMail::class, fn ($mail) => $mail->hasTo($user->email));
    expect($order->fresh()->review_invite_sent_at)->not->toBeNull();
});

it('misafir siparisine yorum daveti gitmez', function () {
    Mail::fake();

    $product = reviewProduct();
    $order = Order::create([
        'user_id' => null,
        'first_name' => 'Misafir', 'last_name' => 'Musteri',
        'email' => 'misafir@example.com', 'phone' => '05551112233',
        'address' => 'Adres', 'city' => 'Istanbul',
        'total_amount' => 100, 'currency' => 'TRY', 'status' => 'shipped',
    ]);

    $order->update(['status' => 'delivered']);

    Mail::assertNothingSent();
});

it('ayni siparis tekrar kaydedilince ikinci davet gitmez', function () {
    Mail::fake();

    $user = User::factory()->create();
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product, status: 'shipped');

    $order->update(['status' => 'delivered']);
    $order->update(['estimated_delivery_at' => now()]);

    Mail::assertSent(ReviewInviteMail::class, 1);
});

it('yorum daveti sadece yorum yazilabilecek urunleri listeler', function () {
    $user = User::factory()->create();
    $product = reviewProduct(['name' => 'Davetli Urun']);
    $order = deliveredOrderWithProduct($user, $product, status: 'shipped');

    $order->update(['status' => 'delivered']);

    $html = (new ReviewInviteMail($order->fresh()))->render();

    expect($html)->toContain('Davetli Urun')
        ->and($html)->toContain(route('product.detail', $product->slug));
});

it('yonetici yorumu onaylayabilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product);
    $yorum = ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $order->id, 'rating' => 5, 'comment' => 'Onaylanacak olan yorum budur.', 'status' => 'pending']);

    $yorum->approve();

    expect($yorum->fresh()->status)->toBe('approved')
        ->and($yorum->fresh()->approved_at)->not->toBeNull();
});

it('yonetici yorumu reddedebilir', function () {
    $user = User::factory()->create();
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product);
    $yorum = ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $order->id, 'rating' => 1, 'comment' => 'Reddedilecek olan yorum budur.', 'status' => 'pending']);

    $yorum->reject();

    expect($yorum->fresh()->status)->toBe('rejected');
});

it('yorum paneli admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/product-reviews')
        ->assertOk();
});

it('yonetici olmayan yorum paneline giremez', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/product-reviews')
        ->assertForbidden();
});

it('reviewer name soyadi maskeler', function () {
    $user = User::factory()->create(['name' => 'Mehmet Can Ozturk']);
    $product = reviewProduct();
    $order = deliveredOrderWithProduct($user, $product);
    $yorum = ProductReview::create(['product_id' => $product->id, 'user_id' => $user->id, 'order_id' => $order->id, 'rating' => 5, 'comment' => 'Isim maskeleme testi icin yorum.', 'status' => 'approved']);

    expect($yorum->reviewer_name)->toBe('Mehmet O.');
});
