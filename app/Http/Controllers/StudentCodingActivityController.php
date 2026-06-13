<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitActivityAttemptRequest;
use App\Models\CodingActivity;
use App\Models\UserStreak;
use App\Models\UserXpLog;
use App\Services\CodingActivityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentCodingActivityController extends Controller
{
    public function index(CodingActivityService $service): View
    {
        $today = Carbon::today('Europe/Istanbul');
        $activity = $service->resolveTodayActivityForStudent();
        $activities = collect(array_filter([$activity]));

        if ($activities->isEmpty()) {
            $fallback = CodingActivity::with('questions.options')->where('is_active', true)->whereHas('questions')->first();
            if ($fallback) {
                $activities = collect([$fallback]);
            }
        }

        $streak = UserStreak::firstWhere('user_id', auth()->id());
        $todayXp = (int) UserXpLog::where('user_id', auth()->id())->whereDate('awarded_on', $today)->sum('xp_delta');

        return view('student-portal.daily-coding', compact('activities', 'streak', 'todayXp'));
    }

    public function submit(SubmitActivityAttemptRequest $request, CodingActivity $activity, CodingActivityService $service): RedirectResponse
    {
        $this->authorize('attempt', $activity);
        $attempt = $service->submitAttempt(
            $activity,
            (int) auth()->id(),
            $request->validated('answers', []),
            (int) $request->validated('duration_seconds', 0)
        );

        $message = (string) ($attempt->feedback_message ?? 'G?rev tamamland?.');

        if ($attempt->all_correct) {
            return redirect()->route('student.portal.dashboard')->with('ok', $message);
        }

        return back()
            ->with('warning', $message)
            ->with('wrong_details', (array) ($attempt->wrong_details ?? []));
    }
}
