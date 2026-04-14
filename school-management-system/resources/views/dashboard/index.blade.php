@extends('layout.app')
@section('title','Öğretmen Paneli')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>{{ $dashboard['headline_name'] }} | Öğretmen Paneli</h1>
                    <p>Öğrenci, sınıf ve ders verilerinin anlık özeti</p>
                </div>
                <div class="v2-rates">
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>Katılım</span>
                            <strong>%{{ $dashboard['summary']['participation'] }}</strong>
                        </div>
                        <div class="v2-rate-bar">
                            <i style="width: {{ max(0, min(100, (int) ($dashboard['summary']['participation'] ?? 0))) }}%"></i>
                        </div>
                    </div>
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>İlerleme</span>
                            <strong>%{{ $dashboard['summary']['progress'] }}</strong>
                        </div>
                        <div class="v2-rate-bar">
                            <i style="width: {{ max(0, min(100, (int) ($dashboard['summary']['progress'] ?? 0))) }}%"></i>
                        </div>
                    </div>
                </div>
            </section>

            <section class="v2-metrics">
                <article class="card soft-surface soft-surface-mint"><span>Toplam Öğrenci</span><strong>{{ $dashboard['summary']['total_students'] }}</strong></article>
                <article class="card soft-surface soft-surface-peach"><span>Aktif Öğrenci</span><strong>{{ $dashboard['summary']['active_students'] }}</strong></article>
                <article class="card soft-surface soft-surface-lilac"><span>Sınıf Sayısı</span><strong>{{ $dashboard['summary']['total_classes'] }}</strong></article>
                <article class="card soft-surface soft-surface-sky"><span>Ders Sayısı</span><strong>{{ $dashboard['summary']['total_courses'] }}</strong></article>
                <article class="card soft-surface soft-surface-yellow"><span>Ortalama Not</span><strong>%{{ $dashboard['summary']['avg_completion'] }}</strong></article>
                <article class="card soft-surface soft-surface-rose"><span>Toplam XP</span><strong>{{ $dashboard['summary']['total_xp'] }}</strong></article>
            </section>

            <div class="v2-grid">
                <section class="card soft-surface soft-surface-sky">
                    <h2>Sınıf Sinyalleri</h2>
                    <div class="signal-list">
                        <div><span>Destek Gereken Sınıf</span><strong>{{ $dashboard['signals']['support'] }}</strong></div>
                        <div><span>Motivasyon Lideri</span><strong>{{ $dashboard['signals']['xp_leader'] }}</strong></div>
                        <div><span>Günün Odağı</span><strong>{{ $dashboard['signals']['focus'] }}</strong></div>
                        <div><span>Durum</span><strong>{{ $dashboard['signals']['status'] }}</strong></div>
                    </div>
                </section>

                <section class="card soft-surface soft-surface-lilac">
                    <h2>Öğretmen Notları</h2>
                    <div class="note-list">
                        <article><span>Odak</span><p>{{ $dashboard['highlights']['focus_title'] }}: {{ $dashboard['highlights']['focus_desc'] }}</p></article>
                        <article><span>Güç</span><p>{{ $dashboard['highlights']['power_title'] }}: {{ $dashboard['highlights']['power_desc'] }}</p></article>
                        <article><span>Ritim</span><p>{{ $dashboard['highlights']['rhythm_title'] }}: {{ $dashboard['highlights']['rhythm_desc'] }}</p></article>
                    </div>
                </section>

                <section class="card soft-surface soft-surface-mint weekly-compact">
                    <h2>Haftalık Özet</h2>
                    <div class="weekly-v2">
                        <article><span>En Aktif</span><strong>{{ $dashboard['weekly']['most_active'] }}</strong><p>%{{ $dashboard['summary']['participation'] }} aktif</p></article>
                        <article><span>En İyi Tamamlama</span><strong>{{ $dashboard['weekly']['best_completion'] }}</strong><p>%{{ $dashboard['summary']['progress'] }} ort.</p></article>
                        <article><span>XP Lideri</span><strong>{{ $dashboard['weekly']['xp_leader'] }}</strong><p>{{ $dashboard['signals']['xp_per_student'] }} XP/öğrenci</p></article>
                        <article><span>Düşük Aktiflik</span><strong>{{ $dashboard['weekly']['low_activity'] }}</strong><p>{{ $dashboard['summary']['absent_today'] }} devamsız</p></article>
                    </div>
                </section>
            </div>

            <div class="teacher-bottom-grid">
                <aside class="card teacher-top10 soft-surface soft-surface-peach">
                    <h2>İlk 10 Öğrenci Başarı Listesi</h2>
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
                </aside>

                <section class="card soft-surface soft-surface-blue parent-whatsapp-panel">
                    <h2>Veli WhatsApp Bilgilendirme</h2>
                    <p class="parent-wa-help">Mesaj içinde <code>{ogrenci}</code>, <code>{sinif}</code>, <code>{rapor_linki}</code> değişkenlerini kullanabilirsiniz.</p>
                    <form id="parentWhatsappForm" class="parent-wa-form">
                        @csrf
                        <div class="parent-wa-row">
                            <label>Alıcı Türü</label>
                            <select class="form-control" name="recipient_mode" id="waRecipientMode">
                                <option value="parents">Sistemdeki Veliler (Öğrenciye bağlı)</option>
                                <option value="manual">Excel/CSV veya elle numara</option>
                            </select>
                        </div>
                        <div class="parent-wa-row">
                            <label>Gönderici WhatsApp Numarası (görsel)</label>
                            <input type="text" class="form-control" name="send_phone_display" id="waSenderDisplay" placeholder="+90 5xx xxx xx xx">
                        </div>
                        <div class="parent-wa-row">
                            <label>Gönderici Phone Number ID (Cloud API)</label>
                            <input type="text" class="form-control" name="send_phone_number_id" id="waSenderNumberId" placeholder="varsayılan için boş bırak">
                        </div>
                        <div class="parent-wa-row" id="waClassRow">
                            <label>Sınıf Filtresi</label>
                            <select class="form-control" name="school_class_id" id="waClassSelect">
                                <option value="">Tüm sınıflar</option>
                            </select>
                        </div>
                        <div class="parent-wa-row" id="waManualRow" style="display:none;">
                            <label>Numaralar (virgül/satır ile ayır)</label>
                            <textarea class="form-control" name="manual_numbers" id="waManualNumbers" rows="4" placeholder="905551112233, 905441112233"></textarea>
                            <div class="parent-wa-upload">
                                <input type="file" id="waCsvFile" accept=".csv,.txt,.xlsx,.xls">
                                <small>CSV/TXT/XLSX dosyasını yükleyebilirsiniz. Numaralar otomatik ayrıştırılır.</small>
                            </div>
                        </div>
                        <div class="parent-wa-row">
                            <label>Gönderim Tipi</label>
                            <select class="form-control" name="send_mode" id="waSendMode">
                                <option value="template_document">Cloud API Template + PDF Eki</option>
                                <option value="text">Düz Metin Mesaj</option>
                            </select>
                        </div>
                        <div id="waTemplateFields">
                            <div class="parent-wa-row">
                                <label>Template Adı</label>
                                <input type="text" class="form-control" name="template_name" id="waTemplateName" placeholder="ornek: veli_bilgilendirme">
                            </div>
                            <div class="parent-wa-row">
                                <label>Template Dil Kodu</label>
                                <input type="text" class="form-control" name="template_language" id="waTemplateLang" value="tr">
                            </div>
                            <div class="parent-wa-row">
                                <label class="parent-wa-checkbox">
                                    <input type="checkbox" name="include_pdf_attachment" id="waIncludePdf" value="1" checked>
                                    Öğrenci gelişim raporunu PDF eki olarak gönder
                                </label>
                            </div>
                            <div class="parent-wa-row">
                                <label>PDF Açıklama (caption)</label>
                                <input type="text" class="form-control" name="document_caption" id="waDocCaption" value="Öğrenci gelişim raporu">
                            </div>
                        </div>
                        <div class="parent-wa-row">
                            <label>Mesaj</label>
                            <textarea class="form-control" name="message" id="waMessage" rows="4" required>Merhaba, {ogrenci} için haftalık gelişim raporu hazırlandı. {rapor_linki}</textarea>
                        </div>
                        <div class="parent-wa-row">
                            <label class="parent-wa-checkbox">
                                <input type="checkbox" name="include_report_link" id="waIncludeReport" value="1" checked>
                                Öğrenci gelişim raporu linkini ekle
                            </label>
                        </div>
                        <div class="parent-wa-actions">
                            <button class="btn" type="submit" id="waStartBtn">WhatsApp Gönderimi Başlat</button>
                        </div>
                    </form>
                    <div class="pdf-status" id="waStatusBox">Gönderim hazırlanıyor... %0</div>
                    <div id="waManualLinksWrap" style="display:none;">
                        <h3>Manuel WhatsApp Linkleri</h3>
                        <div id="waManualLinks" class="parent-wa-links"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
(() => {
    const form = document.getElementById('parentWhatsappForm');
    if (!form) return;

    const recipientModeEl = document.getElementById('waRecipientMode');
    const classRow = document.getElementById('waClassRow');
    const classSelect = document.getElementById('waClassSelect');
    const manualRow = document.getElementById('waManualRow');
    const manualNumbersEl = document.getElementById('waManualNumbers');
    const csvFileEl = document.getElementById('waCsvFile');
    const includeReportEl = document.getElementById('waIncludeReport');
    const includePdfEl = document.getElementById('waIncludePdf');
    const sendModeEl = document.getElementById('waSendMode');
    const templateFieldsEl = document.getElementById('waTemplateFields');
    const templateNameEl = document.getElementById('waTemplateName');
    const templateLangEl = document.getElementById('waTemplateLang');
    const docCaptionEl = document.getElementById('waDocCaption');
    const senderDisplayEl = document.getElementById('waSenderDisplay');
    const senderNumberIdEl = document.getElementById('waSenderNumberId');
    const messageEl = document.getElementById('waMessage');
    const statusBox = document.getElementById('waStatusBox');
    const startBtn = document.getElementById('waStartBtn');
    const manualLinksWrap = document.getElementById('waManualLinksWrap');
    const manualLinksEl = document.getElementById('waManualLinks');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setStatus = (text, show = true) => {
        if (!statusBox) return;
        statusBox.textContent = text;
        statusBox.classList.toggle('show', show);
    };

    const modeChanged = () => {
        const isManual = recipientModeEl.value === 'manual';
        manualRow.style.display = isManual ? 'grid' : 'none';
        classRow.style.display = isManual ? 'none' : 'grid';
        includeReportEl.disabled = isManual;
        if (isManual) includeReportEl.checked = false;
    };
    recipientModeEl.addEventListener('change', modeChanged);
    modeChanged();

    const sendModeChanged = () => {
        const isTemplate = sendModeEl.value === 'template_document';
        templateFieldsEl.style.display = isTemplate ? 'grid' : 'none';
        if (!isTemplate) includePdfEl.checked = false;
    };
    sendModeEl.addEventListener('change', sendModeChanged);
    sendModeChanged();

    async function loadClasses() {
        try {
            const res = await fetch('{{ route('parent-whatsapp.classes') }}', { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            const classes = Array.isArray(data.classes) ? data.classes : [];
            for (const item of classes) {
                const op = document.createElement('option');
                op.value = item.id;
                op.textContent = `${item.name}/${item.section}`;
                classSelect.appendChild(op);
            }
        } catch (_) {}
    }
    loadClasses();

    csvFileEl?.addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const lower = file.name.toLowerCase();
        let normalized = '';
        if ((lower.endsWith('.xlsx') || lower.endsWith('.xls')) && window.XLSX) {
            const arrayBuffer = await file.arrayBuffer();
            const workbook = XLSX.read(arrayBuffer, { type: 'array' });
            const values = [];
            workbook.SheetNames.forEach((sheetName) => {
                const sheet = workbook.Sheets[sheetName];
                const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false });
                rows.forEach((row) => {
                    row.forEach((cell) => {
                        if (cell !== null && cell !== undefined) values.push(String(cell));
                    });
                });
            });
            normalized = values
                .join('\n')
                .split(/[\n,;\t ]+/)
                .map((v) => v.trim())
                .filter(Boolean)
                .join('\n');
        } else {
            const text = await file.text();
            normalized = text
                .replace(/\r/g, '\n')
                .split(/[\n,;\t ]+/)
                .map((v) => v.trim())
                .filter(Boolean)
                .join('\n');
        }
        manualNumbersEl.value = [manualNumbersEl.value, normalized].filter(Boolean).join('\n');
    });

    async function stepTask(taskId) {
        while (true) {
            const res = await fetch(`{{ url('/veli-bildirim/whatsapp/adim') }}/${taskId}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                }
            });
            if (!res.ok) throw new Error('step_failed');
            const data = await res.json();
            setStatus(`WhatsApp gönderimi: %${data.percent} (${data.processed}/${data.total})`, true);
            if (data.completed) return data;
            await new Promise((r) => setTimeout(r, 500));
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!messageEl.value.trim()) {
            if (window.AppDialog?.alert) await window.AppDialog.alert('Mesaj alani bos olamaz.');
            return;
        }

        manualLinksWrap.style.display = 'none';
        manualLinksEl.innerHTML = '';
        startBtn.disabled = true;
        setStatus('Gönderim hazırlanıyor... %0', true);

        try {
            const payload = new FormData();
            payload.append('recipient_mode', recipientModeEl.value);
            payload.append('school_class_id', classSelect.value || '');
            payload.append('manual_numbers', manualNumbersEl.value || '');
            payload.append('message', messageEl.value);
            payload.append('include_report_link', includeReportEl.checked ? '1' : '0');
            payload.append('send_mode', sendModeEl.value);
            payload.append('template_name', templateNameEl.value || '');
            payload.append('template_language', templateLangEl.value || 'tr');
            payload.append('include_pdf_attachment', includePdfEl.checked ? '1' : '0');
            payload.append('document_caption', docCaptionEl.value || '');
            payload.append('send_phone_display', senderDisplayEl.value || '');
            payload.append('send_phone_number_id', senderNumberIdEl.value || '');

            const start = await fetch('{{ route('parent-whatsapp.start') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: payload,
            });
            if (!start.ok) {
                const err = await start.json().catch(() => ({}));
                throw new Error(err.message || 'Gonderim baslatilamadi.');
            }
            const startData = await start.json();
            const done = await stepTask(startData.task_id);
            setStatus(`Gönderim tamamlandı. Başarılı: ${done.success}, Hatalı: ${done.failed}`, true);

            if (Array.isArray(done.manual_links) && done.manual_links.length > 0) {
                manualLinksWrap.style.display = 'block';
                done.manual_links.forEach((link, i) => {
                    const a = document.createElement('a');
                    a.href = link;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = `WhatsApp Aç (${i + 1})`;
                    manualLinksEl.appendChild(a);
                });
            }
        } catch (err) {
            setStatus('Gönderim sırasında hata oluştu.', true);
        } finally {
            startBtn.disabled = false;
        }
    });
})();
</script>
@endpush
