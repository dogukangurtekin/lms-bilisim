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
                $this->assertNotContains($b['game'], ['flowchart-runner', 'lightbot-runner', 'dot-cards-runner']);
                $this->assertGreaterThanOrEqual(5, $b['from']);
                $this->assertLessThanOrEqual(30, $b['to']);
            }
        }
        // Lightbot ve Küçük Nokta da canlı quiz mini oyunlarından çıkarıldı.
        foreach (['lightbot-runner', 'dot-cards-runner'] as $slug) {
            $this->assertFalse(QuizMiniGame::supported($slug), $slug);
            $this->assertArrayNotHasKey($slug, QuizMiniGame::options());
        }
        $this->assertEqualsCanonicalizing(['compute-it-runner', 'block-grid-runner', 'block-3d-runner', 'word-bee-runner', 'number-bee-runner'], array_keys(QuizMiniGame::GAMES));
        // Bölümler kolay aralıkta: 5-30
        $this->assertSame([5, 30], [QuizMiniGame::FIRST_LEVEL, QuizMiniGame::LAST_LEVEL]);
    }
}
