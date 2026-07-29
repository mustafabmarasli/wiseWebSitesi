@extends('layouts.app')

@section('title', ($post->meta_title ?? $post->title) . ' - Buy WISEly')
@section('meta_description', $post->meta_description ?? $post->summary)

@section('og_type', 'article')
@section('og_title', $post->title)
@section('og_description', $post->summary)
@if ($post->image_url)
    @section('og_image', $post->image_url)
@endif

{{-- Article şeması: Google yazıyı haber/rehber olarak tanır, arama
     sonucunda tarih ve yazar bilgisiyle gösterebilir. --}}
@section('schema')
@php
    $yaziSemasi = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'headline'         => Str::limit($post->title, 110),
        'description'      => $post->summary,
        'image'            => $post->image_url,
        'datePublished'    => $post->published_at?->toAtomString(),
        'dateModified'     => $post->updated_at?->toAtomString(),
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
        'author'           => ['@type' => 'Organization', 'name' => 'Wise Solutions', 'url' => url('/')],
        'publisher'        => ['@type' => 'Organization', 'name' => 'Wise Solutions', 'url' => url('/')],
    ]);

    $kirintiSemasi = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => route('landing')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Rehberler', 'item' => route('blog.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => url()->current()],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($yaziSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($kirintiSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="text-xs font-bold text-slate-400 mb-5">
        <a href="{{ route('landing') }}" class="hover:text-trendyol">Ana Sayfa</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-trendyol">Rehberler</a>
    </nav>

    <article class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        @if ($post->image_url)
            <img src="{{ $post->image_url }}" alt="{{ $post->cover_alt ?? $post->title }}"
                 class="w-full h-56 sm:h-80 object-cover">
        @endif

        <div class="p-6 sm:p-8">
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-slate-400 uppercase tracking-wide mb-3">
                <a href="{{ route('blog.index', ['kanal' => $post->channel]) }}" class="text-trendyol hover:underline">{{ $post->channel_label }}</a>
                <span>·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
                <span>·</span>
                <span>{{ $post->reading_minutes }} dakikalık okuma</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">{{ $post->title }}</h1>

            @if (filled($post->excerpt))
                <p class="text-sm text-slate-600 font-semibold leading-relaxed border-l-4 border-trendyol/30 pl-4 mb-6">{{ $post->excerpt }}</p>
            @endif

            {{-- İçerik panelden zengin metin olarak girilir; HTML olduğu gibi
                 basılır. Yalnızca yöneticiler yazabildiği için güvenli. --}}
            <div class="blog-body text-sm text-slate-700 leading-relaxed">
                {!! $post->body !!}
            </div>

            {{-- Sağlık yazılarında yasal uyarı ZORUNLU: sitede zaten duran
                 ifadenin aynısı. "Tedavi eder / iyileştirir" iması taşıyan bir
                 metnin altında bu uyarı bulunmazsa sorumluluk doğar. --}}
            @if ($post->channel === 'health')
                <div class="mt-8 bg-blue-50/50 border border-blue-200/60 rounded-xl p-4 text-xs">
                    <p class="text-slate-700 font-semibold leading-relaxed">
                        <strong>Önemli Sağlık Notu:</strong> Bu içerik yalnızca bilgilendirme amaçlıdır ve
                        tıbbi teşhis veya tedavi aracı değildir. Ürünleri kullanmadan önce mutlaka göz
                        doktorunuza veya yetkili uygulayıcınıza danışınız.
                    </p>
                </div>
            @endif

            @include('partials.share_buttons', [
                'shareUrl'   => route('blog.show', $post->slug),
                'shareTitle' => $post->title,
            ])
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-base font-extrabold text-slate-900 mb-4">İlgili Yazılar</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($related as $item)
                    <a href="{{ route('blog.show', $item->slug) }}"
                       class="bg-white rounded-xl border border-slate-100 p-4 hover:border-trendyol/40 hover:shadow-sm transition-all">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wide mb-1.5">{{ $item->published_at->translatedFormat('d F Y') }}</p>
                        <p class="text-xs font-extrabold text-slate-800 leading-snug line-clamp-3">{{ $item->title }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection

@section('styles')
<style>
    /* Zengin metin editöründen gelen HTML'in okunabilir görünmesi için.
       Tailwind'in typography eklentisi yok; yazı gövdesi başlık, liste ve
       bağlantı stillerini buradan alır. */
    .blog-body h2 { font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 1.75rem 0 .75rem; }
    .blog-body h3 { font-size: 1rem;     font-weight: 800; color: #0f172a; margin: 1.5rem 0 .5rem; }
    .blog-body p  { margin-bottom: 1rem; }
    .blog-body ul, .blog-body ol { margin: 0 0 1rem 1.25rem; }
    .blog-body ul { list-style: disc; }
    .blog-body ol { list-style: decimal; }
    .blog-body li { margin-bottom: .375rem; }
    .blog-body a  { color: #005B96; font-weight: 700; text-decoration: underline; }
    .blog-body a:hover { color: #00426d; }
    .blog-body img { max-width: 100%; height: auto; border-radius: .75rem; margin: 1.25rem 0; }
    .blog-body blockquote { border-left: 4px solid #e2e8f0; padding-left: 1rem; color: #475569; font-style: italic; margin: 1.25rem 0; }
    .blog-body strong { font-weight: 800; color: #0f172a; }
    .blog-body code { background: #f1f5f9; padding: .125rem .375rem; border-radius: .25rem; font-size: .8125rem; }
    .blog-body table { width: 100%; border-collapse: collapse; margin: 1.25rem 0; font-size: .8125rem; }
    .blog-body th, .blog-body td { border: 1px solid #e2e8f0; padding: .5rem .75rem; text-align: left; }
    .blog-body th { background: #f8fafc; font-weight: 800; }
</style>
@endsection
