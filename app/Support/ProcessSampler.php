<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Hosting hesabının "Maksimum İşlem" sınırına (120) ne yaklaştığını görmek için hafif örnekleyici.
 * /proc'tan bu kullanıcıya ait işlem ve iş parçacığı sayısını okur; storage/logs/process-samples.log'a
 * satır başına bir JSON yazar. Veritabanına dokunmaz; dakikada en fazla bir kez çalışır.
 */
class ProcessSampler
{
    public static function maybeRecord(): void
    {
        try {
            if (Cache::add('proc_sample_gate', 1, 60)) {
                self::record();
            }
        } catch (\Throwable) {
            // Ölçüm hiçbir zaman isteği bozmamalı.
        }
    }

    public static function record(): void
    {
        $uid = @fileowner('/proc/'.getmypid());
        $entries = @scandir('/proc');
        if ($uid === false || ! is_array($entries)) {
            return;
        }

        $procs = 0;
        $threads = 0;
        $comm = [];
        $commThreads = [];
        $state = [];
        foreach ($entries as $pid) {
            if (! ctype_digit($pid) || @fileowner('/proc/'.$pid) !== $uid) {
                continue;
            }
            $stat = @file_get_contents('/proc/'.$pid.'/stat');
            if ($stat === false || ! preg_match('/^\d+ \((.*)\) (\S) (.*)$/s', $stat, $m)) {
                continue;
            }
            $f = explode(' ', $m[3]);
            $procs++;
            $n = (int) ($f[16] ?? 1); // num_threads (stat alan 20; ppid'den sonra 0 tabanlı sıra 16)
            $threads += $n;
            $commThreads[$m[1]] = ($commThreads[$m[1]] ?? 0) + $n;
            $comm[$m[1]] = ($comm[$m[1]] ?? 0) + 1;
            $state[$m[2]] = ($state[$m[2]] ?? 0) + 1;
        }
        arsort($comm);
        arsort($commThreads);

        $file = storage_path('logs/process-samples.log');
        if (is_file($file) && filesize($file) > 1_000_000) {
            @rename($file, $file.'.1');
        }
        @file_put_contents($file, json_encode([
            't' => now()->toIso8601String(),
            'procs' => $procs,
            'threads' => $threads,
            'comm' => array_slice($comm, 0, 6, true),
            'threads_by_comm' => array_slice($commThreads, 0, 6, true),
            'state' => $state,
        ], JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
