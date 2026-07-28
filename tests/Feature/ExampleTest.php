<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Bu sinif Pest testi degil, bu yuzden tests/Pest.php icindeki
    // RefreshDatabase otomatik uygulanmiyor; elle eklenmesi gerekiyor.
    // Ana sayfa artik ayarlari veritabanindan okuyor.
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
