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

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = strtr($decoded, [
            'Ã–' => 'Ö',
            'Ãœ' => 'Ü',
            'Ã‡' => 'Ç',
            'Ã–' => 'Ö',
            'Ã¶' => 'ö',
            'Ã¼' => 'ü',
            'Ã§' => 'ç',
            'ÃŸ' => 'ß',
            'ÄŸ' => 'ğ',
            'Ä°' => 'İ',
            'Ä±' => 'ı',
            'ÅŸ' => 'ş',
            'Ã‚' => 'Â',
            'Ã€' => 'À',
            'Ã©' => 'é',
            'Ãª' => 'ê',
            'Ã®' => 'î',
            'Ã´' => 'ô',
            'Ã»' => 'û',
            'Â' => '',
        ]);
        $candidates = [$decoded];
        foreach (['UTF-8', 'Windows-1254', 'ISO-8859-9', 'ISO-8859-1', 'Windows-1252', 'Latin1'] as $sourceEncoding) {
            $converted = @mb_convert_encoding($decoded, 'UTF-8', $sourceEncoding);
            if (is_string($converted) && $converted !== '') {
                $candidates[] = $converted;
            }
        }

        $best = $decoded;
        $bestScore = PHP_INT_MAX;
        foreach (array_unique($candidates) as $candidate) {
            $score = self::score($candidate);
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $candidate;
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
        foreach (['Ã', 'Â', 'Ä', '�'] as $needle) {
            $score += substr_count($value, $needle) * 10;
        }

        if (preg_match('/\?{2,}/', $value)) {
            $score += 30;
        }

        return $score + strlen($value);
    }
}
