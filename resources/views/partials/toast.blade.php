@php
    $initialToasts = [];

    $pushToast = function (string $type, $value) use (&$initialToasts): void {
        if (is_array($value)) {
            foreach ($value as $msg) {
                $text = trim((string) $msg);
                if ($text !== '') {
                    $initialToasts[] = ['type' => $type, 'message' => $text];
                }
            }
            return;
        }

        $text = trim((string) $value);
        if ($text !== '') {
            $initialToasts[] = ['type' => $type, 'message' => $text];
        }
    };

    $pushToast('success', session('success'));
    $pushToast('error', session('error'));
    $pushToast('warning', session('warning'));
    $pushToast('success', session('ok'));
    if (isset($errors) && $errors->any()) {
        $pushToast('error', $errors->all());
    }
@endphp

<section id="app-toast-root" class="app-toast-root" aria-live="polite" aria-atomic="false"></section>

<style>
    .app-toast-root {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 1000002;
        width: min(92vw, 28rem);
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .app-toast {
        position: relative;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid transparent;
        padding: 12px 42px 12px 14px;
        color: #fff;
        backdrop-filter: blur(8px);
        pointer-events: auto;
        transform: translateX(12px) translateY(-8px) scale(.98);
        opacity: 0;
        transition: opacity .22s ease, transform .22s ease;
    }
    .app-toast.show {
        opacity: 1;
        transform: translateX(0) translateY(0) scale(1);
    }
    .app-toast.hide {
        opacity: 0;
        transform: translateX(8px) translateY(-6px) scale(.98);
    }
    .app-toast::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        border-radius: 10px 0 0 10px;
    }
    .app-toast.success {
        border-color: rgba(110, 231, 183, .75);
        background: rgba(16, 185, 129, .16);
        box-shadow: 0 12px 30px rgba(16, 185, 129, .16), 0 0 14px rgba(110, 231, 183, .20);
    }
    .app-toast.success::before { background: rgba(209, 250, 229, .96); }
    .app-toast.error {
        border-color: rgba(252, 165, 165, .76);
        background: rgba(239, 68, 68, .16);
        box-shadow: 0 12px 30px rgba(239, 68, 68, .15), 0 0 14px rgba(252, 165, 165, .20);
    }
    .app-toast.error::before { background: rgba(254, 202, 202, .96); }
    .app-toast.warning {
        border-color: rgba(253, 224, 71, .8);
        background: rgba(245, 158, 11, .18);
        box-shadow: 0 12px 30px rgba(245, 158, 11, .16), 0 0 14px rgba(253, 224, 71, .22);
    }
    .app-toast.warning::before { background: rgba(254, 240, 138, .96); }
    .app-toast-message {
        margin: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.35;
        letter-spacing: .01em;
        color: rgba(255,255,255,.98);
    }
    .app-toast-repeat {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255,255,255,.78);
    }
    .app-toast-close {
        position: absolute;
        right: 8px;
        top: 8px;
        height: 28px;
        width: 28px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 9999px;
        background: rgba(255,255,255,.1);
        color: rgba(255,255,255,.92);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }
    .app-toast-close:hover { background: rgba(255,255,255,.22); }
    .app-toast-close:focus-visible { outline: 2px solid rgba(255,255,255,.72); outline-offset: 1px; }
</style>

<script>
(() => {
    const initial = @js($initialToasts);
    const root = document.getElementById('app-toast-root');
    if (!root) return;

    const state = {
        seed: 0,
        dedupeMs: 2200,
        active: [],
    };

    const normType = (type) => {
        const t = String(type || 'success').toLowerCase();
        return ['success', 'error', 'warning'].includes(t) ? t : 'success';
    };
    const normDuration = (raw) => {
        const n = Number(raw || 4200);
        if (!Number.isFinite(n)) return 4200;
        return Math.max(3000, Math.min(5000, n));
    };

    const removeToast = (id) => {
        const idx = state.active.findIndex((t) => t.id === id);
        if (idx < 0) return;
        const t = state.active[idx];
        if (t.closing) return;
        t.closing = true;
        if (t.timer) {
            window.clearTimeout(t.timer);
            t.timer = null;
        }
        t.el.classList.remove('show');
        t.el.classList.add('hide');
        window.setTimeout(() => {
            t.el.remove();
            state.active = state.active.filter((x) => x.id !== id);
        }, 240);
    };

    const renderToast = (toast) => {
        const article = document.createElement('article');
        article.className = `app-toast ${toast.type}`;
        article.setAttribute('role', toast.type === 'error' ? 'alert' : 'status');
        article.setAttribute('tabindex', '0');
        article.innerHTML = `
            <p class="app-toast-message"></p>
            <p class="app-toast-repeat" style="display:none;"></p>
            <button type="button" class="app-toast-close" aria-label="Bildirimi kapat">&times;</button>
        `;
        article.querySelector('.app-toast-message').textContent = toast.message;
        const repeat = article.querySelector('.app-toast-repeat');
        const closeBtn = article.querySelector('.app-toast-close');

        closeBtn.addEventListener('click', () => removeToast(toast.id));
        article.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                removeToast(toast.id);
            }
        });

        root.appendChild(article);
        requestAnimationFrame(() => article.classList.add('show'));

        toast.el = article;
        toast.repeatEl = repeat;
    };

    const push = (type, message, options = {}) => {
        const msg = String(message || '').trim();
        if (!msg) return;
        const safeType = normType(type);
        const now = Date.now();
        const existing = state.active.find((t) => !t.closing && t.type === safeType && t.message === msg && (now - t.createdAt) <= state.dedupeMs);
        const duration = normDuration(options.duration);
        const sticky = options && options.sticky === true;

        if (existing) {
            existing.count += 1;
            existing.createdAt = now;
            if (existing.repeatEl) {
                existing.repeatEl.style.display = '';
                existing.repeatEl.textContent = `Tekrar: ${existing.count}`;
            }
            if (existing.timer) window.clearTimeout(existing.timer);
            if (!sticky) {
                existing.timer = window.setTimeout(() => removeToast(existing.id), duration);
            } else {
                existing.timer = null;
            }
            return existing.id;
        }

        const toast = {
            id: `toast_${++state.seed}_${now}`,
            type: safeType,
            message: msg,
            count: 1,
            createdAt: now,
            closing: false,
            timer: null,
            el: null,
            repeatEl: null,
        };
        state.active.push(toast);
        renderToast(toast);
        if (!sticky) {
            toast.timer = window.setTimeout(() => removeToast(toast.id), duration);
        }
        return toast.id;
    };

    window.appToast = (type, message, options = {}) => push(type, message, options);
    window.appToastDismiss = (id) => removeToast(id);
    window.addEventListener('app:toast', (event) => {
        const d = event?.detail || {};
        push(d.type, d.message, d.options || {});
    });

    (Array.isArray(initial) ? initial : []).forEach((t) => push(t.type, t.message));
})();
</script>
