<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gelisim Karnesi</title>
    <style>
        body{margin:0;background:#f1f5f9;font-family:Segoe UI,Arial,sans-serif}
        .tools{text-align:center;padding:12px}
        .page{width:210mm;min-height:297mm;margin:0 auto 12px;background:#fff;padding:12mm;box-sizing:border-box}
        .head{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #e2e8f0;padding-bottom:8px}
        h1{font-size:24px;margin:0;color:#0f172a}
        .meta{font-size:13px;color:#475569}
        .stats{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:10px}
        .s{border:1px solid #e2e8f0;border-radius:8px;padding:8px}
        .s b{display:block;font-size:18px;color:#0f172a}
        .tbl{width:100%;border-collapse:collapse;margin-top:12px}
        .tbl th,.tbl td{border:1px solid #e2e8f0;padding:6px;font-size:12px;text-align:left}
        .section{margin-top:12px}
        @media print {.tools{display:none}.page{margin:0}}
    </style>
</head>
<body>
<div class="tools"><button onclick="window.print()">PDF Olarak Kaydet / Yazdir</button></div>
<div class="page">
    <div class="head">
        <div>
            <h1>Ogrenci Gelisim Karnesi</h1>
            <div class="meta">{{ $student->user?->name }} - {{ $student->schoolClass?->name }}/{{ $student->schoolClass?->section }}</div>
        </div>
        @if($student->currentAvatar)
            <img src="{{ asset($student->currentAvatar->image_path) }}" style="width:60px;height:60px;object-fit:cover;border-radius:8px" alt="avatar">
        @endif
    </div>

    <div class="stats">
        <div class="s"><span>Toplam XP</span><b>{{ $reportStats['total_xp'] }}</b></div>
        <div class="s"><span>Not Ortalamasi</span><b>{{ $reportStats['grade_avg'] }}</b></div>
        <div class="s"><span>Tamamlanan Icerik</span><b>{{ $reportStats['completed_contents'] }}</b></div>
        <div class="s"><span>Verilen Odev</span><b>{{ $reportStats['assignment_count'] }}</b></div>
        <div class="s"><span>Rozet</span><b>{{ $reportStats['badge_count'] }}</b></div>
    </div>

    <div class="section">
        <h3>Atanan Odevler ve Uygulamalar</h3>
        <table class="tbl">
            <thead><tr><th>Uygulama</th><th>Odev</th><th>Teslim</th><th>Level</th><th>Puan Plani</th></tr></thead>
            <tbody>
            @forelse($assigned as $a)
                <tr>
                    <td>{{ $a->game_name }}</td>
                    <td>{{ $a->title }}</td>
                    <td>{{ $a->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $a->level_from ?? '-' }} - {{ $a->level_to ?? '-' }}</td>
                    <td>{{ $a->levels->map(fn($l) => 'L'.$l->level.':'.$l->points)->implode(', ') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Ogrenci sinifi icin tanimli odev bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Ogrenci Ilerleme Kayitlari (Son 30)</h3>
        <table class="tbl">
            <thead><tr><th>Icerik ID</th><th>Tamamlandi</th><th>Kazanilan XP</th><th>Tarih</th></tr></thead>
            <tbody>
            @forelse($progressRows as $row)
                <tr>
                    <td>{{ $row->content_id }}</td>
                    <td>{{ $row->completed ? 'Evet' : 'Hayir' }}</td>
                    <td>{{ $row->xp_awarded }}</td>
                    <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Ilerleme kaydi bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Kazanilan Rozetler</h3>
        <p>{{ $student->badges->pluck('name')->implode(', ') ?: 'Henuz rozet yok.' }}</p>
    </div>
</div>
</body>
</html>

