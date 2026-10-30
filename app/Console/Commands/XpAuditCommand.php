<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Salt okunur XP denetimi: hiçbir kayıt değiştirilmez, yalnızca alışılmadık değerler listelenir.
 * Kullanım: php artisan xp:audit [--top=10]
 */
class XpAuditCommand extends Command
{
    protected $signature = 'xp:audit {--top=10 : Her listede gösterilecek en fazla satır}';

    protected $description = 'Alışılmadık yüksek XP kayıtlarını listeler (salt okunur)';

    public function handle(): int
    {
        $top = max(1, (int) $this->option('top'));
        $names = DB::table('users')->pluck('name', 'id');

        $this->info('1) Toplam XP en yüksek kullanıcılar');
        $this->table(['XP', 'Kullanıcı'], DB::table('user_profiles')->orderByDesc('xp')->limit($top)->get(['user_id', 'xp'])
            ->map(fn ($r) => [$r->xp, ($names[$r->user_id] ?? '?')." (#{$r->user_id})"])->all());

        $this->info('2) Profil XP, bilinen kaynakların (ders/ödev, yarışma, klavye yarışı, quiz) toplamından en çok sapanlar');
        $sum = fn (string $sql) => collect(DB::select($sql))->pluck('s', 'u')->all();
        $sources = [
            $sum('select user_id u, sum(xp_awarded) s from content_progresses group by user_id'),
            $sum('select student_user_id u, sum(xp_earned) s from competition_participants group by student_user_id'),
            $sum('select user_id u, sum(xp_earned) s from race_results where user_id is not null group by user_id'),
            $sum('select student_user_id u, sum(xp_earned) s from live_quiz_answers group by student_user_id'),
        ];
        $rows = [];
        foreach (DB::table('user_profiles')->pluck('xp', 'user_id') as $uid => $xp) {
            $known = 0;
            foreach ($sources as $m) {
                $known += (int) ($m[$uid] ?? 0);
            }
            $rows[] = ['uid' => $uid, 'xp' => (int) $xp, 'known' => $known, 'diff' => (int) $xp - $known];
        }
        $diffs = collect($rows)->pluck('diff')->sort()->values();
        $median = $diffs->isEmpty() ? 0 : $diffs[intdiv($diffs->count(), 2)];
        $this->line("   Medyan sapma: {$median} (çoğu kullanıcıda ortak bir başlangıç/bonus farkı olabilir; asıl bakılacak olan medyandan çok uzaklaşanlardır)");
        usort($rows, fn ($a, $b) => $b['diff'] <=> $a['diff']);
        $this->table(['Profil XP', 'Kaynak toplamı', 'Sapma', 'Medyandan fark', 'Kullanıcı'], array_map(
            fn ($r) => [$r['xp'], $r['known'], $r['diff'], $r['diff'] - $median, ($names[$r['uid']] ?? '?')." (#{$r['uid']})"],
            array_slice($rows, 0, $top)
        ));

        $this->info('3) Ödev XP\'si (genel ödev için beklenen: 15)');
        $this->table(['XP', 'Tür', 'Başlık', 'Öğrenci #'], DB::table('student_homework_progresses as p')
            ->join('course_homeworks as h', 'h.id', '=', 'p.course_homework_id')
            ->where(fn ($q) => $q->where(fn ($g) => $g->where('h.assignment_type', 'homework')->where('p.xp_awarded', '>', 15))
                ->orWhere(fn ($g) => $g->where('h.assignment_type', 'lesson')->where('p.xp_awarded', '>', 15)))
            ->orderByDesc('p.xp_awarded')->limit($top)->get(['p.xp_awarded', 'h.assignment_type', 'h.title', 'p.student_id'])
            ->map(fn ($r) => [$r->xp_awarded, $r->assignment_type, $r->title, $r->student_id])->all());

        $this->info('4) Yarışma ve klavye yarışı: tek kayıtta en yüksek XP');
        $this->table(['Kaynak', 'XP', 'Kullanıcı'], array_merge(
            DB::table('competition_participants')->orderByDesc('xp_earned')->limit($top)->get(['student_user_id', 'xp_earned'])
                ->map(fn ($r) => ['yarışma', $r->xp_earned, ($names[$r->student_user_id] ?? '?')." (#{$r->student_user_id})"])->all(),
            DB::table('race_results')->orderByDesc('xp_earned')->limit($top)->get(['user_id', 'xp_earned', 'wpm', 'elapsed_seconds'])
                ->map(fn ($r) => ['klavye (wpm '.round($r->wpm).', '.$r->elapsed_seconds.' sn)', $r->xp_earned, ($names[$r->user_id] ?? '?')." (#{$r->user_id})"])->all(),
        ));

        return self::SUCCESS;
    }
}
