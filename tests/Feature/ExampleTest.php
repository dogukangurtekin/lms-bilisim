<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_shows_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bilişim Kod');
    }

    public function test_legal_pages_and_custom_404_render(): void
    {
        $this->get('/gizlilik-politikasi')->assertOk()->assertSee('KVKK');
        $this->get('/kullanim-sartlari')->assertOk();
        $this->get('/cerez-politikasi')->assertOk();
        $this->get('/bu-sayfa-yok')->assertNotFound()->assertSee('Aradığınız sayfa bulunamadı');
    }
}
