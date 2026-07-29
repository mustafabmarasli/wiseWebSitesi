<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Multi-Channel landing portal page
Route::get('/', [PageController::class, 'landing'])->name('landing');

// Arama motorları için site haritası (robots.txt içinden gösterilir)
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Google Merchant Center ürün akışı — Merchant Center bu adresi günlük çeker
Route::get('/merchant-feed.xml', [\App\Http\Controllers\MerchantFeedController::class, 'index'])->name('merchant.feed');

// Separate store hubs & Consulting pages
Route::get('/elektronik', [HomeController::class, 'electronics'])->name('electronics.home');
Route::get('/saglik', [HomeController::class, 'health'])->name('health.home');
Route::get('/danismanlik', [PageController::class, 'consulting'])->name('consulting');

// Search & Categories & Detail URLs (SEO-optimized)
Route::get('/arama', [ProductController::class, 'search'])->name('product.search');
Route::get('/kategori/{slug}', [ProductController::class, 'category'])->name('category');
Route::get('/urun/{slug}', [ProductController::class, 'detail'])->name('product.detail');

// Session-based Shopping Cart
Route::get('/sepet', [CartController::class, 'index'])->name('cart.index');
Route::post('/sepet/ekle', [CartController::class, 'add'])->name('cart.add');
Route::post('/sepet/guncelle', [CartController::class, 'update'])->name('cart.update');
Route::post('/sepet/sil', [CartController::class, 'remove'])->name('cart.remove');

// Checkout & Ödeme
Route::get('/odeme', [CartController::class, 'showCheckout'])->name('checkout');
Route::post('/odeme/baslat', [CartController::class, 'initiatePayment'])->name('payment.initiate');
Route::post('/kupon/uygula', [CartController::class, 'applyCoupon'])->name('coupon.apply');
Route::post('/kupon/kaldir', [CartController::class, 'removeCoupon'])->name('coupon.remove');

// Dynamic Location Routes
Route::get('/location/districts', [\App\Http\Controllers\LocationController::class, 'getDistricts'])->name('location.districts');
Route::get('/location/neighborhoods', [\App\Http\Controllers\LocationController::class, 'getNeighborhoods'])->name('location.neighborhoods');

// iyzico Callback - CSRF muaf (iyzico sunucusundan POST gelir)
Route::post('/odeme/callback', [App\Http\Controllers\PaymentController::class, 'callback'])
    ->name('payment.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Ödeme sonuç sayfaları
Route::get('/siparis/basarili/{order}', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
Route::post('/siparis/uye-ol/{order}', [App\Http\Controllers\PaymentController::class, 'registerFromOrder'])->name('payment.register-guest');
Route::get('/siparis/basarisiz/{order}', [App\Http\Controllers\PaymentController::class, 'failed'])->name('payment.failed');
// Havale/EFT siparişi sonrası banka bilgilerinin gösterildiği sayfa
Route::get('/siparis/havale/{order}', [App\Http\Controllers\PaymentController::class, 'bankTransfer'])->name('payment.bank-transfer');

// Contact Pages
Route::get('/iletisim', [PageController::class, 'contact'])->name('contact');
Route::post('/iletisim/gonder', [PageController::class, 'submitContact'])->name('contact.submit');

// Procedural and KVKK pages
Route::get('/sayfa/kvkk', [PageController::class, 'kvkk'])->name('kvkk');
Route::get('/sayfa/{page}', [PageController::class, 'procedural'])->name('procedural');

// User Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/giris', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/giris', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/uye-ol', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/uye-ol', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
    
    // Google OAuth redirects
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback']);
});

Route::middleware('auth')->group(function () {
    Route::post('/cikis', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    
    // User Profile, Addresses & Favorites Routes
    Route::get('/hesabim', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/hesabim/bilgi-guncelle', [\App\Http\Controllers\ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::post('/hesabim/sifre-guncelle', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Address CRUD
    Route::post('/hesabim/adres/ekle', [\App\Http\Controllers\ProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::post('/hesabim/adres/guncelle/{address}', [\App\Http\Controllers\ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::post('/hesabim/adres/sil/{address}', [\App\Http\Controllers\ProfileController::class, 'deleteAddress'])->name('profile.address.delete');
    
    // Favorites
    Route::get('/hesabim/favoriler', [\App\Http\Controllers\ProfileController::class, 'favoritesList'])->name('profile.favorites');
    Route::post('/favori/toggle', [\App\Http\Controllers\ProfileController::class, 'toggleFavorite'])->name('favorite.toggle');
    
    Route::get('/hesabim/siparisler', [\App\Http\Controllers\ProfileController::class, 'orders'])->name('profile.orders');
    Route::get('/hesabim/siparis/{order}', [\App\Http\Controllers\ProfileController::class, 'orderDetail'])->name('profile.order-detail');
});
