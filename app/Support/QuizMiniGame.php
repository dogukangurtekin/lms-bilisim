<?php

namespace App\Support;

use App\Http\Controllers\ActivityController;

/**
 * Canlı Quiz soru araları için "mini oyun" ayarları.
 *
 * Soru aralarında öğrenciler, Oyun ve Etkinlikler'den seçilen oyunun 10 bölümünü 30 saniyede çözmeye çalışır.
 * Bölümler oyunun 11. bölümünden sonrasından rastgele seçilir ve oturumdaki HERKES için aynıdır.
 */
class QuizMiniGame
{
    /** Bir mini oyundaki bölüm sayısı ve süre. */
    public const BLOCK_SIZE = 10;
    public const DURATION_MS = 30000;
    /** Rastgele bölümler bu aralıktan seçilir: 10. bölümden sonrası (11+), çok zorlaşmaması için en çok 60. bölüm. */
    public const FIRST_LEVEL = 11;
    public const LAST_LEVEL = 60;
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

    /** Seçilebilecek 10'luk blokların başlangıç bölümleri (11 ... son-9). */
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
     * Her soru arası için 10 ardışık bölümlük bir blok seçer: [['from' => 23, 'to' => 32], ...].
     * Mümkün oldukça birbirinin üstüne binmeyen, art arda tekrar etmeyen bloklar seçilir.
     * Plan oturum başlarken bir kez üretilip saklanır; böylece tüm öğrenciler aynı bölümleri alır.
     */
    public static function plan(string $slug, int $breaks): array
    {
        $starts = self::blockStarts($slug);
        if ($starts === [] || $breaks < 1) {
            return [];
        }

        $plan = [];
        $used = [];
        for ($i = 0; $i < $breaks; $i++) {
            $free = array_values(array_filter($starts, function (int $s) use ($used) {
                foreach ($used as $u) {
                    if (abs($s - $u) < self::BLOCK_SIZE) {
                        return false;
                    }
                }

                return true;
            }));
            if ($free === []) {            // tüm blok yerleri doldu: yalnızca bir önceki bloktan farklı seç
                $used = [];
                $prev = $plan[$i - 1]['from'] ?? null;
                $free = array_values(array_filter($starts, fn (int $s) => $prev === null || abs($s - $prev) >= self::BLOCK_SIZE));
                $free = $free === [] ? $starts : $free;
            }
            $start = $free[random_int(0, count($free) - 1)];
            $used[] = $start;
            $plan[] = ['from' => $start, 'to' => $start + self::BLOCK_SIZE - 1];
        }

        return $plan;
    }
}
