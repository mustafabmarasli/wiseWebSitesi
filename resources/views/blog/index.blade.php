@extends('layouts.app')

@section('title', 'Rehberler ve Yazılar - Buy WISEly')
@section('meta_description', 'Skleral ve sert kontakt lens kullanımı, DMV aparatları, ESP32 ve LED aydınlatma üzerine uygulamalı rehberler.')

@section('og_title', 'Rehberler ve Yazılar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <nav class="text-xs font-bold text-slate-400 mb-3">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Ana Sayfa</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-600">Rehberler</span>
        </nav>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Rehberler ve Yazılar</h1>
        <p class="text-sm text-slate-500 font-semibold mt-2 max-w-2xl">
            Ürünleri doğru seçmek ve doğru kullanmak için hazırladığımız uygulamalı rehberler.
        </p>
    </div>

    {{-- Kanal süzgeci --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('blog.index') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all {{ $kanal ? 'bg-white border border-slate-200 text-slate-600 hover:border-trendyol' : 'bg-trendyol text-white' }}">
            Tümü
        </a>
        @foreach (\App\Models\Post::KANALLAR as $deger => $etiket)
            <a href="{{ route('blog.index', ['kanal' => $deger]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all {{ $kanal === $deger ? 'bg-trendyol text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-trendyol' }}">
                {{ $etiket }}
            </a>
        @endforeach
    </div>

    @if ($posts->isEmpty())
        <div class="bg-white border border-slate-100 rounded-2xl p-10 text-center">
            <p class="text-sm font-bold text-slate-700">Bu bölümde henüz yazı yok.</p>
            <p class="text-xs text-slate-500 font-semibold mt-1.5">Yakında yeni rehberler eklenecek.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($posts as $post)
                <article class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block bg-slate-50 h-44 overflow-hidden">
                        @if ($post->image_url)
                            <img src="{{ $post->image_url }}" alt="{{ $post->cover_alt ?? $post->title }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                        @endif
                    </a>

                    <div class="p-5 flex flex-col flex-grow">
                        <div class="flex items-center gap-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wide mb-2">
                            <span class="text-trendyol">{{ $post->channel_label }}</span>
                            <span>·</span>
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
                            <span>·</span>
                            <span>{{ $post->reading_minutes }} dk</span>
                        </div>

                        <h2 class="text-sm font-extrabold text-slate-900 leading-snug mb-2 line-clamp-2">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-trendyol transition-colors">{{ $post->title }}</a>
                        </h2>

                        <p class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-3 flex-grow">{{ $post->summary }}</p>

                        <a href="{{ route('blog.show', $post->slug) }}"
                           class="mt-4 text-xs font-extrabold text-trendyol hover:underline">Devamını oku →</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif

</div>
@endsection
