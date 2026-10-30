<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Dosya önbelleği süresi dolan kayıtları kendiliğinden silmez; zamanla binlerce ölü dosya birikir ve disk I/O'sunu artırır.
 * Her dosyanın ilk 10 karakteri bitiş zamanıdır; yalnızca bitişi geçmiş dosyalar silinir.
 */
class PurgeExpiredFileCache extends Command
{
    protected $signature = 'cache:purge-expired';
    protected $description = 'Süresi dolmuş dosya önbelleği kayıtlarını siler';

    public function handle(): int
    {
        $dir = storage_path('framework/cache/data');
        if (! is_dir($dir)) {
            return self::SUCCESS;
        }

        $now = time();
        $removed = 0;
        $bytes = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $handle = @fopen($file->getPathname(), 'r');
            if (! $handle) {
                continue;
            }
            $expires = (int) fread($handle, 10);
            fclose($handle);
            if ($expires > 0 && $expires < $now) {
                $size = $file->getSize();
                if (@unlink($file->getPathname())) {
                    $removed++;
                    $bytes += $size;
                }
            }
        }

        $this->info("Silinen: {$removed} dosya, " . round($bytes / 1024) . ' KB');

        return self::SUCCESS;
    }
}
