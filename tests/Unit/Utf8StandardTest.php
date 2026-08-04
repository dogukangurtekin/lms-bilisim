<?php

namespace Tests\Unit;

use App\Support\Utf8Text;
use Tests\TestCase;

class Utf8StandardTest extends TestCase
{
    public function test_it_normalizes_common_turkish_mojibake(): void
    {
        $this->assertSame('Öğretmen', Utf8Text::normalize('Ã–ÄŸretmen'));
        $this->assertSame('Kazanımlar', Utf8Text::normalize('KazanÄ±mlar'));
        $this->assertSame('Derse Başla', Utf8Text::normalize('Derse BaÅŸla'));
    }
}
