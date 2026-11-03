<?php

namespace Tests\Feature;

use App\Models\Avatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonkeyAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_monkey_avatar_exists_and_is_the_most_expensive_one(): void
    {
        $monkey = Avatar::query()->where('name', 'Kral Maymun')->first();
        $this->assertNotNull($monkey);
        $this->assertTrue($monkey->is_active);
        $this->assertFileExists(public_path($monkey->image_path));

        $others = Avatar::query()->where('id', '!=', $monkey->id)->max('required_xp');
        $this->assertGreaterThan((int) $others, (int) $monkey->required_xp);
        $this->assertSame($monkey->id, Avatar::query()->orderByDesc('required_xp')->value('id'));
    }
}
