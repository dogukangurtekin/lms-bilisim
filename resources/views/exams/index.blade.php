@extends('layout.app')
@section('title','Sınav Oluştur')
@section('content')
<div class="top"><h1>📝 Sınav Oluştur</h1><a class="btn" href="{{ route('exams.bank.index') }}" style="margin-left:auto">📚 Soru Havuzu</a></div>

<style>
    .ex-wrap{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:18px;align-items:start}
    .ex-card{background:#fff;border:1px solid #dbe5f2;border-radius:18px;padding:18px;box-shadow:0 10px 24px rgba(15,23,42,.05)}
    .ex-card h2{margin:0 0 4px;font-size:18px}
    .ex-sub{margin:0 0 14px;color:#64748b;font-size:13px;font-weight:600}
    .ex-list{display:grid;gap:10px}
    .ex-item{display:flex;align-items:center;gap:14px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}
    .ex-ico{flex:0 0 auto;width:44px;height:54px;border-radius:8px;background:#fff;border:2px solid #cbd5e1;display:grid;place-items:center;font-size:20px}
    .ex-meta{flex:1 1 auto;min-width:0}
    .ex-meta b{display:block;font-size:15px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .ex-meta small{color:#64748b;font-weight:700}
    .ex-tag{display:inline-block;padding:2px 9px;border-radius:999px;background:#e0e7ff;color:#3730a3;font-size:11.5px;font-weight:800;margin-right:4px}
    .ex-tag.final{background:#dcfce7;color:#166534}
    .ex-del{margin:0}.ex-del button{border:0;background:#fee2e2;color:#b91c1c;border-radius:10px;padding:7px 12px;font:inherit;font-weight:800;cursor:pointer}
    .ex-empty{padding:34px 10px;text-align:center;color:#94a3b8;font-weight:700}
    .ex-form{display:grid;gap:10px}
    .ex-form label{display:grid;gap:4px;font-size:12.5px;font-weight:800;color:#334155}
    .ex-form input,.ex-form select{height:40px;border:1px solid #cbd5e1;border-radius:10px;padding:0 10px;font:inherit;background:#fff;width:100%}
    .ex-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .ex-cols{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .ex-cols label{border:2px solid #e2e8f0;border-radius:12px;padding:10px;cursor:pointer;text-align:center;flex-direction:column;align-items:center}
    .ex-cols input{display:none}
    .ex-cols label:has(input:checked){border-color:#2563eb;background:#eff6ff}
    .ex-cols i{display:flex;gap:4px;justify-content:center;margin-bottom:4px}
    .ex-cols i span{display:block;width:16px;height:24px;border:2px solid #94a3b8;border-radius:3px;background:#fff}
    .ex-submit{height:46px;border:0;border-radius:12px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;font-weight:900;font-size:15px;cursor:pointer;box-shadow:0 8px 18px rgba(37,99,235,.3)}
    .ex-err{background:#fee2e2;color:#991b1b;border-radius:10px;padding:8px 12px;font-weight:700;font-size:13px}
    .ex-word{display:grid;gap:6px;background:#eff6ff;border:1px dashed #93c5fd;border-radius:14px;padding:12px;margin-bottom:16px;font-size:12.5px;color:#334155}
    .ex-word b{font-size:14px;color:#1e3a8a}.ex-word small{font-weight:700;color:#b91c1c}
    @media (max-width:980px){.ex-wrap{grid-template-columns:1fr}}
</style>

<div class="ex-wrap">
    <section class="ex-card">
        <h2>{{ $isAdmin ? 'Tüm Sınavlar' : 'Sınavlarım' }}</h2>
        <p class="ex-sub">Hazırladığınız sınav kâğıtları burada listelenir. Bir sınava tıklayarak soru editörünü açın. Soru havuzu ve PDF/Word çıktısı sonraki adımlarda eklenecek.</p>
        <div class="ex-list">
            @forelse($exams as $exam)
                <div class="ex-item">
                    <div class="ex-ico">📄</div>
                    <div class="ex-meta">
                        <b><a href="{{ route('exams.edit', $exam) }}" style="color:inherit;text-decoration:none">{{ $exam->title }}</a></b>
                        <small>
                            <span class="ex-tag {{ $exam->status === 'final' ? 'final' : '' }}">{{ $exam->status === 'final' ? 'Kesin' : 'Taslak' }}</span>
                            {{ $exam->subject ?: 'Ders belirtilmedi' }}{{ $exam->grade_level ? ' · '.$exam->grade_level.'. sınıf' : '' }}
                            · {{ $exam->columns }} sütun · {{ \App\Models\Exam::TEMPLATES[$exam->template] ?? $exam->template }}
                            · {{ $exam->questions_count }} soru
                            @if($isAdmin && $exam->owner) · {{ $exam->owner->name }} @endif
                            · {{ $exam->updated_at->format('d.m.Y H:i') }}
                        </small>
                    </div>
                    <a href="{{ route('exams.edit', $exam) }}" class="btn" style="padding:7px 14px">Düzenle</a>
                    @if($exam->questions_count > 0)<a href="{{ route('exams.pdf', $exam) }}" class="btn" style="padding:7px 14px" title="Öğrenci kâğıdını PDF olarak indir">PDF</a><a href="{{ route('exams.word', $exam) }}" class="btn" style="padding:7px 14px" title="Öğrenci kâğıdını Word olarak indir">Word</a>@endif
                    <form method="POST" action="{{ route('exams.duplicate', $exam) }}" class="ex-del">@csrf<button type="submit" style="background:#e0e7ff;color:#3730a3" title="Sınavın kopyasını oluştur">Kopyala</button></form>
                    <form method="POST" action="{{ route('exams.destroy', $exam) }}" class="ex-del" data-confirm="Bu sınav silinsin mi?">@csrf @method('DELETE')
                        <button type="submit">Sil</button>
                    </form>
                </div>
            @empty
                <div class="ex-empty">Henüz sınav yok. Sağdaki formdan ilk sınavınızı oluşturun.</div>
            @endforelse
        </div>
    </section>

    <aside class="ex-card">
        <div class="ex-word">
            <b>📎 Word kâğıdından başla</b>
            <span>Hazır bir sınav kâğıdınızı (.docx) yükleyin; şablon olarak kullanılır ve içindeki sorular okunur.</span>
            <label class="btn" style="cursor:pointer;justify-self:start">Word dosyası seç<input type="file" id="ex-word-file" accept=".docx" style="display:none"></label>
            <small id="ex-word-msg"></small>
        </div>
        <h2>Yeni Sınav</h2>
        <p class="ex-sub">Temel bilgileri girin, şablonu ve sayfa düzenini seçin.</p>
        @if($errors->any())<div class="ex-err" style="margin-bottom:10px">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('exams.store') }}" class="ex-form">
            @csrf
            <label>Sınav başlığı<input name="title" value="{{ old('title') }}" maxlength="200" required placeholder="Örn. 1. Dönem 1. Yazılı"></label>
            <div class="ex-row">
                <label>Ders<input name="subject" value="{{ old('subject') }}" maxlength="120" placeholder="Bilişim Teknolojileri"></label>
                <label>Sınıf düzeyi
                    <select name="grade_level"><option value="">—</option>@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected((int) old('grade_level') === $i)>{{ $i }}. sınıf</option>@endfor</select>
                </label>
            </div>
            <div class="ex-row">
                <label>Eğitim-öğretim yılı<input name="academic_year" value="{{ old('academic_year', $defaultYear) }}" maxlength="20" placeholder="2026-2027"></label>
                <label>Tarih<input type="date" name="exam_date" value="{{ old('exam_date') }}"></label>
            </div>
            <div class="ex-row">
                <label>Dönem
                    <select name="term_no"><option value="">—</option><option value="1" @selected(old('term_no') === '1')>1. Dönem</option><option value="2" @selected(old('term_no') === '2')>2. Dönem</option></select>
                </label>
                <label>Yazılı numarası
                    <select name="exam_no"><option value="">—</option>@for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(old('exam_no') === (string) $i)>{{ $i }}. Yazılı</option>@endfor</select>
                </label>
            </div>
            <div class="ex-row">
                <label>Süre (dk)<input type="number" name="duration_min" value="{{ old('duration_min', 40) }}" min="1" max="600"></label>
                <label>Şablon
                    <select name="template">@foreach($templates as $key => $label)<option value="{{ $key }}" @selected(old('template', 'classic') === $key)>{{ $label }}</option>@endforeach</select>
                </label>
            </div>
            <label>Okul adı<input name="school_name" value="{{ old('school_name') }}" maxlength="200" placeholder="Başlıkta görünecek okul adı"></label>
            <div class="ex-cols">
                <label><input type="radio" name="columns" value="1" @checked((int) old('columns', 1) === 1)><i><span></span></i>Tek sütun</label>
                <label><input type="radio" name="columns" value="2" @checked((int) old('columns', 1) === 2)><i><span></span><span></span></i>İki sütun</label>
            </div>
            <button type="submit" class="ex-submit">Sınavı Oluştur</button>
        </form>
    </aside>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('ex-word-file'), msg = document.getElementById('ex-word-msg');
    if (!input) return;
    input.addEventListener('change', async () => {
        const f = input.files[0]; if (!f) return;
        msg.style.color = '#334155'; msg.textContent = 'Yükleniyor ve sorular okunuyor…';
        const fd = new FormData(); fd.append('word', f);
        try {
            const res = await fetch(@json(route('exams.word.import')), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' }, body: fd });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) { msg.style.color = '#b91c1c'; msg.textContent = j.message || (j.errors ? Object.values(j.errors)[0][0] : 'Yüklenemedi (' + res.status + ')'); input.value = ''; return; }
            window.location.href = j.url;
        } catch (_) { msg.style.color = '#b91c1c'; msg.textContent = 'Bağlantı hatası oluştu.'; }
    });
})();
</script>
@endpush
