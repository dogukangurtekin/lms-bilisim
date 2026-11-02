<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Yavaş sayfaları bulmak için: SLOW_REQUEST_SECONDS (varsayılan 2 sn) üzerinde süren istekleri
 * storage/logs/slow-requests.log dosyasına tek satır JSON olarak yazar. Yanıt gönderildikten sonra
 * (terminate) yazıldığı için isteği geciktirmez. Adres şablonu yazılır (token/sorgu parametresi yazılmaz).
 */
class SlowRequestLogger
{
    private int $queries = 0;

    private float $queryMs = 0.0;

    public function handle(Request $request, Closure $next): Response
    {
        DB::listen(function ($q): void {
            $this->queries++;
            $this->queryMs += (float) $q->time;
        });

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $start = defined('LARAVEL_START') ? (float) LARAVEL_START : (float) $request->server('REQUEST_TIME_FLOAT');
            if ($start <= 0) {
                return;
            }
            $elapsed = microtime(true) - $start;
            $limit = (float) env('SLOW_REQUEST_SECONDS', 2);
            if ($elapsed < $limit) {
                return;
            }
            $file = storage_path('logs/slow-requests.log');
            if (is_file($file) && filesize($file) > 2097152) {
                @file_put_contents($file, '');
            }
            $line = json_encode([
                't' => now()->toIso8601String(),
                'sec' => round($elapsed, 2),
                'method' => $request->method(),
                'route' => $request->route()?->getName() ?? $request->route()?->uri() ?? $request->path(),
                'status' => $response->getStatusCode(),
                'user' => $request->user()?->id,
                'queries' => $this->queries,
                'db_ms' => round($this->queryMs),
                'mem_mb' => round(memory_get_peak_usage(true) / 1048576),
            ], JSON_UNESCAPED_UNICODE);
            @file_put_contents($file, $line."\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // Ölçüm hiçbir zaman isteği bozmamalı.
        }
    }
}
