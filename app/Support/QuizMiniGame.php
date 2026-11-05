<?php

namespace App\Support;

use App\Http\Controllers\ActivityController;

/**
 * Canlı Quiz soru araları için "mini oyun" ayarları.
 *
 * Mini oyun her soru arasında değil, 4 soruda bir (4., 8., 12. … sorudan sonra) gelir. Öğrenciler Kodlama Uygulamaları'ndaki
 * kodlama oyunlarından birinin 10 ardışık bölümünü 30 saniyede çözmeye çalışır. Oyun (öğretmen belirli bir oyun seçmediyse)
 * ve bölümler her mini oyunda rastgele seçilir; bölümler 5-30 arasındadır ve oturumdaki HERKES için aynıdır.
 */
class QuizMiniGame
{
    /** Bir mini oyundaki bölüm sayısı ve süre. */
    public const BLOCK_SIZE = 10;
    public const DURATION_MS = 30000;
    /** Öğretmenin seçebileceği mini oyun süresi (sn). */
    public const MIN_SECONDS = 10;
    public const MAX_SECONDS = 300;
    /** Mini oyun kaç soruda bir gelir. */
    public const EVERY_N_QUESTIONS = 4;
    /** Rastgele bölümler bu aralıktan seçilir: 5. ile 30. bölümler arası (öğrenciler 30 sn'de çözebilsin diye kolay tutulur). */
    public const FIRST_LEVEL = 5;
    public const LAST_LEVEL = 30;
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
        'word-bee-runner' => ['levels' => 100, 'xp_cap' => 25],
        'number-bee-runner' => ['levels' => 100, 'xp_cap' => 25],
    ];

    /** Herkese açık (hesapsız) oturumlarda kullanılabilecek mini oyunlar: yönetici profilinde "herkese açık" işaretli olanlar. */
    public static function publicGames(): array
    {
        return array_values(array_filter(array_keys(self::GAMES), fn (string $g) => PublicGamesSetting::gameEnabled($g)));
    }

    /** Oturumun mini oyun süresi (ms): öğretmen seçmediyse 30 sn. */
    public static function durationMs(?int $seconds): int
    {
        return $seconds === null || $seconds <= 0
            ? self::DURATION_MS
            : max(self::MIN_SECONDS, min(self::MAX_SECONDS, $seconds)) * 1000;
    }

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
    public static function blockStarts(string $slug, ?int $from = null, ?int $to = null): array
    {
        $from = max(1, $from ?? self::FIRST_LEVEL);
        $last = min($to ?? self::LAST_LEVEL, (int) (self::GAMES[$slug]['levels'] ?? 0));
        $maxStart = $last - self::BLOCK_SIZE + 1;
        if ($maxStart < $from) {
            return [];
        }

        return range($from, $maxStart);
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
     * gelmez). Bölüm bloğu 5-30 arasından rastgele seçilir; aynı oyunda önceki bloklarla üst üste binmemeye çalışılır.
     * Plan oturum başlarken bir kez üretilip saklanır; böylece tüm öğrenciler aynı oyunu ve bölümleri alır.
     */
    public static function plan(?string $fixedGame, int $questionCount, ?array $allowed = null, ?int $from = null, ?int $to = null): array
    {
        $games = self::supported($fixedGame) ? [$fixedGame] : array_keys(self::GAMES);
        if ($allowed !== null) {          // herkese açık oturum: yalnızca profilde "herkese açık" işaretli oyunlar
            $games = array_values(array_intersect($games, $allowed));
        }
        $games = array_values(array_filter($games, fn (string $g) => self::blockStarts($g, $from, $to) !== []));
        if ($games === []) {
            return [];
        }

        $plan = [];
        $prevGame = null;
        foreach (self::breakIndexes($questionCount) as $breakIndex) {
            $candidates = array_values(array_filter($games, fn (string $g) => $g !== $prevGame));
            $candidates = $candidates === [] ? $games : $candidates;
            $game = $candidates[random_int(0, count($candidates) - 1)];

            $lo = max(1, $from ?? self::FIRST_LEVEL);
            $hi = min($to ?? self::LAST_LEVEL, (int) (self::GAMES[$game]['levels'] ?? 0));
            $prevGame = $game;
            // Bölüm sınırı yok: öğrenci süre dolana kadar adminin belirlediği aralıkta ilerleyebildiği kadar ilerler.
            $plan[$breakIndex] = ['game' => $game, 'from' => $lo, 'to' => $hi];
        }

        return $plan;
    }
}
