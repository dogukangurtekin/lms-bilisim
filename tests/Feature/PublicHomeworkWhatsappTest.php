<?php

namespace Tests\Feature;

use App\Support\PublicHomework;
use Tests\TestCase;

class PublicHomeworkWhatsappTest extends TestCase
{
    public function test_whatsapp_link_carries_the_prepared_parent_message_with_the_public_link(): void
    {
        $link = 'https://bilisimkod.com/acik-odev/cOs7vhkezW0UItiQDAmz9DyW42XEhH7j';
        $text = PublicHomework::whatsappMessage($link);

        $this->assertStringStartsWith('Değerli velim, Bilişim Teknolojileri sınavı için çalışma sayfasına ister ', $text);
        $this->assertStringContainsString("Bilgilerinize\nSınav Çalışma linki: {$link}\nDoğukan Gürtekin\nBilişim Teknolojileri Öğrt.", $text);

        $url = PublicHomework::whatsappUrl($link);
        $this->assertStringStartsWith('https://wa.me/?text=', $url);
        $this->assertSame($text, rawurldecode(substr($url, strlen('https://wa.me/?text='))));
    }
}
