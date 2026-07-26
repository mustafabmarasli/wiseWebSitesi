<?php

return [
    /*
    |--------------------------------------------------------------------------
    | iyzico Ödeme Sistemi
    |--------------------------------------------------------------------------
    |
    | Anahtarlar YALNIZCA .env üzerinden gelir. Buraya sandbox anahtarı
    | fallback olarak yazılırsa, canlıda .env eksik kaldığında sistem sessizce
    | sandbox'a düşer: müşteri "ödeme başarılı" görür ama para tahsil edilmez.
    | Bu yüzden varsayılan değer bilinçli olarak null bırakılmıştır.
    |
    */

    'api_key'    => env('IYZICO_API_KEY'),
    'secret_key' => env('IYZICO_SECRET_KEY'),
    'base_url'   => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com'),
];
