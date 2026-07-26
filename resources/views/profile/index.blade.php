@extends('layouts.app')

@section('title', 'Hesabım - Buy WISEly')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 font-sans">
    
    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 mb-6">
            <ul class="list-disc list-inside text-sm space-y-1 font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sol Menü (Hesap Navigasyonu) -->
        <div class="w-full md:w-1/4 shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-full bg-[#1B3A6B] text-white flex items-center justify-center font-black">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-black text-slate-800 truncate">{{ $user->name }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 truncate">{{ $user->email }}</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-black bg-slate-50 text-[#1B3A6B] border border-slate-100 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Hesap Bilgilerim</span>
                    </a>
                    
                    <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span>Favorilerim</span>
                    </a>

                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Siparişlerim</span>
                    </a>
                    
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50/50 transition-all text-left">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Güvenli Çıkış</span>
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Sağ Taraf: Detaylar -->
        <div class="flex-1 space-y-8">
            
            <!-- Profil Bilgileri Formu -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <h2 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5 text-[#1B3A6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Profil ve Hesap Bilgileri
                </h2>
                
                <form action="{{ route('profile.update-info') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Ad Soyad</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent transition"
                                required>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">E-posta Adresi</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent transition"
                                required>
                        </div>
                    </div>

                    <!-- İletişim Tercihleri -->
                    <div class="border-t border-slate-100 pt-4 space-y-3">
                        <span class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">İletişim Tercihleri</span>
                        <div class="flex items-start gap-2.5">
                            <input type="checkbox" id="sms_consent" name="sms_consent" checked class="h-4 w-4 text-[#1B3A6B] border-slate-300 rounded focus:ring-[#1B3A6B] mt-0.5">
                            <label for="sms_consent" class="text-xs text-slate-500 font-semibold leading-normal">
                                Yeni sipariş durumu güncellemeleri ve ürün bildirimleri için SMS almak istiyorum.
                            </label>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <input type="checkbox" id="email_consent" name="email_consent" checked class="h-4 w-4 text-[#1B3A6B] border-slate-300 rounded focus:ring-[#1B3A6B] mt-0.5">
                            <label for="email_consent" class="text-xs text-slate-500 font-semibold leading-normal">
                                Kampanya ve indirim haberlerini e-posta ile almak istiyorum.
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-6 py-2.5 rounded-lg text-xs transition-colors shadow-sm">
                        Bilgileri Güncelle
                    </button>
                </form>
            </div>

            <!-- Şifre Değiştirme Formu -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <h2 class="text-lg font-black text-slate-900 mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5 text-[#1B3A6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Şifre Güncelleme
                </h2>
                
                <form action="{{ route('profile.update-password') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="current_password" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Mevcut Şifre</label>
                            <input type="password" id="current_password" name="current_password"
                                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent transition"
                                required>
                        </div>
                        <div>
                            <label for="password" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Yeni Şifre</label>
                            <input type="password" id="password" name="password"
                                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent transition"
                                required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent transition"
                                required>
                        </div>
                    </div>

                    <button type="submit" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-6 py-2.5 rounded-lg text-xs transition-colors shadow-sm">
                        Şifreyi Değiştir
                    </button>
                </form>
            </div>

            <!-- Adreslerim Bölümü -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <svg class="h-5 w-5 text-[#1B3A6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Kayıtlı Adreslerim
                    </h2>
                    <button type="button" onclick="toggleAddressForm('new')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold px-4 py-2 rounded-lg text-xs transition-colors shadow-sm flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Yeni Adres Ekle
                    </button>
                </div>

                <!-- Yeni Adres Formu (Gizli) -->
                <div id="address-form-new" class="hidden bg-slate-50 border border-slate-100 rounded-xl p-5 mb-6">
                    <h3 class="text-sm font-black text-slate-800 mb-4 pb-2 border-b border-slate-200">Yeni Adres Ekle</h3>
                    <form action="{{ route('profile.address.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="title" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Adres Başlığı *</label>
                                <input type="text" name="title" placeholder="Örn: Ev Adresim, İş" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none" required>
                            </div>
                            <div>
                                <label for="first_name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Alıcı Adı *</label>
                                <input type="text" name="first_name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none" required>
                            </div>
                            <div>
                                <label for="last_name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Alıcı Soyadı *</label>
                                <input type="text" name="last_name" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none" required>
                            </div>
                            <div>
                                <label for="phone" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Alıcı Telefon *</label>
                                <input type="tel" name="phone" placeholder="05xxxxxxxx" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none" required>
                            </div>
                            <div>
                                <label for="city" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Şehir *</label>
                                <input type="text" name="city" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none" required>
                            </div>
                            <div>
                                <label for="zip_code" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Posta Kodu</label>
                                <input type="text" name="zip_code" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="address" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1">Açık Adres *</label>
                                <textarea name="address" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-[#1B3A6B] focus:border-transparent focus:outline-none resize-none" required></textarea>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold px-5 py-2 rounded-lg text-xs transition-colors shadow-sm">Kaydet</button>
                            <button type="button" onclick="toggleAddressForm('new')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-extrabold px-5 py-2 rounded-lg text-xs transition-colors">İptal</button>
                        </div>
                    </form>
                </div>

                <!-- Adres Listesi -->
                @if($addresses->isEmpty())
                    <p class="text-xs text-slate-400 font-semibold py-4">Henüz kayıtlı bir adresiniz bulunmamaktadır.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($addresses as $addr)
                            <div class="border border-slate-150 rounded-xl p-4 flex flex-col justify-between hover:border-slate-300 transition-all">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-black text-slate-800 bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-md">{{ $addr->title }}</span>
                                        
                                        <!-- Delete form -->
                                        <form action="{{ route('profile.address.delete', $addr->id) }}" method="POST" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?')">
                                            @csrf
                                            <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    <p class="text-xs font-black text-slate-900 mb-1">{{ $addr->full_name }}</p>
                                    <p class="text-[11px] text-slate-500 font-bold mb-2">{{ $addr->phone }}</p>
                                    <p class="text-xs text-slate-600 font-semibold leading-relaxed">{{ $addr->address }}, {{ $addr->city }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>

<script>
function toggleAddressForm(id) {
    const el = document.getElementById('address-form-' + id);
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}
</script>
@endsection
