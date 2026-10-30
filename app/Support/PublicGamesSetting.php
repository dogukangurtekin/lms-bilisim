<?php

namespace App\Support;

/**
 * Anasayfadaki "Oyunlar" menüsünden giriş yapmadan açılan herkese açık oyunlar.
 * Admin, profil sayfasından tümünü ya da tek tek oyunları açıp kapatabilir.
 * Dosya yoksa tüm uygun oyunlar ve tüm bölümler açıktır.
 * Bölüm sınırı: 0 = tüm bölümler, N = her oyunda yalnızca ilk N bölüm (deneme amaçlı).
 */
class PublicGamesSetting
{
    /** Giriş/ödev/sunucu API'si olmadan çalıştığı doğrulanan oyunlar (slug => anasayfa kartı açıklaması). */
    public const GAMES = [
        'word-bee-runner' => 'Harfleri toplayarak kelimeyi hecele.',
        'number-bee-runner' => 'Arıyı yönlendirip rakamları sırayla topla.',
        'dot-cards-runner' => 'Kod kartlarını seç, küçük noktayı hedefe götür.',
        'flowchart-runner' => 'Akış şemasıyla algoritma kurmayı öğren.',
        'lightbot-runner' => 'Robotu komutlarla yönlendirip ışıkları yak.',
        'connect-the-dots-runner' => 'Noktaları komutlarla birleştirip hedef şekli çiz.',
        'flamestone-game' => 'Alev taşını engelleri aşarak hedefe ulaştır.',
        'compute-it-runner' => 'Komutlarla hesaplayıp bölümleri çöz, sayaçları hedefe getir.',
        'block-3d-runner' => '3D blok kodlama ile karakteri hedefe ulaştır.',
        'silent-teacher-runner' => 'Python kodunu oku, çıktıyı tahmin et, soruları çöz.',
        'bee-garden-runner' => 'CSS kodlayarak arıyı bahçede gezdir.',
        'web-lab-runner' => 'HTML, CSS ve JavaScript ile mini web projeleri yap.',
        'keyboard-finger-runner' => 'Doğru parmakla yazmayı adım adım öğren.',
        'python-editor' => 'Tarayıcıda Python kodu yaz ve çalıştır.',
        'word-runner' => 'Word benzeri belge editörüyle çalış.',
        'excel-runner' => 'Excel benzeri hesap tablosunda formüllerle çalış.',
    ];

    public const MAX_LIMIT = 20;
    public const MAX_HW_LIMIT = 999;

    private static ?array $data = null;

    private static function path(): string
    {
        return storage_path('app/public-games.json');
    }

    private static function data(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }
        $enabled = true;
        $games = [];
        $levelLimit = 0;
        if (is_file(self::path())) {
            $decoded = json_decode((string) @file_get_contents(self::path()), true);
            if (is_array($decoded)) {
                $enabled = (bool) ($decoded['enabled'] ?? true);
                $levelLimit = max(0, min(self::MAX_LIMIT, (int) ($decoded['level_limit'] ?? 0)));
                foreach ((array) ($decoded['games'] ?? []) as $slug => $on) {
                    if (isset(self::GAMES[$slug])) {
                        $games[$slug] = (bool) $on;
                    }
                }
            }
        }

        return self::$data = ['enabled' => $enabled, 'games' => $games, 'level_limit' => $levelLimit];
    }

    /**
     * Herkese açık ödev linkinden gelen ziyaretçi için oturuma yazılan izin:
     * ['slug' => oyun, 'limit' => ilk N bölüm (0 = genel ayar)]. Yalnızca o oyun için geçerlidir.
     */
    private static function homeworkOverride(string $slug): ?array
    {
        try {
            $o = session('public_homework');
        } catch (\Throwable) {
            return null;
        }

        return is_array($o) && ($o['slug'] ?? null) === $slug ? $o : null;
    }

    public static function enabled(): bool
    {
        return self::data()['enabled'];
    }

    /** 0 = tüm bölümler açık; N = her oyunda yalnızca ilk N bölüm oynanır. */
    public static function levelLimit(?string $slug = null): int
    {
        if ($slug !== null && ($o = self::homeworkOverride($slug)) && (int) ($o['limit'] ?? 0) > 0) {
            return min(self::MAX_HW_LIMIT, (int) $o['limit']);
        }

        return self::data()['level_limit'];
    }

    public static function gameEnabled(string $slug): bool
    {
        if (! isset(self::GAMES[$slug])) {
            return false;
        }
        if (self::homeworkOverride($slug)) {
            return true;
        }

        return self::enabled() && (self::data()['games'][$slug] ?? true);
    }

    /** Menüde/listede görünecek açık oyunlar: slug => açıklama. */
    public static function openGames(): array
    {
        return array_filter(self::GAMES, fn ($desc, $slug) => self::gameEnabled($slug), ARRAY_FILTER_USE_BOTH);
    }

    /** Admin ekranı için: tek tek ayar (genel anahtardan bağımsız). */
    public static function perGame(): array
    {
        $out = [];
        foreach (array_keys(self::GAMES) as $slug) {
            $out[$slug] = self::data()['games'][$slug] ?? true;
        }

        return $out;
    }

    public static function save(bool $enabled, array $games, int $levelLimit = 0): void
    {
        $clean = [];
        foreach (array_keys(self::GAMES) as $slug) {
            $clean[$slug] = ! empty($games[$slug]);
        }
        $data = ['enabled' => $enabled, 'games' => $clean, 'level_limit' => max(0, min(self::MAX_LIMIT, $levelLimit))];
        file_put_contents(self::path(), json_encode($data), LOCK_EX);
        self::$data = $data;
    }
}
