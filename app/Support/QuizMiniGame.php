<?php

namespace App\Support;

use App\Http\Controllers\ActivityController;

/**
 * Canlı Quiz soru araları için "mini oyun" ayarları.
 *
 * Mini oyun her soru arasında değil, 4 soruda bir (4., 8., 12. … sorudan sonra) gelir. Öğrenciler Oyun ve Etkinlikler'deki
 * kodlama oyunlarından birinin 10 ardışık bölümünü 30 saniyede çözmeye çalışır. Oyun (öğretmen belirli bir oyun seçmediyse)
 * ve bölümler her mini oyunda rastgele seçilir; bölümler 10-40 arasındadır ve oturumdaki HERKES için aynıdır.
 */
class QuizMiniGame
{
    /** Bir mini oyundaki bölüm sayısı ve süre. */
    public const BLOCK_SIZE = 10;
    public const DURATION_MS = 30000;
    /** Mini oyun kaç soruda bir gelir. */
    public const EVERY_N_QUESTIONS = 4;
    /** Rastgele bölümler bu aralıktan seçilir: 10. ile 40. bölümler arası. */
    public const FIRST_LEVEL = 10;
    public const LAST_LEVEL = 40;
    /** Art arda iki bölüm tamamlama bildirimi arasındaki en kısa süre (sunucu doğrulaması). */
    public const MIN_LEVEL_GAP_MS = 1200;

    /**
     * Mesaj sözleşmesi doğrulanmış, bölüm tabanlı oyunlar.
     * levels = oyundaki bölüm sayısı, xp_cap = tek bölümden alınabilecek en yüksek XP (sunucu üst sınırı).
     */
    public const GAMES = [
        'compute-it-runner' => ['levels' => 120, 'xp_cap' => 65],
        'block-grid-runner' => ['levels' => 200, 'xp_cap' => 30],
        'block-3d-runner' => ['levels' => 200, 'xp_cap' => 30],
        'lightbot-runner' => ['levels' => 100, 'xp_cap' => 20],
        'dot-cards-runner' => ['levels' => 100, 'xp_cap' => 25],
        'word-bee-runner' => ['levels' => 100, 'xp_cap' => 25],
        'number-bee-runner' => ['levels' => 100, 'xp_cap' => 25],
        'flowchart-runner' => ['levels' => 40, 'xp_cap' => 25],
    ];

    public static function supported(?string $slug): bool
    {
        return $slug !== null && isset(self::GAMES[$slug]);
    }

    /** Öğretmenin seçebileceği oyunlar: slug => ad. */
    public static function options(): array
    {
        $all = ActivityController::games();
        $out = [];
        foreach (array_keys(self::GAMES) as $slug) {
            $out[$slug] = (string) ($all[$slug]['name'] ?? $slug);
        }

        return $out;
    }

    public static function name(string $slug): string
    {
        return self::options()[$slug] ?? $slug;
    }

    public static function xpCap(string $slug): int
    {
        return (int) (self::GAMES[$slug]['xp_cap'] ?? 30);
    }

    /** Seçilebilecek 10'luk blokların başlangıç bölümleri (10 ... son-9). */
    public static function blockStarts(string $slug): array
    {
        $last = min(self::LAST_LEVEL, (int) (self::GAMES[$slug]['levels'] ?? 0));
        $maxStart = $last - self::BLOCK_SIZE + 1;
        if ($maxStart < self::FIRST_LEVEL) {
            return [];
        }

        return range(self::FIRST_LEVEL, $maxStart);
    }

    /**
     * Mini oyunun geleceği soru arası numaraları (0 tabanlı: 3 = 4. sorudan sonra). Son sorudan sonra bölüm olmaz.
     * Örn. 9 soru → [3, 7]; 4 soru → [].
     */
    public static function breakIndexes(int $questionCount): array
    {
        $out = [];
        for ($i = self::EVERY_N_QUESTIONS - 1; $i < $questionCount - 1; $i += self::EVERY_N_QUESTIONS) {
            $out[] = $i;
        }

        return $out;
    }

    /**
     * Oturumun mini oyun planını üretir: [soru arası => ['game' => slug, 'from' => 23, 'to' => 32], ...].
     * $fixedGame verilirse hep o oyun, yoksa her mini oyun için tüm kodlama oyunları arasından rastgele (art arda aynı oyun
     * gelmez). Bölüm bloğu 10-40 arasından rastgele seçilir; aynı oyunda önceki bloklarla üst üste binmemeye çalışılır.
     * Plan oturum başlarken bir kez üretilip saklanır; böylece tüm öğrenciler aynı oyunu ve bölümleri alır.
     */
    public static function plan(?string $fixedGame, int $questionCount): array
    {
        $games = self::supported($fixedGame) ? [$fixedGame] : array_keys(self::GAMES);
        $games = array_values(array_filter($games, fn (string $g) => self::blockStarts($g) !== []));
        if ($games === []) {
            return [];
        }

        $plan = [];
        $usedByGame = [];
        $prevGame = null;
        foreach (self::breakIndexes($questionCount) as $breakIndex) {
            $candidates = array_values(array_filter($games, fn (string $g) => $g !== $prevGame));
            $candidates = $candidates === [] ? $games : $candidates;
            $game = $candidates[random_int(0, count($candidates) - 1)];

            $starts = self::blockStarts($game);
            $used = $usedByGame[$game] ?? [];
            $free = array_values(array_filter($starts, function (int $s) use ($used) {
                foreach ($used as $u) {
                    if (abs($s - $u) < self::BLOCK_SIZE) {
                        return false;
                    }
                }

                return true;
            }));
            if ($free === []) {            // bu oyunda boş blok kalmadı: yalnızca en son bloktan farklı seç
                $last = $used === [] ? null : end($used);
                $free = array_values(array_filter($starts, fn (int $s) => $last === null || $s !== $last));
                $free = $free === [] ? $starts : $free;
            }
            $start = $free[random_int(0, count($free) - 1)];
            $usedByGame[$game][] = $start;
            $prevGame = $game;
            $plan[$breakIndex] = ['game' => $game, 'from' => $start, 'to' => $start + self::BLOCK_SIZE - 1];
        }

        return $plan;
    }
}
