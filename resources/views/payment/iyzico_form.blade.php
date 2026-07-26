@extends('layouts.app')

@section('title', 'Ödeme Yapılıyor - Buy WISEly')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 font-sans">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-10 text-center">
        <div class="mb-6 flex justify-center">
            <div class="animate-pulse bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Güvenli Ödeme Sayfasına Yönlendiriliyorsunuz
            </div>
        </div>
        
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 mb-2">Ödemenizi Güvenle Tamamlayın</h1>
        <p class="text-slate-500 text-sm mb-8">Aşağıdaki iyzico ödeme formunu kullanarak kart bilgilerinizle ödemenizi gerçekleştirebilirsiniz.</p>

        {{-- iyzico ödeme formu buraya render edilecek --}}
        <div id="iyzipay-checkout-form" class="responsive mx-auto max-w-lg min-h-[400px]">
            {!! $checkoutFormContent !!}
        </div>
    </div>
</div>
@endsection