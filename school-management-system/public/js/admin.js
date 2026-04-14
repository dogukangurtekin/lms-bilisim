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
    alert: (message, title = 'Bilgilendirme') => enqueue({ type: 'alert', message, title, okText: 'Tamam' }),
    confirm: (message, title = 'Onay') => enqueue({ type: 'confirm', message, title, okText: 'Evet', cancelText: 'Vazgec' }),
    prompt: (message, defaultValue = '', title = 'Girdi') => enqueue({ type: 'prompt', message, defaultValue, title, okText: 'Kaydet', cancelText: 'Vazgec' }),
  };
}

window.AppDialog = window.AppDialog || createAppDialog();
window.alert = (message) => window.AppDialog.alert(String(message ?? ''));
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
    const ping = async () => {
      if (!pingUrl || !csrf) return;
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
        if (!res.ok) return;
        const payload = await res.json();
        if (payload && typeof payload.total_seconds !== 'undefined') {
          syncBase(payload.total_seconds);
        }
      } catch (_) {
        // Sessiz gec: sayac yerelde akmaya devam eder.
      }
    };
    const pingOnClose = () => {
      if (!pingUrl || !csrf || !navigator.sendBeacon) return;
      const data = new FormData();
      data.append('_token', csrf);
      navigator.sendBeacon(pingUrl, data);
    };
    render();
    setInterval(render, 1000);
    setInterval(ping, 30000);
    window.addEventListener('beforeunload', pingOnClose);
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'hidden') {
        pingOnClose();
      }
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
