@php
    $baIsAdmin = (bool) ($isAdmin ?? false);
    $baCourses = ($assignableCourses ?? collect())->map(fn ($c) => [
        'id' => (int) $c->id,
        'name' => (string) $c->name,
        'teacher' => (int) ($c->teacher_id ?? 0),
        'cover' => (string) ($c->getAttribute('cover_url') ?? ''),
    ])->values();
    // Yönetici hesabı öğretmen listesinde gösterilmez; yalnızca öğretmen rolündeki kullanıcılar.
    $baTeachers = ($teachers ?? collect())->filter(fn ($t) => $t->user?->hasRole('teacher'))->map(fn ($t) => [
        'id' => (int) $t->id,
        'name' => (string) ($t->user?->name ?? ('Öğretmen #'.$t->id)),
    ])->values();
    $baClasses = $bulkAssignableClasses->map(fn ($c) => [
        'id' => (int) $c->id,
        'label' => $c->name.'/'.$c->section,
        'grade' => (int) ($c->grade_level ?? 0),
    ])->values();
@endphp

<style>
    .ba-overlay{position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:3050;padding:14px}
    .ba-overlay.open{display:flex}
    .ba-dialog{width:min(98vw,1280px);height:min(94vh,860px);background:#f4f7fb;border-radius:24px;box-shadow:0 30px 80px rgba(2,6,23,.35);display:grid;grid-template-rows:auto minmax(0,1fr) auto;overflow:hidden;color:#0f172a}
    .ba-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 22px;background:linear-gradient(120deg,#1e3a8a 0%,#2563eb 55%,#38bdf8 100%);color:#fff}
    .ba-head h3{margin:0;font-size:21px;font-weight:900;letter-spacing:-.01em}
    .ba-head p{margin:3px 0 0;font-size:13px;opacity:.88;font-weight:600}
    .ba-x{width:40px;height:40px;border-radius:12px;border:0;background:rgba(255,255,255,.18);color:#fff;font-size:22px;font-weight:700;cursor:pointer}
    .ba-x:hover{background:rgba(255,255,255,.3)}
    .ba-body{display:grid;grid-template-columns:minmax(300px,380px) minmax(0,1fr);gap:16px;padding:16px;min-height:0}
    .ba-pane{background:#fff;border:1px solid #e2e8f0;border-radius:18px;display:grid;grid-template-rows:auto minmax(0,1fr);min-height:0;overflow:hidden}
    .ba-pane-head{padding:14px 16px 10px;display:grid;gap:10px;border-bottom:1px solid #eef2f7}
    .ba-step{display:flex;align-items:center;gap:8px;font-weight:900;font-size:14px}
    .ba-step i{width:24px;height:24px;border-radius:50%;background:#2563eb;color:#fff;font-style:normal;display:grid;place-items:center;font-size:12px}
    .ba-seg{display:grid;grid-auto-flow:column;grid-auto-columns:1fr;gap:4px;background:#eef2f7;padding:4px;border-radius:14px}
    .ba-seg button{border:0;background:transparent;border-radius:11px;padding:9px 6px;font-weight:800;font-size:13px;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px}
    .ba-seg button.on{background:#fff;color:#1d4ed8;box-shadow:0 4px 12px rgba(15,23,42,.1)}
    .ba-scroll{overflow:auto;padding:12px 14px 16px;min-height:0}
    .ba-search{width:100%;height:42px;border:1px solid #cbd5e1;border-radius:12px;padding:0 12px;font-size:14px;background:#fff}
    .ba-search:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
    .ba-teacher{display:flex;align-items:center;gap:12px;width:100%;text-align:left;padding:10px 12px;margin-bottom:8px;border:2px solid #e2e8f0;border-radius:14px;background:#fff;cursor:pointer;transition:border-color .12s,transform .12s}
    .ba-teacher:hover{border-color:#93c5fd;transform:translateY(-1px)}
    .ba-teacher.on{border-color:#2563eb;background:#eff6ff}
    .ba-avatar{flex:0 0 auto;width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#2563eb);color:#fff;display:grid;place-items:center;font-weight:900;font-size:15px}
    .ba-teacher b{display:block;font-size:14px}.ba-teacher small{color:#64748b;font-weight:700}
    .ba-group{margin-bottom:14px}
    .ba-group-h{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;font-weight:900;font-size:13px;color:#334155}
    .ba-link{border:0;background:none;color:#2563eb;font-weight:800;font-size:12px;cursor:pointer;padding:2px 4px}
    .ba-chips{display:flex;flex-wrap:wrap;gap:8px}
    .ba-chip{border:2px solid #e2e8f0;background:#fff;border-radius:12px;padding:8px 12px;font-weight:800;font-size:13px;color:#334155;cursor:pointer}
    .ba-chip:hover{border-color:#93c5fd}
    .ba-chip.on{border-color:#2563eb;background:#2563eb;color:#fff}
    .ba-levels{display:grid;grid-template-columns:repeat(auto-fill,minmax(112px,1fr));gap:10px}
    .ba-level{border:2px solid #e2e8f0;background:#fff;border-radius:16px;padding:14px 8px;text-align:center;cursor:pointer}
    .ba-level strong{display:block;font-size:26px;line-height:1;color:#1e3a8a}.ba-level span{display:block;margin-top:6px;font-size:11.5px;font-weight:800;color:#64748b}
    .ba-level:hover{border-color:#93c5fd}
    .ba-level.on{border-color:#2563eb;background:linear-gradient(160deg,#eff6ff,#dbeafe)}
    .ba-tools{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .ba-tools .ba-search{flex:1 1 220px;width:auto}
    .ba-pill{border:1px solid #cbd5e1;background:#fff;border-radius:999px;padding:8px 14px;font-weight:800;font-size:12.5px;color:#334155;cursor:pointer}
    .ba-pill:hover{border-color:#2563eb;color:#1d4ed8}
    .ba-courses{display:grid;grid-template-columns:repeat(auto-fill,minmax(176px,1fr));gap:12px}
    .ba-course{position:relative;border:2px solid #e2e8f0;border-radius:16px;background:#fff;overflow:hidden;cursor:pointer;display:flex;flex-direction:column;text-align:left;padding:0;transition:transform .12s,border-color .12s,box-shadow .12s}
    .ba-course:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(15,23,42,.1)}
    .ba-course.on{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.18)}
    .ba-cover{aspect-ratio:16/10;background:linear-gradient(135deg,#c7d2fe,#bae6fd);display:grid;place-items:center;font-size:34px;font-weight:900;color:#4338ca;overflow:hidden}
    .ba-cover img{width:100%;height:100%;object-fit:cover;display:block}
    .ba-tick{position:absolute;top:8px;right:8px;width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.92);border:2px solid #cbd5e1;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:900}
    .ba-course.on .ba-tick{background:#2563eb;border-color:#2563eb}
    .ba-cmeta{padding:9px 11px 11px;display:grid;gap:6px}
    .ba-cmeta b{font-size:13px;line-height:1.25;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.5em}
    .ba-badge{justify-self:start;font-size:11px;font-weight:800;border-radius:999px;padding:3px 9px;background:#f1f5f9;color:#64748b}
    .ba-badge.ok{background:#dcfce7;color:#166534}.ba-badge.part{background:#dbeafe;color:#1d4ed8}.ba-badge.warn{background:#fef3c7;color:#92400e}
    .ba-empty{padding:30px 10px;text-align:center;color:#94a3b8;font-weight:700}
    .ba-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;background:#fff;border-top:1px solid #e2e8f0;flex-wrap:wrap}
    .ba-sum{font-weight:800;font-size:14px;color:#334155}.ba-sum em{font-style:normal;color:#2563eb}
    .ba-actions{display:flex;gap:10px;flex-wrap:wrap}
    .ba-btn{height:44px;padding:0 18px;border-radius:12px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-weight:800;font-size:14px;cursor:pointer}
    .ba-btn.primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);border-color:transparent;color:#fff;box-shadow:0 8px 18px rgba(37,99,235,.3)}
    .ba-btn.danger{background:#fff7ed;border-color:#fdba74;color:#c2410c}
    .ba-btn.danger.armed{background:#ea580c;color:#fff;border-color:transparent}
    .ba-btn:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
    @media (max-width:900px){
        .ba-dialog{height:96vh;border-radius:18px}
        .ba-body{grid-template-columns:1fr;grid-template-rows:minmax(0,.9fr) minmax(0,1.1fr);overflow:auto}
        .ba-courses{grid-template-columns:repeat(auto-fill,minmax(140px,1fr))}
    }
</style>

<div class="ba-overlay" id="ba-modal" role="dialog" aria-modal="true" aria-labelledby="ba-title">
    <div class="ba-dialog">
        <div class="ba-head">
            <div>
                <h3 id="ba-title">Ders Atama Merkezi</h3>
                <p>Hedefi seçin, dersleri işaretleyin ve tek seferde atayın.</p>
            </div>
            <button type="button" class="ba-x" id="ba-close" aria-label="Kapat">×</button>
        </div>

        <div class="ba-body">
            <section class="ba-pane">
                <div class="ba-pane-head">
                    <div class="ba-step"><i>1</i> Kime atanacak?</div>
                    <div class="ba-seg" id="ba-modes">
                        @if($baIsAdmin)<button type="button" data-mode="teacher">👩‍🏫 Öğretmen</button>@endif
                        <button type="button" data-mode="class">🏫 Sınıf</button>
                        <button type="button" data-mode="level">🎓 Kademe</button>
                    </div>
                </div>
                <div class="ba-scroll" id="ba-target"></div>
            </section>

            <section class="ba-pane">
                <div class="ba-pane-head">
                    <div class="ba-step"><i>2</i> Hangi dersler?</div>
                    <div class="ba-tools">
                        <input type="search" class="ba-search" id="ba-q" placeholder="Ders ara…" autocomplete="off">
                        <button type="button" class="ba-pill" id="ba-all">Görünenleri seç</button>
                        <button type="button" class="ba-pill" id="ba-assigned">Atanmış olanları seç</button>
                        <button type="button" class="ba-pill" id="ba-clear">Temizle</button>
                    </div>
                </div>
                <div class="ba-scroll"><div class="ba-courses" id="ba-courses"></div></div>
            </section>
        </div>

        <div class="ba-foot">
            <div class="ba-sum" id="ba-sum"></div>
            <div class="ba-actions">
                <button type="button" class="ba-btn" id="ba-cancel">İptal</button>
                <button type="button" class="ba-btn danger" id="ba-remove">Atamayı Kaldır</button>
                <button type="button" class="ba-btn primary" id="ba-save">Ata</button>
            </div>
        </div>
    </div>
    <form id="ba-form" method="POST" style="display:none">@csrf</form>
</div>

<script>
(() => {
    const modal = document.getElementById('ba-modal');
    const openBtn = document.getElementById('ba-open');
    if (!modal || !openBtn) return;
    const IS_ADMIN = @json($baIsAdmin);
    const COURSES = @json($baCourses);
    const TEACHERS = @json($baTeachers);
    const CLASSES = @json($baClasses);
    const BY_TEACHER = @json((object) ($courseIdsByTeacher ?? []));
    const BY_CLASS = @json((object) ($courseIdsByClass ?? []));
    const MY_TEACHER = @json((int) (optional(auth()->user()?->teacher)->id ?? 0));
    const ROUTES = {
        teacher: { add: @json(route('courses.assign-teacher.bulk')), del: @json(route('courses.unassign-teacher.bulk')) },
        classes: { add: @json(route('courses.assign-classes.bulk')), del: @json(route('courses.unassign-classes.bulk')) },
    };
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const norm = (s) => String(s).toLocaleLowerCase('tr');

    const st = { mode: IS_ADMIN ? 'teacher' : 'class', teacher: 0, classes: new Set(), levels: new Set(), courses: new Set(), q: '', tq: '' };
    let armed = null;

    const levelsList = () => {
        const m = new Map();
        CLASSES.forEach((c) => m.set(c.grade, (m.get(c.grade) || 0) + 1));
        return [...m.entries()].sort((a, b) => a[0] - b[0]);
    };
    const targetClassIds = () => {
        if (st.mode === 'class') return [...st.classes];
        if (st.mode === 'level') return CLASSES.filter((c) => st.levels.has(c.grade)).map((c) => c.id);
        return [];
    };
    const targetCount = () => st.mode === 'teacher' ? (st.teacher ? 1 : 0) : targetClassIds().length;
    const teacherName = (id) => (TEACHERS.find((t) => t.id === id) || {}).name || '';

    // Hedefe (öğretmen / sınıf / kademe) atanmış dersleri otomatik seç.
    function autoSelect() {
        let ids = [];
        if (st.mode === 'teacher') ids = st.teacher ? (BY_TEACHER[st.teacher] || []) : [];
        else ids = [...new Set(targetClassIds().flatMap((id) => BY_CLASS[id] || []))];
        st.courses = new Set(ids.map(Number).filter((id) => COURSES.some((c) => c.id === id)));
    }
    function resetState() {
        clearTimeout(armed); armed = null;
        st.mode = IS_ADMIN ? 'teacher' : 'class';
        st.teacher = 0; st.q = ''; st.tq = '';
        st.classes = new Set(); st.levels = new Set(); st.courses = new Set();
        const q = $('ba-q'); if (q) q.value = '';
    }

    function renderModes() {
        document.querySelectorAll('#ba-modes button').forEach((b) => b.classList.toggle('on', b.dataset.mode === st.mode));
    }

    function renderTarget() {
        const box = $('ba-target');
        if (st.mode === 'teacher') {
            const q = norm(st.tq);
            const list = TEACHERS.filter((t) => !q || norm(t.name).includes(q));
            box.innerHTML = '<input type="search" class="ba-search" id="ba-tq" placeholder="Öğretmen ara…" value="' + esc(st.tq) + '" style="margin-bottom:10px">'
                + (list.length ? list.map((t) => {
                    const n = (BY_TEACHER[t.id] || []).length;
                    return '<button type="button" class="ba-teacher' + (st.teacher === t.id ? ' on' : '') + '" data-teacher="' + t.id + '">'
                        + '<span class="ba-avatar">' + esc(t.name.trim().charAt(0).toLocaleUpperCase('tr') || '?') + '</span>'
                        + '<span><b>' + esc(t.name) + '</b><small>' + n + ' ders</small></span></button>';
                }).join('') : '<div class="ba-empty">Öğretmen bulunamadı.</div>');
            const tq = $('ba-tq');
            if (tq) { tq.addEventListener('input', () => { st.tq = tq.value; const pos = tq.selectionStart; renderTarget(); const n = $('ba-tq'); n.focus(); n.setSelectionRange(pos, pos); }); }
        } else if (st.mode === 'class') {
            const groups = new Map();
            CLASSES.forEach((c) => { if (!groups.has(c.grade)) groups.set(c.grade, []); groups.get(c.grade).push(c); });
            box.innerHTML = [...groups.entries()].sort((a, b) => a[0] - b[0]).map(([g, arr]) => {
                const all = arr.every((c) => st.classes.has(c.id));
                return '<div class="ba-group"><div class="ba-group-h"><span>' + (g || '?') + '. Sınıf</span>'
                    + '<button type="button" class="ba-link" data-grp="' + g + '">' + (all ? 'Seçimi kaldır' : 'Tümünü seç') + '</button></div>'
                    + '<div class="ba-chips">' + arr.map((c) => '<button type="button" class="ba-chip' + (st.classes.has(c.id) ? ' on' : '') + '" data-class="' + c.id + '">' + esc(c.label) + '</button>').join('') + '</div></div>';
            }).join('') || '<div class="ba-empty">Sınıf bulunamadı.</div>';
        } else {
            const lv = levelsList();
            box.innerHTML = '<div class="ba-levels">' + lv.map(([g, n]) =>
                '<button type="button" class="ba-level' + (st.levels.has(g) ? ' on' : '') + '" data-level="' + g + '"><strong>' + (g || '?') + '</strong><span>' + (g || '?') + '. Sınıf · ' + n + ' şube</span></button>'
            ).join('') + '</div>' + (lv.length ? '<p style="margin:12px 2px 0;color:#64748b;font-size:12.5px;font-weight:700">Seçilen kademedeki tüm şubelere ders atanır.</p>' : '<div class="ba-empty">Kademe bulunamadı.</div>');
        }
    }

    function badge(c) {
        if (st.mode === 'teacher') {
            if (st.teacher && c.teacher === st.teacher) return '<span class="ba-badge ok">✓ Bu öğretmende</span>';
            if (c.teacher > 0) return '<span class="ba-badge warn">' + esc(teacherName(c.teacher) || 'Atanmış') + '</span>';
            return '<span class="ba-badge">Boş</span>';
        }
        const ids = targetClassIds();
        if (!ids.length) return '<span class="ba-badge">Hedef seçin</span>';
        const n = ids.filter((id) => (BY_CLASS[id] || []).includes(c.id)).length;
        if (n === 0) return '<span class="ba-badge">Atanmamış</span>';
        return '<span class="ba-badge ' + (n === ids.length ? 'ok' : 'part') + '">' + (n === ids.length ? '✓ ' : '') + n + '/' + ids.length + ' sınıfta</span>';
    }

    function visibleCourses() {
        const q = norm(st.q);
        return COURSES.filter((c) => !q || norm(c.name).includes(q));
    }

    function renderCourses() {
        const list = visibleCourses();
        $('ba-courses').innerHTML = list.length ? list.map((c) => {
            const cover = c.cover
                ? '<img src="' + esc(c.cover) + '" alt="" loading="lazy" onerror="this.remove()">'
                : '';
            return '<button type="button" class="ba-course' + (st.courses.has(c.id) ? ' on' : '') + '" data-course="' + c.id + '">'
                + '<span class="ba-cover">' + cover + esc(c.name.trim().charAt(0).toLocaleUpperCase('tr')) + '</span>'
                + '<span class="ba-tick">' + (st.courses.has(c.id) ? '✓' : '') + '</span>'
                + '<span class="ba-cmeta"><b>' + esc(c.name) + '</b>' + badge(c) + '</span></button>';
        }).join('') : '<div class="ba-empty" style="grid-column:1/-1">Ders bulunamadı.</div>';
    }

    function renderFoot() {
        const k = st.courses.size, t = targetCount();
        const what = st.mode === 'teacher' ? (t ? esc(teacherName(st.teacher)) : 'öğretmen seçilmedi') : (t + ' sınıf');
        $('ba-sum').innerHTML = '<em>' + k + '</em> ders → ' + what;
        const ok = k > 0 && t > 0;
        $('ba-save').disabled = !ok;
        $('ba-remove').disabled = !ok;
        $('ba-remove').classList.toggle('armed', !!armed);
        $('ba-remove').textContent = armed ? 'Emin misiniz? Tekrar tıklayın' : 'Atamayı Kaldır';
    }

    function renderAll() { renderModes(); renderTarget(); renderCourses(); renderFoot(); }
    function refresh() { renderCourses(); renderFoot(); }

    function open() {
        resetState();
        renderAll();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function close() { modal.classList.remove('open'); document.body.style.overflow = ''; resetState(); }

    function submit(kind) {
        const f = $('ba-form');
        [...f.querySelectorAll('input[name]:not([name=_token])')].forEach((n) => n.remove());
        const add = (name, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = v; f.appendChild(i); };
        st.courses.forEach((id) => add('course_ids[]', id));
        if (st.mode === 'teacher') { add('teacher_id', st.teacher); f.action = ROUTES.teacher[kind]; }
        else { targetClassIds().forEach((id) => add('class_ids[]', id)); f.action = ROUTES.classes[kind]; }
        f.submit();
    }

    openBtn.addEventListener('click', open);
    $('ba-close').addEventListener('click', close);
    $('ba-cancel').addEventListener('click', close);
    modal.addEventListener('mousedown', (e) => { if (e.target === modal) close(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

    $('ba-modes').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-mode]'); if (!b) return;
        st.mode = b.dataset.mode; armed = null; autoSelect(); renderAll();
    });
    $('ba-target').addEventListener('click', (e) => {
        const t = e.target.closest('[data-teacher]');
        const c = e.target.closest('[data-class]');
        const g = e.target.closest('[data-grp]');
        const l = e.target.closest('[data-level]');
        if (t) {
            // Öğretmene tıklanınca ona atanmış dersler otomatik seçilir.
            st.teacher = Number(t.dataset.teacher);
        }
        else if (c) { const id = Number(c.dataset.class); st.classes.has(id) ? st.classes.delete(id) : st.classes.add(id); }
        else if (g) {
            const grade = Number(g.dataset.grp), arr = CLASSES.filter((x) => x.grade === grade);
            const all = arr.every((x) => st.classes.has(x.id));
            arr.forEach((x) => all ? st.classes.delete(x.id) : st.classes.add(x.id));
        }
        else if (l) { const v = Number(l.dataset.level); st.levels.has(v) ? st.levels.delete(v) : st.levels.add(v); }
        else return;
        armed = null; autoSelect(); renderTarget(); refresh();
    });
    $('ba-courses').addEventListener('click', (e) => {
        const b = e.target.closest('[data-course]'); if (!b) return;
        const id = Number(b.dataset.course);
        st.courses.has(id) ? st.courses.delete(id) : st.courses.add(id);
        b.classList.toggle('on', st.courses.has(id));
        b.querySelector('.ba-tick').textContent = st.courses.has(id) ? '✓' : '';
        armed = null; renderFoot();
    });
    $('ba-q').addEventListener('input', (e) => { st.q = e.target.value; renderCourses(); });
    $('ba-all').addEventListener('click', () => { visibleCourses().forEach((c) => st.courses.add(c.id)); armed = null; renderCourses(); renderFoot(); });
    $('ba-clear').addEventListener('click', () => { st.courses.clear(); armed = null; renderCourses(); renderFoot(); });
    $('ba-assigned').addEventListener('click', () => {
        const ids = st.mode === 'teacher'
            ? (st.teacher ? (BY_TEACHER[st.teacher] || []) : [])
            : [...new Set(targetClassIds().flatMap((id) => BY_CLASS[id] || []))];
        ids.forEach((id) => { if (COURSES.some((c) => c.id === Number(id))) st.courses.add(Number(id)); });
        armed = null; renderCourses(); renderFoot();
    });
    $('ba-save').addEventListener('click', () => { if (st.courses.size && targetCount()) submit('add'); });
    $('ba-remove').addEventListener('click', () => {
        if (!st.courses.size || !targetCount()) return;
        if (!armed) { armed = setTimeout(() => { armed = null; renderFoot(); }, 4000); renderFoot(); return; }
        clearTimeout(armed); armed = null; submit('del');
    });
})();
</script>
