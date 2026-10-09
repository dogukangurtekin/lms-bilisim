<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_profiles') || ! Schema::hasTable('student_reports')) {
            return;
        }

        DB::table('student_reports')
            ->select(['user_id', 'total_xp'])
            ->where('total_xp', '>', 0)
            ->orderBy('user_id')
            ->chunkById(250, function ($reports): void {
                foreach ($reports as $report) {
                    DB::table('user_profiles')
                        ->where('user_id', $report->user_id)
                        ->where('xp', '<', (int) $report->total_xp)
                        ->update([
                            'xp' => (int) $report->total_xp,
                            'updated_at' => now(),
                        ]);
                }
            }, 'user_id');
    }

    public function down(): void
    {
        // Geri yüklenen kazanılmış XP güvenli biçimde azaltılamaz.
    }
};
