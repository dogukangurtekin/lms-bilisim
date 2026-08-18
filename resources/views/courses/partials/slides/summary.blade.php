@php
    $summary = (array) ($summary ?? []);
    $outcomes = array_values(array_filter((array) ($summary['outcomes'] ?? []), fn ($item) => trim((string) $item) !== ''));
    $activities = array_values(array_filter((array) ($summary['activities'] ?? []), fn ($item) => trim((string) $item) !== ''));
    $questionTotal = max(0, (int) ($summary['question_total'] ?? 0));
    $solvedQuestions = max(0, (int) ($summary['solved_questions'] ?? 0));
@endphp
<div class="lesson-summary-shell" style="min-height:72vh;display:flex;align-items:center;justify-content:center;padding:20px;max-width:100%;overflow:hidden;background:transparent">
    <div style="width:min(100%,1180px);max-width:100%;border-radius:28px;padding:30px 32px;background:rgba(255,255,255,.58);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(191,219,254,.9);box-shadow:0 22px 50px rgba(37,99,235,.12);overflow:hidden">
        <div style="display:grid;gap:12px;margin-bottom:18px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:nowrap">
                <div style="min-width:0;flex:1 1 auto">
                    <h2 style="margin:0;font-size:34px;line-height:1.12;font-weight:900;color:#0f172a;word-break:break-word;overflow-wrap:anywhere">{{ $summary['lesson_title'] ?? 'Ders Özeti' }}</h2>
                </div>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                <span data-summary-earned-xp style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:900;white-space:nowrap">Kazanılan XP: {{ (int) ($summary['lesson_total_xp'] ?? 0) }}</span>
                @if($questionTotal > 0)
                    <span data-summary-solved-questions style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:900;white-space:nowrap">Çözülen soru sayısı: {{ $solvedQuestions }} / {{ $questionTotal }}</span>
                @endif
            </div>
        </div>
        <div style="display:grid;grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);gap:18px;max-width:100%">
            <div style="padding:22px;border-radius:22px;background:#fff;border:1px solid #dbeafe;min-width:0;overflow:hidden">
                <h3 style="margin:0 0 12px;font-size:22px;font-weight:900;color:#111827">Bu derste ne öğrendin?</h3>
                <p style="margin:0 0 16px;font-size:18px;line-height:1.9;color:#334155">{{ $summary['topic'] ?: 'Bu bölümde temel konular ve örnekler işlendi.' }}</p>
                @if($outcomes !== [])
                    <ul style="margin:0;padding-left:20px;display:grid;gap:10px;color:#334155">
                        @foreach($outcomes as $outcome)
                            <li style="font-size:17px;line-height:1.7">{{ $outcome }}</li>
                        @endforeach
                    </ul>
                @else
                    <p style="margin:0;color:#64748b">Henüz kazanım girilmedi.</p>
                @endif
            </div>
            <div style="padding:22px;border-radius:22px;background:#fff;border:1px solid #dbeafe;display:grid;gap:16px;min-width:0;overflow:hidden">
                <div>
                    <h3 style="margin:0 0 10px;font-size:22px;font-weight:900;color:#111827">Sonraki çalışma</h3>
                    <p style="margin:0;font-size:17px;line-height:1.9;color:#334155">Kısa tekrar yap, örnekleri bir kez daha incele ve kazanımları kendi cümlelerinle anlatmaya çalış.</p>
                </div>
                <div>
                    <h4 style="margin:0 0 8px;font-size:18px;font-weight:900;color:#111827">Yapılacaklar</h4>
                    @if($activities !== [])
                        <ul style="margin:0;padding-left:20px;display:grid;gap:8px;color:#334155">
                            @foreach($activities as $activity)
                                <li style="font-size:16px;line-height:1.6">{{ $activity }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p style="margin:0;color:#64748b">Bu konuyla ilgili kısa tekrar ve uygulama önerilir.</p>
                    @endif
                </div>
                <div style="padding:14px 16px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#0ea5e9);color:#fff;font-weight:800;line-height:1.7;min-width:0;overflow-wrap:anywhere">
                    Toplam slayt sayısı: {{ (int) ($summary['slide_count'] ?? 0) }} <br>
                    Ders tamamlandığında bu özet, öğrenci gelişim raporuna işlenecek.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width:768px){
    .lesson-summary-shell{
        padding:12px !important;
        width:100% !important;
        max-width:100% !important;
        overflow:hidden !important;
    }
    .lesson-summary-shell > div{
        width:100% !important;
        max-width:100% !important;
        padding:18px !important;
        border-radius:22px !important;
        overflow:hidden !important;
    }
    .lesson-summary-shell h2{
        font-size:clamp(24px,7vw,30px) !important;
        word-break:break-word;
        overflow-wrap:anywhere;
    }
    .lesson-summary-shell h3{
        font-size:clamp(18px,5vw,22px) !important;
        word-break:break-word;
        overflow-wrap:anywhere;
    }
    .lesson-summary-shell p,
    .lesson-summary-shell li{
        font-size:clamp(14px,3.8vw,16px) !important;
        line-height:1.6 !important;
        word-break:break-word;
        overflow-wrap:anywhere;
        hyphens:auto;
    }
    .lesson-summary-shell > div > div[style*="grid-template-columns"]{
        grid-template-columns:1fr !important;
    }
    .lesson-summary-shell > div > div[style*="grid-template-columns"] > div{
        min-width:0 !important;
        overflow:hidden !important;
    }
}
</style>