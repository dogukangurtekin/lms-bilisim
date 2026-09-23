<?php

namespace App\Http\Controllers;

use App\Models\ActivityAttempt;
use App\Models\Attendance;
use App\Models\BlockBuilderDesign;
use App\Models\BookTaskProgress;
use App\Models\ClassBoardPost;
use App\Models\CompetitionParticipant;
use App\Models\ContentProgress;
use App\Models\CourseFavorite;
use App\Models\Flowchart;
use App\Models\GameState;
use App\Models\Grade;
use App\Models\Leaderboard;
use App\Models\Level;
use App\Models\LiveQuizAnswer;
use App\Models\LiveQuizParticipant;
use App\Models\RaceResult;
use App\Models\Score;
use App\Models\Student;
use App\Models\StudentGameAssignmentProgress;
use App\Models\StudentHomeworkProgress;
use App\Models\StudentReport;
use App\Models\StudentTimeStat;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Models\UserXpLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemMaintenanceController extends Controller
{
    /**
     * Sadece admin'e ozel: ogretmenler, ogrenci hesaplari ve siniflar
     * OLDUGU GIBI kalir - ama ogrencilere ait TUM ilerleme/sonuc verisi
     * (notlar, quiz/oyun/etkinlik/odev/ders ilerlemesi, XP, rozetler,
     * satin alinan avatarlar, devamsizlik vb.) kalici olarak siliniyor.
     * Amac: sanki ogrenciler sisteme yeni yuklenmis gibi tertemiz bir
     * baslangic durumu elde etmek.
     */
    public function resetStudentData(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $request->validate([
            'confirm_phrase' => ['required', 'string'],
        ]);
        if (trim((string) $request->input('confirm_phrase')) !== 'SİSTEMİ SIFIRLA') {
            return back()->withErrors(['confirm_phrase' => 'Onay metni hatalı. Sıfırlama yapılmadı.']);
        }

        $studentIds = Student::query()->pluck('id');
        $studentUserIds = Student::query()->pluck('user_id');

        DB::transaction(function () use ($studentIds, $studentUserIds) {
            // --- Notlar, devamsizlik, odev/oyun/ders ilerlemesi -----------
            Grade::whereIn('student_id', $studentIds)->delete();
            Attendance::whereIn('student_id', $studentIds)->delete();
            StudentHomeworkProgress::whereIn('student_id', $studentIds)->delete();
            StudentGameAssignmentProgress::whereIn('student_id', $studentIds)->delete();
            StudentTimeStat::whereIn('student_id', $studentIds)->delete();
            ClassBoardPost::whereIn('student_id', $studentIds)->delete();

            // --- Icerik/ders tamamlama, favoriler, XP, streak, raporlar ---
            ContentProgress::whereIn('user_id', $studentUserIds)->delete();
            CourseFavorite::whereIn('user_id', $studentUserIds)->delete();
            UserXpLog::whereIn('user_id', $studentUserIds)->delete();
            UserStreak::whereIn('user_id', $studentUserIds)->delete();
            StudentReport::whereIn('user_id', $studentUserIds)->delete();

            // --- Oyun/etkinlik sonuclari (quiz, yaris, kod editoru vb.) ---
            ActivityAttempt::whereIn('user_id', $studentUserIds)->delete();
            Leaderboard::whereIn('user_id', $studentUserIds)->delete();
            Score::whereIn('user_id', $studentUserIds)->delete();
            RaceResult::whereIn('user_id', $studentUserIds)->delete();
            GameState::whereIn('user_id', $studentUserIds)->delete();
            Level::whereIn('user_id', $studentUserIds)->delete();
            Flowchart::whereIn('user_id', $studentUserIds)->delete();
            BlockBuilderDesign::whereIn('user_id', $studentUserIds)->delete();
            BookTaskProgress::whereIn('user_id', $studentUserIds)->delete();

            // --- Canli Yarisma / Canli Quiz katilim ve sonuclari ----------
            CompetitionParticipant::whereIn('student_user_id', $studentUserIds)->delete();
            LiveQuizAnswer::whereIn('student_user_id', $studentUserIds)->delete();
            LiveQuizParticipant::whereIn('student_user_id', $studentUserIds)->delete();

            // --- Satin alinan avatarlar, kazanilan rozetler ----------------
            DB::table('student_avatar')->whereIn('student_id', $studentIds)->delete();
            DB::table('student_badge')->whereIn('student_id', $studentIds)->delete();
            Student::whereIn('id', $studentIds)->update([
                'current_avatar_id' => null,
                'avatar_xp_spent' => 0,
            ]);

            // --- Profil XP/sure/avatar bilgisi sifirla (kimlik bilgisi kalir) ---
            UserProfile::whereIn('user_id', $studentUserIds)->update([
                'xp' => 0,
                'total_time_seconds' => 0,
                'selected_avatar_id' => null,
            ]);
        });

        return redirect()->route('profile.edit')->with('ok', 'Sistem sıfırlandı: öğretmenler, öğrenciler ve sınıflar korundu, öğrencilere ait tüm ilerleme/sonuç verisi silindi.');
    }
}
