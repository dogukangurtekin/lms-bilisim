(function () {
    var libs = {};
    function loadScript(src) {
        return libs[src] || (libs[src] = new Promise(function (res, rej) {
            var el = document.createElement('script');
            el.src = src; el.onload = res; el.onerror = function () { rej(new Error('lib')); };
            document.head.appendChild(el);
        }));
    }
    function esc(t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function msg(box, t) { box.innerHTML = '<div class="sh-msg">' + t + '</div>'; }


    var MIME = { png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', gif: 'image/gif', bmp: 'image/bmp', webp: 'image/webp', svg: 'image/svg+xml' };
    function resolve(base, target) {
        if (target.charAt(0) === '/') return target.slice(1);
        var parts = base.split('/'); parts.pop();
        target.split('/').forEach(function (p) { if (p === '..') parts.pop(); else if (p && p !== '.') parts.push(p); });
        return parts.join('/');
    }
    function relsOf(zip, path) {
        var dir = path.substring(0, path.lastIndexOf('/'));
        var rp = dir + '/_rels/' + path.substring(path.lastIndexOf('/') + 1) + '.rels';
        var f = zip.file(rp);
        if (!f) return Promise.resolve({});
        return f.async('string').then(function (xml) {
            var doc = new DOMParser().parseFromString(xml, 'application/xml'), map = {};
            Array.prototype.forEach.call(doc.getElementsByTagName('Relationship'), function (r) {
                map[r.getAttribute('Id')] = resolve(path, r.getAttribute('Target'));
            });
            return map;
        });
    }
    // xlsx icindeki sayfa -> resim listesi (data URI)
    async function sheetImages(buf, sheetNames) {
        var zip = await window.JSZip.loadAsync(buf);
        var wbPath = 'xl/workbook.xml';
        if (!zip.file(wbPath)) return {};
        var wbDoc = new DOMParser().parseFromString(await zip.file(wbPath).async('string'), 'application/xml');
        var wbRels = await relsOf(zip, wbPath);
        var out = {};
        var sheets = wbDoc.getElementsByTagName('sheet');
        for (var i = 0; i < sheets.length; i++) {
            var name = sheets[i].getAttribute('name');
            var rid = sheets[i].getAttribute('r:id') || sheets[i].getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
            var sheetPath = wbRels[rid];
            if (!sheetPath || !zip.file(sheetPath)) continue;
            var sRels = await relsOf(zip, sheetPath);
            var drawings = Object.keys(sRels).map(function (k) { return sRels[k]; }).filter(function (v) { return /drawings\/drawing[^/]*\.xml$/.test(v); });
            for (var d = 0; d < drawings.length; d++) {
                var dp = drawings[d];
                if (!zip.file(dp)) continue;
                var dRels = await relsOf(zip, dp);
                var dDoc = new DOMParser().parseFromString(await zip.file(dp).async('string'), 'application/xml');
                var blips = dDoc.getElementsByTagName('a:blip');
                for (var b = 0; b < blips.length; b++) {
                    var emb = blips[b].getAttribute('r:embed');
                    var mp = dRels[emb];
                    if (!mp || !zip.file(mp)) continue;
                    var e = mp.split('.').pop().toLowerCase();
                    if (!MIME[e]) continue;
                    var b64 = await zip.file(mp).async('base64');
                    (out[name] = out[name] || []).push('data:' + MIME[e] + ';base64,' + b64);
                }
            }
        }
        return out;
    }

    // Word icerigini sayfalara boler: acik sayfa sonlari + yaklasik A4 yuksekligi
    function paginateDocx(box, html) {
        var PAGE_H = 1050;
        var measure = document.createElement('div');
        measure.className = 'sh-docx';
        measure.style.cssText = 'position:absolute;visibility:hidden;left:-9999px;top:0;width:' + Math.max(320, Math.min(box.clientWidth || 800, 820)) + 'px';
        measure.innerHTML = html;
        document.body.appendChild(measure);
        var pages = [], cur = [], h = 0;
        var flush = function () { if (cur.length) { pages.push(cur.join('')); } cur = []; h = 0; };
        Array.prototype.slice.call(measure.children).forEach(function (el) {
            if (el.classList && el.classList.contains('sh-pagebreak')) { flush(); return; }
            var inner = el.querySelectorAll ? el.querySelectorAll('hr.sh-pagebreak') : [];
            if (inner.length) { // paragraf icindeki sayfa sonu
                var parts = el.innerHTML.split(/<hr[^>]*sh-pagebreak[^>]*>/i);
                parts.forEach(function (part, i) {
                    if (i > 0) flush();
                    if (part.replace(/<[^>]*>|\s|&nbsp;/g, '') !== '' || /<img/i.test(part)) {
                        cur.push('<' + el.tagName.toLowerCase() + '>' + part + '</' + el.tagName.toLowerCase() + '>');
                    }
                });
                return;
            }
            var eh = el.offsetHeight + 12;
            if (cur.length && h + eh > PAGE_H) flush();
            cur.push(el.outerHTML);
            h += eh;
        });
        flush();
        document.body.removeChild(measure);
        if (!pages.length) pages = ['<p>(Boş belge)</p>'];
        var idx = 0;
        box.innerHTML = '<div class="sh-pager"><button type="button" data-d="-1">‹ Önceki</button><span class="sh-pager-info"></span><button type="button" data-d="1">Sonraki ›</button></div><div class="sh-docx sh-docx-page"></div>';
        var info = box.querySelector('.sh-pager-info'), body = box.querySelector('.sh-docx-page');
        var btns = box.querySelectorAll('.sh-pager button');
        var show = function (i) {
            idx = Math.max(0, Math.min(pages.length - 1, i));
            body.innerHTML = pages[idx];
            info.textContent = 'Sayfa ' + (idx + 1) + ' / ' + pages.length;
            btns[0].disabled = idx === 0;
            btns[1].disabled = idx === pages.length - 1;
            box.scrollTop = 0;
        };
        btns.forEach(function (b) { b.addEventListener('click', function () { show(idx + (+b.dataset.d)); }); });
        if (pages.length === 1) box.querySelector('.sh-pager').style.display = 'none';
        show(0);
    }

    // box: hedef element, url: ayni alan adindaki dosya, alive: iptal kontrolu
    async function render(box, url, name, alive) {
        alive = alive || function () { return true; };
        var ext = (url.split('?')[0].split('.').pop() || '').toLowerCase();
        msg(box, 'Dosya yükleniyor…');
        try {
            if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp'].indexOf(ext) > -1) {
                box.innerHTML = '<img alt="' + esc(name) + '" src="' + url + '">';
            } else if (ext === 'pdf') {
                box.innerHTML = '<iframe title="PDF" src="' + url + '"></iframe>';
            } else if (['mp4', 'webm', 'mov'].indexOf(ext) > -1) {
                box.innerHTML = '<video controls src="' + url + '"></video>';
            } else if (['mp3', 'wav', 'ogg', 'm4a'].indexOf(ext) > -1) {
                box.innerHTML = '<audio controls style="width:100%" src="' + url + '"></audio>';
            } else if (['txt', 'csv', 'md', 'json', 'xml', 'html', 'css', 'js', 'py', 'java', 'c', 'cpp', 'cs', 'php', 'sql', 'ino', 'rtf'].indexOf(ext) > -1) {
                var t = await (await fetch(url)).text();
                if (!alive()) return;
                box.innerHTML = '<pre>' + esc(t.slice(0, 200000)) + '</pre>';
            } else if (ext === 'docx') {
                var dbuf = await (await fetch(url)).arrayBuffer();
                var done = false;
                try {
                    await loadScript('/js/preview/jszip.min.js?v=2');
                    await loadScript('/js/preview/docx-preview.min.js?v=1');
                    if (!alive()) return;
                    box.innerHTML = '<div class="sh-pager"><button type="button" data-d="-1">‹ Önceki</button><span class="sh-pager-info"></span><button type="button" data-d="1">Sonraki ›</button></div><div class="sh-docxhost"></div>';
                    var host = box.querySelector('.sh-docxhost');
                    await window.docx.renderAsync(dbuf, host, null, { className: 'docx', inWrapper: true, breakPages: true, ignoreLastRenderedPageBreak: false, useBase64URL: true });
                    var pgs = Array.prototype.slice.call(host.querySelectorAll('section.docx'));
                    if (pgs.length) {
                        var di = 0, dinfo = box.querySelector('.sh-pager-info'), dbtn = box.querySelectorAll('.sh-pager button');
                        var dshow = function (i) {
                            di = Math.max(0, Math.min(pgs.length - 1, i));
                            pgs.forEach(function (pg, k) { pg.style.display = k === di ? '' : 'none'; });
                            dinfo.textContent = 'Sayfa ' + (di + 1) + ' / ' + pgs.length;
                            dbtn[0].disabled = di === 0; dbtn[1].disabled = di === pgs.length - 1;
                            box.scrollTop = 0;
                        };
                        dbtn.forEach(function (bt) { bt.addEventListener('click', function () { dshow(di + (+bt.dataset.d)); }); });
                        if (pgs.length === 1) box.querySelector('.sh-pager').style.display = 'none';
                        dshow(0);
                        done = true;
                    }
                } catch (er2) { done = false; }
                if (!done) { // yedek: mammoth ile akis halinde goster
                    await loadScript('/js/preview/mammoth.browser.min.js?v=2');
                    var out = await window.mammoth.convertToHtml({ arrayBuffer: dbuf }, { styleMap: ["br[type='page'] => hr.sh-pagebreak"] });
                    if (!alive()) return;
                    paginateDocx(box, out.value);
                }
            } else if (['xlsx', 'xls', 'ods', 'csv'].indexOf(ext) > -1) {
                await loadScript('/js/preview/xlsx.full.min.js?v=2');
                var b2 = await (await fetch(url)).arrayBuffer();
                var wb = window.XLSX.read(b2, { type: 'array' });
                var imgs = {};
                if (ext === 'xlsx') {
                    try { await loadScript('/js/preview/jszip.min.js?v=2'); imgs = await sheetImages(b2, wb.SheetNames); } catch (er) { imgs = {}; }
                }
                if (!alive()) return;
                var show = function (i) {
                    box.querySelectorAll('.sh-sheet-tabs button').forEach(function (b, k) { b.classList.toggle('on', k === i); });
                    box.querySelector('.sh-sheet').innerHTML = window.XLSX.utils.sheet_to_html(wb.Sheets[wb.SheetNames[i]], { header: '', footer: '' })
                        + (imgs[wb.SheetNames[i]] && imgs[wb.SheetNames[i]].length ? '<div class="sh-sheet-imgs"><div class="sh-sheet-imgs-t">Bu sayfadaki görseller</div>' + imgs[wb.SheetNames[i]].map(function (u) { return '<img alt="" src="' + u + '">'; }).join('') + '</div>' : '');
                };
                box.innerHTML = '<div class="sh-sheet-tabs">' + wb.SheetNames.map(function (n, i) { return '<button type="button" data-i="' + i + '">' + esc(n) + '</button>'; }).join('') + '</div><div class="sh-sheet"></div>';
                box.querySelectorAll('.sh-sheet-tabs button').forEach(function (b) { b.addEventListener('click', function () { show(+b.dataset.i); }); });
                show(0);
            } else {
                msg(box, 'Bu dosya türü (' + esc(ext.toUpperCase()) + ') tarayıcıda önizlenemiyor. “İndir” butonunu kullanın.');
            }
        } catch (e) {
            msg(box, 'Önizleme oluşturulamadı. “İndir” butonuyla dosyaya ulaşabilirsiniz.');
        }
    }
    window.FilePreview = { render: render };
})();
