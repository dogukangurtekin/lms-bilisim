@php
    $summary = (array) ($summary ?? []);
    $outcomes = array_values(array_filter((array) ($summary['outcomes'] ?? []), fn ($item) => trim((string) $item) !== ''));
    $activities = array_values(array_filter((array) ($summary['activities'] ?? []), fn ($item) => trim((string) $item) !== ''));
@endphp
<div style="min-height:72vh;display:flex;align-items:center;justify-content:center;padding:20px">
    <div style="width:min(100%,980px);border-radius:28px;padding:28px;background:linear-gradient(135deg,#eff6ff 0%,#f8fafc 48%,#ecfeff 100%);border:1px solid #bfdbfe;box-shadow:0 22px 50px rgba(37,99,235,.12)">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px">
            <div>
                <p style="margin:0 0 8px;font-size:14px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563eb">Son Sayfa</p>
                <h2 style="margin:0;font-size:34px;line-height:1.1;font-weight:900;color:#0f172a">{{ $summary['lesson_title'] ?? 'Ders Özeti' }}</h2>
            </div>
            <div style="display:grid;gap:8px;justify-items:end">
                <span data-summary-earned-xp style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:900">Kazanılan XP: {{ (int) ($summary['lesson_total_xp'] ?? 0) }}</span>
                <span style="display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#ede9fe;color:#5b21b6;font-weight:900">Ders No: {{ (int) ($summary['lesson_number'] ?? 1) }}</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1.25fr .95fr;gap:16px">
            <div style="padding:20px;border-radius:22px;background:#fff;border:1px solid #dbeafe">
                <h3 style="margin:0 0 12px;font-size:22px;font-weight:900;color:#111827">Bu derste ne öğrendin?</h3>
                <p style="margin:0 0 14px;font-size:18px;line-height:1.8;color:#334155">{{ $summary['topic'] ?: 'Bu bölümde temel konular ve örnekler işlendi.' }}</p>
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
            <div style="padding:20px;border-radius:22px;background:#fff;border:1px solid #dbeafe;display:grid;gap:14px">
                <div>
                    <h3 style="margin:0 0 10px;font-size:22px;font-weight:900;color:#111827">Sonraki çalışma</h3>
                    <p style="margin:0;font-size:17px;line-height:1.8;color:#334155">Kısa tekrar yap, örnekleri bir kez daha incele ve kazanımları kendi cümlelerinle anlatmaya çalış.</p>
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
                <div style="padding:14px 16px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#0ea5e9);color:#fff;font-weight:800;line-height:1.7">
                    Toplam slayt sayısı: {{ (int) ($summary['slide_count'] ?? 0) }} <br>
                    Ders tamamlandığında bu özet, öğrenci gelişim raporuna işlenecek.
                </div>
            </div>
        </div>
    </div>
</div>
