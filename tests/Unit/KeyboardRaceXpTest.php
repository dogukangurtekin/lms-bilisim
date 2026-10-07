<?php

namespace Tests\Unit;

use App\Http\Controllers\RaceController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class KeyboardRaceXpTest extends TestCase
{
    public static function correctCharacterCases(): array
    {
        return [
            'no correct characters' => [0, 0],
            'one correct character' => [1, 5],
            'typical race text' => [87, 435],
            'negative value is protected' => [-3, 0],
        ];
    }

    #[DataProvider('correctCharacterCases')]
    public function test_xp_is_five_per_correct_character(int $correctCharacters, int $expectedXp): void
    {
        $method = new ReflectionMethod(RaceController::class, 'calculateXp');

        $this->assertSame(
            $expectedXp,
            $method->invoke(new RaceController(), $correctCharacters)
        );
    }
}
