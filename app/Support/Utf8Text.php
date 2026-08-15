<?php

namespace App\Support;

final class Utf8Text
{
    /**
     * Normalize text into UTF-8 without double-encoding.
     */
    public static function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[self::normalize($key)] = self::normalize($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        // Decode HTML entities first
        $best = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // If the string is already valid UTF-8, just run the strtr repair map
        // and return – do NOT run mb_convert_encoding which would corrupt clean UTF-8.
        if (mb_check_encoding($best, 'UTF-8')) {
            $repaired = self::repairMojibake($best);
            return trim($repaired);
        }

        // String is NOT valid UTF-8 – try to detect and convert the source encoding.
        for ($i = 0; $i < 3; $i++) {
            $decoded = self::repairMojibake($best);
            $candidates = [$decoded];
            foreach (['Windows-1254', 'ISO-8859-9', 'ISO-8859-1', 'Windows-1252'] as $sourceEncoding) {
                $converted = @mb_convert_encoding($decoded, 'UTF-8', $sourceEncoding);
                if (is_string($converted) && $converted !== '') {
                    $candidates[] = self::repairMojibake($converted);
                }
            }

            $nextBest = $decoded;
            $bestScore = PHP_INT_MAX;
            foreach (array_unique($candidates) as $candidate) {
                $score = self::score($candidate);
                if ($score < $bestScore) {
                    $bestScore = $score;
                    $nextBest = $candidate;
                }
            }

            if ($nextBest === $best) {
                break;
            }

            $best = $nextBest;

            // Stop as soon as we get valid UTF-8
            if (mb_check_encoding($best, 'UTF-8')) {
                break;
            }
        }

        return trim($best);
    }

    public static function sanitizeArray(array $data): array
    {
        return self::normalize($data);
    }

    private static function score(string $value): int
    {
        $score = 0;
        foreach (['Ãƒ', 'Ã‚', 'Ã„', 'ï¿½'] as $needle) {
            $score += substr_count($value, $needle) * 10;
        }

        if (preg_match('/\?{2,}/', $value)) {
            $score += 30;
        }

        return $score + strlen($value);
    }

    private static function repairMojibake(string $value): string
    {
        return strtr($value, [
            'ï»¿' => '',
            'ÃƒÂ¼' => 'ü',
            'ÃƒÂ¶' => 'ö',
            'ÃƒÂ§' => 'ç',
            'ÃƒÂŸ' => 'ß',
            'ÃƒÂ°' => 'ð',
            'ÃƒÂ±' => 'ñ',
            'ÃƒÂª' => 'ê',
            'ÃƒÂ¢' => 'â',
            'ÃƒÂ®' => 'î',
            'ÃƒÂ´' => 'ô',
            'ÃƒÂ»' => 'û',
            'ÃƒÂ©' => 'é',
            'ÃƒÂ€' => 'À',
            'ÃƒÂ‡' => 'Ç',
            'Ãƒâ€¹' => '‹',
            'Ãƒâ€º' => '›',
            'ÃƒÂœ' => 'Ü',
            'ÃƒÂ–' => 'Ö',
            'ÃƒÂ±' => 'ñ',
            'Ã„Â±' => 'ı',
            'Ã„Â°' => 'İ',
            'Ã„Å¸' => 'ğ',
            'Ã…Å¸' => 'ş',
            'Ãƒâ€ž' => 'Ä',
            'Ãƒâ‚¬' => 'À',
            'Ãƒâ€“' => 'Ö',
            'ÃƒÅ“' => 'Ü',
            'Ãƒâ€¡' => 'Ç',
            'ÃƒÂ' => '',
            'Ã–' => 'Ö',
            'Ãœ' => 'Ü',
            'Ã‡' => 'Ç',
            'Ã¶' => 'ö',
            'Ã¼' => 'ü',
            'Ã§' => 'ç',
            'ÃŸ' => 'ß',
            'ÄŸ' => 'ğ',
            'Ä°' => 'İ',
            'Ä±' => 'ı',
            'ÅŸ' => 'ş',
            'Ã¢' => 'â',
            'Ãª' => 'ê',
            'Ã®' => 'î',
            'Ã´' => 'ô',
            'Ã»' => 'û',
            'Ã©' => 'é',
            'ï¿½' => '',
        ]);
    }
}
