@extends('layout.app')
@section('title','Bildirimler')
@section('content')
<div class="teacher-v2 teacher-v2-compact">
    <div class="teacher-v2-layout">
        <div class="teacher-v2-main">
            <section class="v2-hero card soft-surface soft-surface-blue">
                <div>
                    <h1>Bildirimler</h1>
                    <p>WhatsApp ve uygulama ici cihaz bildirimlerini bu sayfadan yonetin.</p>
                </div>
                <div class="v2-rates">
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>Kanal</span>
                            <strong>WhatsApp + Mesaj</strong>
                        </div>
                        <div class="v2-rate-bar"><i style="width: 100%"></i></div>
                    </div>
                    <div class="v2-rate-item">
                        <div class="v2-rate-head">
                            <span>Hedef</span>
                            <strong>PC / iOS / Android</strong>
                        </div>
                        <div class="v2-rate-bar"><i style="width: 100%"></i></div>
                    </div>
                </div>
            </section>

            <section class="card soft-surface soft-surface-lilac notification-tabs-card">
                <div class="notification-tabs" role="tablist" aria-label="Bildirim sekmeleri">
                    <button type="button" class="notification-tab-btn is-active" data-tab="whatsapp" role="tab" aria-selected="true">WhatsApp</button>
                    <button type="button" class="notification-tab-btn" data-tab="messages" role="tab" aria-selected="false">Mesajlar</button>
                </div>
            </section>

            <section class="card soft-surface soft-surface-blue parent-whatsapp-panel" data-tab-panel="whatsapp">
                <h2>Veli WhatsApp Bilgilendirme</h2>
                <p class="parent-wa-help">Mesaj icinde <code>{ogrenci}</code>, <code>{sinif}</code>, <code>{rapor_linki}</code> degiskenlerini kullanabilirsiniz.</p>
                <form id="parentWhatsappForm" class="parent-wa-form">
                    @csrf
                    <div class="parent-wa-row">
                        <label>Alici Turu</label>
                        <select class="form-control" name="recipient_mode" id="waRecipientMode">
                            <option value="parents">Sistemdeki Veliler (Ogrenciye bagli)</option>
                            <option value="manual">Excel/CSV veya elle numara</option>
                        </select>
                    </div>
                    <div class="parent-wa-row">
                        <label>Gonderici WhatsApp Numarasi (gorsel)</label>
                        <input type="text" class="form-control" name="send_phone_display" id="waSenderDisplay" placeholder="+90 5xx xxx xx xx">
                    </div>
                    <div class="parent-wa-row">
                        <label>Gonderici Phone Number ID (Cloud API)</label>
                        <input type="text" class="form-control" name="send_phone_number_id" id="waSenderNumberId" placeholder="varsayilan icin bos birak">
                    </div>
                    <div class="parent-wa-row" id="waClassRow">
                        <label>Sinif Filtresi</label>
                        <select class="form-control" name="school_class_id" id="waClassSelect">
                            <option value="">Tum siniflar</option>
                        </select>
                    </div>
                    <div class="parent-wa-row" id="waManualRow" style="display:none;">
                        <label>Numaralar (virgul/satir ile ayir)</label>
                        <textarea class="form-control" name="manual_numbers" id="waManualNumbers" rows="4" placeholder="905551112233, 905441112233"></textarea>
                        <div class="parent-wa-upload">
                            <input type="file" id="waCsvFile" accept=".csv,.txt,.xlsx,.xls">
                            <small>CSV/TXT/XLSX dosyasi yukleyebilirsiniz. Numaralar otomatik ayristirilir.</small>
                        </div>
                    </div>
                    <div class="parent-wa-row">
                        <label>Gonderim Tipi</label>
                        <select class="form-control" name="send_mode" id="waSendMode">
                            <option value="template_document">Cloud API Template + PDF Eki</option>
                            <option value="text">Duz Metin Mesaj</option>
                        </select>
                    </div>
                    <div id="waTemplateFields">
                        <div class="parent-wa-row">
                            <label>Template Adi</label>
                            <input type="text" class="form-control" name="template_name" id="waTemplateName" placeholder="ornek: veli_bilgilendirme">
                        </div>
                        <div class="parent-wa-row">
                            <label>Template Dil Kodu</label>
                            <input type="text" class="form-control" name="template_language" id="waTemplateLang" value="tr">
                        </div>
                        <div class="parent-wa-row">
                            <label class="parent-wa-checkbox">
                                <input type="checkbox" name="include_pdf_attachment" id="waIncludePdf" value="1" checked>
                                Ogrenci gelisim raporunu PDF eki olarak gonder
                            </label>
                        </div>
                        <div class="parent-wa-row">
                            <label>PDF Aciklama (caption)</label>
                            <input type="text" class="form-control" name="document_caption" id="waDocCaption" value="Ogrenci gelisim raporu">
                        </div>
                    </div>
                    <div class="parent-wa-row">
                        <label>Mesaj</label>
                        <textarea class="form-control" name="message" id="waMessage" rows="4" required>Merhaba, {ogrenci} icin haftalik gelisim raporu hazirlandi. {rapor_linki}</textarea>
                    </div>
                    <div class="parent-wa-row">
                        <label class="parent-wa-checkbox">
                            <input type="checkbox" name="include_report_link" id="waIncludeReport" value="1" checked>
                            Ogrenci gelisim raporu linkini ekle
                        </label>
                    </div>
                    <div class="parent-wa-actions">
                        <button class="btn" type="submit" id="waStartBtn">WhatsApp Gonderimi Baslat</button>
                    </div>
                </form>
                <div class="pdf-status" id="waStatusBox">Gonderim hazirlaniyor... %0</div>
                <div id="waManualLinksWrap" style="display:none;">
                    <h3>Manuel WhatsApp Linkleri</h3>
                    <div id="waManualLinks" class="parent-wa-links"></div>
                </div>
            </section>

            <section class="card soft-surface soft-surface-mint" data-tab-panel="messages" style="display:none;">
                <h2>Uygulama Ici Cihaz Bildirimi</h2>
                <p class="parent-wa-help">Buradan gonderdiginiz mesajlar, uygulamayi kullanan cihazlarda sistem bildirimi olarak gosterilir.</p>

                <form id="systemMessageForm" class="parent-wa-form">
                    @csrf
                    <div class="parent-wa-row">
                        <label>Hedef Kitle</label>
                        <select class="form-control" id="sysAudience" name="audience">
                            <option value="all">Tum Kullanicilar</option>
                            <option value="students">Sadece Ogrenciler</option>
                            <option value="teachers">Sadece Ogretmenler</option>
                        </select>
                    </div>
                    <div class="parent-wa-row">
                        <label>Baslik</label>
                        <input type="text" class="form-control" id="sysTitle" name="title" maxlength="190" placeholder="Ornek: Yeni Odev Yayinlandi" required>
                    </div>
                    <div class="parent-wa-row">
                        <label>Mesaj</label>
                        <textarea class="form-control" id="sysContent" name="content" rows="4" maxlength="4000" placeholder="Bildirim metnini yazin..." required></textarea>
                    </div>
                    <div class="parent-wa-actions">
                        <button class="btn" type="submit" id="sysSendBtn">Mesaji Cihazlara Gonder</button>
                    </div>
                </form>
                <div class="pdf-status" id="sysStatusBox">Hazir</div>

                <div class="notification-recent">
                    <h3>Son Mesajlar</h3>
                    <div id="systemRecentList" class="notification-recent-list">
                        @forelse($recentAnnouncements as $item)
                            <article class="notification-recent-item" data-id="{{ $item->id }}">
                                <header>
                                    <strong>{{ $item->title }}</strong>
                                    <span>{{ strtoupper($item->audience) }}</span>
                                </header>
                                <p>{{ $item->content }}</p>
                            </article>
                        @empty
                            <p id="systemRecentEmpty">Henuz mesaj gonderilmedi.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
(() => {
    const tabButtons = document.querySelectorAll('.notification-tab-btn');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');
    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            tabButtons.forEach((b) => {
                b.classList.toggle('is-active', b === btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });
            tabPanels.forEach((panel) => {
                panel.style.display = panel.getAttribute('data-tab-panel') === tab ? '' : 'none';
            });
        });
    });

    const sysForm = document.getElementById('systemMessageForm');
    const sysStatusBox = document.getElementById('sysStatusBox');
    const sysSendBtn = document.getElementById('sysSendBtn');
    const sysRecentList = document.getElementById('systemRecentList');
    const sysEmpty = document.getElementById('systemRecentEmpty');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setSysStatus = (text, ok = true) => {
        if (!sysStatusBox) return;
        sysStatusBox.textContent = text;
        sysStatusBox.classList.add('show');
        sysStatusBox.style.borderColor = ok ? '#10b981' : '#ef4444';
        sysStatusBox.style.background = ok ? '#ecfdf5' : '#fef2f2';
        sysStatusBox.style.color = ok ? '#065f46' : '#991b1b';
    };

    if (sysForm) {
        sysForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const title = document.getElementById('sysTitle')?.value?.trim() || '';
            const content = document.getElementById('sysContent')?.value?.trim() || '';
            const audience = document.getElementById('sysAudience')?.value || 'all';

            if (!title || !content) {
                setSysStatus('Baslik ve mesaj zorunludur.', false);
                return;
            }

            sysSendBtn.disabled = true;
            setSysStatus('Mesaj gonderiliyor...', true);

            try {
                const payload = new FormData();
                payload.append('title', title);
                payload.append('content', content);
                payload.append('audience', audience);

                const res = await fetch('{{ route('notifications.messages.store') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: payload,
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || 'Mesaj gonderilemedi.');
                }

                setSysStatus('Mesaj cihazlara dagitima alindi.', true);
                sysForm.reset();

                if (sysEmpty) sysEmpty.remove();
                const item = document.createElement('article');
                item.className = 'notification-recent-item';
                item.setAttribute('data-id', String(data.id || ''));
                item.innerHTML = `<header><strong>${title}</strong><span>${String(audience).toUpperCase()}</span></header><p>${content}</p>`;
                sysRecentList?.prepend(item);
            } catch (err) {
                setSysStatus('Mesaj gonderilirken hata olustu.', false);
            } finally {
                sysSendBtn.disabled = false;
            }
        });
    }

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
            setStatus(`WhatsApp gonderimi: %${data.percent} (${data.processed}/${data.total})`, true);
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
        setStatus('Gonderim hazirlaniyor... %0', true);

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
            setStatus(`Gonderim tamamlandi. Basarili: ${done.success}, Hatali: ${done.failed}`, true);

            if (Array.isArray(done.manual_links) && done.manual_links.length > 0) {
                manualLinksWrap.style.display = 'block';
                done.manual_links.forEach((link, i) => {
                    const a = document.createElement('a');
                    a.href = link;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = `WhatsApp Ac (${i + 1})`;
                    manualLinksEl.appendChild(a);
                });
            }
        } catch (err) {
            setStatus('Gonderim sirasinda hata olustu.', true);
        } finally {
            startBtn.disabled = false;
        }
    });
})();
</script>
@endpush

