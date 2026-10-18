<?php

namespace App\Support;

use ZipArchive;

/**
 * xlsx / csv / txt dosyasındaki ilk sayfayı satır dizisi olarak okur.
 * Excel'in boş bıraktığı hücreler atlanabildiği için sütun konumları korunur (boşluklar '' ile doldurulur).
 */
class SpreadsheetReader
{
    /** @return array<int, array<int, string>>|null */
    public static function read(string $path, string $extension): ?array
    {
        $extension = strtolower($extension);

        if (in_array($extension, ['csv', 'txt'], true)) {
            return self::readDelimited($path);
        }
        if ($extension === 'xlsx') {
            return self::readXlsx($path);
        }

        return null;
    }

    private static function readDelimited(string $path): ?array
    {
        $raw = @file_get_contents($path);
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        if (! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1254');
        }
        $first = strtok($raw, "\n") ?: '';
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : (substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',');

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);
        while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($cols === [null] || $cols === []) {
                continue;
            }
            $rows[] = array_map(static fn ($c) => (string) $c, $cols);
        }
        fclose($handle);

        return $rows ?: null;
    }

    private static function readXlsx(string $path): ?array
    {
        if (! class_exists(ZipArchive::class)) {
            return null;
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml)) {
            $sx = @simplexml_load_string($sharedXml);
            if ($sx !== false && isset($sx->si)) {
                foreach ($sx->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string) $si->t;
                        continue;
                    }
                    $parts = [];
                    foreach ($si->r ?? [] as $run) {
                        $parts[] = (string) ($run->t ?? '');
                    }
                    $shared[] = implode('', $parts);
                }
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! is_string($sheetXml)) {
            return null;
        }
        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || ! isset($sheet->sheetData->row)) {
            return null;
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cols = [];
            $auto = 0;
            foreach ($row->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $raw = (string) ($cell->v ?? '');
                $value = match ($type) {
                    's' => $shared[(int) $raw] ?? '',
                    'inlineStr' => self::inlineText($cell),
                    default => $raw,
                };
                $index = self::columnIndex((string) ($cell['r'] ?? ''));
                $cols[$index ?? $auto] = $value;
                $auto = ($index ?? $auto) + 1;
            }
            if ($cols === []) {
                continue;
            }
            $max = max(array_keys($cols));
            $line = [];
            for ($i = 0; $i <= $max; $i++) {
                $line[] = (string) ($cols[$i] ?? '');
            }
            $rows[] = $line;
        }

        return $rows ?: null;
    }

    private static function inlineText(\SimpleXMLElement $cell): string
    {
        if (isset($cell->is->t)) {
            return (string) $cell->is->t;
        }
        $parts = [];
        foreach ($cell->is->r ?? [] as $run) {
            $parts[] = (string) ($run->t ?? '');
        }

        return implode('', $parts);
    }

    private static function columnIndex(string $ref): ?int
    {
        if (! preg_match('/^([A-Z]+)/i', $ref, $m)) {
            return null;
        }
        $n = 0;
        foreach (str_split(strtoupper($m[1])) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }

        return max(0, $n - 1);
    }
}
