<?php

namespace App\Console\Commands;

use App\Models\StudentActivityLog;
use Illuminate\Console\Command;

class PurgeOldActivityLogs extends Command
{
    protected $signature   = 'activity-logs:purge';
    protected $description = 'Son 10 günden eski öğrenci aktivite loglarını siler';

    public function handle(): int
    {
        $cutoff  = now()->subDays(10)->startOfDay();
        $deleted = StudentActivityLog::where('logged_at', '<', $cutoff)->delete();

        $this->info("Silinen kayıt: {$deleted} (kesme tarihi: {$cutoff->toDateTimeString()})");

        return self::SUCCESS;
    }
}
