@extends('layout.app')
@section('title','Ödevlerim')
@section('content')
<div class="top"><h1>Ödevlerim</h1></div>
<style>
    @media (max-width:768px){
        .student-assignment-table thead{display:none}
        .student-assignment-table,
        .student-assignment-table tbody,
        .student-assignment-table tr,
        .student-assignment-table td{display:block;width:100%}
        .student-assignment-table tr{
            border:1px solid #dbe5f2;
            border-radius:12px;
            padding:10px;
            margin-bottom:10px;
            background:#fff;
        }
        .student-assignment-table td{
            border-bottom:1px dashed #e2e8f0;
            padding:8px 0;
            text-align:left;
        }
        .student-assignment-table td:last-child{border-bottom:0}
        .student-assignment-table td::before{
            content:attr(data-label);
            display:block;
            font-size:12px;
            font-weight:700;
            color:#475569;
            margin-bottom:4px;
        }
        .student-assignment-table td.actions{
            display:grid;
            gap:8px;
        }
        .student-assignment-table td.actions .btn,
        .student-assignment-table td.actions .badge{
            width:100%;
            text-align:center;
            justify-content:center;
        }
    }
</style>

<div class="card">
    <h3>Ödevlerim</h3>
    <table class="student-assignment-table">
        <thead><tr><th>Ders</th><th>Başlık</th><th>Sınıf</th><th>Teslim</th><th>Durum</th><th>İşlem</th></tr></thead>
        <tbody>
        @forelse($courseHomeworks as $h)
            @php $p = $progress[$h->id] ?? null; @endphp
            <tr>
                <td data-label="Ders">{{ $h->course?->name ?? '-' }}</td>
                <td data-label="Başlık">{{ $h->title }}</td>
                <td data-label="Sınıf">{{ $h->schoolClass?->name }}/{{ $h->schoolClass?->section }}</td>
                <td data-label="Teslim">{{ $h->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="badge">{{ $p?->completed_at ? 'Tamamlandı' : ($p?->started_at ? 'Devam Ediyor' : 'Bekliyor') }}</span>
                </td>
                <td class="actions" data-label="İşlem">
                    <button
                        class="btn btn-detail"
                        type="button"
                        data-homework-detail
                        data-title="{{ e($h->title) }}"
                        data-course="{{ e($h->course?->name ?? '-') }}"
                        data-class="{{ e(($h->schoolClass?->name ?? '-') . '/' . ($h->schoolClass?->section ?? '-')) }}"
                        data-due="{{ e($h->due_date?->format('Y-m-d') ?? '-') }}"
                        data-type="{{ e(strtoupper($h->assignment_type ?? 'lesson')) }}"
                        data-description="{{ e($h->details ?: '-') }}"
                        data-file-url="{{ $h->attachment_path ? e(asset('storage/'.$h->attachment_path)) : '' }}"
                        data-file-name="{{ e($h->attachment_original_name ?? '') }}"
                    >Detay</button>

                    @if($p?->completed_at)
                        <span class="badge">Tamamlandı</span>
                    @else
                        <a class="btn" href="{{ route('student.portal.homework.open', $h) }}">
                            {{ $p?->started_at ? 'Devam Et' : 'Ödeve Başla' }}
                        </a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Ödev bulunmuyor.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $courseHomeworks->links('partials.pagination') }}
</div>

<div class="card">
    <h3>Oyun ve Etkinlik Ödevleri</h3>
    <table class="student-assignment-table">
        <thead><tr><th>Uygulama</th><th>Ödev</th><th>Teslim</th><th>Level</th><th>Durum</th><th>Başla</th></tr></thead>
        <tbody>
        @forelse($assignments as $a)
            @php $gp = $gameProgress[$a->id] ?? null; @endphp
            <tr>
                <td data-label="Uygulama">{{ $a->game_name }}</td>
                <td data-label="Ödev">{{ $a->title }}</td>
                <td data-label="Teslim">{{ $a->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td data-label="Level">{{ $a->level_from ?? '-' }} - {{ $a->level_to ?? '-' }}</td>
                <td data-label="Durum">
                    <span class="badge">{{ $gp?->completed_at ? 'Tamamlandı' : ($gp?->started_at ? 'Devam Ediyor' : 'Bekliyor') }}</span>
                </td>
                <td data-label="Başla">
                    @if($gp?->completed_at)
                        <span class="badge">Tamamlandı</span>
                    @else
                        <a class="btn" href="{{ route('student.portal.game-assignment.open', $a) }}">Ödeve Başla</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Oyun/etkinlik ödevi yok.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $assignments->links('partials.pagination') }}
</div>

<div id="student-homework-detail-modal" class="modal">
    <div class="modal-card">
        <div class="modal-head">
            <strong>Ödev Detayı</strong>
            <button class="btn" type="button" data-close-modal>Kapat</button>
        </div>
        <div class="detail-grid">
            <p><b>Başlık:</b> <span id="detail-title">-</span></p>
            <p><b>Ders:</b> <span id="detail-course">-</span></p>
            <p><b>Sınıf/Şube:</b> <span id="detail-class">-</span></p>
            <p><b>Son Teslim:</b> <span id="detail-due">-</span></p>
            <p><b>Tür:</b> <span id="detail-type">-</span></p>
            <p><b>Açıklama:</b></p>
            <p id="detail-description" style="white-space:pre-wrap">-</p>
            <p><b>Ek Dosya:</b> <a id="detail-file" href="#" target="_blank" style="display:none">Dosyayı Aç</a><span id="detail-no-file">Yok</span></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('student-homework-detail-modal');
    const titleEl = document.getElementById('detail-title');
    const courseEl = document.getElementById('detail-course');
    const classEl = document.getElementById('detail-class');
    const dueEl = document.getElementById('detail-due');
    const typeEl = document.getElementById('detail-type');
    const descEl = document.getElementById('detail-description');
    const fileEl = document.getElementById('detail-file');
    const noFileEl = document.getElementById('detail-no-file');

    function openModal() { modal.classList.add('open'); }

    document.querySelectorAll('[data-homework-detail]').forEach((btn) => {
        btn.addEventListener('click', function () {
            titleEl.textContent = this.dataset.title || '-';
            courseEl.textContent = this.dataset.course || '-';
            classEl.textContent = this.dataset.class || '-';
            dueEl.textContent = this.dataset.due || '-';
            typeEl.textContent = this.dataset.type || '-';
            descEl.textContent = this.dataset.description || '-';

            if (this.dataset.fileUrl) {
                fileEl.href = this.dataset.fileUrl;
                fileEl.textContent = this.dataset.fileName || 'Dosyayı Aç';
                fileEl.style.display = 'inline';
                noFileEl.style.display = 'none';
            } else {
                fileEl.removeAttribute('href');
                fileEl.style.display = 'none';
                noFileEl.style.display = 'inline';
            }

            openModal();
        });
    });
})();
</script>
@endpush
@endsection
