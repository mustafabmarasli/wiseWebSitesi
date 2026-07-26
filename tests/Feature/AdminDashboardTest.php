<?php

use App\Models\User;

it('admin paneli dashboard hatasiz yuklenir', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

it('admin olmayan kullanici panele giremez', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('butun dashboard widgetlari render edilir', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    foreach (glob(app_path('Filament/Widgets/*.php')) as $file) {
        $class = 'App\\Filament\\Widgets\\' . basename($file, '.php');

        Livewire::test($class)->assertOk();
    }
});
