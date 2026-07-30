<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Post;
use App\Models\User;

function panelAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

it('yeni yazi sayfasi acilir', function () {
    $this->actingAs(panelAdmin())->get('/admin/posts/create')->assertOk();
});

/**
 * Aşağıdaki üç test aynı tuzağı kolluyor: `Filament\Forms\Set` (Filament v5'te
 * taşındı) kullanan bir closure, alandan çıkıldığı anda TypeError atıyor ve
 * panelde yalnızca "yüklenirken hata oluştu" olarak görünüyor — log'a bir şey
 * düşmüyor. Sunucu tarafı sayfa testi bunu YAKALAMAZ, form etkileşimi gerekir.
 */
it('yazi basligi yazilinca slug uretilir', function () {
    Livewire::actingAs(panelAdmin())
        ->test(CreatePost::class)
        ->fillForm(['title' => 'Skleral Lens Nasıl Takılır'])
        ->assertFormSet(['slug' => 'skleral-lens-nasil-takilir']);
});

it('urun adi yazilinca slug uretilir', function () {
    Livewire::actingAs(panelAdmin())
        ->test(CreateProduct::class)
        ->fillForm(['name' => 'ESP32 Geliştirme Kartı'])
        ->assertFormSet(['slug' => 'esp32-gelistirme-karti']);
});

it('kategori adi yazilinca slug uretilir', function () {
    Livewire::actingAs(panelAdmin())
        ->test(CreateCategory::class)
        ->fillForm(['name' => 'Lens Aksesuarları'])
        ->assertFormSet(['slug' => 'lens-aksesuarlari']);
});

it('yazi panelden kaydedilir', function () {
    Livewire::actingAs(panelAdmin())
        ->test(CreatePost::class)
        ->fillForm([
            'title'        => 'ESP32 Rehberi',
            'slug'         => 'esp32-rehberi',
            'body'         => '<p>Icerik</p>',
            'channel'      => 'electronics',
            'is_published' => true,
            'published_at' => now()->toDateTimeString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::where('slug', 'esp32-rehberi')->exists())->toBeTrue();
});

it('yayindaki yazinin slugu duzenlemede degismez', function () {
    $post = Post::create([
        'title'        => 'Eski Baslik',
        'slug'         => 'eski-baslik',
        'body'         => '<p>metin</p>',
        'channel'      => 'general',
        'is_published' => true,
        'published_at' => now(),
    ]);

    // Başlık değişse bile adres sabit kalmalı: gelen bağlantılar ve arama
    // sıralaması eski adrese bağlı.
    Livewire::actingAs(panelAdmin())
        ->test(EditPost::class, ['record' => $post->id])
        ->fillForm(['title' => 'Yepyeni Baslik'])
        ->assertFormSet(['slug' => 'eski-baslik']);
});
