/* Excel hesaplama motoru: biçimlendirici, ayrıştırıcı, fonksiyonlar (TR + EN adlar) */
(function (g) {
  'use strict';
  function FErr(code) { this.code = code; }
  FErr.prototype.toString = function () { return this.code; };
  var E = { VAL: '#DEĞER!', DIV: '#SIFIRA_BÖL!', NAME: '#AD?', REF: '#BAŞV!', NUM: '#SAYI!', NA: '#YOK', CYC: '#DÖNGÜ!', SYN: '#SÖZDİZİMİ!' };
  function err(c) { return new FErr(c); }

  function colName(i) { var s = ''; i++; while (i > 0) { var m = (i - 1) % 26; s = String.fromCharCode(65 + m) + s; i = Math.floor((i - 1) / 26); } return s; }
  function colIndex(s) { s = s.toUpperCase(); var n = 0; for (var i = 0; i < s.length; i++) n = n * 26 + (s.charCodeAt(i) - 64); return n - 1; }
  function addr(r, c) { return colName(c) + (r + 1); }
  function parseAddr(a) { var m = /^\$?([A-Za-z]{1,3})\$?(\d+)$/.exec(a); return m ? { r: +m[2] - 1, c: colIndex(m[1]) } : null; }
  function norm(s) { return String(s).replace(/İ/g, 'I').replace(/ı/g, 'I').toUpperCase().replace(/İ/g, 'I'); }

  /* ---------- ayrıştırıcı ---------- */
  var ID = '[A-Za-zÇĞİÖŞÜçğıöşü_][A-Za-z0-9ÇĞİÖŞÜçğıöşü._]*';
  var reTok = new RegExp(
    '^(?:' +
    '(\\s+)|' +                                         // 1 boşluk
    '("(?:[^"]|"")*")|' +                               // 2 metin
    '(\\d+\\.?\\d*(?:[eE][+-]?\\d+)?|\\.\\d+)|' +       // 3 sayı
    '((?:\'[^\']+\'|' + ID + ')!)?(\\$?[A-Za-z]{1,3}\\$?\\d+)(?![A-Za-z0-9ÇĞİÖŞÜçğıöşü._(])|' + // 4 sayfa, 5 hücre
    '(' + ID + ')|' +                                   // 6 ad
    '(<=|>=|<>|[=<>+\\-*/^&%(),;:])' +                  // 7 işleç
    ')');
  function tokenize(src) {
    var out = [], s = src;
    while (s.length) {
      var m = reTok.exec(s);
      if (!m) throw err(E.SYN);
      if (m[1]) { }
      else if (m[2]) out.push({ t: 'str', v: m[2].slice(1, -1).replace(/""/g, '"') });
      else if (m[3]) out.push({ t: 'num', v: parseFloat(m[3]) });
      else if (m[5]) out.push({ t: 'ref', sheet: m[4] ? m[4].slice(0, -1).replace(/^'|'$/g, '') : null, a: m[5] });
      else if (m[6]) out.push({ t: 'name', v: m[6] });
      else out.push({ t: 'op', v: m[7] === ';' ? ',' : m[7] });
      s = s.slice(m[0].length);
    }
    return out;
  }
  function parse(src) {
    var t = tokenize(src), p = 0;
    function peek() { return t[p]; }
    function isOp(v) { return t[p] && t[p].t === 'op' && t[p].v === v; }
    function expr() { return cmp(); }
    function cmp() {
      var l = cat();
      while (t[p] && t[p].t === 'op' && /^(=|<>|<=|>=|<|>)$/.test(t[p].v)) { var o = t[p++].v; l = { k: 'bin', o: o, l: l, r: cat() }; }
      return l;
    }
    function cat() { var l = add(); while (isOp('&')) { p++; l = { k: 'bin', o: '&', l: l, r: add() }; } return l; }
    function add() { var l = mul(); while (isOp('+') || isOp('-')) { var o = t[p++].v; l = { k: 'bin', o: o, l: l, r: mul() }; } return l; }
    function mul() { var l = pow(); while (isOp('*') || isOp('/')) { var o = t[p++].v; l = { k: 'bin', o: o, l: l, r: pow() }; } return l; }
    function pow() { var l = unary(); while (isOp('^')) { p++; l = { k: 'bin', o: '^', l: l, r: unary() }; } return l; }
    function unary() {
      if (isOp('-')) { p++; return { k: 'neg', e: unary() }; }
      if (isOp('+')) { p++; return unary(); }
      var e = prim();
      while (isOp('%')) { p++; e = { k: 'bin', o: '/', l: e, r: { k: 'lit', v: 100 } }; }
      return e;
    }
    function prim() {
      var k = t[p++];
      if (!k) throw err(E.SYN);
      if (k.t === 'num' || k.t === 'str') return { k: 'lit', v: k.v };
      if (k.t === 'ref') {
        var a = parseAddr(k.a);
        if (isOp(':') && t[p + 1] && t[p + 1].t === 'ref') {
          p++; var k2 = t[p++], b = parseAddr(k2.a);
          return { k: 'range', sheet: k.sheet, r1: Math.min(a.r, b.r), c1: Math.min(a.c, b.c), r2: Math.max(a.r, b.r), c2: Math.max(a.c, b.c) };
        }
        return { k: 'ref', sheet: k.sheet, r: a.r, c: a.c };
      }
      if (k.t === 'name') {
        if (isOp('(')) {
          p++; var args = [];
          if (isOp(')')) p++;
          else {
            for (;;) {
              if (isOp(',') || isOp(')')) args.push({ k: 'lit', v: null }); else args.push(expr());
              if (isOp(',')) { p++; continue; }
              if (isOp(')')) { p++; break; }
              throw err(E.SYN);
            }
          }
          return { k: 'fn', n: norm(k.v), args: args };
        }
        var n = norm(k.v);
        if (n === 'TRUE' || n === 'DOGRU') return { k: 'lit', v: true };
        if (n === 'FALSE' || n === 'YANLIS' || n === 'YANLIŞ') return { k: 'lit', v: false };
        throw err(E.NAME);
      }
      if (k.t === 'op' && k.v === '(') { var e = expr(); if (!isOp(')')) throw err(E.SYN); p++; return e; }
      throw err(E.SYN);
    }
    var ast = expr();
    if (p < t.length) throw err(E.SYN);
    return ast;
  }

  /* ---------- yardımcılar ---------- */
  function isArr(v) { return Array.isArray(v); }
  function scalar(v) { while (isArr(v)) v = v[0] ? v[0][0] : null; return v == null ? null : v; }
  function toNum(v) {
    v = scalar(v);
    if (v == null || v === '') return 0;
    if (typeof v === 'number') return v;
    if (typeof v === 'boolean') return v ? 1 : 0;
    var s = String(v).trim().replace(',', '.');
    if (/^[+-]?(\d+\.?\d*|\.\d+)(e[+-]?\d+)?$/i.test(s)) return parseFloat(s);
    throw err(E.VAL);
  }
  function toStr(v) {
    v = scalar(v);
    if (v == null) return '';
    if (typeof v === 'boolean') return v ? 'DOĞRU' : 'YANLIŞ';
    if (typeof v === 'number') return String(parseFloat(v.toPrecision(12)));
    return String(v);
  }
  function toBool(v) {
    v = scalar(v);
    if (typeof v === 'boolean') return v;
    if (v == null || v === '') return false;
    if (typeof v === 'number') return v !== 0;
    var n = norm(v);
    if (n === 'TRUE' || n === 'DOGRU') return true;
    if (n === 'FALSE' || n === 'YANLIS') return false;
    throw err(E.VAL);
  }
  function flat(args) {
    var out = [];
    (function rec(a) { a.forEach(function (x) { if (isArr(x)) rec(x); else out.push(x); }); })(args);
    return out;
  }
  // Aralıktaki sayılar (metin/boş atlanır); doğrudan verilen argümanlar zorlanır
  function numbers(args) {
    var out = [];
    args.forEach(function (a) {
      if (isArr(a)) flat([a]).forEach(function (x) { if (typeof x === 'number') out.push(x); });
      else if (a != null) out.push(toNum(a));
    });
    return out;
  }
  function cmpVals(a, b) {
    a = scalar(a); b = scalar(b);
    if (a == null) a = typeof b === 'string' ? '' : 0;
    if (b == null) b = typeof a === 'string' ? '' : 0;
    if (typeof a === 'number' && typeof b === 'number') return a < b ? -1 : a > b ? 1 : 0;
    if (typeof a === 'string' && typeof b === 'string') { var x = a.toLocaleLowerCase('tr'), y = b.toLocaleLowerCase('tr'); return x < y ? -1 : x > y ? 1 : 0; }
    if (typeof a === 'boolean' && typeof b === 'boolean') return a === b ? 0 : a ? 1 : -1;
    var rank = function (v) { return typeof v === 'number' ? 0 : typeof v === 'string' ? 1 : 2; };
    return rank(a) < rank(b) ? -1 : 1;
  }
  function matcher(crit) {
    crit = scalar(crit);
    var op = '=', val = crit;
    if (typeof crit === 'string') {
      var m = /^(<=|>=|<>|=|<|>)?(.*)$/.exec(crit);
      op = m[1] || '='; val = m[2];
      if (val !== '' && !isNaN(parseFloat(val.replace(',', '.'))) && /^[+-]?[\d.,]+$/.test(val)) val = parseFloat(val.replace(',', '.'));
    }
    return function (x) {
      x = scalar(x);
      if (typeof val === 'string' && /[*?]/.test(val) && (op === '=' || op === '<>')) {
        var re = new RegExp('^' + val.replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*').replace(/\?/g, '.') + '$', 'i');
        var r = typeof x === 'string' && re.test(x); return op === '=' ? r : !r;
      }
      if (x == null) x = typeof val === 'number' ? null : '';
      if (x == null) return op === '<>';
      if (typeof val === 'number' && typeof x !== 'number') return op === '<>';
      if (typeof val === 'string' && typeof x !== 'string') return op === '<>' ? true : false;
      var c = cmpVals(x, val);
      switch (op) { case '=': return c === 0; case '<>': return c !== 0; case '<': return c < 0; case '>': return c > 0; case '<=': return c <= 0; default: return c >= 0; }
    };
  }
  function serial(d) { return Math.floor((Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()) - Date.UTC(1899, 11, 30)) / 864e5); }
  function fromSerial(n) { return new Date(Date.UTC(1899, 11, 30) + Math.floor(n) * 864e5); }

  /* ---------- fonksiyonlar ---------- */
  var F = {}, lazy = {};
  function def(names, fn, isLazy) { names.split('|').forEach(function (n) { (isLazy ? lazy : F)[norm(n)] = fn; }); }
  function need(a, n) { if (a.length < n) throw err(E.VAL); }

  def('SUM|TOPLA', function (a) { return numbers(a).reduce(function (x, y) { return x + y; }, 0); });
  def('AVERAGE|ORTALAMA', function (a) { var n = numbers(a); if (!n.length) throw err(E.DIV); return n.reduce(function (x, y) { return x + y; }, 0) / n.length; });
  def('MIN|MİN', function (a) { var n = numbers(a); return n.length ? Math.min.apply(null, n) : 0; });
  def('MAX|MAK|MAKS', function (a) { var n = numbers(a); return n.length ? Math.max.apply(null, n) : 0; });
  def('COUNT|BAĞ_DEĞ_SAY|BAG_DEG_SAY', function (a) { var c = 0; a.forEach(function (x) { if (isArr(x)) flat([x]).forEach(function (y) { if (typeof y === 'number') c++; }); else if (x != null && !isNaN(parseFloat(x))) c++; }); return c; });
  def('COUNTA|BAĞ_DEĞ_DOLU_SAY|DOLUSAY', function (a) { return flat(a).filter(function (x) { return x != null && x !== ''; }).length; });
  def('COUNTBLANK|BOŞLUKSAY', function (a) { return flat(a).filter(function (x) { return x == null || x === ''; }).length; });
  def('PRODUCT|ÇARPIM', function (a) { return numbers(a).reduce(function (x, y) { return x * y; }, 1); });
  def('MEDIAN|ORTANCA', function (a) { var n = numbers(a).sort(function (x, y) { return x - y; }); if (!n.length) throw err(E.NUM); var m = n.length >> 1; return n.length % 2 ? n[m] : (n[m - 1] + n[m]) / 2; });
  def('MODE|ENÇOK_OLAN', function (a) { var n = numbers(a), cnt = {}, best = null, bc = 1; n.forEach(function (x) { cnt[x] = (cnt[x] || 0) + 1; if (cnt[x] > bc) { bc = cnt[x]; best = x; } }); if (best == null) throw err(E.NA); return best; });
  def('STDEV|STDSAPMA', function (a) { var n = numbers(a); if (n.length < 2) throw err(E.DIV); var m = n.reduce(function (x, y) { return x + y; }, 0) / n.length; return Math.sqrt(n.reduce(function (s, x) { return s + (x - m) * (x - m); }, 0) / (n.length - 1)); });
  def('LARGE|BÜYÜK', function (a) { var n = numbers([a[0]]).sort(function (x, y) { return y - x; }), k = toNum(a[1]); if (k < 1 || k > n.length) throw err(E.NUM); return n[k - 1]; });
  def('SMALL|KÜÇÜK', function (a) { var n = numbers([a[0]]).sort(function (x, y) { return x - y; }), k = toNum(a[1]); if (k < 1 || k > n.length) throw err(E.NUM); return n[k - 1]; });
  def('RANK|SIRA', function (a) { var x = toNum(a[0]), n = numbers([a[1]]), asc = a[2] != null && toBool(a[2]); if (n.indexOf(x) < 0) throw err(E.NA); return 1 + n.filter(function (y) { return asc ? y < x : y > x; }).length; });
  def('SUMPRODUCT|TOPLA.ÇARPIM', function (a) { need(a, 1); var arrs = a.map(function (x) { return flat([x]); }), len = arrs[0].length, s = 0; for (var i = 0; i < len; i++) { var p = 1; arrs.forEach(function (ar) { p *= typeof ar[i] === 'number' ? ar[i] : 0; }); s += p; } return s; });

  def('ABS|MUTLAK', function (a) { return Math.abs(toNum(a[0])); });
  def('SQRT|KAREKÖK', function (a) { var x = toNum(a[0]); if (x < 0) throw err(E.NUM); return Math.sqrt(x); });
  def('POWER|KUVVET', function (a) { return Math.pow(toNum(a[0]), toNum(a[1])); });
  def('MOD', function (a) { var d = toNum(a[1]); if (!d) throw err(E.DIV); var n = toNum(a[0]); return n - d * Math.floor(n / d); });
  def('INT|TAMSAYI', function (a) { return Math.floor(toNum(a[0])); });
  def('TRUNC|NSAT', function (a) { var d = a[1] == null ? 0 : toNum(a[1]), f = Math.pow(10, d); return Math.trunc(toNum(a[0]) * f) / f; });
  def('SIGN|İŞARET', function (a) { return Math.sign(toNum(a[0])); });
  def('PI', function () { return Math.PI; });
  def('EXP', function (a) { return Math.exp(toNum(a[0])); });
  def('LN', function (a) { var x = toNum(a[0]); if (x <= 0) throw err(E.NUM); return Math.log(x); });
  def('LOG10', function (a) { var x = toNum(a[0]); if (x <= 0) throw err(E.NUM); return Math.log10(x); });
  def('LOG', function (a) { var x = toNum(a[0]), b = a[1] == null ? 10 : toNum(a[1]); if (x <= 0 || b <= 0) throw err(E.NUM); return Math.log(x) / Math.log(b); });
  def('SIN', function (a) { return Math.sin(toNum(a[0])); });
  def('COS', function (a) { return Math.cos(toNum(a[0])); });
  def('TAN', function (a) { return Math.tan(toNum(a[0])); });
  def('RADIANS|RADYAN', function (a) { return toNum(a[0]) * Math.PI / 180; });
  def('DEGREES|DERECE', function (a) { return toNum(a[0]) * 180 / Math.PI; });
  def('FACT|ÇARPINIM', function (a) { var n = Math.floor(toNum(a[0])), r = 1; if (n < 0) throw err(E.NUM); for (var i = 2; i <= n; i++) r *= i; return r; });
  function round(x, d, mode) { var f = Math.pow(10, d), v = x * f; v = mode === 'up' ? (v < 0 ? -Math.ceil(-v - 1e-12) : Math.ceil(v - 1e-12)) : mode === 'down' ? Math.trunc(v) : (v < 0 ? -Math.round(-v + 1e-12) : Math.round(v + 1e-12)); return v / f; }
  def('ROUND|YUVARLA', function (a) { return round(toNum(a[0]), a[1] == null ? 0 : toNum(a[1])); });
  def('ROUNDUP|YUKARIYUVARLA', function (a) { return round(toNum(a[0]), a[1] == null ? 0 : toNum(a[1]), 'up'); });
  def('ROUNDDOWN|AŞAĞIYUVARLA', function (a) { return round(toNum(a[0]), a[1] == null ? 0 : toNum(a[1]), 'down'); });
  def('RAND|RASTGELE', function () { return Math.random(); });
  def('RANDBETWEEN|RASTGELEARALIK', function (a) { var lo = Math.ceil(toNum(a[0])), hi = Math.floor(toNum(a[1])); return lo + Math.floor(Math.random() * (hi - lo + 1)); });

  def('AND|VE', function (a) { var r = true; flat(a).forEach(function (x) { if (x != null && x !== '' && !toBool(x)) r = false; }); return r; });
  def('OR|YADA', function (a) { var r = false; flat(a).forEach(function (x) { if (x != null && x !== '' && toBool(x)) r = true; }); return r; });
  def('NOT|DEĞİL', function (a) { return !toBool(a[0]); });
  def('XOR|ÖZELVEYA', function (a) { var c = 0; flat(a).forEach(function (x) { if (toBool(x)) c++; }); return c % 2 === 1; });
  def('TRUE|DOĞRU', function () { return true; });
  def('FALSE|YANLIŞ', function () { return false; });
  def('ISNUMBER|SAYIYSA|ESAYIYSA', function (a) { return typeof scalar(a[0]) === 'number'; });
  def('ISTEXT|METİNSE|EMETİNSE', function (a) { return typeof scalar(a[0]) === 'string'; });
  def('ISBLANK|BOŞSA|EBOŞSA', function (a) { var v = scalar(a[0]); return v == null || v === ''; });
  def('ISNA|YOKSA|EYOKSA', function (a) { return false; });

  def('IF|EĞER', function (a, ev) {
    need(a, 2);
    return toBool(ev(a[0])) ? ev(a[1]) : (a.length > 2 ? ev(a[2]) : false);
  }, true);
  def('IFS|ÇOKEĞER', function (a, ev) {
    for (var i = 0; i + 1 < a.length; i += 2) if (toBool(ev(a[i]))) return ev(a[i + 1]);
    throw err(E.NA);
  }, true);
  def('IFERROR|EĞERHATA', function (a, ev) {
    need(a, 2);
    try { var v = ev(a[0]); if (v instanceof FErr) return ev(a[1]); return v; } catch (e) { if (e instanceof FErr) return ev(a[1]); throw e; }
  }, true);
  def('ISERROR|EHATALIYSA|HATALIYSA', function (a, ev) { try { var v = ev(a[0]); return v instanceof FErr; } catch (e) { if (e instanceof FErr) return true; throw e; } }, true);
  def('CHOOSE|ELEÇ', function (a) { var i = Math.floor(toNum(a[0])); if (i < 1 || i >= a.length) throw err(E.VAL); return a[i]; });

  function condRange(a, i) { return flat([a[i]]); }
  def('SUMIF|ETOPLA|EĞERTOPLA', function (a) {
    need(a, 2); var m = matcher(a[1]), rng = condRange(a, 0), sum = a[2] != null ? condRange(a, 2) : rng, s = 0;
    rng.forEach(function (x, i) { if (m(x) && typeof sum[i] === 'number') s += sum[i]; }); return s;
  });
  def('COUNTIF|EĞERSAY', function (a) { need(a, 2); var m = matcher(a[1]); return condRange(a, 0).filter(m).length; });
  def('AVERAGEIF|ORTALAMAEĞER', function (a) {
    need(a, 2); var m = matcher(a[1]), rng = condRange(a, 0), av = a[2] != null ? condRange(a, 2) : rng, s = 0, c = 0;
    rng.forEach(function (x, i) { if (m(x) && typeof av[i] === 'number') { s += av[i]; c++; } }); if (!c) throw err(E.DIV); return s / c;
  });
  function multiCrit(a, start) {
    var pairs = []; for (var i = start; i + 1 < a.length; i += 2) pairs.push([flat([a[i]]), matcher(a[i + 1])]);
    return function (idx) { return pairs.every(function (p) { return p[1](p[0][idx]); }); };
  }
  def('SUMIFS|ÇOKETOPLA', function (a) { var sum = flat([a[0]]), ok = multiCrit(a, 1), s = 0; sum.forEach(function (x, i) { if (ok(i) && typeof x === 'number') s += x; }); return s; });
  def('COUNTIFS|ÇOKEĞERSAY', function (a) { var ok = multiCrit(a, 0), n = flat([a[0]]).length, c = 0; for (var i = 0; i < n; i++) if (ok(i)) c++; return c; });

  def('CONCATENATE|CONCAT|BİRLEŞTİR', function (a) { return flat(a).map(toStr).join(''); });
  def('LEN|UZUNLUK', function (a) { return toStr(a[0]).length; });
  def('UPPER|BÜYÜKHARF', function (a) { return toStr(a[0]).toLocaleUpperCase('tr'); });
  def('LOWER|KÜÇÜKHARF', function (a) { return toStr(a[0]).toLocaleLowerCase('tr'); });
  def('PROPER|YAZIM.DÜZENİ', function (a) { return toStr(a[0]).toLocaleLowerCase('tr').replace(/(^|[^A-Za-zÇĞİÖŞÜçğıöşü])([A-Za-zÇĞİÖŞÜçğıöşü])/g, function (m, p, c) { return p + c.toLocaleUpperCase('tr'); }); });
  def('LEFT|SOL', function (a) { return toStr(a[0]).slice(0, a[1] == null ? 1 : toNum(a[1])); });
  def('RIGHT|SAĞ', function (a) { var s = toStr(a[0]), n = a[1] == null ? 1 : toNum(a[1]); return n ? s.slice(-n) : ''; });
  def('MID|PARÇAAL', function (a) { return toStr(a[0]).substr(toNum(a[1]) - 1, toNum(a[2])); });
  def('TRIM|KIRP', function (a) { return toStr(a[0]).trim().replace(/\s+/g, ' '); });
  def('SUBSTITUTE|YERİNEKOY', function (a) { return toStr(a[0]).split(toStr(a[1])).join(toStr(a[2])); });
  def('REPT|YİNELE', function (a) { return new Array(Math.max(0, toNum(a[1])) + 1).join(toStr(a[0])); });
  def('FIND|BUL', function (a) { var i = toStr(a[1]).indexOf(toStr(a[0]), a[2] == null ? 0 : toNum(a[2]) - 1); if (i < 0) throw err(E.VAL); return i + 1; });
  def('SEARCH|MBUL', function (a) { var i = toStr(a[1]).toLocaleLowerCase('tr').indexOf(toStr(a[0]).toLocaleLowerCase('tr'), a[2] == null ? 0 : toNum(a[2]) - 1); if (i < 0) throw err(E.VAL); return i + 1; });
  def('VALUE|SAYIYAÇEVİR|DEĞER', function (a) { return toNum(a[0]); });
  def('TEXT|METNEÇEVİR', function (a) { return g.XL.formatNumber(toNum(a[0]), 'num', a[1] != null && /\.(0+)/.test(toStr(a[1])) ? /\.(0+)/.exec(toStr(a[1]))[1].length : 0); });
  def('EXACT|ÖZDEŞ', function (a) { return toStr(a[0]) === toStr(a[1]); });

  def('VLOOKUP|DÜŞEYARA', function (a) {
    need(a, 3); var key = scalar(a[0]), tbl = a[1], col = Math.floor(toNum(a[2])), approx = a[3] == null ? true : toBool(a[3]);
    if (!isArr(tbl)) throw err(E.VAL); if (col < 1 || col > tbl[0].length) throw err(E.REF);
    if (!approx) { for (var i = 0; i < tbl.length; i++) if (cmpVals(tbl[i][0], key) === 0 && tbl[i][0] != null) return tbl[i][col - 1]; throw err(E.NA); }
    var best = -1; for (var j = 0; j < tbl.length; j++) { if (cmpVals(tbl[j][0], key) <= 0 && tbl[j][0] != null) best = j; else break; }
    if (best < 0) throw err(E.NA); return tbl[best][col - 1];
  });
  def('HLOOKUP|YATAYARA', function (a) {
    need(a, 3); var key = scalar(a[0]), tbl = a[1], row = Math.floor(toNum(a[2])), approx = a[3] == null ? true : toBool(a[3]);
    if (!isArr(tbl)) throw err(E.VAL); if (row < 1 || row > tbl.length) throw err(E.REF);
    var first = tbl[0];
    if (!approx) { for (var i = 0; i < first.length; i++) if (first[i] != null && cmpVals(first[i], key) === 0) return tbl[row - 1][i]; throw err(E.NA); }
    var best = -1; for (var j = 0; j < first.length; j++) { if (first[j] != null && cmpVals(first[j], key) <= 0) best = j; else break; }
    if (best < 0) throw err(E.NA); return tbl[row - 1][best];
  });
  def('INDEX|İNDİS', function (a) {
    var t = a[0]; if (!isArr(t)) return t;
    var r = a[1] == null ? 1 : Math.floor(toNum(a[1])), c = a[2] == null ? 1 : Math.floor(toNum(a[2]));
    if (t.length === 1 && a[2] == null) { c = r; r = 1; }
    if (r < 1 || r > t.length || c < 1 || c > t[0].length) throw err(E.REF); return t[r - 1][c - 1];
  });
  def('MATCH|KAÇINCI', function (a) {
    var key = scalar(a[0]), arr = flat([a[1]]), t = a[2] == null ? 1 : toNum(a[2]);
    if (t === 0) { for (var i = 0; i < arr.length; i++) if (arr[i] != null && cmpVals(arr[i], key) === 0) return i + 1; throw err(E.NA); }
    var best = -1; for (var j = 0; j < arr.length; j++) { var c = cmpVals(arr[j], key); if (arr[j] != null && (t > 0 ? c <= 0 : c >= 0)) best = j; else if (arr[j] != null) break; }
    if (best < 0) throw err(E.NA); return best + 1;
  });

  def('TODAY|BUGÜN', function () { return serial(new Date()); });
  def('NOW|ŞİMDİ', function () { var d = new Date(); return serial(d) + (d.getHours() * 3600 + d.getMinutes() * 60 + d.getSeconds()) / 86400; });
  def('YEAR|YIL', function (a) { return fromSerial(toNum(a[0])).getUTCFullYear(); });
  def('MONTH|AY', function (a) { return fromSerial(toNum(a[0])).getUTCMonth() + 1; });
  def('DAY|GÜN', function (a) { return fromSerial(toNum(a[0])).getUTCDate(); });
  def('DATE|TARİH', function (a) { return serial(new Date(toNum(a[0]), toNum(a[1]) - 1, toNum(a[2]))); });
  def('DAYS|GÜNSAY', function (a) { return toNum(a[0]) - toNum(a[1]); });
  def('WEEKDAY|HAFTANINGÜNÜ', function (a) { var d = fromSerial(toNum(a[0])).getUTCDay(); return a[1] != null && toNum(a[1]) === 2 ? (d === 0 ? 7 : d) : d + 1; });

  /* ---------- değerlendirme ---------- */
  function Calc(getRaw, sheetIndex, nRows, nCols) {
    // getRaw(sheetIdx, r, c) -> ham metin | undefined
    this.getRaw = getRaw; this.sheetIndex = sheetIndex; this.cache = {}; this.busy = {}; this.astCache = {};
  }
  Calc.prototype.reset = function () { this.cache = {}; this.busy = {}; };
  Calc.prototype.literal = function (raw) {
    if (raw == null || raw === '') return null;
    var s = String(raw);
    if (/^[+-]?(\d+[.,]?\d*|[.,]\d+)(e[+-]?\d+)?$/i.test(s.trim())) return parseFloat(s.trim().replace(',', '.'));
    var n = norm(s.trim());
    if (n === 'TRUE' || n === 'DOGRU') return true;
    if (n === 'FALSE' || n === 'YANLIS') return false;
    return s;
  };
  Calc.prototype.cell = function (si, r, c) {
    var key = si + '|' + r + '|' + c;
    if (key in this.cache) return this.cache[key];
    if (this.busy[key]) throw err(E.CYC);
    var raw = this.getRaw(si, r, c), v;
    if (typeof raw === 'string' && raw.charAt(0) === '=' && raw.length > 1) {
      this.busy[key] = 1;
      try { v = this.evalFormula(raw.slice(1), si); }
      catch (e) { if (e instanceof FErr) v = e; else if (e instanceof RangeError) v = err(E.CYC); else throw e; }
      delete this.busy[key];
    } else v = this.literal(raw);
    // hücre bazında önbellekte hata nesnesi tutulur; okuyan taraf fırlatır
    this.cache[key] = v;
    return v;
  };
  Calc.prototype.evalFormula = function (src, si) {
    var ast = this.astCache[src];
    if (!ast) ast = this.astCache[src] = parse(src);
    var v = this.ev(ast, si);
    if (isArr(v)) v = scalar(v);
    if (v instanceof FErr) throw v;
    return v == null ? 0 : v;
  };
  Calc.prototype.sheet = function (name, si) {
    if (name == null) return si;
    var i = this.sheetIndex(name); if (i < 0) throw err(E.REF); return i;
  };
  Calc.prototype.val = function (si, r, c) {
    var v = this.cell(si, r, c);
    if (v instanceof FErr) throw v;
    return v;
  };
  Calc.prototype.ev = function (n, si) {
    var self = this;
    switch (n.k) {
      case 'lit': return n.v;
      case 'neg': return -toNum(this.ev(n.e, si));
      case 'ref': return this.val(this.sheet(n.sheet, si), n.r, n.c);
      case 'range':
        var sh = this.sheet(n.sheet, si), out = [];
        if ((n.r2 - n.r1 + 1) * (n.c2 - n.c1 + 1) > 200000) throw err(E.REF);
        for (var r = n.r1; r <= n.r2; r++) { var row = []; for (var c = n.c1; c <= n.c2; c++) row.push(this.val(sh, r, c)); out.push(row); }
        return out;
      case 'bin':
        var l = this.ev(n.l, si), rr = this.ev(n.r, si);
        switch (n.o) {
          case '+': return toNum(l) + toNum(rr);
          case '-': return toNum(l) - toNum(rr);
          case '*': return toNum(l) * toNum(rr);
          case '/': var d = toNum(rr); if (d === 0) throw err(E.DIV); return toNum(l) / d;
          case '^': var p = Math.pow(toNum(l), toNum(rr)); if (isNaN(p)) throw err(E.NUM); return p;
          case '&': return toStr(l) + toStr(rr);
          case '=': return cmpVals(l, rr) === 0;
          case '<>': return cmpVals(l, rr) !== 0;
          case '<': return cmpVals(l, rr) < 0;
          case '>': return cmpVals(l, rr) > 0;
          case '<=': return cmpVals(l, rr) <= 0;
          case '>=': return cmpVals(l, rr) >= 0;
        }
        throw err(E.SYN);
      case 'fn':
        if (lazy[n.n]) return lazy[n.n](n.args, function (a) { var v = self.ev(a, si); return isArr(v) ? scalar(v) : v; });
        var fn = F[n.n]; if (!fn) throw err(E.NAME);
        // satır/sütun fonksiyonları bağlam ister
        if (n.n === 'SATIR' || n.n === 'ROW') { var a0 = n.args[0]; return a0 && (a0.k === 'ref' || a0.k === 'range') ? (a0.r != null ? a0.r : a0.r1) + 1 : 1; }
        if (n.n === 'SUTUN' || n.n === 'COLUMN') { var b0 = n.args[0]; return b0 && (b0.k === 'ref' || b0.k === 'range') ? (b0.c != null ? b0.c : b0.c1) + 1 : 1; }
        return fn(n.args.map(function (a) { return a.k === 'lit' && a.v === null ? null : self.ev(a, si); }));
    }
  };

  /* ---------- formül kaydırma (doldurma / yapıştırma / satır ekleme) ---------- */
  var reRef = /("(?:[^"]|"")*")|((?:'[^']+'|[A-Za-zÇĞİÖŞÜçğıöşü_][A-Za-z0-9ÇĞİÖŞÜçğıöşü._]*)!)?(\$?)([A-Za-z]{1,3})(\$?)(\d+)(?![A-Za-z0-9ÇĞİÖŞÜçğıöşü._(])/g;
  function shiftFormula(f, dr, dc) {
    return f.replace(reRef, function (m, str, sh, ca, col, ra, row) {
      if (str) return str;
      var c = colIndex(col), r = +row - 1;
      if (!ca) c += dc; if (!ra) r += dr;
      if (c < 0 || r < 0) return '#BAŞV!';
      return (sh || '') + ca + colName(c) + ra + (r + 1);
    });
  }
  // axis: 'r' | 'c'; at: indeks; n: +ekle / -sil; sheetName: değişen sayfa; own: formülün bulunduğu sayfa adı
  function adjustFormula(f, axis, at, n, sheetName, own) {
    var rangeRe = new RegExp('("(?:[^"]|"")*")|((?:\'[^\']+\'|[A-Za-zÇĞİÖŞÜçğıöşü_][A-Za-z0-9ÇĞİÖŞÜçğıöşü._]*)!)?(\\$?)([A-Za-z]{1,3})(\\$?)(\\d+)(?::(\\$?)([A-Za-z]{1,3})(\\$?)(\\d+))?(?![A-Za-z0-9ÇĞİÖŞÜçğıöşü._(])', 'g');
    return f.replace(rangeRe, function (m, str, sh, ca, col, ra, row, ca2, col2, ra2, row2) {
      if (str) return str;
      var target = sh ? sh.slice(0, -1).replace(/^'|'$/g, '') : own;
      if (String(target).toLowerCase() !== String(sheetName).toLowerCase()) return m;
      function mv(idx, isEnd, isRange) {
        if (n > 0) return idx >= at ? idx + n : idx;
        var del = -n;
        if (idx < at) return idx;
        if (idx >= at + del) return idx - del;
        if (!isRange) return -1;
        return isEnd ? at - 1 : at;
      }
      var r1 = +row - 1, c1 = colIndex(col), isR = row2 != null, r2 = isR ? +row2 - 1 : 0, c2 = isR ? colIndex(col2) : 0;
      if (axis === 'r') { r1 = mv(r1, false, isR); if (isR) r2 = mv(r2, true, isR); } else { c1 = mv(c1, false, isR); if (isR) c2 = mv(c2, true, isR); }
      if (r1 < 0 || c1 < 0 || (isR && (r2 < r1 || c2 < c1))) return '#BAŞV!';
      return (sh || '') + ca + colName(c1) + ra + (r1 + 1) + (isR ? ':' + ca2 + colName(c2) + ra2 + (r2 + 1) : '');
    });
  }

  /* ---------- görüntü biçimlendirme ---------- */
  function formatNumber(v, nf, dp) {
    if (nf === 'date') return fromSerial(v).toLocaleDateString('tr-TR', { timeZone: 'UTC' });
    if (nf === 'time') { var fr = v - Math.floor(v), s = Math.round(fr * 86400); return pad(Math.floor(s / 3600) % 24) + ':' + pad(Math.floor(s / 60) % 60) + ':' + pad(s % 60); }
    if (nf === 'datetime') return formatNumber(v, 'date') + ' ' + formatNumber(v, 'time');
    if (nf === 'pct') return (v * 100).toLocaleString('tr-TR', { minimumFractionDigits: dp == null ? 0 : dp, maximumFractionDigits: dp == null ? 0 : dp }) + '%';
    if (nf === 'num' || nf === 'cur') {
      var d = dp == null ? 2 : dp;
      var t = v.toLocaleString('tr-TR', { minimumFractionDigits: d, maximumFractionDigits: d });
      return nf === 'cur' ? t + ' ₺' : t;
    }
    if (nf === 'sci') return v.toExponential(dp == null ? 2 : dp).replace('.', ',');
    var p = parseFloat(v.toPrecision(12));
    var str = Math.abs(p) >= 1e15 || (p !== 0 && Math.abs(p) < 1e-9) ? p.toExponential().replace('.', ',') : String(p).replace('.', ',');
    return str;
  }
  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  var fnNames = ['TOPLA', 'ORTALAMA', 'MİN', 'MAK', 'BAĞ_DEĞ_SAY', 'BAĞ_DEĞ_DOLU_SAY', 'EĞER', 'VE', 'YADA', 'DEĞİL', 'YUVARLA', 'MUTLAK', 'KAREKÖK', 'KUVVET', 'MOD', 'TAMSAYI',
    'ETOPLA', 'EĞERSAY', 'ORTALAMAEĞER', 'ÇOKETOPLA', 'ÇOKEĞERSAY', 'EĞERHATA', 'BİRLEŞTİR', 'UZUNLUK', 'BÜYÜKHARF', 'KÜÇÜKHARF', 'SOL', 'SAĞ', 'PARÇAAL', 'KIRP',
    'DÜŞEYARA', 'YATAYARA', 'İNDİS', 'KAÇINCI', 'BUGÜN', 'ŞİMDİ', 'YIL', 'AY', 'GÜN', 'TARİH', 'ORTANCA', 'ÇARPIM', 'BÜYÜK', 'KÜÇÜK', 'SIRA', 'TOPLA.ÇARPIM', 'RASTGELE', 'RASTGELEARALIK',
    'ÇOKEĞER', 'ELEÇ', 'YERİNEKOY', 'STDSAPMA', 'PI'];

  g.XL = {
    E: E, FErr: FErr, Calc: Calc, parse: parse, colName: colName, colIndex: colIndex, addr: addr, parseAddr: parseAddr,
    shiftFormula: shiftFormula, adjustFormula: adjustFormula, formatNumber: formatNumber, fnNames: fnNames,
    serial: serial, fromSerial: fromSerial, norm: norm, matcher: matcher, cmpVals: cmpVals
  };
})(window);
