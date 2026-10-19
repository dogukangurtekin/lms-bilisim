(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var ed = $('#editor'), page = $('#page'), stage = $('#stage');
  var KEY = 'bk_word_doc_v1';
  var zoom = 1, savedRange = null, dirtyTimer = null;

  function exec(cmd, val) { ed.focus(); document.execCommand(cmd, false, val == null ? null : val); updateUI(); markDirty(); }

  /* ---------- sekmeler ---------- */
  $$('#ribbonTabs button').forEach(function (b) {
    b.addEventListener('click', function () {
      $$('#ribbonTabs button').forEach(function (x) { x.classList.toggle('active', x === b); });
      $$('.panel').forEach(function (p) { p.classList.toggle('active', p.dataset.panel === b.dataset.tab); });
    });
  });

  /* ---------- seçim koruma ---------- */
  document.addEventListener('selectionchange', function () {
    var s = getSelection();
    if (s.rangeCount && ed.contains(s.anchorNode)) { savedRange = s.getRangeAt(0).cloneRange(); updateUI(); }
  });
  function restore() {
    ed.focus();
    if (savedRange) { var s = getSelection(); s.removeAllRanges(); s.addRange(savedRange); }
  }
  // Ribbon düğmeleri odağı çalmasın
  $('#ribbon').addEventListener('mousedown', function (e) {
    if (e.target.closest('button') ) e.preventDefault();
  });

  /* ---------- komutlar ---------- */
  $$('[data-cmd]').forEach(function (b) {
    b.addEventListener('click', function () { restore(); exec(b.dataset.cmd); });
  });
  $$('[data-block]').forEach(function (b) {
    b.addEventListener('click', function () { restore(); exec('formatBlock', b.dataset.block === 'P' ? 'p' : b.dataset.block.toLowerCase()); });
  });

  /* ---------- yazı tipi / boyutu ---------- */
  function applyStyle(styleFn) {
    restore();
    exec('styleWithCSS', true);
    return styleFn();
  }
  function wrapSelection(css) {
    var s = getSelection();
    if (!s.rangeCount) return;
    var r = s.getRangeAt(0);
    if (r.collapsed) return;
    var span = document.createElement('span');
    Object.keys(css).forEach(function (k) { span.style[k] = css[k]; });
    span.appendChild(r.extractContents());
    r.insertNode(span);
    // iç içe aynı özelliği taşıyan span'ları temizle
    $$('span', span).forEach(function (c) { Object.keys(css).forEach(function (k) { c.style[k] = ''; }); });
    s.removeAllRanges(); var nr = document.createRange(); nr.selectNodeContents(span); s.addRange(nr);
    savedRange = nr.cloneRange(); markDirty();
  }
  $('#fontName').addEventListener('change', function () { restore(); wrapSelection({ fontFamily: this.value }); ed.focus(); });
  $('#fontSize').addEventListener('change', function () { restore(); wrapSelection({ fontSize: this.value + 'pt' }); ed.focus(); });
  function stepSize(d) {
    var sel = $('#fontSize'), i = sel.selectedIndex + d;
    if (i < 0 || i >= sel.options.length) return;
    sel.selectedIndex = i; restore(); wrapSelection({ fontSize: sel.value + 'pt' });
  }
  $('#btnGrow').addEventListener('click', function () { stepSize(1); });
  $('#btnShrink').addEventListener('click', function () { stepSize(-1); });
  $('#fontColor').addEventListener('input', function () { this.parentNode.style.setProperty('--c', this.value); restore(); exec('foreColor', this.value); });
  $('#hiColor').addEventListener('input', function () { this.parentNode.style.setProperty('--c', this.value); restore(); exec('hiliteColor', this.value); });
  $('#lineHeight').addEventListener('change', function () {
    restore();
    var v = this.value;
    blocksInSelection().forEach(function (b) { b.style.lineHeight = v; });
    markDirty();
  });
  function blocksInSelection() {
    var s = getSelection(); if (!s.rangeCount) return [];
    var r = s.getRangeAt(0), out = [];
    $$('p,h1,h2,h3,li,blockquote,pre,div', ed).forEach(function (b) {
      if (b === ed) return;
      if (r.intersectsNode(b) && !b.querySelector('p,h1,h2,h3,li,blockquote,pre,div')) out.push(b);
    });
    if (!out.length) { var n = r.startContainer; while (n && n.parentNode !== ed) n = n.parentNode; if (n && n.nodeType === 1) out.push(n); }
    return out;
  }

  /* ---------- durum (kalın vb. aktif) ---------- */
  function updateUI() {
    ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'insertUnorderedList', 'insertOrderedList',
     'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'].forEach(function (c) {
      var b = $('[data-cmd="' + c + '"]'); if (!b) return;
      var on = false; try { on = document.queryCommandState(c); } catch (e) {}
      b.classList.toggle('on', on);
    });
    var s = getSelection(), n = s.anchorNode;
    $('#tableCtl').classList.toggle('on', !!(n && ed.contains(n) && closest(n, 'td,th')));
    if (n && ed.contains(n)) {
      var el = n.nodeType === 1 ? n : n.parentNode, cs = getComputedStyle(el);
      var fam = cs.fontFamily.split(',')[0].replace(/["']/g, '').trim();
      var fs = Math.round(parseFloat(cs.fontSize) * 0.75);
      setSel($('#fontName'), fam); setSel($('#fontSize'), String(fs));
    }
    stats();
  }
  function setSel(sel, v) { for (var i = 0; i < sel.options.length; i++) if (sel.options[i].text.toLowerCase() === v.toLowerCase()) { sel.selectedIndex = i; return; } }
  function closest(n, sel) { var el = n.nodeType === 1 ? n : n.parentNode; return el && el.closest ? el.closest(sel) : null; }
  function stats() {
    var t = ed.innerText.replace(/​/g, '');
    var words = (t.trim().match(/\S+/g) || []).length;
    $('#stWords').textContent = words + ' kelime';
    $('#stChars').textContent = t.replace(/\n/g, '').length + ' karakter';
    var pageH = 297 * 3.7795 * (pageDims.h / 297);
    var pages = Math.max(1, Math.ceil((page.scrollHeight - 4) / (pageDims.h * 3.7795)));
    $('#stPages').textContent = pages + ' sayfa';
  }

  /* ---------- ekle ---------- */
  function insertHTML(h) { restore(); exec('insertHTML', h); }
  function modal(id, show) { $(id).hidden = !show; }

  $('#btnTable').addEventListener('click', function () { modal('#tblBox', true); });
  $('#tblCancel').addEventListener('click', function () { modal('#tblBox', false); });
  $('#tblOk').addEventListener('click', function () {
    var r = Math.min(50, Math.max(1, +$('#tblRows').value || 1)), c = Math.min(20, Math.max(1, +$('#tblCols').value || 1));
    var h = '<table style="width:100%"><tbody>';
    for (var i = 0; i < r; i++) { h += '<tr>'; for (var j = 0; j < c; j++) h += '<td><br></td>'; h += '</tr>'; }
    insertHTML(h + '</tbody></table><p><br></p>'); modal('#tblBox', false);
  });
  $$('[data-t]').forEach(function (b) {
    b.addEventListener('click', function () {
      var n = savedRange && savedRange.startContainer; var td = n && closest(n, 'td,th'); if (!td) return;
      var tr = td.parentNode, table = closest(td, 'table'), idx = Array.prototype.indexOf.call(tr.children, td);
      switch (b.dataset.t) {
        case 'addRow': var nr = tr.cloneNode(true); $$('td,th', nr).forEach(function (c) { c.innerHTML = '<br>'; }); tr.parentNode.insertBefore(nr, tr.nextSibling); break;
        case 'addCol': $$('tr', table).forEach(function (r) { var c = document.createElement('td'); c.innerHTML = '<br>'; r.insertBefore(c, r.children[idx + 1] || null); }); break;
        case 'delRow': if ($$('tr', table).length > 1) tr.remove(); else table.remove(); break;
        case 'delCol': if (tr.children.length > 1) $$('tr', table).forEach(function (r) { if (r.children[idx]) r.children[idx].remove(); }); else table.remove(); break;
        case 'delTable': table.remove(); break;
        case 'shade': var col = prompt('Hücre rengi (örn. #ffe699 veya yellow):', '#ffe699'); if (col) td.style.background = col; break;
      }
      markDirty(); updateUI();
    });
  });
  $('#btnImage').addEventListener('click', function () { $('#fileImage').click(); });
  $('#fileImage').addEventListener('change', function () {
    var f = this.files[0]; if (!f) return;
    var fr = new FileReader();
    fr.onload = function () { insertHTML('<img src="' + fr.result + '" alt="">'); };
    fr.readAsDataURL(f); this.value = '';
  });
  $('#btnLink').addEventListener('click', function () {
    var u = prompt('Bağlantı adresi (https://...):', 'https://');
    if (!u) return;
    if (!/^(https?:|mailto:)/i.test(u)) u = 'https://' + u;
    restore();
    if (getSelection().isCollapsed) insertHTML('<a href="' + u.replace(/"/g, '') + '" target="_blank" rel="noopener">' + u.replace(/</g, '') + '</a>');
    else exec('createLink', u);
  });
  $('#btnHr').addEventListener('click', function () { restore(); exec('insertHorizontalRule'); });
  $('#btnPageBreak').addEventListener('click', function () { insertHTML('<hr class="pb"><p><br></p>'); });
  $('#btnDate').addEventListener('click', function () { insertHTML(new Date().toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' })); });
  var syms = '© ® ™ § ¶ † ‡ • … – — « » € £ ¥ ° ± × ÷ ≠ ≈ ≤ ≥ ∞ √ ∑ π Ω µ α β γ δ θ λ ← → ↑ ↓ ↔ ✓ ✗ ★ ☆ ♥ ☺ ½ ¼ ¾ ² ³ ‰'.split(' ');
  $('#symGrid').innerHTML = syms.map(function (s) { return '<button>' + s + '</button>'; }).join('');
  $('#symGrid').addEventListener('click', function (e) { if (e.target.tagName === 'BUTTON') { insertHTML(e.target.textContent); } });
  $('#btnSymbol').addEventListener('click', function () { modal('#symBox', true); });
  $('#symClose').addEventListener('click', function () { modal('#symBox', false); });
  $('#btnPaste').addEventListener('click', function () {
    restore();
    if (navigator.clipboard && navigator.clipboard.readText) navigator.clipboard.readText().then(function (t) { exec('insertText', t); }).catch(function () { alert('Yapıştırmak için Ctrl+V kullanın.'); });
    else alert('Yapıştırmak için Ctrl+V kullanın.');
  });
  ed.addEventListener('click', function (e) {
    $$('img.sel', ed).forEach(function (i) { i.classList.remove('sel'); });
    if (e.target.tagName === 'IMG') e.target.classList.add('sel');
    var a = closest(e.target, 'a'); if (a && (e.ctrlKey || e.metaKey)) window.open(a.href, '_blank', 'noopener');
  });
  // düz metin yapıştırma yerine temizlenmiş HTML
  ed.addEventListener('paste', function (e) {
    var cd = e.clipboardData; if (!cd) return;
    var html = cd.getData('text/html');
    if (html) {
      e.preventDefault();
      var tmp = document.createElement('div'); tmp.innerHTML = html;
      $$('script,style,meta,link,iframe,object,embed', tmp).forEach(function (n) { n.remove(); });
      $$('*', tmp).forEach(function (n) { Array.prototype.slice.call(n.attributes).forEach(function (a) { if (/^on/i.test(a.name) || /^javascript:/i.test(a.value)) n.removeAttribute(a.name); }); });
      document.execCommand('insertHTML', false, tmp.innerHTML);
    }
  });

  /* ---------- bul / değiştir ---------- */
  $('#btnFind').addEventListener('click', function () { modal('#findBox', true); $('#findText').focus(); });
  $('#findClose').addEventListener('click', function () { modal('#findBox', false); });
  function findNext(back) {
    var q = $('#findText').value; if (!q) return false;
    var ok = window.find(q, false, !!back, true, false, false, false);
    $('#findInfo').textContent = ok ? '' : 'Bulunamadı';
    return ok;
  }
  $('#findNext').addEventListener('click', function () { findNext(); });
  $('#replOne').addEventListener('click', function () {
    var q = $('#findText').value, s = getSelection();
    if (q && s.toString().toLowerCase() === q.toLowerCase()) exec('insertText', $('#replText').value);
    findNext();
  });
  $('#replAll').addEventListener('click', function () {
    var q = $('#findText').value, r = $('#replText').value; if (!q) return;
    var n = 0, w = document.createTreeWalker(ed, NodeFilter.SHOW_TEXT), nodes = [];
    while (w.nextNode()) nodes.push(w.currentNode);
    var re = new RegExp(q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
    nodes.forEach(function (t) { var v = t.nodeValue.replace(re, function () { n++; return r; }); if (v !== t.nodeValue) t.nodeValue = v; });
    $('#findInfo').textContent = n + ' yer değiştirildi'; markDirty(); stats();
  });
  $('#findText').addEventListener('keydown', function (e) { if (e.key === 'Enter') findNext(e.shiftKey); });

  /* ---------- sayfa düzeni ---------- */
  var pageDims = { w: 210, h: 297 };
  function applyLayout() {
    var sz = $('#pageSize').value, o = $('#pageOrient').value;
    var d = sz === 'letter' ? { w: 215.9, h: 279.4 } : sz === 'a5' ? { w: 148, h: 210 } : { w: 210, h: 297 };
    if (o === 'l') d = { w: d.h, h: d.w };
    pageDims = d;
    page.style.width = d.w + 'mm'; page.style.minHeight = d.h + 'mm';
    var m = $('#pageMargin').value; page.style.setProperty('--m', m + 'mm');
    ed.style.minHeight = 'calc(' + d.h + 'mm - 2*' + m + 'mm)';
    stats(); markDirty();
  }
  ['#pageSize', '#pageOrient', '#pageMargin'].forEach(function (s) { $(s).addEventListener('change', applyLayout); });
  $('#chkLines').addEventListener('change', function () { document.body.classList.toggle('lines', this.checked); });
  $('#chkSpell').addEventListener('change', function () { ed.spellcheck = this.checked; ed.blur(); ed.focus(); });

  /* ---------- görünüm ---------- */
  function setZoom(z) {
    zoom = Math.min(2, Math.max(0.4, z));
    stage.style.transform = 'scale(' + zoom + ')';
    stage.style.marginBottom = (page.offsetHeight * (zoom - 1)) + 'px';
    $('#zoomVal').textContent = Math.round(zoom * 100) + '%';
  }
  $('#zoomIn').addEventListener('click', function () { setZoom(zoom + 0.1); });
  $('#zoomOut').addEventListener('click', function () { setZoom(zoom - 0.1); });
  $('#zoomFit').addEventListener('click', function () { setZoom(($('#workspace').clientWidth - 24) / page.offsetWidth); });
  $('#btnDark').addEventListener('click', function () { document.body.classList.toggle('dark'); try { localStorage.setItem('bk_word_dark', document.body.classList.contains('dark') ? '1' : ''); } catch (e) {} });
  try { if (localStorage.getItem('bk_word_dark')) document.body.classList.add('dark'); } catch (e) {}

  /* ---------- dosya işlemleri ---------- */
  function markDirty() {
    $('#saveState').textContent = 'Kaydediliyor…';
    clearTimeout(dirtyTimer); dirtyTimer = setTimeout(save, 700);
  }
  function save() {
    try {
      localStorage.setItem(KEY, JSON.stringify({ name: $('#docName').value, html: ed.innerHTML, size: $('#pageSize').value, orient: $('#pageOrient').value, margin: $('#pageMargin').value }));
      $('#saveState').textContent = 'Kaydedildi';
    } catch (e) { $('#saveState').textContent = 'Kaydedilemedi (alan dolu)'; }
  }
  function load() {
    try {
      var d = JSON.parse(localStorage.getItem(KEY) || 'null'); if (!d) return;
      $('#docName').value = d.name || 'Belge1'; ed.innerHTML = d.html || '<p><br></p>';
      if (d.size) $('#pageSize').value = d.size; if (d.orient) $('#pageOrient').value = d.orient; if (d.margin) $('#pageMargin').value = d.margin;
    } catch (e) {}
  }
  function fileName(ext) { return ($('#docName').value || 'Belge').replace(/[\\/:*?"<>|]+/g, '_') + ext; }
  function download(name, mime, content) {
    var a = document.createElement('a'), b = new Blob([content], { type: mime });
    a.href = URL.createObjectURL(b); a.download = name; document.body.appendChild(a); a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
  }
  function fullHtml() {
    var w = pageDims.w, h = pageDims.h, m = $('#pageMargin').value;
    return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + $('#docName').value.replace(/</g, '') + '</title>' +
      '<style>@page{size:' + w + 'mm ' + h + 'mm;margin:' + m + 'mm}body{font-family:Calibri,Arial,sans-serif;font-size:12pt;line-height:1.15}p{margin:0 0 8pt}' +
      'table{border-collapse:collapse}td,th{border:1px solid #888;padding:4px 8px}img{max-width:100%}blockquote{margin-left:24px;border-left:4px solid #9bb7e5;padding-left:12px;font-style:italic}' +
      'pre{background:#f1f3f6;padding:8px;white-space:pre-wrap}hr.pb{border:0;page-break-after:always}</style></head><body>' + ed.innerHTML + '</body></html>';
  }
  $('#btnSave').addEventListener('click', function () { save(); });
  $('#btnDoc').addEventListener('click', function () {
    var h = fullHtml().replace('<html>', '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">');
    download(fileName('.doc'), 'application/msword', '﻿' + h);
  });
  $('#btnHtml').addEventListener('click', function () { download(fileName('.html'), 'text/html', fullHtml()); });
  $('#btnPrint').addEventListener('click', function () { window.print(); });
  $('#btnNew').addEventListener('click', function () {
    if (!confirm('Yeni belge açılsın mı? Kaydedilmemiş değişiklikler silinir.')) return;
    ed.innerHTML = '<p><br></p>'; $('#docName').value = 'Belge1'; save(); ed.focus(); stats();
  });
  $('#btnOpen').addEventListener('click', function () { $('#fileOpen').click(); });
  $('#fileOpen').addEventListener('change', function () {
    var f = this.files[0]; if (!f) return;
    var fr = new FileReader();
    fr.onload = function () {
      var t = String(fr.result);
      if (/\.txt$/i.test(f.name)) ed.innerHTML = t.split(/\r?\n/).map(function (l) { return '<p>' + (l.replace(/&/g, '&amp;').replace(/</g, '&lt;') || '<br>') + '</p>'; }).join('');
      else {
        var doc = new DOMParser().parseFromString(t, 'text/html');
        $$('script,iframe,object,embed,link,meta', doc.body).forEach(function (n) { n.remove(); });
        $$('*', doc.body).forEach(function (n) { Array.prototype.slice.call(n.attributes).forEach(function (a) { if (/^on/i.test(a.name)) n.removeAttribute(a.name); }); });
        ed.innerHTML = doc.body.innerHTML || '<p><br></p>';
      }
      $('#docName').value = f.name.replace(/\.[^.]+$/, ''); save(); stats();
    };
    fr.readAsText(f); this.value = '';
  });

  /* ---------- olaylar ---------- */
  ed.addEventListener('input', function () { markDirty(); stats(); });
  $('#docName').addEventListener('input', markDirty);
  ed.addEventListener('keydown', function (e) {
    var k = e.key.toLowerCase();
    if ((e.ctrlKey || e.metaKey) && k === 's') { e.preventDefault(); save(); }
    else if ((e.ctrlKey || e.metaKey) && k === 'f') { e.preventDefault(); modal('#findBox', true); $('#findText').focus(); }
    else if ((e.ctrlKey || e.metaKey) && k === 'p') { e.preventDefault(); window.print(); }
    else if (e.key === 'Tab') {
      e.preventDefault();
      var td = getSelection().anchorNode && closest(getSelection().anchorNode, 'td,th');
      if (td) { var nx = e.shiftKey ? td.previousElementSibling : td.nextElementSibling; if (nx) { var r = document.createRange(); r.selectNodeContents(nx); r.collapse(true); var s = getSelection(); s.removeAllRanges(); s.addRange(r); } }
      else document.execCommand(e.shiftKey ? 'outdent' : 'indent');
    }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') $$('.modal').forEach(function (m) { m.hidden = true; }); });
  window.addEventListener('beforeunload', save);
  document.execCommand('defaultParagraphSeparator', false, 'p');
  document.execCommand('styleWithCSS', false, true);
  load(); applyLayout(); stats();
  if (window.innerWidth < 900) setTimeout(function () { $('#zoomFit').click(); }, 50);
})();
