@extends('layouts.app')

@section('title', 'İletişim - Wise Solutions')
@section('meta_description', 'Wise Solutions Bilgi Teknolojileri ile iletişime geçin. Adres, telefon numarası, WhatsApp destek hattı ve e-posta bilgilerimiz.')

@section('styles')
    <!-- Leaflet JS Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #contact-map {
            height: 350px;
            width: 100%;
            border-radius: 12px;
        }
    </style>
@endsection

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Giriş Portalı</a>
            <span>/</span>
            <span class="text-slate-700">İletişim</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        
        <div class="text-center max-w-xl mx-auto mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2" id="contact-page-title">Bize Ulaşın</h1>
            <p class="text-slate-500 text-sm">Geliştirme kartları, sağlık & lens aparatları ve dış ticaret danışmanlık hizmetlerimizle ilgili sorularınızı aşağıdaki kanallardan iletebilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Left Side: Contact Information & Map -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Quick Contact Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6" id="contact-info-card">
                    <h3 class="text-lg font-bold text-slate-900">İletişim Bilgileri</h3>
                    
                    <div class="space-y-4">
                        <!-- Corporate Title -->
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wide">Şirket Unvanı</h4>
                            <p class="text-sm font-bold text-slate-800">Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti.</p>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start gap-4">
                            <div class="bg-sky-50 p-2.5 rounded-lg text-trendyol shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wide">E-posta</h4>
                                <a href="mailto:info@wisesolutions.com.tr" class="text-sm font-bold text-slate-800 hover:text-trendyol transition-colors">info@wisesolutions.com.tr</a>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start gap-4">
                            <div class="bg-indigo-50 p-2.5 rounded-lg text-indigo-500 shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wide">Adres</h4>
                                <p class="text-sm font-bold text-slate-800 leading-snug">Melikgazi / Kayseri</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OpenStreetMap Map container -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                    <div id="contact-map" class="shadow-sm border border-slate-200"></div>
                </div>

            </div>

            <!-- Right Side: Contact Form -->
            <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8" id="contact-form-box">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Bize Mesaj Gönderin</h3>
                
                <form id="contact-form" class="space-y-5" method="POST" action="{{ route('contact.submit') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div class="flex flex-col gap-1.5">
                            <label for="contact-name" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Adınız Soyadınız</label>
                            <input
                                type="text"
                                id="contact-name"
                                name="name"
                                value="{{ old('name') }}"
                                maxlength="255"
                                required
                                class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all"
                            >
                        </div>
                        
                        <!-- Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="contact-email" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">E-Posta Adresiniz</label>
                            <input
                                type="email"
                                id="contact-email"
                                name="email"
                                value="{{ old('email') }}"
                                maxlength="255"
                                required
                                class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all"
                            >
                        </div>
                    </div>

                    <!-- Subject -->
                    <div class="flex flex-col gap-1.5">
                        <label for="contact-subject" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Konu</label>
                        <input
                            type="text"
                            id="contact-subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            maxlength="255"
                            required
                            class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all"
                        >
                    </div>

                    <!-- Message -->
                    <div class="flex flex-col gap-1.5">
                        <label for="contact-message" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Mesajınız</label>
                        <textarea
                            id="contact-message"
                            name="message"
                            rows="6"
                            maxlength="5000"
                            required
                            class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all resize-none"
                        >{{ old('message') }}</textarea>
                    </div>

                    @if ($errors->any())
                        <div class="bg-rose-50 border border-rose-200 rounded-lg p-3">
                            <ul class="text-xs font-semibold text-rose-600 space-y-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Consent/KVKK checkbox -->
                    <div class="flex items-start gap-2.5">
                        <input type="checkbox" id="contact-kvkk-check" required class="mt-1 h-4 w-4 text-trendyol border-slate-300 rounded focus:ring-trendyol">
                        <label for="contact-kvkk-check" class="text-xs text-slate-500 font-semibold leading-normal">
                            <a href="{{ route('kvkk') }}" target="_blank" class="text-trendyol hover:underline font-extrabold">Aydınlatma Metni'ni</a> okudum, kişisel verilerimin işlenmesini kabul ediyorum.
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-trendyol hover:bg-trendyolDark text-white py-3 rounded-lg font-extrabold text-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5"
                        id="btn-contact-submit"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Mesajı Gönder</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

@endsection

@section('scripts')
    <!-- Leaflet JS Map Script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Coordinate points for Melikgazi / Kayseri office
            var lat = 38.7205;
            var lng = 35.4826;
            
            var map = L.map('contact-map', {
                scrollWheelZoom: false
            }).setView([lat, lng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            var marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup("<b>Wise Solutions</b><br>Kayseri Ofisi").openPopup();
        });
    </script>
@endsection
