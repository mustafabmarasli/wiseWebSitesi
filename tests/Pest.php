<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Doğrulama algoritmasından geçen geçerli bir TC Kimlik No.
 *
 * Ödeme formu gerçek TC algoritmasını uyguluyor; 11111111111 gibi
 * uydurma numaralar reddedilir.
 */
const GECERLI_TC = '12345678950';

/**
 * Test için il/ilçe/mahalle zinciri oluşturur.
 *
 * Konum verisi migration ile değil SQL dökümünden geldiği için test
 * veritabanında hiç kayıt yoktur; ödeme testleri bunu kendisi kurar.
 *
 * @return array{province_id: int, district_id: int, neighborhood_id: int}
 */
function testLocation(): array
{
    $province = \App\Models\Province::create(['name' => 'Kayseri']);

    $district = \App\Models\District::create([
        'province_id' => $province->id,
        'name'        => 'Melikgazi',
    ]);

    $neighborhood = \App\Models\Neighborhood::create([
        'district_id' => $district->id,
        'name'        => 'Numune Evler',
    ]);

    return [
        'province_id'     => $province->id,
        'district_id'     => $district->id,
        'neighborhood_id' => $neighborhood->id,
    ];
}

/**
 * Ödeme formunun kabul ettiği eksiksiz bir istek gövdesi.
 */
function odemePayload(array $overrides = []): array
{
    return array_merge([
        'first_name'      => 'Test',
        'last_name'       => 'Kullanici',
        'email'           => 'misafir@example.com',
        'phone'           => '05551112233',
        'address_detail'  => 'Test Sokak No:1 Daire:2',
        'identity_number' => GECERLI_TC,
        'billing_same'    => '1',
        'agree_sales'     => '1',
        'agree_kvkk'      => '1',
        'agree_accuracy'  => '1',
    ], testLocation(), $overrides);
}
