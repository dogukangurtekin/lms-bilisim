@extends('layout.app')
@section('title','Öğretmen Paneli')
@section('content')
@php
    $layout = $dashboardLayout ?? [];
    $selectedClassId = (int) ($dashboard['selected_class_id'] ?? 0);
    $selectedClassLabel = 'Genel görünüm';
    foreach (($dashboard['class_tabs'] ?? []) as $tab) {
        if ((int) ($tab['id'] ?? 0) === $selectedClassId) {
            $selectedClassLabel = $tab['label'] ?? $selectedClassLabel;
            break;
        }
    }
@endphp
<div class="dashboard-shell" data-dashboard-shell>
    <section class="dashboard-hero">
        <div>
            <h1>{{ $dashboard['headline_name'] }} | Öğretmen Paneli</h1>
            <p>Öğrenci, sınıf ve ders verilerini tek bakışta izleyin. Widgetleri düzenleyebilir, gizleyebilir ve boyutlandırabilirsiniz.</p>
        </div>
    </section>

    <section class="class-tabs-strip" aria-label="Sınıf sekmeleri">
        <a class="class-tab {{ $selectedClassId === 0 ? 'active' : '' }}" href="{{ route('dashboard') }}">Tümü</a>
        @foreach(($dashboard['class_tabs'] ?? []) as $classTab)
            <a class="class-tab {{ $selectedClassId === (int) ($classTab['id'] ?? 0) ? 'active' : '' }}" href="{{ route('dashboard', ['class_id' => $classTab['id']]) }}">{{ $classTab['label'] }}</a>
        @endforeach
    </section>

    <section class="widget-grid" id="dashboard-widget-grid">
        <a class="dashboard-widget dashboard-widget-qr widget-span-{{ $layout['quick_qr']['span'] ?? 6 }}" data-widget-key="quick_qr" href="{{ route('qr.login.menu') }}" draggable="true">
            <div class="widget-head">
                <div>
                    <strong>Mobil QR Girişi</strong>
                    <span>Mobilde sabit</span>
                    <small class="widget-class-tag">{{ $selectedClassLabel }}</small>
                </div>
                <button type="button" class="widget-toggle" data-widget-toggle="quick_qr" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="qr-widget-body">
                <div>
                    <small>QR giriş</small>
                    <h3>Hemen okut</h3>
                    <p>Mobilde açık kalır.</p>
                </div>
                <img src="{{ asset('qr-mini.svg') }}" alt="QR">
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </a>

        <article class="dashboard-widget widget-span-{{ $layout['students']['span'] ?? 3 }}" data-widget-key="students" draggable="true">
            <div class="widget-head">
                <div><strong>Toplam Öğrenci</strong><span>Aktif havuz</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="students" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>{{ $dashboard['summary']['total_students'] }}</strong>
                <span>Toplam kayıt</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>

        <article class="dashboard-widget widget-span-{{ $layout['active_students']['span'] ?? 3 }}" data-widget-key="active_students" draggable="true">
            <div class="widget-head">
                <div><strong>Aktif Öğrenci</strong><span>Son 10 dakika</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="active_students" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>{{ $dashboard['summary']['active_students'] }}</strong>
                <span>Çevrimiçi</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>

        <article class="dashboard-widget widget-span-{{ $layout['classes']['span'] ?? 3 }}" data-widget-key="classes" draggable="true">
            <div class="widget-head">
                <div><strong>Sınıf Sayısı</strong><span>İzlenen sınıflar</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="classes" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>{{ $dashboard['summary']['total_classes'] }}</strong>
                <span>Sistem genelinde</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>

        <article class="dashboard-widget widget-span-{{ $layout['courses']['span'] ?? 3 }}" data-widget-key="courses" draggable="true">
            <div class="widget-head">
                <div><strong>Ders Sayısı</strong><span>Aktif içerik</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="courses" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>{{ $dashboard['summary']['total_courses'] }}</strong>
                <span>Tanımlı ders</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>

        <article class="dashboard-widget widget-span-{{ $layout['avg_completion']['span'] ?? 3 }}" data-widget-key="avg_completion" draggable="true">
            <div class="widget-head">
                <div><strong>Ortalama Not</strong><span>Genel başarı</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="avg_completion" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>%{{ $dashboard['summary']['avg_completion'] }}</strong>
                <span>Yüzdelik başarı</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>

        <article class="dashboard-widget widget-span-{{ $layout['xp']['span'] ?? 3 }}" data-widget-key="xp" draggable="true">
            <div class="widget-head">
                <div><strong>Toplam XP</strong><span>Biriken puan</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                <button type="button" class="widget-toggle" data-widget-toggle="xp" aria-label="Gizle" title="Gizle">−</button>
            </div>
            <div class="metric-card">
                <strong>{{ $dashboard['summary']['total_xp'] }}</strong>
                <span>Öğrenci üretimi</span>
            </div>
            <span class="widget-resize-handle" aria-hidden="true"></span>
        </article>
    </section>

    <section class="dashboard-main-layout">
        <div class="dashboard-left-rail"></div>
        <div class="dashboard-center-column">
            <section class="dashboard-grid-2">
                <article class="dashboard-widget dashboard-widget-wide widget-span-12" data-widget-key="notes" draggable="true">
                    <div class="widget-head">
                        <div><strong>Öğretmen Notları</strong><span>Hızlı öneriler</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="notes" aria-label="Gizle" title="Gizle">−</button>
                    </div>
                    <div class="note-list">
                        <article><span>Odak</span><p>{{ $dashboard['highlights']['focus_title'] }}: {{ $dashboard['highlights']['focus_desc'] }}</p></article>
                        <article><span>Güç</span><p>{{ $dashboard['highlights']['power_title'] }}: {{ $dashboard['highlights']['power_desc'] }}</p></article>
                        <article><span>Ritim</span><p>{{ $dashboard['highlights']['rhythm_title'] }}: {{ $dashboard['highlights']['rhythm_desc'] }}</p></article>
                        <article><span>Devamsızlık</span><p>Bugün {{ $dashboard['summary']['absent_today'] }} öğrenci devamsız görünüyor.</p></article>
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>

            </section>
        </div>

        <aside class="dashboard-right-column">
            <section class="dashboard-sidebar-stack">
                <div class="dashboard-sidebar-placeholder">
                    <div class="sidebar-placeholder-card sidebar-placeholder-card--dark">
                        <strong>Ek Alan</strong>
                        <span>Öğretmen ve admin hızlı erişim seçenekleri burada olacak.</span>
                    </div>
                </div>

                <article class="dashboard-widget dashboard-widget-wide widget-span-12 dashboard-leaderboard-panel" data-widget-key="leaderboard" draggable="true">
                    <div class="widget-head">
                        <div><strong>İlk 5 Öğrenci Başarı Listesi</strong><span>{{ $selectedClassId === 0 ? 'Tüm sınıflar genelinde' : 'Seçili sınıf' }}</span><small class="widget-class-tag">{{ $selectedClassLabel }}</small></div>
                        <button type="button" class="widget-toggle" data-widget-toggle="leaderboard" aria-label="Gizle" title="Gizle">−</button>
                    </div>
                    <div class="teacher-top10-list">
                        @forelse(($dashboard['top_students'] ?? []) as $row)
                            <div class="teacher-top10-item">
                                <div class="teacher-top10-rank rank-{{ (int) ($row['rank'] ?? 0) }}">{{ $row['rank'] }}</div>
                                <div class="teacher-top10-main">
                                    <strong>{{ $row['name'] }}</strong>
                                    <span>{{ $row['class_name'] }}</span>
                                </div>
                                <div class="teacher-top10-xp">{{ $row['xp'] }} XP</div>
                            </div>
                        @empty
                            <p>Henüz öğrenci verisi yok.</p>
                        @endforelse
                    </div>
                    <span class="widget-resize-handle" aria-hidden="true"></span>
                </article>
            </section>
        </aside>
    </section>

    <div class="dashboard-actions-bar">
        <button type="button" class="btn btn-secondary" data-open-widget-editor>Widgetleri Düzenle</button>
        <button type="button" class="btn btn-warning" id="dashboard-save-btn" hidden>Düzeni Kaydet</button>
        <button type="button" class="btn btn-secondary" id="dashboard-close-edit-btn" hidden>Düzenlemeyi Kapat</button>
    </div>

    <aside class="widget-library-panel" id="widget-library-panel" aria-hidden="true">
        <div class="widget-library-head">
            <div>
                <strong>Widget Kütüphanesi</strong>
                <span>Gizlenen widgetleri geri ekleyin</span>
            </div>
            <div class="widget-library-actions">
                <button type="button" class="widget-library-minimize" id="widget-library-minimize" aria-label="Küçült" title="Küçült">▁</button>
                <button type="button" class="widget-library-close" id="widget-library-close" aria-label="Kapat" title="Kapat">×</button>
            </div>
        </div>
        <div class="widget-library-list" id="dashboard-widget-library"></div>
        <button type="button" class="widget-library-fab" id="widget-library-fab" aria-label="Widget kütüphanesini aç" title="Widget kütüphanesini aç">⊞</button>
    </aside>
</div>

@push('scripts')
<script>
(() => {
    const shell = document.querySelector('[data-dashboard-shell]');
    if (!shell) return;
    const grid = document.getElementById('dashboard-widget-grid');
    const library = document.getElementById('dashboard-widget-library');
    const libraryPanel = document.getElementById('widget-library-panel');
    const saveBtn = document.getElementById('dashboard-save-btn');
    const closeBtn = document.getElementById('dashboard-close-edit-btn');
    const libraryClose = document.getElementById('widget-library-close');
    const libraryMinimize = document.getElementById('widget-library-minimize');
    const libraryFab = document.getElementById('widget-library-fab');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const saveUrl = @json(route('dashboard.widget-layout.save'));
    const initialLayout = @json($layout);

    const defs = {
        quick_qr: { title: 'Mobil QR Girişi', span: 6, order: 10 },
        students: { title: 'Toplam Öğrenci', span: 3, order: 20 },
        active_students: { title: 'Aktif Öğrenci', span: 3, order: 30 },
        classes: { title: 'Sınıf Sayısı', span: 3, order: 40 },
        courses: { title: 'Ders Sayısı', span: 3, order: 50 },
        avg_completion: { title: 'Ortalama Not', span: 3, order: 60 },
        xp: { title: 'Toplam XP', span: 3, order: 70 },
        signals: { title: 'Sınıf Sinyalleri', span: 6, order: 80 },
        notes: { title: 'Öğretmen Notları', span: 6, order: 90 },
        leaderboard: { title: 'Başarı Listesi', span: 6, order: 100 },
    };

    const state = {};
    Object.entries(defs).forEach(([key, def]) => {
        const saved = initialLayout[key] || {};
        state[key] = {
            visible: saved.visible !== false,
            span: Number(saved.span || def.span),
            order: Number(saved.order || def.order),
        };
    });

    let editMode = false;
    let dirty = false;
    let dragKey = null;
    let resizeTarget = null;
    let startX = 0;
    let startWidth = 0;

    const clampSpan = (value) => Math.max(1, Math.min(12, Number(value || 4)));
    const notify = (message) => {
        if (window.appToast) window.appToast('info', message);
    };

    const setEditMode = (enabled) => {
        editMode = !!enabled;
        shell.classList.toggle('is-editing', editMode);
        libraryPanel?.classList.toggle('open', editMode);
        libraryPanel?.classList.toggle('collapsed', false);
        libraryPanel?.classList.toggle('fab-only', false);
        if (saveBtn) saveBtn.hidden = !editMode;
        if (closeBtn) closeBtn.hidden = !editMode;
        if (libraryPanel) libraryPanel.setAttribute('aria-hidden', editMode ? 'false' : 'true');
        render();
        notify(editMode ? 'Düzenleme açık' : 'Düzenleme kapatıldı');
    };

    const getMaxOrder = () => Math.max(...Object.values(state).map((item) => Number(item.order || 0)), 0);

    const renderLibrary = () => {
        if (!library) return;
        const hidden = Object.entries(state).filter(([, conf]) => !conf.visible);
        library.innerHTML = hidden.length
            ? hidden.map(([key]) => `<button type="button" class="widget-library-item" data-library-add="${key}"><strong>${defs[key].title}</strong><span>Geri ekle</span></button>`).join('')
            : '<p class="widget-library-empty">Gizli widget yok.</p>';
    };

    const allWidgetNodes = () => Array.from(shell.querySelectorAll('.dashboard-widget'));

    const render = () => {
        if (!grid) return;
        const cards = Object.entries(state).sort((a, b) => a[1].order - b[1].order);
        cards.forEach(([key, conf]) => {
            const card = shell.querySelector(`[data-widget-key="${key}"]`);
            if (!card) return;
            card.style.display = conf.visible ? '' : 'none';
            card.classList.remove('widget-span-1','widget-span-2','widget-span-3','widget-span-4','widget-span-5','widget-span-6','widget-span-7','widget-span-8','widget-span-9','widget-span-10','widget-span-11','widget-span-12');
            card.classList.add(`widget-span-${clampSpan(conf.span)}`);
            card.draggable = false;
            card.querySelectorAll('.widget-toggle').forEach((btn) => {
                btn.disabled = !editMode;
                btn.textContent = editMode ? '-' : '';
            });
            const handle = card.querySelector('.widget-resize-handle');
            if (handle) handle.style.display = editMode ? 'block' : 'none';
        });
        cards.filter(([, conf]) => conf.visible).forEach(([key]) => {
            const card = shell.querySelector(`[data-widget-key="${key}"]`);
            if (card) grid.appendChild(card);
        });
        if (window.innerWidth <= 640) {
            const qrCard = shell.querySelector('[data-widget-key="quick_qr"]');
            if (qrCard) grid.prepend(qrCard);
        }
        renderLibrary();
    };

    const payload = () => {
        const data = {};
        Object.entries(state).forEach(([key, conf], index) => {
            data[key] = {
                visible: !!conf.visible,
                span: clampSpan(conf.span),
                order: Number(conf.order || (index + 1) * 10),
            };
        });
        return data;
    };

    let dragSource = null;
    let dragPlaceholder = null;
    const clearDragState = () => {
        dragSource?.classList.remove('is-dragging');
        dragSource = null;
        dragPlaceholder?.remove();
        dragPlaceholder = null;
        dragKey = null;
    };
    const getVisibleOrder = () => allWidgetNodes().filter((el) => el.style.display !== 'none');
    const syncOrdersFromDom = () => {
        getVisibleOrder().forEach((card, index) => {
            const key = card.dataset.widgetKey;
            if (state[key]) state[key].order = (index + 1) * 10;
        });
        dirty = true;
    };
    const moveDragPlaceholder = (target, clientX) => {
        if (!dragPlaceholder || !grid || !dragSource || !target || target === dragSource) return;
        const rect = target.getBoundingClientRect();
        const before = clientX < rect.left + rect.width / 2;
        grid.insertBefore(dragPlaceholder, before ? target : target.nextSibling);
    };
    shell.addEventListener('pointerdown', (e) => {
        if (!editMode) return;
        const widget = e.target.closest('.dashboard-widget');
        if (!widget || widget.style.display === 'none') return;
        if (e.target.closest('.widget-toggle') || e.target.closest('.widget-resize-handle')) return;
        dragSource = widget;
        dragKey = widget.dataset.widgetKey;
        widget.classList.add('is-dragging');
        dragPlaceholder = document.createElement('div');
        dragPlaceholder.className = 'dashboard-widget widget-drag-placeholder';
        dragPlaceholder.style.gridColumn = `span ${clampSpan(state[dragKey]?.span || 3)}`;
        dragPlaceholder.style.minHeight = `${Math.max(140, widget.getBoundingClientRect().height)}px`;
        widget.parentNode.insertBefore(dragPlaceholder, widget.nextSibling);
        widget.setPointerCapture?.(e.pointerId);
    });
    shell.addEventListener('pointermove', (e) => {
        if (!editMode || !dragSource) return;
        const target = document.elementFromPoint(e.clientX, e.clientY)?.closest('.dashboard-widget');
        if (!target || target === dragSource || target.classList.contains('widget-drag-placeholder')) return;
        moveDragPlaceholder(target, e.clientX);
    });
    shell.addEventListener('pointerup', () => {
        if (!editMode || !dragSource) return;
        if (dragPlaceholder && dragSource.parentNode) {
            dragPlaceholder.parentNode?.insertBefore(dragSource, dragPlaceholder);
        }
        syncOrdersFromDom();
        clearDragState();
        render();
    });
    shell.addEventListener('pointercancel', () => {
        clearDragState();
        render();
    });

    shell.addEventListener('click', (e) => {
        if (!editMode) return;
        const toggle = e.target.closest('[data-widget-toggle]');
        if (toggle) {
            const key = toggle.dataset.widgetToggle;
            if (state[key]) {
                state[key].visible = !state[key].visible;
                dirty = true;
                render();
            }
        }
    });

    library?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-library-add]');
        if (!btn || !editMode) return;
        const key = btn.dataset.libraryAdd;
        state[key].visible = true;
        state[key].order = getMaxOrder() + 10;
        dirty = true;
        render();
    });

    const save = async () => {
        if (!dirty) {
            notify('Değişiklik yok');
            return;
        }
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ layout: payload() }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.ok) {
            notify(data.message || 'Kaydedilemedi');
            return;
        }
        dirty = false;
        notify('Widget düzeni kaydedildi');
        setEditMode(false);
    };

    saveBtn?.addEventListener('click', save);
    closeBtn?.addEventListener('click', () => setEditMode(false));
    document.querySelector('[data-open-widget-editor]')?.addEventListener('click', () => setEditMode(true));
    libraryClose?.addEventListener('click', () => setEditMode(false));
    libraryMinimize?.addEventListener('click', () => {
        libraryPanel?.classList.add('fab-only');
    });
    libraryFab?.addEventListener('click', () => {
        libraryPanel?.classList.remove('fab-only');
        libraryPanel?.classList.add('open');
        libraryPanel?.setAttribute('aria-hidden', 'false');
    });

    const startResize = (e) => {
        if (!editMode) return;
        const handle = e.target.closest('.widget-resize-handle');
        if (!handle) return;
        resizeTarget = e.target.closest('.dashboard-widget');
        if (!resizeTarget) return;
        startX = e.clientX;
        startWidth = resizeTarget.getBoundingClientRect().width;
        e.preventDefault();
    };
    const moveResize = (e) => {
        if (!editMode || !resizeTarget || !grid) return;
        const delta = e.clientX - startX;
        const width = Math.max(260, startWidth + delta);
        const gridWidth = grid.getBoundingClientRect().width;
        const span = Math.max(1, Math.min(12, Math.round((width / gridWidth) * 12)));
        state[resizeTarget.dataset.widgetKey].span = span;
        dirty = true;
        render();
    };
    const endResize = () => { resizeTarget = null; };
    shell.addEventListener('mousedown', startResize);
    window.addEventListener('mousemove', moveResize);
    window.addEventListener('mouseup', endResize);

    window.addEventListener('resize', () => render());

    render();
})();
</script>
@endpush
@endsection






