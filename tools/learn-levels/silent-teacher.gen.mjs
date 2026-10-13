// Silent Teacher (Python Quiz Lab) — 100 bölümlük müfredat üretici.
// Çalıştır: node tools/learn-levels/silent-teacher.gen.mjs  →  public/runner-assets/silent-teacher-runner/curriculum.js
// Her bölüm 3 soru içerir; soru kodu ve doğru yanıt aynı şablondan, deterministik tohumla üretilir.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(here, '../../public/runner-assets/silent-teacher-runner/curriculum.js');

function mulberry32(a) { return function () { a |= 0; a = (a + 0x6D2B79F5) | 0; let t = Math.imul(a ^ (a >>> 15), 1 | a); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
function RNG(seed) {
  const f = mulberry32(seed);
  const r = {
    f,
    i: (a, b) => a + Math.floor(f() * (b - a + 1)),
    p: (arr) => arr[Math.floor(f() * arr.length)],
    sh: (arr) => { const a = arr.slice(); for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(f() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; },
    uniq: (n, lo, hi) => { const s = new Set(); while (s.size < n) s.add(r.i(lo, hi)); return [...s]; },
  };
  return r;
}

// ---- Python değer biçimleyici
const F = (x) => ({ f: x });               // float
const RAW = (s) => ({ raw: String(s) });   // print çıktısı (tırnaksız)
const TUP = (a) => ({ t: a });
function py(v) {
  if (v === null) return 'None';
  if (typeof v === 'boolean') return v ? 'True' : 'False';
  if (typeof v === 'number') return String(v);
  if (typeof v === 'string') return `'${v}'`;
  if (Array.isArray(v)) return '[' + v.map(py).join(', ') + ']';
  if (v.f !== undefined) return Number.isInteger(v.f) ? v.f.toFixed(1) : String(v.f);
  if (v.raw !== undefined) return v.raw;
  if (v.t) return '(' + v.t.map(py).join(', ') + (v.t.length === 1 ? ',' : '') + ')';
  throw new Error('py: ' + JSON.stringify(v));
}
const L = (...lines) => lines.join('\n');
const ev = (e) => Function(`"use strict"; return (${e});`)();
const fd = (a, b) => Math.floor(a / b);
const md = (a, b) => ((a % b) + b) % b;

// ---- seçenek üretici (6 seçenek + "?!?" tuzak)
function autoDistractors(ans) {
  const out = [];
  if (typeof ans === 'number') out.push(ans + 1, ans - 1, ans + 2, ans - 2, ans * 2, ans + 10, 0, 1, -ans);
  else if (typeof ans === 'boolean') out.push(!ans, null, 0, 1, 'True', 'False');
  else if (typeof ans === 'string') {
    if (ans.length > 1) out.push(ans.split('').reverse().join(''), ans.toUpperCase(), ans.slice(1), ans.slice(0, -1), ans + ans[0]);
    out.push('', ans + ' ');
  } else if (Array.isArray(ans)) {
    if (ans.length > 1) out.push(ans.slice().reverse(), ans.slice(1), ans.slice(0, -1));
    out.push([], ans.concat(ans.slice(0, 1)));
  } else if (ans && ans.f !== undefined) out.push(F(ans.f + 1), F(ans.f - 1), F(ans.f * 2), Math.round(ans.f), Math.floor(ans.f) + 1);
  else if (ans && ans.raw !== undefined) out.push(RAW(ans.raw + ' '), RAW(ans.raw.split('').reverse().join('')), RAW('None'));
  else if (ans && ans.t) out.push(TUP(ans.t.slice().reverse()), TUP(ans.t.slice(1)));
  return out;
}
function q(r, code, prompt, ans, near = []) {
  const a = py(ans);
  const seen = new Set([a, '?!?']);
  const pool = [];
  for (const c of [...near, ...autoDistractors(ans)]) {
    const s = py(c);
    if (!seen.has(s) && s !== '') { seen.add(s); pool.push(s); }
  }
  const picked = pool.slice(0, 5);
  for (let k = 1; picked.length < 5 && k < 60; k++) {   // yedek: sayısal gürültü
    const s = typeof ans === 'number' ? String(ans + k * 3) : `'${'xyzqwk'[k % 6]}${k}'`;
    if (!seen.has(s)) { seen.add(s); picked.push(s); }
  }
  const options = r.sh([a, ...picked]).concat('?!?');
  return { code, prompt, options, answer: a };
}

const NAMES = ['a', 'b', 'x', 'y', 'n', 'k', 'm', 'sayi', 'puan', 'toplam'];
const WORDS = ['elma', 'kitap', 'python', 'merhaba', 'bilgi', 'robot', 'kodlama', 'masa', 'kalem', 'okul', 'dunya', 'bahar'];
const CH = 'abcdeklmnoprstuz';
const PRM = 'Sonuc nedir?';
const P1 = 'Son satırın sonucu nedir?';
const PP = 'Ekrana ne yazdırılır?';

// ================= GENERATORS: gk(r, d) -> soru; d = 0..1 zorluk ilerlemesi =================
const G = {};
G.assign = (r) => { const n = r.p(NAMES), A = r.i(2, 15), K = r.i(1, 9); return q(r, L(`${n} = ${A}`, `${n} + ${K}`), P1, A + K); };
G.reassign = (r) => { const n = r.p(NAMES), A = r.i(2, 9), B = r.i(10, 20), K = r.i(1, 5); return q(r, L(`${n} = ${A}`, `${n} = ${B}`, `${n} + ${K}`), P1, B + K, [A + K, A + B]); };
G.addsub = (r) => { const A = r.i(6, 30), B = r.i(1, 5), op = r.p(['+', '-']); return q(r, L(`x = ${A}`, `y = ${B}`, `x ${op} y`), 'x ' + op + ' y sonucu nedir?', ev(`${A}${op}${B}`)); };
G.muldiv = (r) => { if (r.f() < .5) { const A = r.i(2, 9), B = r.i(2, 9); return q(r, `${A} * ${B}`, PRM, A * B); } const B = r.i(2, 6), A = B * r.i(2, 8); return q(r, `${A} / ${B}`, '/ işlemi her zaman ondalıklı (float) sonuç verir. Sonuç nedir?', F(A / B), [A / B]); };
G.floormod = (r) => { const B = r.i(3, 9), A = B * r.i(2, 6) + r.i(1, B - 1); return r.f() < .5 ? q(r, `${A} // ${B}`, '// tam bölmenin bölümünü verir. Sonuç nedir?', fd(A, B), [md(A, B)]) : q(r, `${A} % ${B}`, '% işlemi bölümden kalanı verir. Sonuç nedir?', md(A, B), [fd(A, B)]); };
G.power = (r) => { const A = r.i(2, 6), B = r.i(2, 4); return q(r, `${A} ** ${B}`, '** üs alma işlemidir. Sonuç nedir?', A ** B, [A * B]); };
G.prec = (r) => { const [A, B, C] = [r.i(2, 9), r.i(2, 9), r.i(2, 9)], e = r.p([`${A} + ${B} * ${C}`, `${A} * ${B} - ${C}`, `${A + 10} - ${B} * ${C}`, `${A} + ${B} * ${C} - ${r.i(1, 5)}`]); return q(r, e, 'Önce çarpma/bölme yapılır. Sonuç nedir?', ev(e)); };
G.paren = (r) => { const [A, B, C] = [r.i(2, 9), r.i(2, 9), r.i(2, 6)], e = r.p([`(${A} + ${B}) * ${C}`, `${C} * (${Math.max(A,B)} - ${Math.min(A,B)})`, `(${A} + ${B}) * (${C} - 1)`]); return q(r, e, 'Parantez içi önce hesaplanır. Sonuç nedir?', ev(e)); };
G.selfadd = (r) => { const A = r.i(3, 15), B = r.i(2, 9); return q(r, L(`n = ${A}`, `n = n + ${B}`, 'n'), "n'nin son değeri nedir?", A + B, [A]); };
G.augassign = (r) => { const A = r.i(2, 9), B = r.i(1, 6), C = r.i(2, 4), ops = r.p([['+=', '*=', (a, b, c) => (a + b) * c], ['-=', '*=', (a, b, c) => (a - b) * c], ['*=', '+=', (a, b, c) => a * b + c]]); return q(r, L(`n = ${A}`, `n ${ops[0]} ${B}`, `n ${ops[1]} ${C}`, 'n'), "n'nin son değeri nedir?", ops[2](A, B, C)); };
G.strcat = (r) => { const [a, b] = r.sh(WORDS).slice(0, 2), sp = r.f() < .5; return q(r, L(`s = '${a}'`, `t = '${b}'`, sp ? "s + ' ' + t" : 's + t'), 'Metinler birleşince ne olur?', sp ? `${a} ${b}` : a + b, [b + a]); };
G.strrep = (r) => { const w = r.p(['ab', 'xo', '-', 'ha', '*', 'py']), n = r.i(2, 5); return q(r, `'${w}' * ${n}`, 'Metin tekrarlanınca ne olur?', w.repeat(n)); };
G.len = (r) => { const w = r.p(WORDS); return r.f() < .6 ? q(r, `len('${w}')`, 'len() karakter sayısını verir. Sonuç nedir?', w.length) : (() => { const l = r.uniq(r.i(3, 6), 1, 9); return q(r, `len(${py(l)})`, 'Listede kaç eleman var?', l.length); })(); };
G.stridx = (r) => { const w = r.p(WORDS), k = r.p([0, 1, 2, -1, -2]); return q(r, L(`s = '${w}'`, `s[${k}]`), 'Metin indeksi 0’dan başlar. Sonuç nedir?', w.at(k)); };
G.slice = (r) => { const w = r.p(WORDS.filter((x) => x.length >= 5)), [i, j] = [r.i(0, 2), r.i(3, 5)], f = r.p([`[${i}:${j}]`, `[:${j - 1}]`, `[${i + 1}:]`]); const v = f.includes(':') ? ev(`'${w}'.slice(${f === `[${i}:${j}]` ? `${i},${j}` : f === `[:${j - 1}]` ? `0,${j - 1}` : `${i + 1}`})`) : ''; return q(r, L(`s = '${w}'`, `s${f}`), 'Dilimleme: başlangıç dahil, bitiş hariçtir. Sonuç nedir?', v); };
G.case = (r) => { const w = r.p(WORDS), m = r.p(['upper', 'capitalize']); return q(r, `'${w}'.${m}()`, 'Metin metodunun sonucu nedir?', m === 'upper' ? w.toUpperCase() : w[0].toUpperCase() + w.slice(1)); };
G.print1 = (r) => { const A = r.i(2, 20), B = r.i(2, 9); return q(r, L(`a = ${A}`, `b = ${B}`, 'print(a + b)'), PP, RAW(A + B), [RAW(`${A}${B}`)]); };
G.print2 = (r) => { const w = r.p(WORDS), n = r.i(2, 30), v = r.p([0, 1, 2]); return v === 0 ? q(r, `print('${w}', ${n})`, 'print, virgülle ayrılan değerleri boşlukla yazar. Çıktı nedir?', RAW(`${w} ${n}`)) : v === 1 ? q(r, `print('${w}', 'ok', sep='-')`, 'sep ayırıcıyı belirler. Çıktı nedir?', RAW(`${w}-ok`)) : q(r, `print('${w}' + str(${n}))`, PP, RAW(w + n)); };
G.convert = (r) => { const A = r.i(2, 9), B = r.i(1, 9); const v = r.p([0, 1, 2, 3]); return v === 0 ? q(r, `int('${A}') + ${B}`, "int() metni sayıya çevirir. Sonuç nedir?", A + B, [Number(`${A}${B}`)]) : v === 1 ? q(r, `str(${A}) + '${B}'`, "str() sayıyı metne çevirir. Sonuç nedir?", `${A}${B}`, [A + B]) : v === 2 ? q(r, `int(${A}.9)`, 'int() ondalık kısmı atar. Sonuç nedir?', A, [A + 1]) : q(r, `float(${A})`, 'float() ondalıklı sayıya çevirir. Sonuç nedir?', F(A)); };
G.roundabs = (r) => { const A = r.i(2, 9), v = r.p([0, 1, 2]); return v === 0 ? q(r, `round(${A}.7)`, 'round() en yakın tam sayıya yuvarlar. Sonuç nedir?', A + 1, [A]) : v === 1 ? q(r, `abs(-${A * 3})`, 'abs() mutlak değeri verir. Sonuç nedir?', A * 3, [-A * 3]) : q(r, `round(${A}.2)`, 'round() en yakın tam sayıya yuvarlar. Sonuç nedir?', A); };
G.listidx = (r) => { const l = r.uniq(r.i(4, 6), 1, 40), k = r.i(0, l.length - 1); return q(r, L(`a = ${py(l)}`, `a[${k}]`), 'Liste indeksi 0’dan başlar. Sonuç nedir?', l[k]); };
G.listneg = (r) => { const l = r.uniq(r.i(4, 6), 1, 40), k = r.i(1, 3); return q(r, L(`a = ${py(l)}`, `a[-${k}]`), '-1 son elemandır. Sonuç nedir?', l[l.length - k]); };
G.listlen = (r) => { const l = r.uniq(r.i(3, 6), 1, 30), v = r.f() < .5; return v ? q(r, L(`a = ${py(l)}`, 'len(a)'), 'Listede kaç eleman var?', l.length) : q(r, L(`a = ${py(l)}`, 'a.append(99)', 'len(a)'), 'append sonrası kaç eleman var?', l.length + 1); };
G.append = (r) => { const l = r.uniq(r.i(2, 4), 1, 9), x = r.i(10, 20); return q(r, L(`a = ${py(l)}`, `a.append(${x})`, 'a'), 'append listenin sonuna ekler. a nedir?', [...l, x], [[x, ...l]]); };
G.listset = (r) => { const l = r.uniq(4, 1, 30), k = r.i(0, 3), x = r.i(31, 60), m = l.slice(); m[k] = x; return q(r, L(`a = ${py(l)}`, `a[${k}] = ${x}`, 'a'), 'Liste elemanı değiştirilebilir. a nedir?', m); };
G.eq = (r) => { const A = r.i(1, 20), B = r.p([A, A + 1, A * 2]), v = r.p(['==', '!=']); return q(r, `${A} ${v} ${B}`, 'Sonuç True mu False mu?', ev(`${A} ${v === '==' ? '===' : '!=='} ${B}`)); };
G.cmp = (r) => { const A = r.i(1, 20), B = r.i(1, 20), op = r.p(['<', '>', '<=', '>=']); return q(r, `${A} ${op} ${B}`, 'Karşılaştırmanın sonucu nedir?', ev(`${A} ${op} ${B}`)); };
G.and = (r) => { const [A, B, C, D] = [r.i(1, 9), r.i(1, 9), r.i(1, 9), r.i(1, 9)]; return q(r, `${A} > ${B} and ${C} < ${D}`, 'and: iki koşul da doğru olmalı. Sonuç nedir?', ev(`${A} > ${B} && ${C} < ${D}`)); };
G.or = (r) => { const [A, B, C, D] = [r.i(1, 9), r.i(1, 9), r.i(1, 9), r.i(1, 9)]; return q(r, `${A} == ${B} or ${C} > ${D}`, 'or: biri doğruysa sonuç doğru olur. Sonuç nedir?', ev(`${A} === ${B} || ${C} > ${D}`)); };
G.not = (r) => { const [A, B] = [r.i(1, 9), r.i(1, 9)]; return r.f() < .5 ? q(r, `not (${A} > ${B})`, 'not sonucu tersine çevirir. Sonuç nedir?', !(A > B)) : q(r, `not ${r.p(['True', 'False'])}`, 'not sonucu tersine çevirir. Sonuç nedir?', false) && (() => { const t = r.p([true, false]); return q(r, `not ${py(t)}`, 'not sonucu tersine çevirir. Sonuç nedir?', !t); })(); };
G.in = (r) => { const v = r.p([0, 1, 2]); if (v === 0) { const w = r.p(WORDS), c = r.p([...w, 'z']); return q(r, `'${c}' in '${w}'`, 'in, içinde var mı diye bakar. Sonuç nedir?', w.includes(c)); } if (v === 1) { const l = r.uniq(4, 1, 12), x = r.i(1, 12); return q(r, `${x} in ${py(l)}`, 'Listede var mı? Sonuç nedir?', l.includes(x)); } const w = r.p(WORDS), c = r.p([...w, 'z']); return q(r, `'${c}' not in '${w}'`, 'not in, içinde yok mu diye bakar. Sonuç nedir?', !w.includes(c)); };
G.if1 = (r) => { const A = r.i(2, 9), B = r.i(2, 9), C = r.i(1, 9); return q(r, L(`x = ${A}`, `if x > ${B}:`, `    x = x + ${C}`, 'x'), "x'in son değeri nedir?", A > B ? A + C : A); };
G.ifelse = (r) => { const A = r.i(1, 9), B = r.i(1, 9), C = r.i(10, 20), D = r.i(21, 30); return q(r, L(`n = ${A}`, `if n > ${B}:`, `    n = ${C}`, 'else:', `    n = ${D}`, 'n'), "n'nin son değeri nedir?", A > B ? C : D, [C, D]); };
G.minmax = (r) => { const l = r.uniq(5, 1, 50), v = r.p([0, 1, 2, 3]); return v === 0 ? q(r, `max(${py(l)})`, 'max en büyüğü verir. Sonuç nedir?', Math.max(...l)) : v === 1 ? q(r, `min(${py(l)})`, 'min en küçüğü verir. Sonuç nedir?', Math.min(...l)) : v === 2 ? q(r, `sum(${py(l)})`, 'sum toplamı verir. Sonuç nedir?', l.reduce((a, b) => a + b, 0)) : (() => { const [a, b, c] = l; return q(r, `max(${a}, ${b}, ${c})`, 'En büyük değer hangisi?', Math.max(a, b, c)); })(); };

G.elif = (r) => { const p = r.i(30, 100), [t1, t2] = [r.p([90, 85]), r.p([70, 60])]; return q(r, L(`p = ${p}`, `if p >= ${t1}:`, "    n = 'A'", `elif p >= ${t2}:`, "    n = 'B'", 'else:', "    n = 'C'", 'n'), 'İlk doğru koşulun dalı çalışır. n nedir?', p >= t1 ? 'A' : p >= t2 ? 'B' : 'C', ['A', 'B', 'C']); };
G.nestif = (r) => { const [x, y, a, b] = [r.i(1, 9), r.i(1, 9), r.i(1, 9), r.i(1, 9)]; return q(r, L(`x = ${x}`, `y = ${y}`, `if x > ${a}:`, `    if y > ${b}:`, '        r = 1', '    else:', '        r = 2', 'else:', '    r = 3', 'r'), "r'nin son değeri nedir?", x > a ? (y > b ? 1 : 2) : 3, [1, 2, 3]); };
G.andif = (r) => { const [x, y, a, b] = [r.i(1, 12), r.i(1, 12), r.i(2, 9), r.i(4, 11)]; return q(r, L(`x = ${x}`, `y = ${y}`, `if x > ${a} and y < ${b}:`, "    s = 'evet'", 'else:', "    s = 'hayir'", 's'), 's nedir?', x > a && y < b ? 'evet' : 'hayir', ['evet', 'hayir']); };
G.forsum = (r) => { const N = r.i(3, 8); return q(r, L('t = 0', `for i in range(${N}):`, '    t = t + i', 't'), 'range(n) 0’dan n-1’e kadar sayar. t nedir?', (N * (N - 1)) / 2, [(N * (N + 1)) / 2]); };
G.rangeab = (r) => { const A = r.i(2, 5), B = A + r.i(2, 5); let t = 0; for (let i = A; i < B; i++) t += i; return q(r, L('t = 0', `for i in range(${A}, ${B}):`, '    t += i', 't'), 'range(a, b): a dahil, b hariç. t nedir?', t); };
G.rangestep = (r) => { const A = r.i(0, 3), S = r.i(2, 4), B = A + S * r.i(2, 4) + r.i(0, 1), l = []; for (let i = A; i < B; i += S) l.push(i); return r.f() < .6 ? q(r, `list(range(${A}, ${B}, ${S}))`, 'Üçüncü sayı adım miktarıdır. Sonuç nedir?', l) : q(r, L('t = 0', `for i in range(${A}, ${B}, ${S}):`, '    t += 1', 't'), 'Döngü kaç kez çalışır?', l.length); };
G.forlist = (r) => { const l = r.uniq(r.i(3, 5), 1, 9), op = r.p(['sum', 'prod']); const v = op === 'sum' ? l.reduce((a, b) => a + b, 0) : l.reduce((a, b) => a * b, 1); return q(r, L(op === 'sum' ? 't = 0' : 't = 1', `for x in ${py(l)}:`, op === 'sum' ? '    t += x' : '    t *= x', 't'), 't nedir?', v); };
G.whilecnt = (r) => { const N = r.i(8, 20), S = r.i(2, 5); let i = 0; while (i < N) i += S; return q(r, L('i = 0', `while i < ${N}:`, `    i += ${S}`, 'i'), 'Koşul bozulana kadar döngü sürer. i nedir?', i); };
G.whilesum = (r) => { const N = r.i(3, 8); return q(r, L('t = 0', 'i = 1', `while i <= ${N}:`, '    t += i', '    i += 1', 't'), 't nedir?', (N * (N + 1)) / 2, [(N * (N - 1)) / 2]); };
G.brk = (r) => { const K = r.i(3, 7); return r.f() < .5 ? q(r, L('for i in range(10):', `    if i == ${K}:`, '        break', 'i'), 'break döngüyü hemen bitirir. i nedir?', K) : (() => { let t = 0; for (let i = 0; i < 10; i++) { if (i === K) break; t += i; } return q(r, L('t = 0', 'for i in range(10):', `    if i == ${K}:`, '        break', '    t += i', 't'), 'break görülünce döngü biter. t nedir?', t); })(); };
G.cont = (r) => { const N = r.i(5, 9); let t = 0; for (let i = 0; i < N; i++) { if (i % 2 === 0) continue; t += i; } return q(r, L('t = 0', `for i in range(${N}):`, '    if i % 2 == 0:', '        continue', '    t += i', 't'), 'continue o turu atlar. t nedir?', t); };
G.counteven = (r) => { const l = r.uniq(r.i(5, 7), 1, 30); return q(r, L('c = 0', `for x in ${py(l)}:`, '    if x % 2 == 0:', '        c += 1', 'c'), 'Çift sayıları sayar. c nedir?', l.filter((x) => x % 2 === 0).length); };
G.strloop = (r) => { const w = r.p(['abc', 'kod', 'ali', 'xyz', 'pi']), m = r.p([0, 1]); const v = m === 0 ? w.split('').reverse().join('') : w.split('').map((c) => c + c).join(''); return q(r, L("s = ''", `for c in '${w}':`, m === 0 ? '    s = c + s' : '    s = s + c * 2', 's'), 's nedir?', v); };
G.def1 = (r) => { const [x, y] = [r.i(2, 9), r.i(2, 9)], op = r.p(['+', '*', '-']); return q(r, L('def f(a, b):', `    return a ${op} b`, '', `f(${x}, ${y})`), 'Fonksiyon çağrısının sonucu nedir?', ev(`${x}${op}${y}`)); };
G.def2 = (r) => { const A = r.i(2, 9), K = r.i(1, 5); return q(r, L('def kare(n):', '    return n * n', '', `kare(${A}) + ${K}`), 'Sonuç nedir?', A * A + K, [A * A]); };
G.defdef = (r) => { const A = r.i(2, 9), B = r.i(2, 4); return q(r, L('def f(a, b=2):', '    return a * b', '', `f(${A})`), 'Verilmeyen parametre varsayılanı kullanır. Sonuç nedir?', A * 2); };
G.defif = (r) => { const [x, y] = [r.i(1, 20), r.i(1, 20)]; return q(r, L('def sec(a, b):', '    if a > b:', '        return a', '    return b', '', `sec(${x}, ${y})`), 'Sonuç nedir?', Math.max(x, y)); };
G.defsum = (r) => { const [x, y] = [r.i(2, 9), r.i(2, 9)]; return q(r, L('def f(x):', '    return x * 2', '', `f(${x}) + f(${y})`), 'Sonuç nedir?', x * 2 + y * 2); };
G.lslice = (r) => { const l = r.uniq(6, 1, 50), f = r.p([[1, 3], [0, 2], [2, 5]]), v = r.p([0, 1, 2]); return v === 0 ? q(r, L(`a = ${py(l)}`, `a[${f[0]}:${f[1]}]`), 'Dilimleme başlangıç dahil, bitiş hariçtir. Sonuç nedir?', l.slice(f[0], f[1])) : v === 1 ? q(r, L(`a = ${py(l)}`, 'a[:2]'), 'İlk iki eleman. Sonuç nedir?', l.slice(0, 2)) : q(r, L(`a = ${py(l)}`, 'a[-2:]'), 'Son iki eleman. Sonuç nedir?', l.slice(-2)); };
G.lmeth = (r) => { const l = r.uniq(3, 1, 9), x = r.i(10, 20), y = r.i(21, 30), m = l.slice(); m.push(x); m.pop(); m.unshift(y); return q(r, L(`a = ${py(l)}`, `a.append(${x})`, 'a.pop()', `a.insert(0, ${y})`, 'a'), 'pop son elemanı siler, insert araya ekler. a nedir?', m); };
G.lsort = (r) => { const l = r.uniq(4, 1, 30), v = r.p([0, 1, 2]), s = l.slice().sort((a, b) => a - b); return v === 0 ? q(r, `sorted(${py(l)})`, 'sorted küçükten büyüğe sıralar. Sonuç nedir?', s) : v === 1 ? q(r, L(`a = ${py(l)}`, 'a.sort()', 'a[0]'), 'sort sonrası ilk eleman nedir?', s[0]) : q(r, L(`a = ${py(l)}`, 'a.reverse()', 'a'), 'reverse listeyi ters çevirir. a nedir?', l.slice().reverse()); };
G.findmax = (r) => { const l = r.uniq(5, 1, 60); return q(r, L(`a = ${py(l)}`, 'm = a[0]', 'for x in a:', '    if x > m:', '        m = x', 'm'), 'm neyi bulur? Değeri nedir?', Math.max(...l)); };
G.strmeth = (r) => { const w = r.p(['banana', 'kalem', 'araba', 'elma', 'masa', 'ananas']), v = r.p([0, 1, 2, 3]), c = r.p([...new Set(w)]); return v === 0 ? q(r, `'${w}'.count('${c}')`, 'count harf sayısını verir. Sonuç nedir?', [...w].filter((x) => x === c).length) : v === 1 ? q(r, `'${w}'.replace('${c}', 'o')`, 'replace tüm eşleşmeleri değiştirir. Sonuç nedir?', w.split(c).join('o')) : v === 2 ? q(r, `'${w}'.find('${c}')`, 'find ilk bulunduğu indeksi verir. Sonuç nedir?', w.indexOf(c)) : q(r, `'${w}'.startswith('${w[0]}')`, 'startswith başlangıcı kontrol eder. Sonuç nedir?', true); };
G.splitjoin = (r) => { const p = r.sh(WORDS).slice(0, r.i(2, 4)), sep = r.p(['-', ',', '+']), v = r.p([0, 1, 2]); return v === 0 ? q(r, `'${p.join(sep)}'.split('${sep}')`, 'split metni listeye böler. Sonuç nedir?', p) : v === 1 ? q(r, `'${sep}'.join(${py(p)})`, 'join listeyi tek metne birleştirir. Sonuç nedir?', p.join(sep)) : q(r, `len('${p.join(' ')}'.split())`, 'Kaç kelime var?', p.length); };
G.dictget = (r) => { const ks = r.sh(['a', 'b', 'c', 'd']).slice(0, 3), vs = r.uniq(3, 1, 30), k = r.i(0, 2); return q(r, L(`d = {${ks.map((x, i) => `'${x}': ${vs[i]}`).join(', ')}}`, `d['${ks[k]}']`), 'Sözlükte anahtar ile değere ulaşılır. Sonuç nedir?', vs[k]); };
G.dictupd = (r) => { const vs = r.uniq(2, 1, 20), v = r.p([0, 1, 2]); return v === 0 ? q(r, L(`d = {'a': ${vs[0]}, 'b': ${vs[1]}}`, "d['c'] = 3", 'len(d)'), 'Yeni anahtar eklenince eleman sayısı artar. Sonuç nedir?', 3) : v === 1 ? q(r, L(`d = {'a': ${vs[0]}, 'b': ${vs[1]}}`, "'b' in d"), 'in sözlükte anahtara bakar. Sonuç nedir?', true) : q(r, L(`d = {'a': ${vs[0]}}`, "d['a'] = d['a'] + 5", "d['a']"), 'Sonuç nedir?', vs[0] + 5); };
G.dictvals = (r) => { const vs = r.uniq(3, 1, 20); return r.f() < .6 ? q(r, L(`d = {'a': ${vs[0]}, 'b': ${vs[1]}, 'c': ${vs[2]}}`, 'sum(d.values())'), 'values() değerleri verir. Sonuç nedir?', vs[0] + vs[1] + vs[2]) : q(r, L(`d = {'a': ${vs[0]}, 'b': ${vs[1]}, 'c': ${vs[2]}}`, 'list(d.keys())'), 'keys() anahtarları verir. Sonuç nedir?', ['a', 'b', 'c']); };
G.boolmix = (r) => { const [a, b, c, d, e, f] = Array.from({ length: 6 }, () => r.i(1, 9)); const ex = r.p([[`(${a} > ${b} or ${c} < ${d}) and not (${e} == ${f})`, `(${a} > ${b} || ${c} < ${d}) && !(${e} === ${f})`], [`not (${a} > ${b}) and (${c} == ${d} or ${e} > ${f})`, `!(${a} > ${b}) && (${c} === ${d} || ${e} > ${f})`]]); return q(r, ex[0], 'Önce parantezler, sonra not, and, or. Sonuç nedir?', ev(ex[1])); };
G.scope = (r) => { const [A, B] = [r.i(1, 9), r.i(10, 20)]; return q(r, L(`x = ${A}`, 'def f():', `    x = ${B}`, '    return x', '', 'f() + x'), 'Fonksiyon içindeki x yereldir. Sonuç nedir?', B + A, [B * 2, A * 2]); };
G.listcomp = (r) => { const N = r.i(3, 6), v = r.p([0, 1]); if (v === 0) { const l = []; for (let x = 0; x < N; x++) l.push(x * 2); return q(r, `[x * 2 for x in range(${N})]`, 'Liste üreteci her x için ifadeyi hesaplar. Sonuç nedir?', l); } const l = r.uniq(6, 1, 20), K = r.i(5, 12); return q(r, `[x for x in ${py(l)} if x > ${K}]`, 'Koşulu sağlayanlar listeye girer. Sonuç nedir?', l.filter((x) => x > K)); };
G.zip = (r) => { const a = r.uniq(3, 1, 9), b = r.uniq(3, 1, 9); return q(r, `[x + y for x, y in zip(${py(a)}, ${py(b)})]`, 'zip iki listeyi eşleştirir. Sonuç nedir?', a.map((x, i) => x + b[i])); };
G.fstr = (r) => { const [A, B] = [r.i(2, 9), r.i(2, 9)]; return q(r, L(`a = ${A}`, `b = ${B}`, "f'{a} + {b} = {a + b}'"), 'f-string içindeki ifadeler hesaplanır. Sonuç nedir?', `${A} + ${B} = ${A + B}`); };
G.fact = (r) => { const N = r.i(3, 6); let t = 1; for (let i = 1; i <= N; i++) t *= i; return q(r, L('t = 1', `for i in range(1, ${N + 1}):`, '    t *= i', 't'), 't nedir?', t); };
G.nested = (r) => { const [x, y] = [r.i(2, 6), r.i(1, 4)]; return q(r, L(`x = ${x}`, `y = ${y}`, 'if x > 1:', '    if y < 3:', '        x = x + y', 'x'), "x'in son değeri nedir?", x > 1 && y < 3 ? x + y : x); };
G.nfor = (r) => { const [A, B] = [r.i(2, 5), r.i(2, 5)]; return q(r, L('c = 0', `for i in range(${A}):`, `    for j in range(${B}):`, '        c += 1', 'c'), 'İç döngü her dış turda tamamen çalışır. c nedir?', A * B); };
G.nfor2 = (r) => { const [A, B] = [r.i(2, 4), r.i(2, 4)]; let t = 0; for (let i = 0; i < A; i++) for (let j = 0; j < B; j++) t += i * j; return q(r, L('t = 0', `for i in range(${A}):`, `    for j in range(${B}):`, '        t += i * j', 't'), 't nedir?', t); };
G.brkcond = (r) => { const N = r.i(10, 60); let i = 2; for (; i < 20; i++) if (i * i > N) break; return q(r, L('for i in range(2, 20):', `    if i * i > ${N}:`, '        break', 'i - 1'), 'Döngü ilk uygun i’de durur. Sonuç nedir?', i - 1); };
G.digits = (r) => { const n = r.i(120, 987); let m = n, s = 0; while (m > 0) { s += m % 10; m = fd(m, 10); } return q(r, L(`n = ${n}`, 's = 0', 'while n > 0:', '    s += n % 10', '    n //= 10', 's'), 'Basamakları toplar. s nedir?', s); };
G.deffor = (r) => { const N = r.i(3, 9); return q(r, L('def topla(n):', '    t = 0', '    for i in range(1, n + 1):', '        t += i', '    return t', '', `topla(${N})`), 'Fonksiyon 1’den n’ye kadar toplar. Sonuç nedir?', (N * (N + 1)) / 2); };
G.defmax = (r) => { const l = r.uniq(5, 1, 60); return q(r, L('def enb(l):', '    m = l[0]', '    for x in l:', '        if x > m:', '            m = x', '    return m', '', `enb(${py(l)})`), 'Fonksiyon en büyüğü bulur. Sonuç nedir?', Math.max(...l)); };
G.recfact = (r) => { const N = r.i(3, 6); let t = 1; for (let i = 1; i <= N; i++) t *= i; return q(r, L('def f(n):', '    if n == 0:', '        return 1', '    return n * f(n - 1)', '', `f(${N})`), 'Özyineleme: fonksiyon kendini çağırır. Sonuç nedir?', t); };
G.recsum = (r) => { const N = r.i(3, 8); return q(r, L('def t(n):', '    if n == 0:', '        return 0', '    return n + t(n - 1)', '', `t(${N})`), 'Özyinelemeli toplam. Sonuç nedir?', (N * (N + 1)) / 2); };
G.fib = (r) => { const fib = (n) => (n < 2 ? n : fib(n - 1) + fib(n - 2)), N = r.i(5, 9); return q(r, L('def fib(n):', '    if n < 2:', '        return n', '    return fib(n - 1) + fib(n - 2)', '', `fib(${N})`), 'Fibonacci: 0, 1, 1, 2, 3, 5, ... Sonuç nedir?', fib(N)); };
G.compcond = (r) => { const N = r.i(5, 10), l = []; for (let x = 0; x < N; x++) if (x % 2 === 1) l.push(x * x); return q(r, `[x * x for x in range(${N}) if x % 2 == 1]`, 'Koşullu liste üreteci. Sonuç nedir?', l); };
G.matrix = (r) => { const m = [r.uniq(3, 1, 9), r.uniq(3, 1, 9), r.uniq(3, 1, 9)], i = r.i(0, 2), j = r.i(0, 2); return r.f() < .6 ? q(r, L(`m = ${py(m)}`, `m[${i}][${j}]`), 'İlk indeks satır, ikinci sütundur. Sonuç nedir?', m[i][j]) : q(r, L(`m = ${py(m)}`, 'm[0][0] + m[1][1] + m[2][2]'), 'Köşegen toplamı nedir?', m[0][0] + m[1][1] + m[2][2]); };
G.step = (r) => { const l = r.uniq(6, 1, 50), w = r.p(WORDS), v = r.p([0, 1, 2]); return v === 0 ? q(r, L(`a = ${py(l)}`, 'a[::2]'), '[::2] bir atlayarak alır. Sonuç nedir?', l.filter((_, i) => i % 2 === 0)) : v === 1 ? q(r, `'${w}'[::-1]`, '[::-1] ters çevirir. Sonuç nedir?', w.split('').reverse().join('')) : q(r, L(`a = ${py(l)}`, 'a[1::2]'), '[1::2] 1’den başlayıp ikişer atlar. Sonuç nedir?', l.filter((_, i) => i % 2 === 1)); };
G.dictcount = (r) => { const w = r.p(['abca', 'kokok', 'ababa', 'mama', 'aabbb']), c = w[0]; return q(r, L('d = {}', `for c in '${w}':`, '    d[c] = d.get(c, 0) + 1', `d['${c}']`), 'Sözlük harfleri sayar. Sonuç nedir?', [...w].filter((x) => x === c).length); };
G.dictgetdef = (r) => { const [A, B] = [r.i(1, 9), r.i(10, 20)]; const v = r.p([0, 1]); return v === 0 ? q(r, L(`d = {'a': ${A}}`, "d.get('z', 0)"), 'get yoksa varsayılanı verir. Sonuç nedir?', 0) : q(r, L(`d = {'a': ${A}, 'b': ${B}}`, "d.get('b', 0) + d.get('x', 5)"), 'Sonuç nedir?', B + 5); };
G.palin = (r) => { const w = r.p(['level', 'radar', 'kalem', 'ana', 'masa', 'kayak', 'robot']); return q(r, L(`s = '${w}'`, 's == s[::-1]'), 'Palindrom, tersi kendisine eşit olandır. Sonuç nedir?', w === w.split('').reverse().join('')); };
G.compos = (r) => { const A = r.i(2, 9), v = r.p([0, 1]); return v === 0 ? q(r, L('def f(x):', '    return x + 1', 'def g(x):', '    return x * 2', '', `f(g(${A}))`), 'İçteki fonksiyon önce çalışır. Sonuç nedir?', A * 2 + 1, [(A + 1) * 2]) : q(r, L('def f(x):', '    return x + 1', 'def g(x):', '    return x * 2', '', `g(f(${A}))`), 'İçteki fonksiyon önce çalışır. Sonuç nedir?', (A + 1) * 2, [A * 2 + 1]); };
G.glob = (r) => { const A = r.i(2, 6); return q(r, L('n = 0', 'def art():', '    global n', `    n += ${A}`, '', 'art()', 'art()', 'n'), 'global ile dış değişken değişir. n nedir?', A * 2); };
G.swap = (r) => { const [A, B] = [r.i(1, 9), r.i(10, 20)]; return r.f() < .5 ? q(r, L(`a = ${A}`, `b = ${B}`, 't = a', 'a = b', 'b = t', 'a - b'), 'Yer değiştirmeden sonra a - b nedir?', B - A) : q(r, L(`a = ${A}`, `b = ${B}`, 'a, b = b, a', 'a - b'), 'Yer değiştirmeden sonra a - b nedir?', B - A); };
G.sortidx = (r) => { const l = r.uniq(5, 1, 60), s = l.slice().sort((a, b) => a - b), v = r.p([0, 1, 2]); return v === 0 ? q(r, `sorted(${py(l)})[1]`, 'Sıralanmış listenin 1. indeksi nedir?', s[1]) : v === 1 ? q(r, `sorted(${py(l)}, reverse=True)[0]`, 'reverse=True büyükten küçüğe sıralar. Sonuç nedir?', s[s.length - 1]) : q(r, L(`l = ${py(l)}`, 'l.index(max(l))'), 'index değerin yerini verir. Sonuç nedir?', l.indexOf(Math.max(...l))); };
G.set = (r) => { const base = r.uniq(3, 1, 9), l = r.sh([...base, ...base.slice(0, 2)]); return r.f() < .6 ? q(r, `len(set(${py(l)}))`, 'set tekrarları atar. Sonuç nedir?', new Set(l).size) : q(r, `sorted(set(${py(l)}))`, 'set tekrarsızdır, sorted sıralar. Sonuç nedir?', [...new Set(l)].sort((a, b) => a - b)); };
G.tuple = (r) => { const t = r.uniq(3, 1, 20); return r.f() < .5 ? q(r, L(`t = ${py(TUP(t))}`, 'x, y, z = t', 'y + z'), 'Tuple açılımı sırayla atar. Sonuç nedir?', t[1] + t[2]) : q(r, L(`t = ${py(TUP(t))}`, 't[1]'), 'Tuple indeksi de 0’dan başlar. Sonuç nedir?', t[1]); };
G.lambda = (r) => { const l = r.uniq(3, 1, 9), [A, B] = [r.i(2, 9), r.i(2, 9)]; return r.f() < .5 ? q(r, `list(map(lambda x: x * 2, ${py(l)}))`, 'map her elemana fonksiyonu uygular. Sonuç nedir?', l.map((x) => x * 2)) : q(r, `(lambda a, b: a + b)(${A}, ${B})`, 'lambda küçük bir fonksiyondur. Sonuç nedir?', A + B); };
G.gensum = (r) => { const N = r.i(8, 20); let t = 0; for (let x = 0; x < N; x++) if (x % 3 === 0) t += x; return q(r, `sum(x for x in range(${N}) if x % 3 == 0)`, '3’e bölünenleri toplar. Sonuç nedir?', t); };
G.tryex = (r) => { const v = r.p(['abc', '12', 'x9', '7']); const ok = /^\d+$/.test(v); return q(r, L('try:', `    n = int('${v}')`, 'except ValueError:', '    n = -1', 'n'), 'Hata olursa except bloğu çalışır. n nedir?', ok ? Number(v) : -1); };
G.joinrange = (r) => { const N = r.i(3, 7), sep = r.p(['', '-', ' ']); return q(r, `'${sep}'.join([str(i) for i in range(${N})])`, 'Sayıları metne çevirip birleştirir. Sonuç nedir?', Array.from({ length: N }, (_, i) => i).join(sep)); };
G.revnum = (r) => { const n = r.i(120, 987); let m = n, rv = 0; while (m > 0) { rv = rv * 10 + (m % 10); m = fd(m, 10); } return q(r, L(`n = ${n}`, 'r = 0', 'while n > 0:', '    r = r * 10 + n % 10', '    n //= 10', 'r'), 'Sayıyı ters çevirir. r nedir?', rv); };
G.prime = (r) => { const N = r.p([2, 7, 9, 11, 15, 17, 21, 23, 25, 29]), isP = (n) => { if (n < 2) return false; for (let i = 2; i < n; i++) if (n % i === 0) return false; return true; }; return q(r, L('def asal(n):', '    if n < 2:', '        return False', '    for i in range(2, n):', '        if n % i == 0:', '            return False', '    return True', '', `asal(${N})`), 'Asal sayı 1 ve kendisinden başka böleni olmayandır. Sonuç nedir?', isP(N)); };
G.avg = (r) => { const n = r.i(3, 5), m = r.i(3, 12), l = Array.from({ length: n }, () => r.i(1, 15)); const s = l.reduce((a, b) => a + b, 0), adj = l.slice(); adj[0] += (n - (s % n)) % n; const t = adj.reduce((a, b) => a + b, 0); return q(r, L('def ort(l):', '    return sum(l) / len(l)', '', `ort(${py(adj)})`), 'Ortalama = toplam / eleman sayısı. Sonuç nedir?', F(t / n)); };
G.revlist = (r) => { const l = r.uniq(5, 1, 50), v = r.p([0, 1]); return v === 0 ? q(r, L(`a = ${py(l)}`, 'b = a[::-1]', 'b[0] + b[1]'), 'Ters listenin ilk iki elemanı toplamı nedir?', l[4] + l[3]) : q(r, L(`a = ${py(l)}`, 'a.reverse()', 'a[0] - a[-1]'), 'reverse sonrası a[0] - a[-1] nedir?', l[4] - l[0]); };
G.wordcount = (r) => { const p = r.sh(WORDS).slice(0, r.i(3, 5)); if (r.f() < .6) return q(r, `len('${p.join(' ')}'.split())`, 'Kaç kelime var?', p.length); const w = p[0]; return q(r, `'${[w, p[1], w, p[2]].join(' ')}'.split().count('${w}')`, '“' + w + '” kaç kez geçiyor?', 2); };
G.mix1 = (r) => { const l = r.uniq(r.i(5, 7), 1, 30), ev2 = l.filter((x) => x % 2 === 0); return r.f() < .5 ? q(r, L('def ciftler(l):', '    s = []', '    for x in l:', '        if x % 2 == 0:', '            s.append(x)', '    return s', '', `len(ciftler(${py(l)}))`), 'Fonksiyon çift sayıları toplar. len kaç döner?', ev2.length) : q(r, L('def ciftler(l):', '    s = []', '    for x in l:', '        if x % 2 == 0:', '            s.append(x)', '    return s', '', `sum(ciftler(${py(l)}))`), 'Çift sayıların toplamı nedir?', ev2.reduce((a, b) => a + b, 0)); };
G.mix2 = (r) => { const N = r.p([6, 8, 9, 10, 12, 15, 16]); const d = []; for (let i = 1; i <= N; i++) if (N % i === 0) d.push(i); return r.f() < .6 ? q(r, L('def f(n):', '    r = []', '    for i in range(1, n + 1):', '        if n % i == 0:', '            r.append(i)', '    return r', '', `f(${N})`), 'Fonksiyon bölenleri bulur. Sonuç nedir?', d) : q(r, L('def f(n):', '    r = []', '    for i in range(1, n + 1):', '        if n % i == 0:', '            r.append(i)', '    return r', '', `len(f(${N}))`), 'Kaç bölen bulunur?', d.length); };

// ================= MÜFREDAT (100 bölüm) =================
const S = (title, lesson, gen) => ({ title, lesson, gen });
const EASY = [
  S('Değişkenler', 'Değişken, bir değere verilen isimdir. a = 5 yazarsan a artık 5’tir.', 'assign'),
  S('Değeri Değiştirme', 'Değişkene yeni değer atarsan eski değer silinir; son atama geçerlidir.', 'reassign'),
  S('Toplama ve Çıkarma', '+ ve - işlemleri sayıları toplar ve çıkarır; sonuç değişkenlerle hesaplanır.', 'addsub'),
  S('Çarpma ve Bölme', '* çarpar, / böler. Bölme sonucu her zaman ondalıklı (float) çıkar: 10 / 2 → 5.0.', 'muldiv'),
  S('Tam Bölme ve Kalan', '// tam bölümü, % kalanı verir. 17 // 5 → 3, 17 % 5 → 2.', 'floormod'),
  S('Üs Alma', '** üs alır: 2 ** 3 → 8.', 'power'),
  S('İşlem Önceliği', 'Önce çarpma ve bölme, sonra toplama ve çıkarma yapılır.', 'prec'),
  S('Parantez Kullanımı', 'Parantez içindeki işlem öncelik kazanır.', 'paren'),
  S('Kendine Ekleme', 'n = n + 5 yazarsan n’nin eski değerine 5 eklenir.', 'selfadd'),
  S('Kısa İşleçler', '+=, -=, *= işleçleri değişkeni güncellemenin kısa yoludur.', 'augassign'),
  S('Metin Birleştirme', '+ işleci metinleri uç uca ekler: "a" + "b" → "ab".', 'strcat'),
  S('Metin Tekrarlama', 'Bir metni sayıyla çarparsan metin tekrarlanır: "ab" * 3 → "ababab".', 'strrep'),
  S('Uzunluk: len()', 'len() bir metindeki karakter ya da listedeki eleman sayısını verir.', 'len'),
  S('Metin İndeksi', 'İndeks 0’dan başlar. s[0] ilk, s[-1] son karakterdir.', 'stridx'),
  S('Metin Dilimleme', 's[a:b] a’dan b’ye kadar (b hariç) olan parçayı verir.', 'slice'),
  S('Büyük ve Küçük Harf', 'upper() hepsini büyütür, capitalize() ilk harfi büyütür.', 'case'),
  S('print ile Yazdırma', 'print() değeri ekrana yazar; metinlerin tırnağı yazılmaz.', 'print1'),
  S('print ile Çoklu Yazma', 'print(a, b) değerleri boşlukla ayırarak yazar; sep= ile ayırıcıyı değiştirebilirsin.', 'print2'),
  S('Tür Dönüşümü', 'int(), str(), float() ile değerin türünü değiştirebilirsin.', 'convert'),
  S('Yuvarlama ve Mutlak Değer', 'round() yuvarlar, abs() mutlak değer verir, int() ondalığı atar.', 'roundabs'),
  S('Liste ve İndeks', 'Liste köşeli parantezle yazılır; indeks 0’dan başlar.', 'listidx'),
  S('Negatif İndeks', 'a[-1] listenin son elemanıdır, a[-2] sondan ikincidir.', 'listneg'),
  S('Liste Uzunluğu', 'len(liste) eleman sayısını verir; append eleman ekler.', 'listlen'),
  S('append ile Ekleme', 'append(x) listenin sonuna x ekler.', 'append'),
  S('Liste Elemanını Değiştirme', 'a[i] = x ile i. elemanı değiştirebilirsin.', 'listset'),
  S('Eşitlik Karşılaştırma', '== eşit mi, != eşit değil mi diye bakar; sonuç True ya da False olur.', 'eq'),
  S('Büyük / Küçük Karşılaştırma', '<, >, <=, >= ile sayıları kıyaslarsın.', 'cmp'),
  S('and Mantıksal İşleci', 'and: iki koşul da doğruysa sonuç True olur.', 'and'),
  S('or Mantıksal İşleci', 'or: koşullardan biri doğruysa sonuç True olur.', 'or'),
  S('not Mantıksal İşleci', 'not, True/False değerini tersine çevirir.', 'not'),
  S('in İşleci', 'in, bir değerin metin ya da liste içinde olup olmadığını söyler.', 'in'),
  S('if Koşulu', 'if koşul doğruysa içindeki girintili kod çalışır.', 'if1'),
  S('if / else', 'Koşul doğruysa if bloğu, değilse else bloğu çalışır.', 'ifelse'),
  S('min, max, sum', 'max en büyüğü, min en küçüğü, sum toplamı verir.', 'minmax'),
];
const MEDIUM = [
  S('elif Zinciri', 'İlk doğru koşulun bloğu çalışır, diğer dallar atlanır.', 'elif'),
  S('İç İçe Koşul', 'Bir if bloğunun içinde başka bir if olabilir.', 'nestif'),
  S('Koşullarda and', 'if içinde and kullanarak birden çok şartı birlikte kontrol edebilirsin.', 'andif'),
  S('for ve range(n)', 'range(n) 0’dan n-1’e kadar sayar; for her sayı için bloğu çalıştırır.', 'forsum'),
  S('range(başla, bitir)', 'range(a, b): a dahil, b hariç sayılar üretir.', 'rangeab'),
  S('range ile Adım', 'range(a, b, s) s’er s’er sayar.', 'rangestep'),
  S('Liste Üzerinde for', 'for x in liste: her eleman için bloğu çalıştırır.', 'forlist'),
  S('while Döngüsü', 'while koşul doğru olduğu sürece bloğu tekrarlar.', 'whilecnt'),
  S('while ile Toplam', 'while ile sayaç artırarak toplam biriktirebilirsin.', 'whilesum'),
  S('break', 'break döngüyü hemen bitirir.', 'brk'),
  S('continue', 'continue o turu atlayıp bir sonrakine geçer.', 'cont'),
  S('Döngüde Koşul ve Sayaç', 'Döngü içinde if ile koşullu sayma yapılabilir.', 'counteven'),
  S('Döngü ile Metin Oluşturma', 'Döngüyle bir metni karakter karakter inşa edebilirsin.', 'strloop'),
  S('Fonksiyon Tanımlama', 'def ile fonksiyon yazılır; return sonucu geri verir.', 'def1'),
  S('Parametreli Fonksiyon', 'Fonksiyon aldığı değerle hesap yapıp sonucu döndürür.', 'def2'),
  S('Varsayılan Parametre', 'Parametreye varsayılan değer verirsen çağrıda yazmak zorunda kalmazsın.', 'defdef'),
  S('Fonksiyon ve Koşul', 'Fonksiyon içinde if ile farklı sonuçlar döndürebilirsin.', 'defif'),
  S('Fonksiyon Sonuçlarını Kullanma', 'Fonksiyon sonucu bir ifadenin parçası olabilir.', 'defsum'),
  S('Liste Dilimleme', 'liste[a:b] a’dan b’ye (b hariç) alt liste verir.', 'lslice'),
  S('Liste Metotları', 'append ekler, pop sondakini siler, insert araya ekler.', 'lmeth'),
  S('Sıralama', 'sorted() yeni sıralı liste verir; sort() listeyi yerinde sıralar.', 'lsort'),
  S('En Büyüğü Bulma', 'Döngüyle her elemanı mevcut en büyükle kıyaslayarak maksimumu bulabilirsin.', 'findmax'),
  S('Metin Metotları', 'count, replace, find, startswith gibi metotlar metni inceler.', 'strmeth'),
  S('split ve join', 'split metni listeye böler, join listeyi metne birleştirir.', 'splitjoin'),
  S('Sözlük Erişimi', 'Sözlük anahtar: değer çiftleri tutar; d["a"] ile değere ulaşılır.', 'dictget'),
  S('Sözlüğü Güncelleme', 'd[anahtar] = değer ile ekleme/güncelleme yapılır; in anahtarı sorgular.', 'dictupd'),
  S('Sözlük Değerleri', 'values() değerleri, keys() anahtarları verir.', 'dictvals'),
  S('Karma Mantık İfadeleri', 'Parantezler ve not/and/or birlikte kullanılabilir.', 'boolmix'),
  S('Değişken Kapsamı', 'Fonksiyonun içinde tanımlanan değişken yereldir, dışarıyı etkilemez.', 'scope'),
  S('Liste Üreteci', '[ifade for x in ...] tek satırda liste üretir.', 'listcomp'),
  S('zip ile Eşleştirme', 'zip iki listeyi eleman eleman eşleştirir.', 'zip'),
  S('f-string', 'f"..." içinde {ifade} yazarak değerleri metne yerleştirirsin.', 'fstr'),
  S('Çarpım Biriktirme', 'Döngüde değişkeni çarparak faktöriyel gibi sonuçlar hesaplanır.', 'fact'),
];
const HARD = [
  S('Koşul Akışını İzleme', 'Kodu satır satır izle: iç koşul yalnızca dış koşul doğruysa denenir.', 'nested'),
  S('İç İçe Döngü Sayısı', 'İç döngü her dış turda baştan çalışır: toplam tur = dış × iç.', 'nfor'),
  S('İç İçe Döngü Toplamı', 'İç içe döngülerde i ve j birlikte değişir; her çiftte işlem yapılır.', 'nfor2'),
  S('Döngü, Koşul ve break', 'break döngüyü durdurduğunda değişkenin son değeri kalır.', 'brkcond'),
  S('Basamak Toplamı', '% 10 son basamağı, // 10 son basamağı atar.', 'digits'),
  S('Fonksiyon + Döngü', 'Döngüyü fonksiyonun içine koyarak yeniden kullanılabilir hesaplar yaparsın.', 'deffor'),
  S('Fonksiyon + Liste', 'Fonksiyona liste verip içinde dolaşabilirsin.', 'defmax'),
  S('Özyineleme: Faktöriyel', 'Fonksiyon kendini küçük bir değerle çağırır; durma koşulu şarttır.', 'recfact'),
  S('Özyineleme: Toplam', 'n + toplam(n-1) mantığıyla özyinelemeli toplama.', 'recsum'),
  S('Özyineleme: Fibonacci', 'fib(n) = fib(n-1) + fib(n-2); ilk iki terim 0 ve 1’dir.', 'fib'),
  S('Koşullu Liste Üreteci', 'Liste üretecine if ekleyerek sadece uygun elemanları alabilirsin.', 'compcond'),
  S('Matris (Liste İçinde Liste)', 'm[satır][sütun] ile iç içe listenin elemanına ulaşılır.', 'matrix'),
  S('Adımlı Dilimleme', '[::2] bir atlar, [::-1] ters çevirir.', 'step'),
  S('Sözlükle Sayma', 'd.get(k, 0) + 1 deseni, her öğenin kaç kez geçtiğini sayar.', 'dictcount'),
  S('get ile Varsayılan Değer', 'd.get(anahtar, varsayılan) anahtar yoksa hata vermez.', 'dictgetdef'),
  S('Palindrom Kontrolü', 'Bir metin tersine eşitse palindromdur.', 'palin'),
  S('Fonksiyon Zinciri', 'f(g(x)) içte g, sonra f çalışır; sıra sonucu değiştirir.', 'compos'),
  S('global Değişken', 'global bildirimi fonksiyonun dış değişkeni değiştirmesini sağlar.', 'glob'),
  S('Değer Takası', 'İki değişkenin yerini geçici değişkenle ya da a, b = b, a ile değiştirirsin.', 'swap'),
  S('Sıralama ve İndeks', 'sorted ve index birlikte kullanılabilir.', 'sortidx'),
  S('Küme (set)', 'set tekrarlı elemanları tek tek tutar.', 'set'),
  S('Tuple', 'Tuple değişmez bir listedir; x, y, z = t ile açılır.', 'tuple'),
  S('lambda ve map', 'lambda kısa fonksiyon yazar; map her elemana uygular.', 'lambda'),
  S('Üreteç ile Toplam', 'sum(x for ... if ...) koşullu toplamı tek satırda hesaplar.', 'gensum'),
  S('try / except', 'Hata oluşabilecek kod try içine yazılır; hata olursa except çalışır.', 'tryex'),
  S('join ile Sayıları Birleştirme', 'str() ile çevirip join ile tek metin yapabilirsin.', 'joinrange'),
  S('Sayıyı Ters Çevirme', 'while ile basamakları sondan alıp r * 10 + basamak diyerek ters sayı kurulur.', 'revnum'),
  S('Asal Sayı Algoritması', 'Bir sayı 2’den n-1’e kadar hiçbir sayıya bölünmüyorsa asaldır.', 'prime'),
  S('Ortalama Hesabı', 'Ortalama = toplam / eleman sayısı.', 'avg'),
  S('Listeyi Ters Çevirme', '[::-1] veya reverse() ile liste tersine döner.', 'revlist'),
  S('Kelime Sayma', 'split() kelimelere böler; len ve count ile sayılır.', 'wordcount'),
  S('Karma Problem 1', 'Fonksiyon, liste ve koşulu birleştir: çift sayıları ayıklayan fonksiyon.', 'mix1'),
  S('Karma Problem 2 (Final)', 'Bölenleri bulan fonksiyon: bir sayının tam böleni 0 kalan bırakır.', 'mix2'),
];
if (EASY.length + MEDIUM.length + HARD.length !== 100) throw new Error(`Bölüm sayısı ${EASY.length + MEDIUM.length + HARD.length}`);

function buildSection(spec, gi, group, no) {
  const used = new Set(), questions = [];
  for (let attempt = 0; questions.length < 3 && attempt < 60; attempt++) {
    const r = RNG(7919 * gi + 104729 * attempt + 13);
    const item = G[spec.gen](r, gi / 100);
    if (!item || used.has(item.code)) continue;
    if (item.options.length !== 7 || new Set(item.options).size !== 7 || !item.options.includes(item.answer)) throw new Error(`Seçenek hatası: ${spec.gen} ${JSON.stringify(item)}`);
    used.add(item.code); questions.push(item);
  }
  if (questions.length < 3) throw new Error('Yeterli soru çeşitliliği yok: ' + spec.gen);
  return { id: `${group}_${no}`, title: spec.title, lesson: spec.lesson, questions };
}
const out = { easy: [], medium: [], hard: [] };
let gi = 0;
for (const [group, list] of [['easy', EASY], ['medium', MEDIUM], ['hard', HARD]]) list.forEach((spec, i) => { out[group].push(buildSection(spec, ++gi, group === 'easy' ? 'easy' : group === 'medium' ? 'mid' : 'hard', i + 1)); });

const js = `/* Python Quiz Lab — 100 bölümlük müfredat (otomatik üretildi: tools/learn-levels/silent-teacher.gen.mjs, elle düzenlemeyin). */\nconst CURRICULUM = ${JSON.stringify(out, null, 1)};\n`;
fs.writeFileSync(OUT, js);
console.log(`100 bölüm, ${100 * 3} soru -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
