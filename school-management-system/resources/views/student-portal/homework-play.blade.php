@extends('layout.app')
@section('title','Odev Oynatici')
@section('body_class','play-compact')
@section('content')
<div class="top"><h1>{{ $homework->title }}</h1></div>
<div class="card" style="padding:12px">
    <p><b>Tip:</b> {{ strtoupper($homework->assignment_type) }} | <b>Level Araligi:</b> {{ $homework->level_from ?? '-' }} - {{ $homework->level_to ?? '-' }}</p>
    <p>{{ $homework->details }}</p>

    @if($gameUrl)
        <div class="card" style="padding:10px">
            <p><b>Kurallar:</b> Sadece verilen level araliginda ({{ $homework->level_from }}-{{ $homework->level_to }}) ilerleyin.</p>
            <iframe
                id="homework-runner"
                src="{{ $gameUrl }}"
                data-slug="{{ $gameSlug ?? '' }}"
                data-level-start="{{ (int) ($homework->level_from ?? 1) }}"
                data-level-end="{{ (int) ($homework->level_to ?? ($homework->level_from ?? 1)) }}"
                style="width:100%;height:calc(100vh - 220px);min-height:680px;border:1px solid #d1d5db;border-radius:10px;display:block"
            ></iframe>
        </div>
    @else
        <div class="card">
            <p>Bu odev ders icerigi uzerinden tamamlanacaktir.</p>
            <a class="btn" href="{{ route('student.portal.course-show', $homework->course_id) }}">Ders Icerigine Git</a>
        </div>
    @endif

    <form id="homework-complete-form" method="POST" action="{{ route('student.portal.homework.complete', $homework) }}" style="margin-top:12px">
        @csrf
        @if($homework->level_from && $homework->level_to)
            <label>Ulastiginiz Son Level ({{ $homework->level_from }}-{{ $homework->level_to }})</label>
            <input type="number" name="reached_level" min="{{ $homework->level_from }}" max="{{ $homework->level_to }}" required>
        @endif
        <input type="hidden" name="earned_xp" id="earned-xp-input" value="0">
        <input type="hidden" name="duration_seconds" id="duration-seconds-input" value="0">
        <input type="hidden" name="completed_level_ids" id="completed-level-ids-input" value="">
        <input type="hidden" name="exit_to_panel" id="exit-to-panel-input" value="0">
        @if($errors->any())
            <div style="color:#b91c1c;margin:8px 0">{{ $errors->first() }}</div>
        @endif
        <button class="btn" type="submit">Odevi Tamamladim</button>
    </form>
</div>

<div id="homework-complete-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:12px;max-width:520px;width:100%;padding:20px">
        <h3 style="margin:0 0 10px 0">Tebrikler</h3>
        <p style="margin:0 0 8px 0">Verilen level araligini tamamladiniz.</p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-top:8px">
            <div><b>Odev:</b> {{ $homework->title }}</div>
            <div><b>Level Araligi:</b> {{ $homework->level_from ?? '-' }} - {{ $homework->level_to ?? '-' }}</div>
            <div><b>Kazanilan XP:</b> <span id="modal-earned-xp">0</span></div>
            <div><b>Sure:</b> <span id="modal-duration">0 sn</span></div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:14px">
            <button type="button" id="save-exit-btn" class="btn">Kaydet ve Cik</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var iframe = document.getElementById('homework-runner');
    if (!iframe) return;

    var slug = String(iframe.dataset.slug || '');
    var levelStart = Math.max(1, Number(iframe.dataset.levelStart || 1));
    var levelEnd = Math.max(levelStart, Number(iframe.dataset.levelEnd || levelStart));
    var needsPostMessageLock = ['compute-it-runner', 'block-grid-runner', 'lightbot-runner'].includes(slug);

    var startedAt = Date.now();
    var completeForm = document.getElementById('homework-complete-form');
    var earnedXpInput = document.getElementById('earned-xp-input');
    var durationInput = document.getElementById('duration-seconds-input');
    var completedIdsInput = document.getElementById('completed-level-ids-input');
    var exitToPanelInput = document.getElementById('exit-to-panel-input');
    var modal = document.getElementById('homework-complete-modal');
    var modalXp = document.getElementById('modal-earned-xp');
    var modalDuration = document.getElementById('modal-duration');
    var saveExitBtn = document.getElementById('save-exit-btn');

    function formatDuration(seconds) {
        var s = Math.max(0, Number(seconds || 0));
        if (s < 60) return Math.round(s) + ' sn';
        var m = Math.floor(s / 60);
        var sec = Math.floor(s % 60);
        return m + ' dk ' + sec + ' sn';
    }

    function openCompletionModal(payload) {
        var xp = Math.max(0, Number(payload.xp || 0));
        var sec = Math.max(0, Math.round(Number(payload.durationSeconds || ((Date.now() - startedAt) / 1000))));
        var completedIds = Array.isArray(payload.completedLevelIds)
            ? payload.completedLevelIds.map(function (v) { return Number(v); }).filter(function (v) { return Number.isFinite(v); })
            : [];
        if (modalXp) modalXp.textContent = String(xp);
        if (modalDuration) modalDuration.textContent = formatDuration(sec);
        if (earnedXpInput) earnedXpInput.value = String(xp);
        if (durationInput) durationInput.value = String(sec);
        if (completedIdsInput) completedIdsInput.value = completedIds.join(',');
        if (exitToPanelInput) exitToPanelInput.value = '1';
        if (modal) modal.style.display = 'flex';
    }

    if (needsPostMessageLock) {
        iframe.addEventListener('load', function () {
            var payload = { type: 'SET_LEVEL_RANGE', levelStart: levelStart, levelEnd: levelEnd };
            try {
                iframe.contentWindow.postMessage(payload, '*');
                setTimeout(function () {
                    iframe.contentWindow.postMessage(payload, '*');
                }, 150);
            } catch (e) {}
        });
    }

    window.addEventListener('message', function (event) {
        var data = event && event.data;
        if (!data || typeof data !== 'object') return;
        if (data.type === 'ASSIGNMENT_RANGE_COMPLETED') {
            openCompletionModal({
                xp: Number(data.xp || 0),
                durationSeconds: Number(data.elapsedSeconds || 0),
                completedLevelIds: data.completedLevelIds || []
            });
            return;
        }
        if (data.type === 'LEVEL_COMPLETED' && levelStart === levelEnd) {
            openCompletionModal({
                xp: Number(data.xp || 0),
                durationSeconds: Math.round((Date.now() - startedAt) / 1000),
                completedLevelIds: [Number(data.levelId || 0)]
            });
        }
    });

    if (saveExitBtn && completeForm) {
        saveExitBtn.addEventListener('click', function () {
            var reachedInput = completeForm.querySelector('input[name="reached_level"]');
            if (reachedInput) reachedInput.value = String(levelEnd);
            completeForm.submit();
        });
    }
});
</script>
@endpush
