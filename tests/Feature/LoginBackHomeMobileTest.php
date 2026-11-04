<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginBackHomeMobileTest extends TestCase
{
    public function test_back_home_becomes_a_small_arrow_house_icon_on_phones_without_losing_the_text_for_screen_readers(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('class="back-home" aria-label="Ana sayfaya dön"', false)
            ->assertSee('class="bh-home"', false)
            ->assertSee('class="bh-text"', false)
            ->assertSee('.bh-text{display:none}', false)
            ->assertSee('.bh-home{display:inline}', false);
    }
}
