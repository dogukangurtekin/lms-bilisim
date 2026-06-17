@extends('layout.app')
@section('title','KullanÄ±cÄ± YÃ¶netimi')
@section('content')
<div class="top"><h1>KullanÄ±cÄ± YÃ¶netimi</h1></div>
<style>
    .users-form .field-wrap{min-width:180px}
    .users-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .bulk-tools{display:flex;flex-direction:column;gap:10px;align-items:stretch;margin-bottom:12px}
    .bulk-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .bulk-tools .template-btn{background:#166534 !important;border-color:#14532d !important;color:#fff !important;padding:8px 12px;font-size:13px;line-height:1.2;border-radius:8px}
    .bulk-upload-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .bulk-upload-form input[type="file"]{max-width:240px;width:240px;font-size:12px;padding:4px}
    .bulk-upload-form .btn{padding:8px 12px;font-size:13px;line-height:1.2;border-radius:8px}
    .student-delete-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:10px 0 14px}
    .student-delete-tools .btn-danger{background:#b91c1c !important;border-color:#991b1b !important}
    .users-form .submit-wrap .btn{height:42px;padding:0 14px;display:inline-flex;align-items:center}
    .users-form .submit-wrap{padding-top:28px}
    .form-progress-widget{position:fixed;right:18px;bottom:18px;z-index:1200;width:220px;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border:1px solid #dbe4f0;border-radius:18px;box-shadow:0 18px 45px rgba(15,23,42,.14);padding:12px 14px;display:none}
    .form-progress-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;font-weight:700;color:#0f172a}
    .form-progress-track{width:100%;height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden}
    .form-progress-bar{height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,#2563eb 0%,#22c55e 100%);transition:width .18s ease}
    .form-progress-meta{margin-top:8px;font-size:12px;color:#64748b;display:flex;justify-content:space-between;gap:10px}
    @media (max-width:768px){
        .users-form{display:grid !important;grid-template-columns:1fr;gap:10px;align-items:stretch !important}
        .users-form .field-wrap{min-width:0;width:100%}
        .users-form input,.users-form select{width:100%}
        .users-form .btn{justify-self:start}
        .users-table-wrap table{min-width:680px}
        .bulk-upload-form input[type="file"]{width:100%;max-width:100%}
        .form-progress-widget{left:12px;right:12px;bottom:12px;width:auto}
    }
</style>
<div class="card">
<div class="bulk-tools">
    <div class="bulk-row">
        <a class="btn template-btn" href="{{ route('users.bulk.students.template') }}">Ogrenci Sablonu (.xlsx)</a>
        <form method="POST" action="{{ route('users.bulk.students.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
            <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
            <button class="btn" type="submit">Toplu Ogrenci Yukle</button>
        </form>
    </div>
    <div class="bulk-row">
        <a class="btn template-btn" href="{{ route('users.bulk.teachers.template') }}">Ogretmen Sablonu (.xlsx)</a>
        <form method="POST" action="{{ route('users.bulk.teachers.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
            <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
            <button class="btn" type="submit">Toplu Ogretmen Yukle</button>
        </form>
    </div>
</div>
<form method="POST" action="{{ route('users.store') }}" class="actions users-form" style="margin-bottom:14px;align-items:end;flex-wrap:wrap">@csrf
<div class="field-wrap" style="min-width:220px"><label>Ad Soyad</label><input name="name" required></div>
<div class="field-wrap" style="min-width:220px"><label>E-posta</label><input type="email" name="email" required></div>
<div class="field-wrap"><label>Åžifre</label><input type="password" name="password" required></div>
<div class="field-wrap"><label>Rol</label><select name="role" id="role-select" required><option value="teacher">Ã–ÄŸretmen</option><option value="student">Ã–ÄŸrenci</option><option value="admin">Admin</option></select></div>
<div class="field-wrap" style="min-width:220px" id="class-wrap"><label>SÄ±nÄ±f (Ã–ÄŸrenci)</label><select name="school_class_id"><option value="">SeÃ§iniz</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} / {{ $class->section }}</option>@endforeach</select></div>
<div class="submit-wrap"><button class="btn" type="submit">KullanÄ±cÄ± Ekle</button></div>
</form>
<div class="student-delete-tools">
    <button form="delete-selected-students-form" type="submit" class="btn btn-danger">Secilen Ogrencileri Sil</button>
    <form id="delete-all-students-form" method="POST" action="{{ route('users.students.destroy-all') }}" style="display:inline">@csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Tum Ogrencileri Sil</button>
    </form>
</div>
<form id="delete-selected-students-form" method="POST" action="{{ route('users.students.destroy-selected') }}">@csrf @method('DELETE')
<div class="table-responsive users-table-wrap"><table><thead><tr><th><input type="checkbox" id="select-all-students" title="Tum ogrencileri sec"></th><th>ID</th><th>Ad</th><th>E-posta</th><th>Rol</th><th>Ä°ÅŸlem</th></tr></thead><tbody>@foreach($users as $item)<tr><td>@if($item->hasRole('student'))<input type="checkbox" class="student-row-checkbox" form="delete-selected-students-form" name="user_ids[]" value="{{ $item->id }}">@endif</td><td>{{ $item->id }}</td><td>{{ $item->name }} @if($item->hasRole('teacher') && $item->teacher)<a class="btn" href="{{ route('users.teachers.classes.edit', $item->teacher) }}" style="margin-left:8px">SÄ±nÄ±f Ata</a>@endif</td><td>{{ $item->email }}</td><td>{{ $item->role?->slug ?? '-' }}</td><td class="actions">@if($item->hasRole('admin'))<button class="btn" type="button" disabled>Admin Silinemez</button>@else<form method="POST" action="{{ route('users.destroy', $item) }}">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Sil</button></form>@endif</td></tr>@endforeach</tbody></table></div>
</form>
{{ $users->links() }}
</div>
<div class="form-progress-widget" id="form-progress-widget" aria-live="polite">
    <div class="form-progress-title">
        <span>KullanÄ±cÄ± Ekleme Ä°lerlemesi</span>
        <span id="form-progress-percent">0%</span>
    </div>
    <div class="form-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div id="form-progress-bar" class="form-progress-bar"></div>
    </div>
    <div class="form-progress-meta">
        <span id="form-progress-status">AlanlarÄ± doldurun</span>
        <span id="form-progress-step">0/4</span>
    </div>
</div>
<script>
(() => {
const role = document.getElementById('role-select');
const wrap = document.getElementById('class-wrap');
const set = () => wrap.style.display = (role && role.value === 'student') ? 'block' : 'none';
role?.addEventListener('change', set);
set();

const selectAll = document.getElementById('select-all-students');
const checkboxes = Array.from(document.querySelectorAll('.student-row-checkbox'));
selectAll?.addEventListener('change', () => checkboxes.forEach((cb) => { cb.checked = selectAll.checked; }));

const widget = document.getElementById('form-progress-widget');
const progressBar = document.getElementById('form-progress-bar');
const progressPercent = document.getElementById('form-progress-percent');
const progressStatus = document.getElementById('form-progress-status');
const progressStep = document.getElementById('form-progress-step');
const bulkForms = Array.from(document.querySelectorAll('.bulk-upload-form'));
const bulkFileInputs = bulkForms.map((f) => f.querySelector('input[type="file"]')).filter(Boolean);
let bulkProgressTotal = 0;
let bulkProgressCurrent = 0;

const showWidget = () => { if (widget) widget.style.display = 'block'; };
const hideWidget = () => { if (widget) widget.style.display = 'none'; };
const renderBulkProgress = () => {
    if (bulkProgressTotal <= 0) return;
    const pct = Math.min(100, Math.round((bulkProgressCurrent / bulkProgressTotal) * 100));
    if (progressBar) progressBar.style.width = pct + '%';
    if (progressPercent) progressPercent.textContent = pct + '%';
    if (progressStep) progressStep.textContent = bulkProgressCurrent + '/' + bulkProgressTotal;
    if (progressStatus) progressStatus.textContent = bulkProgressCurrent >= bulkProgressTotal ? 'Tamamlandý' : 'Ýþleniyor...';
};

const estimateRows = async (file) => {
    if (!file) return 0;
    const name = (file.name || '').toLowerCase();
    if (name.endsWith('.csv') || name.endsWith('.txt')) {
        const text = await file.text();
        const lines = text.split(/\r?\n/).map((v) => v.trim()).filter(Boolean);
        return Math.max(0, lines.length - 1);
    }
    return 0;
};

bulkFileInputs.forEach((input) => {
    input.addEventListener('change', () => {
        if (input.files && input.files.length > 0) {
            showWidget();
            bulkProgressCurrent = 0;
            if (progressStatus) progressStatus.textContent = 'Dosya analiz ediliyor...';
            if (progressPercent) progressPercent.textContent = '0%';
            if (progressBar) progressBar.style.width = '0%';
            if (progressStep) progressStep.textContent = '0/0';
            estimateRows(input.files[0]).then((count) => {
                bulkProgressTotal = Math.max(1, count || 1);
                bulkProgressCurrent = 0;
                if (progressStep) progressStep.textContent = '0/' + bulkProgressTotal;
                if (progressStatus) progressStatus.textContent = 'Yüklemeye hazýr';
                if (progressPercent) progressPercent.textContent = '0%';
                if (progressBar) progressBar.style.width = '0%';
            }).catch(() => {
                bulkProgressTotal = 1;
                if (progressStatus) progressStatus.textContent = 'Dosya okunamadý';
            });
        } else {
            bulkProgressTotal = 0;
            bulkProgressCurrent = 0;
            hideWidget();
        }
    });
});

bulkForms.forEach((bulkForm) => {
    bulkForm.addEventListener('submit', (e) => {
        const fileInput = bulkForm.querySelector('input[type="file"]');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            return;
        }
        e.preventDefault();
        showWidget();
        if (bulkProgressTotal <= 0) bulkProgressTotal = 1;
        bulkProgressCurrent = 0;
        if (progressStatus) progressStatus.textContent = 'Yükleniyor...';
        if (progressPercent) progressPercent.textContent = '0%';
        if (progressBar) progressBar.style.width = '0%';
        if (progressStep) progressStep.textContent = '0/' + bulkProgressTotal;

        const xhr = new XMLHttpRequest();
        xhr.open(bulkForm.method || 'POST', bulkForm.action, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.responseType = 'json';
        xhr.upload.addEventListener('progress', (ev) => {
            if (!ev.lengthComputable) return;
            const pct = Math.min(30, Math.max(1, Math.round((ev.loaded / ev.total) * 30)));
            if (progressBar) progressBar.style.width = pct + '%';
            if (progressPercent) progressPercent.textContent = pct + '%';
            if (progressStatus) progressStatus.textContent = 'Dosya aktarýlýyor...';
        });
        xhr.onload = () => {
            const data = xhr.response || {};
            bulkProgressTotal = Math.max(1, Number(data.total || bulkProgressTotal || 1));
            if (progressStatus) progressStatus.textContent = data.ok === false ? 'Ýþlenemedi' : 'Ýþleniyor...';
            const animate = () => {
                if (bulkProgressCurrent < bulkProgressTotal) {
                    bulkProgressCurrent += 1;
                    renderBulkProgress();
                    window.setTimeout(animate, 85);
                    return;
                }
                if (progressStatus) progressStatus.textContent = data.ok === false ? 'Ýþlenemedi' : 'Tamamlandý';
                window.setTimeout(() => window.location.reload(), 350);
            };
            animate();
        };
        xhr.onerror = () => {
            if (progressStatus) progressStatus.textContent = 'Yükleme baþarýsýz';
        };
        xhr.send(new FormData(bulkForm));
    });
});

hideWidget();
})();
</script>
@endsection

