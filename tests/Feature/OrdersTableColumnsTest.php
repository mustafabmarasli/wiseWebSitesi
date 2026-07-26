<?php

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\User;

function tableOrder(array $overrides = []): Order
{
    return Order::create(array_merge([
        'first_name' => 'mustafa', 'last_name' => 'maraşlı',
        'email' => 'musteri@example.com', 'phone' => '05512055885',
        'address' => 'numune evler mah. s akin ucar cad. demirçelik ap 75',
        'city' => 'Dörtyol', 'zip_code' => '31600',
        'total_amount' => 4900.00, 'currency' => 'TRY', 'status' => 'paid',
    ], $overrides));
}

it('siparis listesinde telefon gorunur', function () {
    tableOrder();

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->assertCanSeeTableRecords(Order::all())
        ->assertCanRenderTableColumn('phone')
        ->assertSee('05512055885');
});

it('siparis listesinde adres gorunur', function () {
    tableOrder();

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->assertCanRenderTableColumn('address')
        ->assertSee('numune evler mah.')
        ->assertSee('31600 Dörtyol');
});

it('bireysel fatura tipi gorunur', function () {
    tableOrder(['is_corporate' => false]);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->assertCanRenderTableColumn('is_corporate')
        ->assertSee('Bireysel');
});

it('ticari fatura isteyen siparis isaretlenir', function () {
    tableOrder(['is_corporate' => true, 'company_name' => 'Ornek Ltd Sti']);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->assertSee('Ticari Fatura')
        ->assertSee('Ornek Ltd Sti');
});

it('musteri adi soyadi tek sutunda birlesir', function () {
    tableOrder();

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->assertSee('mustafa maraşlı')
        ->assertSee('musteri@example.com');
});

it('musteri adiyla arama calisir', function () {
    tableOrder();
    $other = tableOrder(['first_name' => 'ayse', 'last_name' => 'demir', 'email' => 'ayse@example.com']);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->searchTable('maraşlı')
        ->assertCanSeeTableRecords(Order::where('last_name', 'maraşlı')->get())
        ->assertCanNotSeeTableRecords([$other]);
});

it('telefonla arama calisir', function () {
    $target = tableOrder();
    $other  = tableOrder(['phone' => '05009998877']);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->searchTable('05512055885')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('sehirle arama adres sutunundan calisir', function () {
    $target = tableOrder();
    $other  = tableOrder(['city' => 'Ankara', 'address' => 'Baska adres']);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ListOrders::class)
        ->searchTable('Dörtyol')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});
