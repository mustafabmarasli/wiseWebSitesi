<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /** Yazı listesi. Kanal filtresi isteğe bağlı (?kanal=health). */
    public function index(Request $request)
    {
        $kanal = $request->query('kanal');

        if ($kanal && ! array_key_exists($kanal, Post::KANALLAR)) {
            $kanal = null;
        }

        // Sıralama anasayfa rafıyla AYNI: panelden verilen sıra önce, sonra
        // yayın tarihi. Öne çıkarmak istediğin rehber her iki yerde de üstte
        // olsun diye; iki liste ayrı sıralanırsa "sürükledim ama değişmedi"
        // sorusu çıkıyor.
        $posts = Post::published()
            ->channel($kanal)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('posts', 'kanal'));
    }

    public function show(string $slug)
    {
        // Taslak yazı 404 döner: yayında olmayan adres paylaşılırsa
        // arama motoru onu dizine almasın.
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->channel($post->channel)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
