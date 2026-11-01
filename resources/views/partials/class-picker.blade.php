{{-- Ödev formlarında kademe + sınıf seçici. Kullanım: @include('partials.class-picker', ['classes' => $classes, 'uid' => 'hw']) --}}
@php
    $uid = $uid ?? 'cp';
    $oldIds = collect(old('class_ids', []))->map(fn ($id) => (int) $id)->all();
    $groups = $classes->groupBy(fn ($c) => $c->grade_level ?: (preg_match('/^\d+/', (string) $c->name, $m) ? (int) $m[0] : 0))->sortKeys();
    $isTeacher = auth()->user()?->hasRole('teacher');
@endphp
<div class="cp" id="{{ $uid }}-cp">
    <style>
        .cp{display:grid;gap:10px;margin:2px 0 8px}
        .cp-all{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1.5px solid #cbd5e1;border-radius:12px;background:#f8fafc;cursor:pointer;font-weight:800;margin:0}
        .cp-all input{width:auto;margin:0}.cp-all.on{border-color:#2563eb;background:#eff6ff;color:#1e3a8a}
        .cp-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
        .cp-lab{font-size:12px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.04em}
        .cp-k{border:1.5px solid #cbd5e1;background:#fff;color:#334155;border-radius:999px;padding:6px 14px;font-weight:800;font-size:13px;cursor:pointer}
        .cp-k.on{background:#2563eb;border-color:#2563eb;color:#fff}.cp-k.part{background:#dbeafe;border-color:#93c5fd;color:#1e3a8a}
        .cp-grp{border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;display:grid;gap:8px;background:#fff}
        .cp-chip{position:relative;margin:0;cursor:pointer}
        .cp-chip input{position:absolute;opacity:0;pointer-events:none;width:0;height:0;margin:0}
        .cp-chip span{display:inline-block;min-width:54px;text-align:center;padding:7px 12px;border:1.5px solid #cbd5e1;border-radius:10px;font-weight:800;font-size:13.5px;background:#f8fafc;color:#334155;transition:all .12s}
        .cp-chip input:checked+span{background:#16a34a;border-color:#16a34a;color:#fff}
        .cp-chip input:checked+span::before{content:"✓ "}
        .cp-chip input:focus-visible+span{outline:2px solid #2563eb;outline-offset:2px}
        .cp.is-all .cp-k,.cp.is-all .cp-grp{opacity:.45;pointer-events:none}
        .cp-sum{font-size:12.5px;font-weight:700;color:#475569}
    </style>
    <label class="cp-all"><input type="checkbox" name="all_classes" value="1" data-cp-all @checked(old('all_classes'))><span>{{ $isTeacher ? 'Tüm atandığım sınıflara ver' : 'Tüm sınıflara ver' }}</span></label>
    @if($groups->count() > 0)
        <div class="cp-row"><span class="cp-lab">Kademe</span>
            @foreach($groups as $grade => $items)
                <button type="button" class="cp-k" data-cp-grade="{{ $grade }}">{{ $grade ? $grade.'. sınıf' : 'Diğer' }}</button>
            @endforeach
        </div>
        @foreach($groups as $grade => $items)
            <div class="cp-grp" data-cp-group="{{ $grade }}">
                <div class="cp-lab">{{ $grade ? $grade.'. sınıf şubeleri' : 'Diğer sınıflar' }}</div>
                <div class="cp-row">
                    @foreach($items->sortBy('section') as $class)
                        <label class="cp-chip" title="{{ $class->academic_year }}"><input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked(in_array((int) $class->id, $oldIds, true))><span>{{ $class->name }}/{{ $class->section }}</span></label>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="cp-sum">Seçilebilir sınıf bulunamadı.</div>
    @endif
    <div class="cp-sum" data-cp-sum></div>
</div>
<script>
(() => {
    const root = document.getElementById(@json($uid.'-cp')); if (!root) return;
    const all = root.querySelector('[data-cp-all]');
    const boxes = [...root.querySelectorAll('input[name="class_ids[]"]')];
    const ks = [...root.querySelectorAll('[data-cp-grade]')];
    const gradeBoxes = (g) => [...root.querySelectorAll('[data-cp-group="' + g + '"] input')];
    const sync = () => {
        root.classList.toggle('is-all', all.checked);
        all.closest('.cp-all').classList.toggle('on', all.checked);
        boxes.forEach((b) => { b.disabled = all.checked; });
        ks.forEach((k) => {
            const g = gradeBoxes(k.dataset.cpGrade), n = g.filter((b) => b.checked).length;
            k.classList.toggle('on', n > 0 && n === g.length); k.classList.toggle('part', n > 0 && n < g.length);
        });
        const n = boxes.filter((b) => b.checked).length;
        root.querySelector('[data-cp-sum]').textContent = all.checked ? 'Tüm sınıflara verilecek.' : (n ? n + ' sınıf seçildi.' : 'Henüz sınıf seçilmedi.');
    };
    ks.forEach((k) => k.addEventListener('click', () => {
        const g = gradeBoxes(k.dataset.cpGrade), every = g.every((b) => b.checked);
        g.forEach((b) => { b.checked = !every; }); sync();
    }));
    root.addEventListener('change', sync);
    root.closest('form')?.addEventListener('submit', (e) => {
        if (!all.checked && !boxes.some((b) => b.checked)) { e.preventDefault(); alert('En az bir sınıf seçin veya "Tüm sınıflara ver" kutusunu işaretleyin.'); }
    });
    sync();
})();
</script>
