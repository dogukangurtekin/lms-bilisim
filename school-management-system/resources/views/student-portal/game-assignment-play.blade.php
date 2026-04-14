@extends('layout.app')
@section('title','Etkinlik Odev Oynatici')
@section('body_class','play-compact')
@section('content')
<div class="card" style="padding:12px">
    <div class="card" style="padding:10px">
        <p><b>{{ $assignment->title }}</b> | <b>Kurallar:</b> Sadece verilen level araliginda ({{ $assignment->level_from }}-{{ $assignment->level_to }}) ilerleyin.</p>
        <iframe
            id="assignment-runner"
            src="{{ $gameUrl }}"
            data-slug="{{ $assignment->game_slug }}"
            data-level-start="{{ (int) ($assignment->level_from ?? 1) }}"
            data-level-end="{{ (int) ($assignment->level_to ?? ($assignment->level_from ?? 1)) }}"
            style="width:100%;height:calc(100vh - 220px);min-height:680px;border:1px solid #d1d5db;border-radius:10px;display:block"
        ></iframe>
    </div>

    <form id="game-assignment-complete-form" method="POST" action="{{ route('student.portal.game-assignment.complete', $assignment) }}" style="display:none">
        @csrf
        <input type="hidden" name="reached_level" id="ga-reached-level" value="{{ (int) ($assignment->level_to ?? ($assignment->level_from ?? 1)) }}">
        <input type="hidden" name="earned_xp" id="ga-earned-xp" value="0">
        <input type="hidden" name="duration_seconds" id="ga-duration-seconds" value="0">
        <input type="hidden" name="completed_level_ids" id="ga-completed-level-ids" value="">
    </form>
</div>

<div id="ga-complete-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:12px;max-width:520px;width:100%;padding:20px">
        <h3 style="margin:0 0 10px 0">Tebrikler</h3>
        <p style="margin:0 0 8px 0">Tum odev uygulamasini tamamladiniz.</p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-top:8px">
            <div><b>Odev:</b> {{ $assignment->title }}</div>
            <div><b>Uygulama:</b> {{ $assignment->game_name }}</div>
            <div><b>Level Araligi:</b> {{ $assignment->level_from ?? '-' }} - {{ $assignment->level_to ?? '-' }}</div>
            <div><b>Kazanilan XP:</b> <span id="ga-modal-earned-xp">0</span></div>
            <div><b>Sure:</b> <span id="ga-modal-duration">0 sn</span></div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:14px">
            <button type="button" id="ga-save-exit-btn" class="btn">Kaydet ve Cik</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var iframe = document.getElementById('assignment-runner');
    if (!iframe) return;

    var startedAt = Date.now();
    var levelStart = Math.max(1, Number(iframe.dataset.levelStart || 1));
    var levelEnd = Math.max(levelStart, Number(iframe.dataset.levelEnd || levelStart));
    var slug = String(iframe.dataset.slug || '');

    var form = document.getElementById('game-assignment-complete-form');
    var reachedInput = document.getElementById('ga-reached-level');
    var xpInput = document.getElementById('ga-earned-xp');
    var durationInput = document.getElementById('ga-duration-seconds');
    var completedIdsInput = document.getElementById('ga-completed-level-ids');
    var modal = document.getElementById('ga-complete-modal');
    var modalXp = document.getElementById('ga-modal-earned-xp');
    var modalDuration = document.getElementById('ga-modal-duration');
    var saveBtn = document.getElementById('ga-save-exit-btn');
    var completedHandled = false;

    function formatDuration(seconds) {
        var s = Math.max(0, Number(seconds || 0));
        if (s < 60) return Math.round(s) + ' sn';
        var m = Math.floor(s / 60);
        var sec = Math.floor(s % 60);
        return m + ' dk ' + sec + ' sn';
    }

    var needsPostMessageLock = ['compute-it-runner', 'block-grid-runner', 'lightbot-runner'].includes(slug);
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
        if (data.type !== 'ASSIGNMENT_RANGE_COMPLETED' && data.type !== 'LEVEL_COMPLETED') return;
        if (data.type === 'LEVEL_COMPLETED' && levelStart !== levelEnd) return;
        if (completedHandled) return;
        completedHandled = true;

        var xp = Math.max(0, Number(data.xp || 0));
        var sec = Math.max(0, Math.round(Number(data.elapsedSeconds || ((Date.now() - startedAt) / 1000))));
        var completedIds = Array.isArray(data.completedLevelIds)
            ? data.completedLevelIds.map(function (v) { return Number(v); }).filter(function (v) { return Number.isFinite(v); })
            : (data.levelId ? [Number(data.levelId)] : []);
        if (xpInput) xpInput.value = String(xp);
        if (durationInput) durationInput.value = String(sec);
        if (completedIdsInput) completedIdsInput.value = completedIds.join(',');
        if (reachedInput) reachedInput.value = String(levelEnd);
        if (modalXp) modalXp.textContent = String(xp);
        if (modalDuration) modalDuration.textContent = formatDuration(sec);
        if (modal) modal.style.display = 'flex';
    });

    if (saveBtn && form) {
        saveBtn.addEventListener('click', function () {
            form.submit();
        });
    }
});
</script>
@endpush
