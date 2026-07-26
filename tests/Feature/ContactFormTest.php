<?php

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name'    => 'Ahmet Yilmaz',
        'email'   => 'ahmet@example.com',
        'subject' => 'Siparis hakkinda soru',
        'message' => 'Merhaba, siparisim ne zaman kargoya verilir?',
    ], $overrides);
}

it('iletisim mesaji veritabanina kaydedilir', function () {
    Mail::fake();

    $this->post(route('contact.submit'), contactPayload())
        ->assertRedirect()
        ->assertSessionHas('success');

    $message = ContactMessage::sole();

    expect($message->name)->toBe('Ahmet Yilmaz');
    expect($message->email)->toBe('ahmet@example.com');
    expect($message->subject)->toBe('Siparis hakkinda soru');
    expect($message->read_at)->toBeNull();
});

it('yonetici adresine e-posta gonderilir', function () {
    Mail::fake();

    $this->post(route('contact.submit'), contactPayload());

    Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
        return $mail->hasTo(config('mail.admin_address'));
    });
});

it('e-posta info@wisesolutions.com.tr adresine gider', function () {
    Mail::fake();

    $this->post(route('contact.submit'), contactPayload());

    Mail::assertSent(ContactMessageMail::class, fn ($mail) => $mail->hasTo('info@wisesolutions.com.tr'));
});

it('yanit adresi ziyaretcinin adresidir', function () {
    Mail::fake();

    $this->post(route('contact.submit'), contactPayload(['email' => 'musteri@ornek.com']));

    Mail::assertSent(ContactMessageMail::class, fn ($mail) => $mail->hasReplyTo('musteri@ornek.com'));
});

it('e-posta gonderimi patlarsa bile mesaj kaydedilir', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP baglanamadi'));

    $this->post(route('contact.submit'), contactPayload())
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(1);
});

it('eksik alanlar reddedilir', function () {
    Mail::fake();

    $this->post(route('contact.submit'), ['name' => 'Sadece isim'])
        ->assertSessionHasErrors(['email', 'subject', 'message']);

    expect(ContactMessage::count())->toBe(0);
});

it('mesaj panelde goruntulenince okundu isaretlenir', function () {
    Mail::fake();
    $this->post(route('contact.submit'), contactPayload());

    $message = ContactMessage::sole();
    expect($message->read_at)->toBeNull();

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get("/admin/contact-messages/{$message->id}")
        ->assertOk();

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('mesaj listesi admin panelinde acilir', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/contact-messages')
        ->assertOk();
});
