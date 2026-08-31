@extends('layout.app')
@section('title','Kullanıcı Yönetimi')
@section('content')
<div class="top"><h1>Kullanıcı Yönetimi</h1></div>
<style>
    .panel-section{border:1px solid var(--line,#e2e8f0);border-radius:14px;padding:16px;margin-bottom:16px;background:var(--surface,#fff)}
    .panel-section:last-child{margin-bottom:0}
    .panel-section-head{margin-bottom:14px}
    .panel-section-head h3{margin:0;font-family:var(--font-display);font-size:15px;color:var(--ink,#16182B)}
    .panel-section-head p{margin:2px 0 0;font-size:12.5px;color:var(--ink-soft,#585A72)}
    .panel-form-row{display:flex;gap:12px;align-items:end;flex-wrap:wrap}
    .panel-form-row .field-wrap{min-width:180px;display:flex;flex-direction:column;gap:6px}
    .panel-form-row .field-wrap label{font-size:12.5px;font-weight:600;color:var(--ink-soft,#585A72);margin:0}
    .panel-form-row .field-wrap input,.panel-form-row .field-wrap select{margin:0}

    .bulk-upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .bulk-upload-card{border:1px solid var(--line,#e2e8f0);border-radius:12px;padding:14px;background:var(--paper,#f8fafc)}
    .bulk-upload-card h4{margin:0 0 10px;font-size:13.5px;font-weight:700;color:var(--ink,#16182B)}
    .bulk-upload-card .btn-ghost{background:#fff;color:var(--violet-ink,#3E28B8);border:1px solid var(--violet,#5B3DF5);padding:8px 12px;font-size:13px}
    .bulk-upload-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:10px}
    .bulk-upload-form input[type="file"]{flex:1;min-width:160px;font-size:12px;padding:8px;margin:0}
    .bulk-upload-form .btn{padding:8px 12px;font-size:13px;white-space:nowrap}

    .student-delete-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .users-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}

    .form-progress-widget{position:fixed;right:18px;bottom:18px;z-index:1200;width:220px;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border:1px solid #dbe4f0;border-radius:18px;box-shadow:0 18px 45px rgba(15,23,42,.14);padding:12px 14px;display:none}
    .form-progress-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;font-weight:700;color:#0f172a}
    .form-progress-track{width:100%;height:10px;border-radius:999px;background:#e2e8f0;overflow:hidden}
    .form-progress-bar{height:100%;width:0%;border-radius:999px;background:linear-gradient(90deg,#5B3DF5 0%,#0EA57A 100%);transition:width .18s ease}
    .form-progress-meta{margin-top:8px;font-size:12px;color:#64748b;display:flex;justify-content:space-between;gap:10px}
    @media (max-width:768px){
        .bulk-upload-grid{grid-template-columns:1fr}
        .panel-form-row{display:grid !important;grid-template-columns:1fr;gap:10px !important;align-items:stretch !important}
        .panel-form-row .field-wrap{min-width:0;width:100%}
        .panel-form-row input,.panel-form-row select{width:100%}
        .panel-form-row .btn{justify-self:stretch;width:100%}
        .users-table-wrap table{min-width:680px}
        .bulk-upload-form input[type="file"]{width:100%;max-width:100%}
        .form-progress-widget{left:12px;right:12px;bottom:12px;width:auto}
    }
</style>

<div class="panel-section">
    <div class="panel-section-head">
        <h3>Toplu Kullanıcı Yükleme</h3>
        <p>Şablonu indirin, doldurup buradan yükleyin.</p>
    </div>
    <div class="bulk-upload-grid">
        <div class="bulk-upload-card">
            <h4>Öğrenci</h4>
            <a class="btn btn-ghost" href="{{ route('users.bulk.students.template') }}">Şablonu İndir (.xlsx)</a>
            <form method="POST" action="{{ route('users.bulk.students.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
                <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
                <button class="btn" type="submit">Yükle</button>
            </form>
        </div>
        <div class="bulk-upload-card">
            <h4>Öğretmen</h4>
            <a class="btn btn-ghost" href="{{ route('users.bulk.teachers.template') }}">Şablonu İndir (.xlsx)</a>
            <form method="POST" action="{{ route('users.bulk.teachers.store') }}" enctype="multipart/form-data" class="bulk-upload-form">@csrf
                <input type="file" name="file" accept=".xls,.xlsx,.csv,.txt" required>
                <button class="btn" type="submit">Yükle</button>
            </form>
        </div>
    </div>
</div>

<div class="panel-section">
    <div class="panel-section-head">
        <h3>Yeni Kullanıcı Ekle</h3>
        <p>Tek bir kullanıcı hesabı oluşturun.</p>
    </div>
    <form method="POST" action="{{ route('users.store') }}" class="panel-form-row">@csrf
        <div class="field-wrap" style="min-width:200px"><label>Ad Soyad</label><input name="name" required></div>
        <div class="field-wrap" style="min-width:220px"><label>E-posta</label><input type="email" name="email" required></div>
        <div class="field-wrap"><label>Şifre</label><input type="password" name="password" required></div>
        <div class="field-wrap"><label>Rol</label><select name="role" id="role-select" required><option value="teacher">Öğretmen</option><option value="student">Öğrenci</option><option value="admin">Admin</option></select></div>
        <div class="field-wrap" style="min-width:200px" id="class-wrap"><label>Sınıf (Öğrenci)</label><select name="school_class_id"><option value="">Seçiniz</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} / {{ $class->section }}</option>@endforeach</select></div>
        <button class="btn" type="submit">Kullanıcı Ekle</button>
    </form>
</div>

<div class="panel-section">
    <div class="panel-section-head">
        <h3>Kullanıcı Listesi</h3>
    </div>
    <div class="student-delete-tools" style="margin-bottom:12px">
        <button form="delete-selected-students-form" type="submit" class="btn btn-danger" style="padding:8px 14px;font-size:13px;">Seçilen Öğrencileri Sil</button>
        <form id="delete-all-students-form" method="POST" action="{{ route('users.students.destroy-all') }}" data-confirm="Tüm öğrenciler sistemden kaldırılsın mı?" style="display:inline">@csrf @method('DELETE')
            <button type="submit" class="btn btn-danger" style="padding:8px 14px;font-size:13px;">Tüm Öğrencileri Sil</button>
        </form>
    </div>
    <form id="delete-selected-students-form" method="POST" action="{{ route('users.students.destroy-selected') }}" data-confirm="Seçili öğrenciler sistemden kaldırılsın mı?" style="display:none">@csrf @method('DELETE')</form>
    <div class="table-responsive users-table-wrap">
        <table>
            <thead><tr><th><input type="checkbox" id="select-all-students" title="Tum ogrencileri sec"></th><th>ID</th><th>Ad</th><th>E-posta</th><th>Rol</th><th>İşlem</th></tr></thead>
            <tbody>
            @foreach($users as $item)
                <tr>
                    <td>@if($item->hasRole('student'))<input type="checkbox" class="student-row-checkbox" form="delete-selected-students-form" name="user_ids[]" value="{{ $item->id }}">@endif</td>
                    <td>{{ $item->id }}</td>
                    <td>
                        {{ $item->name }}
                        @if(in_array($item->role?->slug, ['teacher', 'admin'], true))
                            <a class="btn" href="{{ route('users.classes.edit', $item) }}" style="margin-left:8px;padding:6px 10px;font-size:12px;">Sınıf Ata</a>
                        @endif
                    </td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->role?->slug ?? '-' }}</td>
                    <td class="actions">
                        @if($item->hasRole('admin'))
                            <button class="btn" type="button" disabled style="padding:7px 12px;font-size:13px;">Admin Silinemez</button>
                        @else
                            <form method="POST" action="{{ route('users.destroy', $item) }}" data-confirm="Bu kullanıcı silinsin mi?">@csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" style="padding:7px 12px;font-size:13px;">Sil</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>
<div class="form-progress-widget" id="form-progress-widget" aria-live="polite">
    <div class="form-progress-title">
        <span>Kullanıcı Ekleme İlerlemesi</span>
        <span id="form-progress-percent">0%</span>
    </div>
    <div class="form-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div id="form-progress-bar" class="form-progress-bar"></div>
    </div>
    <div class="form-progress-meta">
        <span id="form-progress-status">Alanları doldurun</span>
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
    if (progressStatus) progressStatus.textContent = bulkProgressCurrent >= bulkProgressTotal ? 'Tamamland�' : '��leniyor...';
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
                if (progressStatus) progressStatus.textContent = 'Y�klemeye haz�r';
                if (progressPercent) progressPercent.textContent = '0%';
                if (progressBar) progressBar.style.width = '0%';
            }).catch(() => {
                bulkProgressTotal = 1;
                if (progressStatus) progressStatus.textContent = 'Dosya okunamad�';
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
        if (progressStatus) progressStatus.textContent = 'Y�kleniyor...';
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
            if (progressStatus) progressStatus.textContent = 'Dosya aktar�l�yor...';
        });
        xhr.onload = () => {
            const data = xhr.response || {};
            bulkProgressTotal = Math.max(1, Number(data.total || bulkProgressTotal || 1));
            if (progressStatus) progressStatus.textContent = data.ok === false ? '��lenemedi' : '��leniyor...';
            const animate = () => {
                if (bulkProgressCurrent < bulkProgressTotal) {
                    bulkProgressCurrent += 1;
                    renderBulkProgress();
                    window.setTimeout(animate, 85);
                    return;
                }
                if (progressStatus) progressStatus.textContent = data.ok === false ? '��lenemedi' : 'Tamamland�';
                window.setTimeout(() => window.location.reload(), 350);
            };
            animate();
        };
        xhr.onerror = () => {
            if (progressStatus) progressStatus.textContent = 'Y�kleme ba�ar�s�z';
        };
        xhr.send(new FormData(bulkForm));
    });
});

hideWidget();
})();
</script>
@endsection