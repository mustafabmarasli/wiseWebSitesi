<?php

use App\Mail\BackInStockMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function tukenmisUrun(int $stock = 0): Product
{
    $category = Category::create([
        'name'    => 'Kategori',
        'slug'    => 'kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => 'Stoksuz Urun',
        'slug'        => 'stoksuz-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 250.00,
        'stock'       => $stock,
        'rating'      => 5,
    ]);
}

it('misafir stok bildirimi kaydi acabilir', function () {
    $product = tukenmisUrun();

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'Misafir@Example.com',
    ])->assertOk()->assertJson(['status' => 'created']);

    $bildirim = StockNotification::first();

    expect($bildirim->product_id)->toBe($product->id)
        ->and($bildirim->email)->toBe('misafir@example.com')   // küçük harfe indirilir
        ->and($bildirim->user_id)->toBeNull()
        ->and($bildirim->notified_at)->toBeNull();
});

it('uye girisliyken e-posta hesaptan alinir', function () {
    $product = tukenmisUrun();
    $user = User::factory()->create(['email' => 'uye@example.com']);

    $this->actingAs($user)
        ->postJson(route('stock.notify'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJson(['status' => 'created']);

    $bildirim = StockNotification::first();

    expect($bildirim->email)->toBe('uye@example.com')
        ->and($bildirim->user_id)->toBe($user->id);
});

it('ayni e-posta ayni urun icin ikinci kayit acmaz', function () {
    $product = tukenmisUrun();

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'ayni@example.com',
    ])->assertOk();

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'AYNI@example.com',
    ])->assertOk()->assertJson(['status' => 'already']);

    expect(StockNotification::count())->toBe(1);
});

it('stoktaki urune bildirim kaydi acilmaz', function () {
    $product = tukenmisUrun(stock: 5);

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'stokta@example.com',
    ])->assertOk()->assertJson(['status' => 'in_stock']);

    expect(StockNotification::count())->toBe(0);
});

it('gecersiz e-posta reddedilir', function () {
    $product = tukenmisUrun();

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'eposta-degil',
    ])->assertStatus(422);

    expect(StockNotification::count())->toBe(0);
});

it('stok girisinde bekleyenlere e-posta gider ve kayit isaretlenir', function () {
    Mail::fake();

    $product = tukenmisUrun();

    StockNotification::create(['product_id' => $product->id, 'email' => 'bekleyen@example.com']);
    StockNotification::create(['product_id' => $product->id, 'email' => 'ikinci@example.com']);

    $product->update(['stock' => 3]);

    Mail::assertSent(BackInStockMail::class, 2);
    Mail::assertSent(BackInStockMail::class, fn ($mail) => $mail->hasTo('bekleyen@example.com'));

    expect(StockNotification::whereNull('notified_at')->count())->toBe(0);
});

it('ayni bekleyene ikinci kez e-posta gitmez', function () {
    Mail::fake();

    $product = tukenmisUrun();
    StockNotification::create(['product_id' => $product->id, 'email' => 'tek@example.com']);

    $product->update(['stock' => 3]);
    // Tükenip yeniden dolar: kayıt zaten bildirilmiş, tekrar gitmemeli.
    $product->update(['stock' => 0]);
    $product->update(['stock' => 7]);

    Mail::assertSent(BackInStockMail::class, 1);
});

it('stok sifirdan yukari cikmadikca e-posta gitmez', function () {
    Mail::fake();

    $product = tukenmisUrun(stock: 4);
    StockNotification::create(['product_id' => $product->id, 'email' => 'stokvar@example.com']);

    $product->update(['stock' => 9]);   // zaten stokta, eşik geçilmedi
    $product->update(['name' => 'Yeni Ad']);

    Mail::assertNothingSent();
});

it('panelde bekleyen sayisi gosterilir', function () {
    $product = tukenmisUrun();

    StockNotification::create(['product_id' => $product->id, 'email' => 'bir@example.com']);
    StockNotification::create(['product_id' => $product->id, 'email' => 'iki@example.com']);
    // Bildirilmiş kayıt sayaca girmemeli.
    StockNotification::create([
        'product_id'  => $product->id,
        'email'       => 'uc@example.com',
        'notified_at' => now(),
    ]);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(\App\Filament\Resources\Products\Pages\ListProducts::class)
        ->assertTableColumnStateSet('pending_stock_notifications_count', 2, $product);
});

it('stok bildirimi e-postasi hatasiz olusturulur', function () {
    $product = tukenmisUrun(stock: 5);

    $html = (new BackInStockMail($product))->render();

    expect($html)->toContain($product->name)
        ->and($html)->toContain(route('product.detail', $product->slug));
});

it('bildirilmis kayit urun tekrar tukendiginde yeniden acilir', function () {
    $product = tukenmisUrun();

    StockNotification::create([
        'product_id'  => $product->id,
        'email'       => 'tekrar@example.com',
        'notified_at' => now()->subDay(),
    ]);

    $this->postJson(route('stock.notify'), [
        'product_id' => $product->id,
        'email'      => 'tekrar@example.com',
    ])->assertOk()->assertJson(['status' => 'created']);

    expect(StockNotification::count())->toBe(1)
        ->and(StockNotification::first()->notified_at)->toBeNull();
});
