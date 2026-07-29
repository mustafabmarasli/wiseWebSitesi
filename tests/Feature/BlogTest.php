<?php

use App\Models\Post;
use App\Models\User;

function blogPost(array $overrides = []): Post
{
    return Post::create(array_merge([
        'title'        => 'Skleral Lens Nasil Takilir',
        'slug'         => 'skleral-lens-nasil-takilir-' . uniqid(),
        'excerpt'      => 'Adim adim uygulamali rehber.',
        'body'         => '<p>Once ellerinizi yikayin.</p><p>Sonra vantuzu temizleyin.</p>',
        'channel'      => 'health',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ], $overrides));
}

it('blog listesi acilir ve yayindaki yaziyi gosterir', function () {
    $post = blogPost();

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee($post->title);
});

it('taslak yazi listede gorunmez', function () {
    $taslak = blogPost(['title' => 'Taslak Yazi', 'is_published' => false]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertDontSee($taslak->title);
});

it('ileri tarihli yazi henuz gorunmez', function () {
    $ileri = blogPost(['title' => 'Gelecek Yazi', 'published_at' => now()->addWeek()]);

    $this->get(route('blog.index'))->assertOk()->assertDontSee($ileri->title);
    $this->get(route('blog.show', $ileri->slug))->assertNotFound();
});

it('yazi detayi acilir', function () {
    $post = blogPost();

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Once ellerinizi yikayin', false);
});

it('taslak yazinin adresi 404 doner', function () {
    $taslak = blogPost(['is_published' => false]);

    $this->get(route('blog.show', $taslak->slug))->assertNotFound();
});

it('yazi detayinda article semasi basilir', function () {
    $post = blogPost();

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('"@type":"Article"', false)
        ->assertSee('"@type":"BreadcrumbList"', false);
});

it('yazi detayinda open graph etiketleri article tipindedir', function () {
    $post = blogPost();

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('property="og:type" content="article"', false)
        ->assertSee('property="og:title" content="' . $post->title . '"', false);
});

it('saglik yazisinin altinda tibbi uyari cikar', function () {
    $post = blogPost(['channel' => 'health']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('tıbbi teşhis veya tedavi aracı değildir');
});

it('elektronik yazisinda tibbi uyari cikmaz', function () {
    $post = blogPost(['channel' => 'electronics', 'title' => 'ESP32 Rehberi']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('tıbbi teşhis veya tedavi aracı değildir');
});

it('kanal suzgeci yalnizca o bolumun yazilarini gosterir', function () {
    $saglik     = blogPost(['title' => 'Lens Bakimi', 'channel' => 'health']);
    $elektronik = blogPost(['title' => 'ESP32 Karsilastirma', 'channel' => 'electronics']);

    $this->get(route('blog.index', ['kanal' => 'electronics']))
        ->assertOk()
        ->assertSee($elektronik->title)
        ->assertDontSee($saglik->title);
});

it('gecersiz kanal suzgeci tum yazilari gosterir', function () {
    $post = blogPost();

    $this->get(route('blog.index', ['kanal' => 'uydurma']))
        ->assertOk()
        ->assertSee($post->title);
});

it('yayindaki yazi site haritasina girer taslak girmez', function () {
    $yayinda = blogPost(['slug' => 'yayindaki-yazi']);
    $taslak  = blogPost(['slug' => 'taslak-yazi', 'is_published' => false]);

    \Illuminate\Support\Facades\Cache::forget('sitemap.xml');

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('blog.show', $yayinda->slug), false)
        ->assertDontSee(route('blog.show', $taslak->slug), false);
});

it('ozet bos birakilirsa icerikten uretilir', function () {
    $post = blogPost(['excerpt' => null, 'body' => '<p>Bu metin ozet olarak kullanilir.</p>']);

    expect($post->summary)->toBe('Bu metin ozet olarak kullanilir.');
});

it('ilgili yazilar ayni bolumden gelir', function () {
    $post   = blogPost(['title' => 'Ana Yazi', 'channel' => 'health']);
    $ayni   = blogPost(['title' => 'Ayni Bolum Yazisi', 'channel' => 'health']);
    $baska  = blogPost(['title' => 'Baska Bolum Yazisi', 'channel' => 'electronics']);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee($ayni->title)
        ->assertDontSee($baska->title);
});

it('blog yonetimi admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/posts')
        ->assertOk();
});

it('yonetici olmayan blog paneline giremez', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/posts')
        ->assertForbidden();
});
