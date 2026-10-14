<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Öğrenci ⇄ öğretmen/admin tek seferlik mesajlaşma.
 * Kural: öğrencinin cevaplanmamış bir mesajı varken yeni mesaj yazamaz;
 * öğretmen/admin cevap verince öğrenciye bir mesaj hakkı daha doğar.
 */
class StudentMessageController extends Controller
{
    private function currentStudent(): Student
    {
        $student = Student::with(['schoolClass.teacher.user'])->where('user_id', auth()->id())->first();
        abort_if(! $student, 403, 'Bu hesap için öğrenci kaydı bulunamadı.');

        return $student;
    }

    public function studentIndex(): View
    {
        $student = $this->currentStudent();

        $messages = StudentMessage::query()
            ->with(['recipient', 'replier'])
            ->where('student_id', $student->id)
            ->latest('id')
            ->limit(30)
            ->get();

        // Cevabı gören öğrencinin bildirim sayacı sıfırlanır.
        StudentMessage::query()
            ->where('student_id', $student->id)
            ->whereNotNull('replied_at')
            ->whereNull('student_read_at')
            ->update(['student_read_at' => now()]);

        $teacher = $student->schoolClass?->teacher;

        return view('student-portal.messages', [
            'student' => $student,
            'messages' => $messages,
            'teacherName' => $teacher?->user?->name,
            'hasTeacher' => (bool) $teacher?->user_id,
            'canSend' => ! StudentMessage::hasPendingFor($student->id),
        ]);
    }

    public function studentSend(Request $request): RedirectResponse
    {
        $student = $this->currentStudent();
        $data = $request->validate([
            'recipient_type' => ['required', 'in:teacher,admin'],
            'body' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'body.required' => 'Lütfen mesajını yaz.',
            'body.min' => 'Mesajın çok kısa.',
            'body.max' => 'Mesajın en fazla 500 karakter olabilir.',
        ]);

        $recipientUserId = null;
        if ($data['recipient_type'] === 'teacher') {
            $recipientUserId = $student->schoolClass?->teacher?->user_id;
            if (! $recipientUserId) {
                return back()->withInput()->with('error', 'Sana atanmış bir öğretmen bulunamadı. Mesajı yöneticiye gönderebilirsin.');
            }
        }

        // Çift tıklama / eşzamanlı istekte iki mesaj geçmesin diye öğrenci satırı kilitlenir.
        $sent = DB::transaction(function () use ($student, $data, $recipientUserId): bool {
            Student::query()->whereKey($student->id)->lockForUpdate()->first();
            if (StudentMessage::hasPendingFor($student->id)) {
                return false;
            }

            StudentMessage::create([
                'student_id' => $student->id,
                'student_user_id' => $student->user_id,
                'recipient_type' => $data['recipient_type'],
                'recipient_user_id' => $recipientUserId,
                'body' => trim($data['body']),
            ]);

            return true;
        });

        if (! $sent) {
            return back()->with('error', 'Önceki mesajına henüz cevap gelmedi. Cevap gelince yeni mesaj yazabilirsin.');
        }

        return redirect()->route('student.messages.index')->with('ok', 'Mesajın gönderildi. Cevap gelince burada göreceksin.');
    }

    public function staffIndex(Request $request): View
    {
        $user = $request->user();
        $base = StudentMessage::query()->visibleTo($user)->with(['student.user', 'student.schoolClass', 'recipient', 'replier']);

        return view('student-messages.inbox', [
            'pending' => (clone $base)->whereNull('replied_at')->oldest('id')->get(),
            'answered' => (clone $base)->whereNotNull('replied_at')->latest('replied_at')->limit(40)->get(),
            'isAdmin' => $user->hasRole('admin'),
        ]);
    }

    public function staffReply(Request $request, StudentMessage $message): RedirectResponse
    {
        $user = $request->user();
        abort_unless(StudentMessage::query()->visibleTo($user)->whereKey($message->id)->exists(), 403);

        $data = $request->validate([
            'reply_body' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'reply_body.required' => 'Lütfen cevabını yaz.',
            'reply_body.max' => 'Cevap en fazla 1000 karakter olabilir.',
        ]);

        $updated = StudentMessage::query()
            ->whereKey($message->id)
            ->whereNull('replied_at')
            ->update([
                'reply_body' => trim($data['reply_body']),
                'replied_by' => $user->id,
                'replied_at' => now(),
                'student_read_at' => null,
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Bu mesaja zaten cevap verilmiş.');
        }

        return back()->with('ok', 'Cevabın öğrenciye gönderildi.');
    }

    /** Yönetici bir öğrenci mesajını gelen kutusundan kaldırır (öğrencinin kendi geçmişi etkilenmez). */
    public function staffDestroy(Request $request, StudentMessage $message): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $message->forceFill(['staff_deleted_at' => now(), 'staff_deleted_by' => $user->id])->save();

        return back()->with('ok', 'Mesaj silindi.');
    }

    /** Yönetici, cevaplanmış tüm mesajları gelen kutusundan topluca temizler. */
    public function staffClearAnswered(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('admin'), 403);

        $count = StudentMessage::query()
            ->whereNull('staff_deleted_at')
            ->whereNotNull('replied_at')
            ->update(['staff_deleted_at' => now(), 'staff_deleted_by' => $user->id]);

        return back()->with('ok', $count . ' cevaplanmış mesaj silindi.');
    }
}
