function createAppDialog() {
  let root = null;
  let queue = Promise.resolve();

  const ensureRoot = () => {
    if (root) return root;
    root = document.createElement('div');
    root.id = 'app-dialog-root';
    root.innerHTML = `
      <div id="app-dialog-overlay" style="position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px;">
        <div style="width:min(460px,96vw);background:#fff;border:1px solid #dbe3ef;border-radius:14px;box-shadow:0 24px 56px rgba(2,6,23,.35);padding:16px;">
          <h3 id="app-dialog-title" style="margin:0 0 8px 0;font-size:18px;color:#0f172a;">Bilgilendirme</h3>
          <p id="app-dialog-message" style="margin:0 0 12px 0;color:#334155;white-space:pre-wrap;"></p>
          <input id="app-dialog-input" type="text" style="display:none;width:100%;margin:0 0 12px 0;padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
          <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button id="app-dialog-cancel" type="button" class="btn btn-danger" style="display:none">Vazgec</button>
            <button id="app-dialog-ok" type="button" class="btn">Tamam</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(root);
    return root;
  };

  const open = (opts) => {
    const host = ensureRoot();
    const overlay = host.querySelector('#app-dialog-overlay');
    const title = host.querySelector('#app-dialog-title');
    const message = host.querySelector('#app-dialog-message');
    const input = host.querySelector('#app-dialog-input');
    const ok = host.querySelector('#app-dialog-ok');
    const cancel = host.querySelector('#app-dialog-cancel');

    title.textContent = opts.title || 'Bilgilendirme';
    message.textContent = opts.message || '';
    ok.textContent = opts.okText || 'Tamam';
    cancel.textContent = opts.cancelText || 'Vazgec';

    input.style.display = opts.type === 'prompt' ? 'block' : 'none';
    input.value = opts.defaultValue || '';
    cancel.style.display = opts.type === 'confirm' || opts.type === 'prompt' ? 'inline-block' : 'none';

    return new Promise((resolve) => {
      const cleanup = () => {
        overlay.style.display = 'none';
        ok.removeEventListener('click', onOk);
        cancel.removeEventListener('click', onCancel);
        overlay.removeEventListener('click', onBackdrop);
      };
      const onOk = () => {
        cleanup();
        if (opts.type === 'confirm') resolve(true);
        else if (opts.type === 'prompt') resolve(input.value);
        else resolve(undefined);
      };
      const onCancel = () => {
        cleanup();
        if (opts.type === 'confirm') resolve(false);
        else if (opts.type === 'prompt') resolve(null);
        else resolve(undefined);
      };
      const onBackdrop = (e) => {
        if (e.target === overlay) onCancel();
      };

      ok.addEventListener('click', onOk);
      cancel.addEventListener('click', onCancel);
      overlay.addEventListener('click', onBackdrop);
      overlay.style.display = 'flex';
      if (opts.type === 'prompt') input.focus();
      else ok.focus();
    });
  };

  const enqueue = (opts) => {
    queue = queue.then(() => open(opts));
    return queue;
  };

  return {
    alert: (message, title = 'Bilgilendirme') => {
      const text = String(message ?? '').trim();
      if (!text) return Promise.resolve();
      if (typeof window.appToast === 'function') {
        window.appToast('warning', text);
        return Promise.resolve();
      }
      return enqueue({ type: 'alert', message: text, title, okText: 'Tamam' });
    },
    confirm: (message, title = 'Onay') => enqueue({ type: 'confirm', message, title, okText: 'Evet', cancelText: 'Vazgec' }),
    prompt: (message, defaultValue = '', title = 'Girdi') => enqueue({ type: 'prompt', message, defaultValue, title, okText: 'Kaydet', cancelText: 'Vazgec' }),
  };
}

window.AppDialog = window.AppDialog || createAppDialog();
window.alert = (message) => {
  const text = String(message ?? '').trim();
  if (!text) return;
  if (typeof window.appToast === 'function') {
    window.appToast('warning', text);
    return;
  }
  window.AppDialog.alert(text);
};
window.confirm = () => false;
window.prompt = () => null;

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-delete-form]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const ok = await window.AppDialog.confirm('Silmek istediginize emin misiniz?');
      if (ok) {
        document.getElementById(btn.dataset.deleteForm).submit();
      }
    });
  });

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = form.getAttribute('data-confirm') || 'Islemi onayliyor musunuz?';
      const ok = await window.AppDialog.confirm(msg);
      if (ok) form.submit();
    });
  });

  document.querySelectorAll('[data-open-modal]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.openModal);
      if (modal) modal.classList.add('open');
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.closest('.modal').classList.remove('open');
    });
  });

  const ajaxForms = document.querySelectorAll('form[data-ajax="true"]');
  ajaxForms.forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = new FormData(form);
      const token = document.querySelector('meta[name="csrf-token"]').content;
      const method = (form.dataset.method || form.method || 'POST').toUpperCase();
      if (method !== 'POST') data.append('_method', method);

      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: data,
      });

      if (response.ok) {
        location.reload();
      } else {
        const payload = await response.json().catch(() => ({}));
        await window.AppDialog.alert(payload.message || 'Islem basarisiz');
      }
    });
  });

  const bulkForm = document.getElementById('bulk-upload-form');
  if (bulkForm) {
    bulkForm.addEventListener('submit', () => {
      const overlay = document.getElementById('bulk-upload-overlay');
      const page = document.getElementById('students-page-content');
      const submit = document.getElementById('bulk-upload-submit');
      if (submit) {
        submit.disabled = true;
        submit.textContent = 'Yukleniyor...';
      }
      if (overlay) overlay.classList.add('open');
      if (page) page.classList.add('is-blurred');
    });
  }

  const studentTimerWrap = document.getElementById('student-sidebar-time');
  const studentLiveTime = document.getElementById('student-live-time');
  if (studentTimerWrap && studentLiveTime) {
    let startedAt = Date.now();
    let baseSeconds = Math.max(0, Number(studentTimerWrap.dataset.initialSeconds || 0));
    const pingUrl = studentTimerWrap.dataset.pingUrl || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const formatDuration = (totalSeconds) => {
      const t = Math.max(0, Number(totalSeconds || 0));
      const day = Math.floor(t / 86400);
      const hour = Math.floor((t % 86400) / 3600);
      const minute = Math.floor((t % 3600) / 60);
      const second = Math.floor(t % 60);
      return `${day}g ${hour}sa ${minute}dk ${second}sn`;
    };
    const render = () => {
      const elapsed = baseSeconds + Math.floor((Date.now() - startedAt) / 1000);
      studentLiveTime.textContent = formatDuration(elapsed);
    };
    const syncBase = (total) => {
      const next = Math.max(0, Number(total || 0));
      baseSeconds = next;
      startedAt = Date.now();
      render();
    };
    let lastPingAt = 0;
    let pingTimer = null;
    // Ping'ler sekmeler/sayfalar arasında ortak sınırlanır: okuldaki çok öğrenci sayfa değiştirirken
    // aynı IP'den ardışık istek patlaması oluşup sunucu/hosting koruması 403 vermesin.
    // (Geçen süre zaten her sayfa isteğinde sunucuda ayrıca sayılıyor.)
    const PING_KEY = 'student_ping_at', BLOCK_KEY = 'student_ping_block_until';
    const lsGet = (k) => { try { return Number(localStorage.getItem(k) || 0); } catch (_) { return 0; } };
    const lsSet = (k, v) => { try { localStorage.setItem(k, String(v)); } catch (_) {} };
    const canPing = (minGapMs) => Date.now() >= lsGet(BLOCK_KEY) && Date.now() - lsGet(PING_KEY) >= minGapMs;
    const ping = async () => {
      if (!pingUrl || !csrf) return;
      if (document.hidden) return;   // arka plandaki sekme sunucuyu yormasın
      if (!canPing(290000)) return;   // son ~5 dk içinde (herhangi bir sayfa/sekme) ping atıldıysa atlama
      lastPingAt = Date.now();
      lsSet(PING_KEY, lastPingAt);
      try {
        const res = await fetch(pingUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
        });
        if (res.status === 419 || res.status === 401) { clearInterval(pingTimer); return; }   // oturum/CSRF süresi dolmuş: aynı hatayı tekrar tekrar gönderme
        if (res.status === 403 || res.status === 429 || res.status === 503) {   // engel/limit: 15 dk boyunca hiç ping atma
          lsSet(BLOCK_KEY, Date.now() + 15 * 60 * 1000);
          return;
        }
        if (!res.ok) return;
        const payload = await res.json();
        if (payload && typeof payload.total_seconds !== 'undefined') {
          syncBase(payload.total_seconds);
        }
        // Tek ping herkese yeter: kodsuz yarışma bildirimi gibi bileşenler bu olayı dinler.
        window.dispatchEvent(new CustomEvent('student-ping', { detail: payload || {} }));
      } catch (_) {
        // Sessiz gec: sayac yerelde akmaya devam eder.
      }
    };
    render();
    setInterval(render, 1000);
    ping();
    pingTimer = setInterval(ping, 300000);
    // Sayaç tarayıcıda akar; sunucu süreyi her sayfa isteğinde zaten sayar. Ping yalnızca 5 dakikada bir
    // (sayfa açılışı/F5 ve sekmeye dönüş de bu sınıra tabi), kapanışta beacon yok.
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') ping();
    });
  }

  const globalMenuToggle = document.getElementById('global-menu-toggle');
  const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');
  const closeMobileSidebar = () => document.body.classList.remove('mobile-sidebar-open');
  if (globalMenuToggle) {
    const key = document.body.classList.contains('role-student')
      ? 'student_sidebar_hidden'
      : 'app_sidebar_hidden';
    if (window.innerWidth <= 960) {
      const saved = localStorage.getItem(key);
      if (saved === '1') document.body.classList.add('sidebar-hidden');
    } else {
      document.body.classList.remove('sidebar-hidden');
      localStorage.removeItem(key);
    }

    globalMenuToggle.addEventListener('click', () => {
      if (window.innerWidth <= 960) {
        document.body.classList.toggle('mobile-sidebar-open');
        return;
      }
      // Desktop: menu button intentionally disabled.
    });
  }
  // Yönetici/öğretmen: menüyü küçült / büyüt (masaüstü; tercih tarayıcıda saklanır)
  const miniBtn = document.getElementById('sidebar-mini-toggle');
  if (miniBtn) {
    const MINI_KEY = 'staff_sidebar_mini';
    document.querySelectorAll('.sidebar > a, .student-sidebar-nav > a').forEach((a) => { if (!a.title) a.title = a.textContent.trim().replace(/\s+/g, ' '); });
    const sync = () => { miniBtn.setAttribute('aria-pressed', document.body.classList.contains('sidebar-mini') ? 'true' : 'false'); };
    sync();
    miniBtn.addEventListener('click', () => {
      const on = document.body.classList.toggle('sidebar-mini');
      try { localStorage.setItem(MINI_KEY, on ? '1' : '0'); } catch (e) {}
      sync();
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth <= 960) document.body.classList.remove('sidebar-mini');
      else { try { if (localStorage.getItem(MINI_KEY) === '1') document.body.classList.add('sidebar-mini'); } catch (e) {} }
    });
  }
  if (mobileBackdrop) {
    mobileBackdrop.addEventListener('click', closeMobileSidebar);
  }
  window.addEventListener('resize', () => {
    if (window.innerWidth > 960) closeMobileSidebar();
  });
  document.querySelectorAll('.sidebar a').forEach((link) => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 960) closeMobileSidebar();
    });
  });
});
