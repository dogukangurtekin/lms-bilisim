<?php

namespace Tests\Feature;

use App\Support\QuizMiniGame;
use Tests\TestCase;

class QuizMiniGameExclusionTest extends TestCase
{
    public function test_flowchart_is_not_a_quiz_mini_game_and_never_comes_up_randomly_or_in_the_list(): void
    {
        $this->assertFalse(QuizMiniGame::supported('flowchart-runner'));
        $this->assertArrayNotHasKey('flowchart-runner', QuizMiniGame::options());

        for ($i = 0; $i < 50; $i++) {
            foreach (QuizMiniGame::plan(null, 41) as $b) {
                $this->assertNotSame('flowchart-runner', $b['game']);
            }
        }
        // 7 kodlama oyunu kalır
        $this->assertCount(7, QuizMiniGame::GAMES);
    }
}
