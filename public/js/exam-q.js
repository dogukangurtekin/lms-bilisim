/* Sınav modülü: soru modeli + soru formu (editör ve soru havuzu ortak kullanır) */
window.ExamQ = (() => {
    const LETTERS = 'ABCDEFGHIJ';
    const TYPE_NAME = { multiple_choice: 'Çoktan seçmeli', true_false: 'Doğru / Yanlış', fill_blank: 'Boşluk doldurma', open_ended: 'Açık uçlu', matching: 'Eşleştirme' };
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function newQuestion(type) {
        const base = { type, body: '', options: [], answer: '', points: 5, section_title: '', answer_lines: 0, bank_item_id: null };
        if (type === 'multiple_choice') { base.options = ['', '', '', '']; base.answer = 'A'; }
        if (type === 'true_false') { base.answer = 'D'; }
        if (type === 'open_ended') { base.answer_lines = 3; base.points = 10; }
        if (type === 'matching') { base.options = [{ l: '', r: '' }, { l: '', r: '' }, { l: '', r: '' }, { l: '', r: '' }]; base.points = 10; }
        return base;
    }

    // Eşleştirmede sağ sütun karışık gösterilir; doğru eşleşme anahtarı üretilir (ör. 1-C 2-A).
    const shiftFor = (n) => Math.max(1, Math.floor(n / 2));
    function matchOrder(n) { const s = shiftFor(n); return Array.from({ length: n }, (_, j) => (j + s) % n); }
    function matchKey(q) {
        const order = matchOrder(q.options.length);
        return q.options.map((_, i) => (i + 1) + '-' + LETTERS[order.indexOf(i)]).join(' ');
    }
    /** Sunucuya gönderilecek cevap: eşleştirmede anahtar, diğerlerinde girilen cevap. */
    const answerOf = (q) => (q.type === 'matching' ? matchKey(q) : (q.answer || ''));

    /**
     * Soru düzenleme formu.
     * opts.section: bölüm başlığı alanı, opts.points: puan alanı, opts.uid: radyo grubu için benzersiz anahtar
     */
    function formHtml(q, opts = {}) {
        const uid = opts.uid ?? 'q';
        let f = '<div class="xe-qb">';
        if (opts.section) f += '<label class="xe-f">Bölüm başlığı (isteğe bağlı)<input type="text" data-q="section_title" value="' + esc(q.section_title) + '" maxlength="200" placeholder="Örn. A. Çoktan Seçmeli Sorular"></label>';
        f += '<label class="xe-f">' + TYPE_NAME[q.type] + ' — soru metni<textarea data-q="body" maxlength="6000" placeholder="' + (q.type === 'fill_blank' ? 'Boşlukları ........ ile gösterin' : 'Soru metnini yazın') + '">' + esc(q.body) + '</textarea></label>';
        if (q.type === 'multiple_choice') {
            f += '<div class="xe-f">Seçenekler (doğru olanı işaretleyin)</div>';
            q.options.forEach((o, k) => {
                f += '<div class="xe-opt"><input type="radio" name="ans' + uid + '" data-ans="' + LETTERS[k] + '"' + (q.answer === LETTERS[k] ? ' checked' : '') + '><b>' + LETTERS[k] + ')</b>'
                    + '<input type="text" data-opt="' + k + '" value="' + esc(o) + '" maxlength="500">'
                    + (q.options.length > 2 ? '<button type="button" class="xe-ic del" data-act="optdel" data-k="' + k + '">✕</button>' : '') + '</div>';
            });
            if (q.options.length < 6) f += '<div><button type="button" class="xe-mini" data-act="optadd">+ Seçenek ekle</button></div>';
        } else if (q.type === 'true_false') {
            f += '<div class="xe-f">Doğru cevap</div><div class="xe-row2"><label class="xe-sw"><input type="radio" name="ans' + uid + '" data-ans="D"' + (q.answer !== 'Y' ? ' checked' : '') + '> Doğru</label><label class="xe-sw"><input type="radio" name="ans' + uid + '" data-ans="Y"' + (q.answer === 'Y' ? ' checked' : '') + '> Yanlış</label></div>';
        } else if (q.type === 'matching') {
            f += '<div class="xe-f">Eşleşen çiftler (sol ↔ sağ)</div>';
            q.options.forEach((o, k) => {
                f += '<div class="xe-opt"><b>' + (k + 1) + '</b><input type="text" data-pl="' + k + '" value="' + esc(o.l) + '" placeholder="Sol" maxlength="500"><input type="text" data-pr="' + k + '" value="' + esc(o.r) + '" placeholder="Sağ" maxlength="500">'
                    + (q.options.length > 2 ? '<button type="button" class="xe-ic del" data-act="pairdel" data-k="' + k + '">✕</button>' : '') + '</div>';
            });
            if (q.options.length < 8) f += '<div><button type="button" class="xe-mini" data-act="pairadd">+ Çift ekle</button></div>';
            f += '<div class="xe-f" data-matchkey>Doğru eşleşme: ' + esc(matchKey(q)) + '</div>';
        } else if (q.type === 'open_ended') {
            f += '<div class="xe-row2"><label class="xe-f">Cevap satırı<input type="number" data-q="answer_lines" min="0" max="20" value="' + Number(q.answer_lines) + '"></label>'
                + (opts.points ? '<label class="xe-f">Puan<input type="number" data-q="points" min="0" max="1000" step="0.5" value="' + Number(q.points) + '"></label>' : '') + '</div>';
            f += '<label class="xe-f">Cevap anahtarı (öğretmen için)<textarea data-q="answer" maxlength="2000">' + esc(q.answer) + '</textarea></label>';
        } else if (q.type === 'fill_blank') {
            f += '<label class="xe-f">Doğru cevap<input type="text" data-q="answer" value="' + esc(q.answer) + '" maxlength="2000"></label>';
        }
        if (opts.points && q.type !== 'open_ended') f += '<label class="xe-f">Puan<input type="number" data-q="points" min="0" max="1000" step="0.5" value="' + Number(q.points) + '"></label>';
        return f + '</div>';
    }

    /** Form içindeki bir tıklamayı soruya uygular (seçenek/çift ekle-sil). Yapı değiştiyse true döner. */
    function applyClick(q, target) {
        const btn = target.closest('[data-act]'); if (!btn) return false;
        const act = btn.dataset.act, k = Number(btn.dataset.k);
        if (act === 'optadd') q.options.push('');
        else if (act === 'optdel') { q.options.splice(k, 1); if (LETTERS.indexOf(q.answer) >= q.options.length) q.answer = 'A'; }
        else if (act === 'pairadd') q.options.push({ l: '', r: '' });
        else if (act === 'pairdel') q.options.splice(k, 1);
        else return false;
        return true;
    }

    /** Form alanındaki girişi soruya yazar. İşlendiyse true döner. */
    function applyInput(q, t, root) {
        if (t.dataset.q) {
            const key = t.dataset.q;
            if (key === 'points') q.points = t.value === '' ? 0 : Number(t.value);
            else if (key === 'answer_lines') q.answer_lines = Number(t.value) || 0;
            else q[key] = t.value;
        } else if (t.dataset.opt !== undefined) q.options[Number(t.dataset.opt)] = t.value;
        else if (t.dataset.pl !== undefined) q.options[Number(t.dataset.pl)].l = t.value;
        else if (t.dataset.pr !== undefined) q.options[Number(t.dataset.pr)].r = t.value;
        else if (t.dataset.ans) q.answer = t.dataset.ans;
        else return false;
        const mk = root && root.querySelector ? root.querySelector('[data-matchkey]') : null;
        if (mk && q.type === 'matching') mk.textContent = 'Doğru eşleşme: ' + matchKey(q);
        return true;
    }

    return { LETTERS, TYPE_NAME, esc, newQuestion, matchOrder, matchKey, answerOf, formHtml, applyClick, applyInput };
})();
