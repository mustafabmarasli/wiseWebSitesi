@extends('layouts.app')

@section('title', 'Giriş Yap - Buy WISEly')
@section('meta_description', 'Hesabınıza giriş yapın veya Google ile hızlıca bağlanın.')

@section('content')

    <div class="max-w-md mx-auto px-4 sm:px-6 mt-16 mb-20">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 sm:p-10 font-sans">
            
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Giriş Yap</h2>
                <p class="text-slate-500 text-xs sm:text-sm mt-1">Buy WISEly ayrıcalıklarına erişmek için hesabınıza bağlanın.</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold p-4 rounded-xl mb-6">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="flex flex-col gap-1.5">
                    <label for="login-email" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">E-Posta Adresiniz</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="login-email" 
                        value="{{ old('email') }}"
                        required
                        class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all"
                        placeholder="ornek@alanadi.com"
                    >
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="login-password" class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Şifreniz</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="login-password" 
                        required
                        class="bg-slate-50 border border-slate-200 focus:border-transparent rounded-lg text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white text-slate-800 transition-all"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember-me" class="h-4 w-4 text-trendyol border-slate-300 rounded focus:ring-trendyol">
                        <label for="remember-me" class="text-xs text-slate-500 font-semibold">Beni Hatırla</label>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-trendyol hover:bg-trendyolDark text-white py-3 rounded-lg font-extrabold text-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5"
                    id="btn-login-submit"
                >
                    <span>Giriş Yap</span>
                </button>
            </form>

            @if (config('services.google.client_id'))
            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-100"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-3 text-slate-400 font-bold tracking-wider">veya</span>
                </div>
            </div>

            <!-- Google OAuth Button -->
            <a
                href="{{ route('auth.google') }}" 
                class="w-full bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 py-3 rounded-lg font-extrabold text-sm transition-all shadow-sm flex items-center justify-center gap-2.5"
                id="btn-google-login"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span>Google ile Giriş Yap</span>
            </a>
            @endif

            <!-- Registration Link -->
            <p class="text-center text-xs text-slate-500 font-semibold mt-8">
                Hesabınız yok mu? <a href="{{ route('register') }}" class="text-trendyol hover:underline font-extrabold">Hemen Kayıt Olun</a>
            </p>

        </div>
    </div>

@endsection
