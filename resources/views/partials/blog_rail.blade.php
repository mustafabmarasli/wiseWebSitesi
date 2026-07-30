{{--
    Rehber yazıları listesi. Kanal anasayfasında kategorilerin karşısında,
    sağ rafta durur.

    @param \Illuminate\Support\Collection $posts
    @param string $mode  'rail' = dikey raf (geniş ekran sağ kolon)
                         'grid' = yatay ızgara (raf sığmayan ekranlarda, sayfa altı)

    Yazı yoksa hiçbir şey basılmaz — boş bir "Rehberler" kutusu sayfada yer
    kaplamaktan başka iş yapmaz.
--}}
@if ($posts->isNotEmpty())
    @php $raf = ($mode ?? 'rail') === 'rail'; @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 {{ $raf ? 'sticky top-6' : '' }}">
        <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-4 bg-trendyol rounded-sm shrink-0"></span>
                <h3 class="text-sm font-extrabold text-slate-900 truncate">Rehberler</h3>
            </div>
            <a href="{{ route('blog.index') }}" class="text-[10px] font-extrabold text-trendyol hover:underline shrink-0">Tümü</a>
        </div>

        <div class="{{ $raf ? 'space-y-3' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4' }}">
            @foreach ($posts as $post)
                {{-- Dikdörtgen kart. Yeni yazı üstte: liste yayın tarihine
                     göre tersten sıralı geliyor. --}}
                <a href="{{ route('blog.show', $post->slug) }}"
                   class="block rounded-xl border border-slate-100 hover:border-trendyol/40 hover:shadow-sm transition-all overflow-hidden group">
                    @if ($post->image_url)
                        <div class="bg-slate-50 {{ $raf ? 'h-24' : 'h-32' }} overflow-hidden">
                            <img src="{{ $post->image_url }}" alt="{{ $post->cover_alt ?? $post->title }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        </div>
                    @endif

                    <div class="p-3">
                        <div class="flex items-center gap-1.5 text-[9px] font-extrabold text-slate-400 uppercase tracking-wide mb-1.5">
                            <span class="text-trendyol">{{ $post->channel_label }}</span>
                            <span>·</span>
                            <span>{{ $post->reading_minutes }} dk</span>
                        </div>

                        <p class="text-xs font-extrabold text-slate-800 leading-snug line-clamp-3 group-hover:text-trendyol transition-colors">
                            {{ $post->title }}
                        </p>

                        @unless ($raf)
                            <p class="text-[11px] text-slate-500 font-medium leading-relaxed line-clamp-2 mt-1.5">{{ $post->summary }}</p>
                        @endunless

                        <time datetime="{{ $post->published_at->toDateString() }}"
                              class="block text-[10px] font-bold text-slate-400 mt-2">
                            {{ $post->published_at->translatedFormat('d F Y') }}
                        </time>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
