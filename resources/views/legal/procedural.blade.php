@extends('layouts.app')

@section('title', $data['title'] . ' - Buy WISEly')
@section('meta_description', $data['description'])

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('home') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">{{ $data['title'] }}</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-6 pb-4 border-b border-slate-100" id="procedural-title">
                {{ $data['title'] }}
            </h1>

            <p class="text-sm text-slate-400 font-bold mb-6">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-semibold">
                <p>{{ $data['content'] }}</p>
                
                <h2 class="text-base font-bold text-slate-900 mt-6 mb-2">Genel Kurallar ve Yükümlülükler</h2>
                <p>Buy WISEly üzerinden sipariş veren tüm kullanıcılar bu sözleşme maddelerini okumuş ve onaylamış kabul edilir. Elektronik bileşenlerin hassas doğası (statik deşarj, aşırı gerilim ve polarite hatası riski) göz önünde bulundurularak teknik süreçler garanti kapsamı sınırlarında değerlendirilir.</p>
                
                <p>Detaylı bilgi almak, iade talebi oluşturmak veya sözleşmeler hakkında soru sormak için <a href="{{ route('contact') }}" class="text-trendyol hover:underline">İletişim Sayfamız</a> üzerinden yetkililerle doğrudan irtibata geçebilirsiniz.</p>
            </div>

        </div>
    </div>

@endsection
