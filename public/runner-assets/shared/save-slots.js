/* Excel / Word için ortak "Kayıtlarım" sistemi.
 *  - Kayıtlar kullanıcıya özeldir (aynı bilgisayarı kullanan başka öğrencinin çalışması görünmez).
 *  - Ödev olarak açıldığında (assignmentId) uygulama BOŞ başlar; önceden yazılmış/kaydedilmiş çalışma varsa sorulur.
 *  - Kaydedilmemiş değişiklik varken sayfadan çıkılırsa uyarı verilir.
 */
(function () {
  var qs = new URLSearchParams(location.search);
  var user = window.RUNNER_USER ? 'u' + window.RUNNER_USER : 'guest';
  var asg = String(qs.get('assignmentId') || '').trim();

  function read(k, d) { try { var v = JSON.parse(localStorage.getItem(k) || 'null'); return v == null ? d : v; } catch (e) { return d; } }
  function write(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); return true; } catch (e) { return false; } }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function when(ts) { try { return new Date(ts).toLocaleString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); } catch (e) { return ''; } }

  var css = document.createElement('style');
  css.textContent = [
    '.ss-btn{background:rgba(255,255,255,.12);color:#fff;border:1px solid transparent;border-radius:4px;padding:4px 8px;cursor:pointer;min-height:28px;font:inherit}',
    '.ss-btn:hover{background:rgba(255,255,255,.28)}',
    '.ss-modal{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:14px;background:rgba(15,23,42,.55)}',
    '.ss-card{background:#fff;color:#0f172a;border-radius:16px;width:min(560px,100%);max-height:88vh;overflow:auto;padding:18px;box-shadow:0 24px 60px rgba(0,0,0,.35);font:14px "Segoe UI",system-ui,sans-serif}',
    '.ss-card h3{margin:0 0 6px;font-size:18px}.ss-card p{margin:0 0 12px;color:#475569;line-height:1.5}',
    '.ss-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}',
    '.ss-act{border:0;border-radius:10px;padding:9px 14px;font:inherit;font-weight:700;cursor:pointer;background:#e2e8f0;color:#0f172a}',
    '.ss-act.primary{background:#16a34a;color:#fff}.ss-act.warn{background:#fee2e2;color:#991b1b}',
    '.ss-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 10px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px}',
    '.ss-row small{display:block;color:#64748b}',
    '.ss-toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%);background:#0f172a;color:#fff;padding:9px 16px;border-radius:99px;z-index:10000;font:700 13px system-ui}'
  ].join('');
  document.head.appendChild(css);

  var opts = null, snapshot = '', blocked = false;
  var api = window.SaveSlots = {
    user: user,
    assignment: asg,
    inAssignment: !!asg,
    // Uygulama otomatik kaydını (taslak) yazmadan önce bunu sormalı.
    get blocked() { return blocked; },
    draftKey: function (base) { return base + ':' + user + (asg ? ':' + asg : ''); }
  };

  function slotsKey() { return 'bk_saves:' + opts.app + ':' + user; }
  function slots() { return read(slotsKey(), []); }

  function toast(msg) {
    var t = document.createElement('div'); t.className = 'ss-toast'; t.textContent = msg; document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 2200);
  }
  function modal(html, bind) {
    var m = document.createElement('div'); m.className = 'ss-modal';
    m.innerHTML = '<div class="ss-card">' + html + '</div>'; document.body.appendChild(m);
    bind(m, function () { m.remove(); }); return m;
  }
  function isDirty() { try { return opts && opts.getState() !== snapshot; } catch (e) { return false; } }

  function saveNow(name) {
    var list = slots();
    var label = String(name || opts.currentName() || opts.title).slice(0, 80);
    var data = opts.getState();
    var existing = list.filter(function (s) { return s.name === label && s.asg === asg; })[0];
    if (existing) { existing.data = data; existing.at = Date.now(); }
    else list.unshift({ id: 's' + Date.now(), name: label, at: Date.now(), asg: asg, data: data });
    if (!write(slotsKey(), list.slice(0, 30))) { toast('Kaydedilemedi (tarayıcı alanı dolu)'); return false; }
    snapshot = data;
    toast('Kaydedildi ✓ — Kayıtlarım listesinde');
    return true;
  }

  function openList() {
    var list = slots();
    modal('<h3>📂 Kayıtlarım</h3><p>Bu bilgisayarda, hesabına ait kayıtlı çalışmalar.</p>' +
      (list.length ? list.map(function (s) {
        return '<div class="ss-row" data-id="' + esc(s.id) + '"><div><b>' + esc(s.name) + '</b><small>' + esc(when(s.at)) + (s.asg ? ' · ödev' : '') + '</small></div>' +
          '<div><button class="ss-act primary" data-open="' + esc(s.id) + '">Aç</button> <button class="ss-act warn" data-del="' + esc(s.id) + '">Sil</button></div></div>';
      }).join('') : '<p>Henüz kayıtlı çalışma yok. "💾 Kaydet" ile ekleyebilirsin.</p>') +
      '<div class="ss-actions"><button class="ss-act" data-close>Kapat</button></div>', function (m, close) {
      m.addEventListener('click', function (e) {
        var t = e.target;
        if (t === m || (t.hasAttribute && t.hasAttribute('data-close'))) return close();
        var oid = t.getAttribute && t.getAttribute('data-open'), did = t.getAttribute && t.getAttribute('data-del');
        if (oid) {
          var s = slots().filter(function (x) { return x.id === oid; })[0]; if (!s) return;
          if (isDirty() && !confirm('Şu anki kaydedilmemiş çalışma kaybolur. "' + s.name + '" açılsın mı?')) return;
          opts.setState(s.data); snapshot = opts.getState(); close(); toast('"' + s.name + '" açıldı');
        } else if (did) {
          if (!confirm('Bu kayıt silinsin mi?')) return;
          write(slotsKey(), slots().filter(function (x) { return x.id !== did; })); close(); openList();
        }
      });
    });
  }

  function promptResume(draft) {
    var info = draft.at ? ' (' + esc(when(draft.at)) + ')' : '';
    modal('<h3>Önceki çalışman bulundu' + info + '</h3><p>Bu ödev boş bir sayfayla açıldı. Daha önce yazdığın çalışmayla devam edebilir, onu <b>Kayıtlarım</b>\'a kaydedip boş başlayabilir ya da silebilirsin.</p>' +
      '<div class="ss-actions"><button class="ss-act primary" data-a="continue">Devam et</button>' +
      '<button class="ss-act" data-a="archive">Kaydet ve boş başla</button>' +
      '<button class="ss-act warn" data-a="discard">Sil ve boş başla</button></div>', function (m, close) {
      m.addEventListener('click', function (e) {
        var a = e.target.getAttribute && e.target.getAttribute('data-a'); if (!a) return;
        if (a === 'continue') {
          opts.setState(draft.data);
        } else {
          if (a === 'archive') {
            var list = slots();
            list.unshift({ id: 's' + Date.now(), name: (draft.name || opts.title) + ' (önceki)', at: draft.at || Date.now(), asg: asg, data: draft.data });
            write(slotsKey(), list.slice(0, 30));
            toast('Önceki çalışma Kayıtlarım\'a kaydedildi');
          }
          try { localStorage.removeItem(opts.draftStorageKey); } catch (x) {}
        }
        blocked = false; snapshot = opts.getState(); close();
      });
    });
  }

  /* opts: { app, title, titleSelector(.tb-actions), getState(): string, setState(string), currentName(): string,
   *         draftStorageKey: string, readDraft(): {data,name,at}|null, isBlank(): bool } */
  api.mount = function (o) {
    opts = o;
    api.save = function () { return saveNow(); };
    var bar = document.querySelector('.tb-actions');
    if (bar) {
      var save = document.createElement('button'); save.className = 'ss-btn'; save.textContent = '💾 Kaydet'; save.title = 'Çalışmayı Kayıtlarım\'a kaydet (Ctrl+S)';
      save.addEventListener('click', function () { saveNow(); });
      if (o.noSaveButton) save.style.display = 'none';
      var list = document.createElement('button'); list.className = 'ss-btn'; list.textContent = '📂 Kayıtlarım'; list.title = 'Kayıtlı çalışmalarım';
      list.addEventListener('click', openList);
      bar.insertBefore(list, bar.firstChild); bar.insertBefore(save, bar.firstChild);
    }
    document.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) { e.preventDefault(); saveNow(); }
    }, true);
    window.addEventListener('beforeunload', function (e) {
      if (asg && isDirty()) { e.preventDefault(); e.returnValue = 'Kaydedilmemiş değişikliklerin var. Çıkmadan önce "Kaydet"e bas.'; return e.returnValue; }
    });
    snapshot = o.getState();
    // Ödev modunda uygulama boş açılır; önceki taslak varsa sor.
    var draft = asg ? o.readDraft() : null;
    if (draft && draft.data && !(o.isBlankData && o.isBlankData(draft.data))) { blocked = true; promptResume(draft); }
  };
})();
