/* Flowchart Programlama — motor (saf JS, DOM'dan bağımsız): ifade ayrıştırıcı, yürütücü, doğrulayıcı, otomatik yerleşim, görev denetleyici.
 * Kullanım: tarayıcıda window.FlowEngine, Node'da module.exports. eval/Function KULLANMAZ. */
(function (root) {
  'use strict';

  var MAX_STEPS = 5000;
  var TYPES = ['start', 'end', 'input', 'output', 'process', 'decision'];

  function FlowError(message, nodeId) { var e = new Error(message); e.name = 'FlowError'; e.nodeId = nodeId || null; return e; }

  // ------------------------------------------------------------------ jeton üretici
  var KEYWORDS = { 've': 'and', 'veya': 'or', 'değil': 'not', 'and': 'and', 'or': 'or', 'not': 'not', 'doğru': 'true', 'yanlış': 'false', 'true': 'true', 'false': 'false' };

  function isLetter(ch) { return /[A-Za-z_çğıİöşüÇĞÖŞÜâîûÂÎÛ]/.test(ch); }
  function isDigit(ch) { return ch >= '0' && ch <= '9'; }

  function tokenize(src) {
    var s = String(src), i = 0, out = [];
    while (i < s.length) {
      var ch = s[i];
      if (/\s/.test(ch)) { i++; continue; }
      if (isDigit(ch) || (ch === '.' && isDigit(s[i + 1] || ''))) {
        var j = i, dot = false;
        while (j < s.length && (isDigit(s[j]) || (s[j] === '.' && !dot))) { if (s[j] === '.') dot = true; j++; }
        out.push({ t: 'num', v: parseFloat(s.slice(i, j)), p: i }); i = j; continue;
      }
      if (ch === '"' || ch === "'" || ch === '“' || ch === '”' || ch === '‘' || ch === '’') {
        var q = ch, j2 = i + 1, str = '';
        var closers = { '"': '"', "'": "'", '“': '”', '”': '”', '‘': '’', '’': '’' };
        while (j2 < s.length && s[j2] !== closers[q] && s[j2] !== q) { str += s[j2]; j2++; }
        if (j2 >= s.length) throw FlowError('Metin tırnağı kapatılmamış: ' + s.slice(i));
        out.push({ t: 'str', v: str, p: i }); i = j2 + 1; continue;
      }
      if (isLetter(ch)) {
        var k = i; while (k < s.length && (isLetter(s[k]) || isDigit(s[k]))) k++;
        var word = s.slice(i, k), low = word.toLocaleLowerCase('tr');
        if (KEYWORDS[low]) out.push({ t: KEYWORDS[low] === 'true' || KEYWORDS[low] === 'false' ? 'bool' : 'op', v: KEYWORDS[low] === 'true' ? true : KEYWORDS[low] === 'false' ? false : KEYWORDS[low], p: i });
        else out.push({ t: 'id', v: word, p: i });
        i = k; continue;
      }
      var two = s.substr(i, 2);
      if (two === '**' || two === '==' || two === '!=' || two === '<=' || two === '>=' || two === '&&' || two === '||' || two === '+=' || two === '-=' || two === '*=' || two === '/=' || two === '%=') {
        out.push({ t: 'op', v: two === '&&' ? 'and' : two === '||' ? 'or' : two, p: i }); i += 2; continue;
      }
      if ('+-*/%^<>()=!,'.indexOf(ch) >= 0) {
        out.push({ t: 'op', v: ch === '^' ? '**' : ch === '!' ? 'not' : ch, p: i }); i++; continue;
      }
      throw FlowError('Tanınmayan karakter: "' + ch + '"');
    }
    return out;
  }

  // ------------------------------------------------------------------ ayrıştırıcı (öncelik: veya < ve < değil < karşılaştırma < + - < * / % < tekli - < ** )
  function Parser(tokens) { this.t = tokens; this.i = 0; }
  Parser.prototype.peek = function () { return this.t[this.i]; };
  Parser.prototype.next = function () { return this.t[this.i++]; };
  Parser.prototype.isOp = function (v) { var k = this.peek(); return k && k.t === 'op' && k.v === v; };
  Parser.prototype.expectOp = function (v) { if (!this.isOp(v)) throw FlowError('"' + v + '" bekleniyordu' + (this.peek() ? '' : ' (ifade yarım kaldı)')); return this.next(); };
  Parser.prototype.parseExpr = function () { return this.or(); };
  Parser.prototype.or = function () { var l = this.and(); while (this.isOp('or')) { this.next(); l = { k: 'bin', op: 'or', l: l, r: this.and() }; } return l; };
  Parser.prototype.and = function () { var l = this.not(); while (this.isOp('and')) { this.next(); l = { k: 'bin', op: 'and', l: l, r: this.not() }; } return l; };
  Parser.prototype.not = function () { if (this.isOp('not')) { this.next(); return { k: 'not', e: this.not() }; } return this.cmp(); };
  Parser.prototype.cmp = function () {
    var l = this.add(); var k = this.peek();
    if (k && k.t === 'op' && (k.v === '==' || k.v === '!=' || k.v === '<' || k.v === '>' || k.v === '<=' || k.v === '>=')) { this.next(); return { k: 'bin', op: k.v, l: l, r: this.add() }; }
    if (k && k.t === 'op' && k.v === '=') throw FlowError('Karşılaştırma için "==" kullan ("=" yalnızca İşlem bloğunda atama yapar)');
    return l;
  };
  Parser.prototype.add = function () { var l = this.mul(); while (this.isOp('+') || this.isOp('-')) { var o = this.next().v; l = { k: 'bin', op: o, l: l, r: this.mul() }; } return l; };
  Parser.prototype.mul = function () { var l = this.unary(); while (this.isOp('*') || this.isOp('/') || this.isOp('%')) { var o = this.next().v; l = { k: 'bin', op: o, l: l, r: this.unary() }; } return l; };
  Parser.prototype.unary = function () { if (this.isOp('-')) { this.next(); return { k: 'neg', e: this.unary() }; } if (this.isOp('+')) { this.next(); return this.unary(); } return this.pow(); };
  Parser.prototype.pow = function () { var b = this.atom(); if (this.isOp('**')) { this.next(); return { k: 'bin', op: '**', l: b, r: this.unary() }; } return b; };
  Parser.prototype.atom = function () {
    var k = this.next();
    if (!k) throw FlowError('İfade yarım kaldı');
    if (k.t === 'num' || k.t === 'str' || k.t === 'bool') return { k: 'lit', v: k.v };
    if (k.t === 'id') {
      if (this.isOp('(')) {
        this.next(); var args = [];
        if (!this.isOp(')')) { args.push(this.parseExpr()); while (this.isOp(',')) { this.next(); args.push(this.parseExpr()); } }
        this.expectOp(')');
        return { k: 'call', name: k.v, args: args };
      }
      return { k: 'var', name: k.v };
    }
    if (k.t === 'op' && k.v === '(') { var e = this.parseExpr(); this.expectOp(')'); return e; }
    if (k.t === 'op' && k.v === ')') throw FlowError('Beklenmeyen ")" işareti');
    throw FlowError('Beklenmeyen işaret: "' + (k.v === 'and' ? 've' : k.v === 'or' ? 'veya' : k.v === 'not' ? 'değil' : k.v) + '"');
  };

  function parseExpression(src) {
    var toks = tokenize(src);
    if (!toks.length) throw FlowError('İfade boş');
    var p = new Parser(toks), e = p.parseExpr();
    if (p.peek()) throw FlowError('Beklenmeyen işaret: "' + (p.peek().v) + '"');
    return e;
  }

  // üst düzey (parantez/tırnak dışı) ayırıcıya göre böl
  function splitTop(src, sep) {
    var parts = [], depth = 0, q = null, cur = '';
    for (var i = 0; i < src.length; i++) {
      var ch = src[i];
      if (q) { cur += ch; if (ch === q) q = null; continue; }
      if (ch === '"' || ch === "'") { q = ch; cur += ch; continue; }
      if (ch === '(') depth++;
      if (ch === ')') depth--;
      if (ch === sep && depth === 0) { parts.push(cur); cur = ''; continue; }
      cur += ch;
    }
    parts.push(cur);
    return parts;
  }

  function parseStatements(src) {
    var out = [];
    splitTop(String(src), ';').forEach(function (part) {
      if (!part.trim()) return;
      var toks = tokenize(part);
      if (toks.length < 3 || toks[0].t !== 'id' || toks[1].t !== 'op' || ['=', '+=', '-=', '*=', '/=', '%='].indexOf(toks[1].v) < 0) {
        throw FlowError('Atama biçimi: değişken = ifade   (örnek: toplam = toplam + 1)   — "' + part.trim() + '"');
      }
      var p = new Parser(toks.slice(2)), e = p.parseExpr();
      if (p.peek()) throw FlowError('Beklenmeyen işaret: "' + p.peek().v + '"');
      out.push({ name: toks[0].v, op: toks[1].v, expr: e });
    });
    if (!out.length) throw FlowError('Bu blok boş');
    return out;
  }
  function parseVarList(src) {
    var names = String(src).split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    if (!names.length) throw FlowError('Okunacak değişken adı yaz (örnek: a, b)');
    names.forEach(function (n) { if (!/^[A-Za-z_çğıİöşüÇĞÖŞÜâîûÂÎÛ][A-Za-z0-9_çğıİöşüÇĞÖŞÜâîûÂÎÛ]*$/.test(n) || KEYWORDS[n.toLocaleLowerCase('tr')]) throw FlowError('Geçersiz değişken adı: "' + n + '"'); });
    return names;
  }
  function parseOutputList(src) {
    var parts = splitTop(String(src), ',').map(function (s) { return s.trim(); }).filter(function (s) { return s.length; });
    if (!parts.length) throw FlowError('Yazdırılacak ifadeyi yaz (örnek: "Toplam:", a + b)');
    return parts.map(parseExpression);
  }

  // ------------------------------------------------------------------ değerlendirme
  function fmt(v) {
    if (typeof v === 'number') {
      if (Number.isInteger(v)) return String(v);
      if (!isFinite(v)) return v > 0 ? 'sonsuz' : (v < 0 ? '-sonsuz' : 'tanımsız');
      return String(parseFloat(v.toPrecision(12)));
    }
    if (typeof v === 'boolean') return v ? 'doğru' : 'yanlış';
    return String(v);
  }
  function typeName(v) { return typeof v === 'number' ? 'sayı' : typeof v === 'string' ? 'metin' : 'mantık değeri'; }
  function needNum(v, what) { if (typeof v !== 'number') throw FlowError(what + ' için sayı gerekli, ancak ' + typeName(v) + ' var'); return v; }

  var FUNCS = {
    'karekök': function (a) { var x = needNum(a[0], 'karekök'); if (x < 0) throw FlowError('Negatif sayının karekökü alınamaz'); return Math.sqrt(x); },
    'karekok': function (a) { return FUNCS['karekök'](a); },
    'mutlak': function (a) { return Math.abs(needNum(a[0], 'mutlak')); },
    'yuvarla': function (a) { return Math.round(needNum(a[0], 'yuvarla')); },
    'taban': function (a) { return Math.floor(needNum(a[0], 'taban')); },
    'tavan': function (a) { return Math.ceil(needNum(a[0], 'tavan')); },
    'enküçük': function (a) { return Math.min.apply(null, a.map(function (x) { return needNum(x, 'enküçük'); })); },
    'enbüyük': function (a) { return Math.max.apply(null, a.map(function (x) { return needNum(x, 'enbüyük'); })); },
    'uzunluk': function (a) { return fmt(a[0]).length; },
    'sayı': function (a) { var n = Number(String(a[0]).replace(',', '.')); if (typeof a[0] === 'boolean' || a[0] === '' || isNaN(n)) throw FlowError('"' + fmt(a[0]) + '" sayıya çevrilemez'); return n; },
    'metin': function (a) { return fmt(a[0]); },
  };
  FUNCS['enkucuk'] = FUNCS['enküçük']; FUNCS['enbuyuk'] = FUNCS['enbüyük']; FUNCS['sayi'] = FUNCS['sayı'];
  var FUNC_ARITY = { 'karekök': 1, 'karekok': 1, 'mutlak': 1, 'yuvarla': 1, 'taban': 1, 'tavan': 1, 'uzunluk': 1, 'sayı': 1, 'sayi': 1, 'metin': 1 };

  function evaluate(node, env) {
    switch (node.k) {
      case 'lit': return node.v;
      case 'var':
        if (!Object.prototype.hasOwnProperty.call(env, node.name)) throw FlowError("'" + node.name + "' değişkenine henüz değer verilmedi");
        return env[node.name];
      case 'neg': return -needNum(evaluate(node.e, env), 'eksi işareti');
      case 'not': { var nv = evaluate(node.e, env); if (typeof nv !== 'boolean') throw FlowError('"değil" yalnızca doğru/yanlış değerlere uygulanır'); return !nv; }
      case 'call': {
        var f = FUNCS[node.name.toLocaleLowerCase('tr')];
        if (!f) throw FlowError("Bilinmeyen fonksiyon: " + node.name + "()");
        var ar = FUNC_ARITY[node.name.toLocaleLowerCase('tr')];
        if (ar !== undefined && node.args.length !== ar) throw FlowError(node.name + '() ' + ar + ' değer ister');
        if (!node.args.length) throw FlowError(node.name + '() en az bir değer ister');
        return f(node.args.map(function (a) { return evaluate(a, env); }));
      }
      case 'bin': {
        if (node.op === 'and' || node.op === 'or') {
          var lv = evaluate(node.l, env);
          if (typeof lv !== 'boolean') throw FlowError('"' + (node.op === 'and' ? 've' : 'veya') + '" için karşılaştırma (doğru/yanlış) gerekli');
          if (node.op === 'and' && !lv) return false;
          if (node.op === 'or' && lv) return true;
          var rv = evaluate(node.r, env);
          if (typeof rv !== 'boolean') throw FlowError('"' + (node.op === 'and' ? 've' : 'veya') + '" için karşılaştırma (doğru/yanlış) gerekli');
          return rv;
        }
        var l = evaluate(node.l, env), r = evaluate(node.r, env), op = node.op;
        if (op === '+') {
          if (typeof l === 'string' || typeof r === 'string') return fmt(l) + fmt(r);
          return needNum(l, 'toplama') + needNum(r, 'toplama');
        }
        if (op === '-' || op === '*' || op === '/' || op === '%' || op === '**') {
          var names = { '-': 'çıkarma', '*': 'çarpma', '/': 'bölme', '%': 'mod alma', '**': 'üs alma' };
          if (typeof l === 'string' || typeof r === 'string') throw FlowError('Metin ile ' + names[op] + ' yapılamaz');
          var a = needNum(l, names[op]), b = needNum(r, names[op]);
          if (op === '-') return a - b;
          if (op === '*') return a * b;
          if (op === '/') { if (b === 0) throw FlowError('Sıfıra bölme'); return a / b; }
          if (op === '%') { if (b === 0) throw FlowError('Sıfıra bölme (mod)'); return ((a % b) + b) % b; }
          return Math.pow(a, b);
        }
        if (op === '==') return l === r;
        if (op === '!=') return l !== r;
        if (typeof l !== typeof r) throw FlowError('Farklı türler karşılaştırılamaz (' + typeName(l) + ' ve ' + typeName(r) + ')');
        if (op === '<') return l < r;
        if (op === '>') return l > r;
        if (op === '<=') return l <= r;
        return l >= r;
      }
    }
    throw FlowError('İç hata: bilinmeyen ifade');
  }

  // ------------------------------------------------------------------ şema yardımcıları
  function indexChart(chart) {
    var nodes = {}, out = {}, inn = {};
    (chart.nodes || []).forEach(function (n) { nodes[n.id] = n; out[n.id] = []; inn[n.id] = []; });
    (chart.edges || []).forEach(function (e) { if (nodes[e.from] && nodes[e.to]) { out[e.from].push(e); inn[e.to].push(e); } });
    return { nodes: nodes, out: out, inn: inn };
  }
  function findStart(chart) { return (chart.nodes || []).filter(function (n) { return n.type === 'start'; })[0] || null; }
  function edgeFrom(ix, id, port) { var l = ix.out[id] || []; for (var i = 0; i < l.length; i++) if ((l[i].port || 'out') === port) return l[i]; return null; }

  function parseNode(n) {
    switch (n.type) {
      case 'input': return { vars: parseVarList(n.text || '') };
      case 'output': return { exprs: parseOutputList(n.text || '') };
      case 'process': return { stmts: parseStatements(n.text || '') };
      case 'decision': { if (!String(n.text || '').trim()) throw FlowError('Koşulu yaz (örnek: a > b)'); return { cond: parseExpression(n.text) }; }
      default: return {};
    }
  }

  function parseInputValue(text) {
    var t = String(text).trim();
    if (/^[+-]?(\d+([.,]\d+)?|[.,]\d+)$/.test(t)) return parseFloat(t.replace(',', '.'));
    if (t === 'doğru') return true;
    if (t === 'yanlış') return false;
    return t;
  }

  // ------------------------------------------------------------------ yürütücü
  function Runner(chart, inputs, opts) {
    opts = opts || {};
    this.chart = chart; this.ix = indexChart(chart);
    this.queue = (inputs || []).slice();
    this.vars = {}; this.outputs = []; this.steps = 0; this.status = 'ready';
    this.maxSteps = opts.maxSteps || MAX_STEPS;
    this.interactive = !!opts.interactive;
    var start = findStart(chart);
    if (!start) throw FlowError('Şemada Başla bloğu yok');
    this.cur = start.id; this.pendingNode = null; this.pendingIdx = 0; this.parsed = {};
  }
  Runner.prototype._parsed = function (n) { return this.parsed[n.id] || (this.parsed[n.id] = parseNode(n)); };
  Runner.prototype.provide = function (text) { this.queue.push(parseInputValue(text)); };
  Runner.prototype.step = function () {
    if (this.status === 'done' || this.status === 'error') return { done: true, status: this.status };
    if (++this.steps > this.maxSteps) { this.status = 'error'; return { error: 'Sonsuz döngü olabilir: ' + this.maxSteps + ' adım aşıldı', nodeId: this.cur, loop: true }; }
    var n = this.ix.nodes[this.cur];
    if (!n) { this.status = 'error'; return { error: 'Bağlantı var olmayan bir bloğa gidiyor', nodeId: this.cur }; }
    try {
      var ev = { nodeId: n.id, type: n.type, changed: [], output: null, edgeId: null, vars: null };
      var port = 'out';
      if (n.type === 'end') { this.status = 'done'; ev.finished = true; ev.vars = Object.assign({}, this.vars); return ev; }
      var p = this._parsed(n);
      if (n.type === 'input') {
        var names = p.vars;
        var from = this.pendingNode === n.id ? this.pendingIdx : 0;   // aynı Girdi bloğunda kaldığı yerden devam
        for (var i = from; i < names.length; i++) {
          if (!this.queue.length) {
            if (this.interactive) { this.steps--; this.pendingNode = n.id; this.pendingIdx = i; return { waiting: true, name: names[i], nodeId: n.id, type: 'input' }; }
            throw FlowError("'" + names[i] + "' için girdi sağlanmadı");
          }
          this.vars[names[i]] = this.queue.shift(); ev.changed.push(names[i]);
        }
        this.pendingNode = null; this.pendingIdx = 0;
      } else if (n.type === 'output') {
        var vals = p.exprs.map(function (e) { return fmt(evaluate(e, this.vars)); }.bind(this));
        ev.output = vals.join(' '); this.outputs.push(ev.output);
      } else if (n.type === 'process') {
        for (var s = 0; s < p.stmts.length; s++) {
          var st = p.stmts[s], val = evaluate(st.expr, this.vars);
          if (st.op !== '=') {
            if (!Object.prototype.hasOwnProperty.call(this.vars, st.name)) throw FlowError("'" + st.name + "' değişkenine henüz değer verilmedi");
            var cur = this.vars[st.name], bop = st.op.charAt(0);
            val = evaluate({ k: 'bin', op: bop, l: { k: 'lit', v: cur }, r: { k: 'lit', v: val } }, this.vars);
          }
          this.vars[st.name] = val; ev.changed.push(st.name);
        }
      } else if (n.type === 'decision') {
        var c = evaluate(p.cond, this.vars);
        if (typeof c !== 'boolean') throw FlowError('Karar bloğunun koşulu doğru/yanlış olmalı (karşılaştırma yaz: örneğin a > b)');
        port = c ? 'yes' : 'no'; ev.decision = c;
      }
      var e = edgeFrom(this.ix, n.id, port);
      if (!e) throw FlowError(n.type === 'decision' ? ('Karar bloğunun ' + (port === 'yes' ? 'Evet' : 'Hayır') + ' çıkışı bağlı değil') : 'Bu bloğun çıkışı bağlı değil');
      ev.edgeId = e.id; this.cur = e.to; ev.vars = Object.assign({}, this.vars);
      return ev;
    } catch (err) {
      if (err && err.name === 'FlowError') { this.status = 'error'; return { error: err.message, nodeId: n.id }; }
      throw err;
    }
  };

  function run(chart, inputs, opts) {
    var res = { outputs: [], steps: 0, finished: false, error: null, errorNodeId: null, vars: {}, loop: false };
    var r;
    try { r = new Runner(chart, inputs, opts); } catch (e) { res.error = e.message; return res; }
    for (;;) {
      var ev = r.step();
      if (ev.error) { res.error = ev.error; res.errorNodeId = ev.nodeId; res.loop = !!ev.loop; break; }
      if (ev.finished) { res.finished = true; break; }
      if (ev.done) break;
    }
    res.outputs = r.outputs.slice(); res.steps = r.steps; res.vars = Object.assign({}, r.vars);
    return res;
  }

  // ------------------------------------------------------------------ yapısal doğrulama
  function validate(chart) {
    var errors = [], warnings = [];
    var nodes = chart.nodes || [];
    var ix = indexChart(chart);
    var starts = nodes.filter(function (n) { return n.type === 'start'; });
    var ends = nodes.filter(function (n) { return n.type === 'end'; });
    if (starts.length === 0) errors.push({ message: 'Şemada Başla bloğu yok.' });
    if (starts.length > 1) starts.slice(1).forEach(function (n) { errors.push({ nodeId: n.id, message: 'Birden fazla Başla bloğu var; yalnızca biri olmalı.' }); });
    if (ends.length === 0) errors.push({ message: 'Şemada Bitir bloğu yok.' });
    nodes.forEach(function (n) {
      var outs = ix.out[n.id] || [], ins = ix.inn[n.id] || [];
      var label = { start: 'Başla', end: 'Bitir', input: 'Girdi', output: 'Çıktı', process: 'İşlem', decision: 'Karar' }[n.type] || n.type;
      if (n.type === 'start' && ins.length) errors.push({ nodeId: n.id, message: 'Başla bloğuna giriş bağlantısı olamaz.' });
      if (n.type === 'end') { if (outs.length) errors.push({ nodeId: n.id, message: 'Bitir bloğundan çıkış bağlantısı olamaz.' }); }
      else if (n.type === 'decision') {
        if (!edgeFrom(ix, n.id, 'yes')) errors.push({ nodeId: n.id, message: 'Karar bloğunun Evet çıkışı bağlı değil.' });
        if (!edgeFrom(ix, n.id, 'no')) errors.push({ nodeId: n.id, message: 'Karar bloğunun Hayır çıkışı bağlı değil.' });
      } else if (outs.length === 0) errors.push({ nodeId: n.id, message: label + ' bloğunun çıkışı bağlı değil.' });
      if (n.type !== 'start' && n.type !== 'end' && ins.length === 0) warnings.push({ nodeId: n.id, message: label + ' bloğuna hiçbir bağlantı gelmiyor.' });
      if (n.type === 'input' || n.type === 'output' || n.type === 'process' || n.type === 'decision') {
        if (!String(n.text || '').trim()) errors.push({ nodeId: n.id, message: label + ' bloğu boş; içini doldur.' });
        else { try { parseNode(n); } catch (e) { errors.push({ nodeId: n.id, message: label + ': ' + e.message }); } }
      }
    });
    // erişilebilirlik
    if (starts.length) {
      var seen = {}, st = [starts[0].id];
      while (st.length) { var id = st.pop(); if (seen[id]) continue; seen[id] = true; (ix.out[id] || []).forEach(function (e) { st.push(e.to); }); }
      nodes.forEach(function (n) { if (!seen[n.id]) warnings.push({ nodeId: n.id, message: 'Bu blok Başla bloğundan ulaşılamıyor.' }); });
      // bitişe ulaşamayan blok
      var canEnd = {}, rst = ends.map(function (n) { return n.id; });
      while (rst.length) { var id2 = rst.pop(); if (canEnd[id2]) continue; canEnd[id2] = true; (ix.inn[id2] || []).forEach(function (e) { rst.push(e.from); }); }
      nodes.forEach(function (n) { if (seen[n.id] && !canEnd[n.id] && n.type !== 'end') warnings.push({ nodeId: n.id, message: 'Bu yol hiçbir zaman Bitir bloğuna ulaşmıyor (sonsuz döngü olabilir).' }); });
    }
    return { errors: errors, warnings: warnings, ok: errors.length === 0 };
  }

  // ------------------------------------------------------------------ analiz
  function analyze(chart) {
    var ix = indexChart(chart), start = findStart(chart);
    var info = { nodeCount: (chart.nodes || []).length, hasDecision: false, hasLoop: false, hasInput: false, hasOutput: false, hasProcess: false };
    (chart.nodes || []).forEach(function (n) { if (n.type === 'decision') info.hasDecision = true; if (n.type === 'input') info.hasInput = true; if (n.type === 'output') info.hasOutput = true; if (n.type === 'process') info.hasProcess = true; });
    if (start) {
      var state = {};
      (function dfs(id) { state[id] = 1; (ix.out[id] || []).forEach(function (e) { if (state[e.to] === 1) info.hasLoop = true; else if (!state[e.to]) dfs(e.to); }); state[id] = 2; })(start.id);
    }
    return info;
  }

  // ------------------------------------------------------------------ otomatik yerleşim
  var GAP_X = 230, GAP_Y = 120;
  function autoLayout(chart) {
    var ix = indexChart(chart), start = findStart(chart);
    var nodes = chart.nodes || [];
    if (!start) return chart;
    // geri kenarları bul (DFS)
    var state = {}, back = {};
    (function dfs(id) { state[id] = 1; (ix.out[id] || []).forEach(function (e) { if (state[e.to] === 1) back[e.id] = true; else if (!state[e.to]) dfs(e.to); }); state[id] = 2; })(start.id);
    // seviye = ileri kenarlarda en uzun yol
    var level = {}, order = [];
    var indeg = {}; nodes.forEach(function (n) { indeg[n.id] = 0; });
    (chart.edges || []).forEach(function (e) { if (!back[e.id] && indeg[e.to] !== undefined) indeg[e.to]++; });
    var queue = [start.id]; level[start.id] = 0;
    var remaining = {}; nodes.forEach(function (n) { remaining[n.id] = indeg[n.id]; });
    while (queue.length) {
      var id = queue.shift(); order.push(id);
      (ix.out[id] || []).forEach(function (e) {
        if (back[e.id]) return;
        level[e.to] = Math.max(level[e.to] || 0, level[id] + 1);
        if (--remaining[e.to] === 0) queue.push(e.to);
      });
    }
    nodes.forEach(function (n) { if (level[n.id] === undefined) { level[n.id] = 0; order.push(n.id); } });
    // şerit (lane) ataması
    var lane = {}, used = {};
    order.forEach(function (id) {
      var n = ix.nodes[id], l = level[id];
      var preds = (ix.inn[id] || []).filter(function (e) { return !back[e.id] && lane[e.from] !== undefined; });
      var pref = preds.length ? lane[preds[0].from] : 0;
      // karar bloğunun Hayır dalı sağa kayar
      var viaNo = preds.filter(function (e) { return e.port === 'no'; })[0];
      if (viaNo && preds.length === 1) pref = lane[viaNo.from] + 1;
      var key = function (ln) { return l + ':' + ln; };
      var ln = pref;
      while (used[key(ln)]) ln++;
      lane[id] = ln; used[key(ln)] = true;
    });
    nodes.forEach(function (n) { n.x = 80 + lane[n.id] * GAP_X; n.y = 50 + level[n.id] * GAP_Y; });
    return chart;
  }

  // ------------------------------------------------------------------ görev denetleyici
  function normLine(s) { return String(s).replace(/\s+/g, ' ').trim(); }
  function sameLine(a, b) {
    var x = normLine(a), y = normLine(b);
    if (x === y) return true;
    var nx = Number(x.replace(',', '.')), ny = Number(y.replace(',', '.'));
    return x !== '' && y !== '' && !isNaN(nx) && !isNaN(ny) && Math.abs(nx - ny) < 1e-6;
  }
  function checkTask(chart, task) {
    var res = { ok: false, valid: true, validation: null, structure: [], tests: [], passed: 0, total: 0 };
    res.validation = validate(chart);
    if (!res.validation.ok) { res.valid = false; return res; }
    var info = analyze(chart);
    var need = task.need || {};
    if (need.decision && !info.hasDecision) res.structure.push('Bu görevde Karar bloğu kullanmalısın.');
    if (need.loop && !info.hasLoop) res.structure.push('Bu görevde bir döngü kurmalısın (bir bağlantıyı geriye, daha önceki bir bloğa bağla).');
    if (need.input && !info.hasInput) res.structure.push('Bu görevde Girdi bloğu kullanmalısın.');
    if (need.maxNodes && info.nodeCount > need.maxNodes) res.structure.push('En çok ' + need.maxNodes + ' blok kullanabilirsin (şu an ' + info.nodeCount + ').');
    (task.tests || []).forEach(function (t) {
      var r = run(chart, t.in || [], { maxSteps: 3000 });
      var expected = t.out || [];
      var ok = !r.error && r.finished && r.outputs.length === expected.length && expected.every(function (e, i) { return sameLine(e, r.outputs[i]); });
      res.tests.push({ ok: ok, input: t.in || [], expected: expected, got: r.outputs, error: r.error, finished: r.finished, hidden: !!t.hidden });
      res.total++; if (ok) res.passed++;
    });
    res.ok = res.structure.length === 0 && res.passed === res.total && res.total > 0;
    return res;
  }

  // ------------------------------------------------------------------ DSL ile şema kur (referans çözümler / örnekler için)
  //  "başla" | "girdi a, b" | "işlem x = 1" | "çıktı x" | "karar x > 3 -> EtiketEvet | EtiketHayır" | "bitir" | "git Etiket"
  //  Etiket: satırın başına "Ad: " yazılır. "-> sonraki | Etiket" ile sıradaki satıra gidilebilir.
  function buildFromDSL(lines) {
    var nodes = [], edges = [], labels = {}, pend = [], prev = null, lastSeq = null;
    var typeOf = { 'başla': 'start', 'girdi': 'input', 'işlem': 'process', 'çıktı': 'output', 'karar': 'decision', 'bitir': 'end' };
    var items = [];
    lines.forEach(function (raw) {
      var line = raw.trim(), label = null, m = /^([A-Za-z0-9_]+):\s*(.*)$/.exec(line);
      if (m && !/^(başla|girdi|işlem|çıktı|karar|bitir|git)\b/.test(line)) { label = m[1]; line = m[2]; }
      var kw = line.split(/\s+/)[0], rest = line.slice(kw.length).trim();
      items.push({ label: label, kw: kw, rest: rest });
    });
    var id = 0, nodeOfItem = [];
    items.forEach(function (it, idx) { if (it.kw !== 'git') { var n = { id: 'n' + (++id), type: typeOf[it.kw], text: it.rest, x: 0, y: 0 }; if (!n.type) throw new Error('DSL: bilinmeyen satır: ' + it.kw); if (n.type === 'decision') n.text = it.rest.split('->')[0].trim(); if (n.type === 'start' || n.type === 'end') n.text = ''; nodes.push(n); nodeOfItem[idx] = n; } });
    // etiketler: bir sonraki gerçek düğüme işaret eder
    items.forEach(function (it, idx) { if (it.label) { var j = idx; while (j < items.length && !nodeOfItem[j]) j++; labels[it.label] = nodeOfItem[j]; } });
    var eid = 0;
    var link = function (from, to, port) { if (!from || !to) throw new Error('DSL: bağlantı hedefi yok'); edges.push({ id: 'e' + (++eid), from: from.id, to: to.id, port: port || 'out' }); };
    var prevNode = null, fall = true;
    items.forEach(function (it, idx) {
      var n = nodeOfItem[idx];
      if (it.kw === 'git') { if (!prevNode) throw new Error('DSL: git öncesi blok yok'); link(prevNode, labels[it.rest]); fall = false; prevNode = null; return; }
      if (prevNode && fall) link(prevNode, n);
      fall = true;
      if (n.type === 'decision') {
        var tg = it.rest.split('->')[1]; if (!tg) throw new Error('DSL: karar hedefi yok: ' + it.rest);
        var parts = tg.split('|').map(function (s) { return s.trim(); });
        var next = nodeOfItem[idx + 1] || (function () { var j = idx + 1; while (j < items.length && !nodeOfItem[j]) j++; return nodeOfItem[j]; })();
        var tYes = parts[0] === 'sonraki' ? next : labels[parts[0]], tNo = parts[1] === 'sonraki' ? next : labels[parts[1]];
        link(n, tYes, 'yes'); link(n, tNo, 'no'); prevNode = null; fall = false;
      } else if (n.type === 'end') { prevNode = null; fall = false; }
      else prevNode = n;
    });
    var chart = { nodes: nodes, edges: edges };
    return autoLayout(chart);
  }

  var api = {
    MAX_STEPS: MAX_STEPS, TYPES: TYPES, GAP_X: GAP_X, GAP_Y: GAP_Y,
    tokenize: tokenize, parseExpression: parseExpression, parseStatements: parseStatements, parseVarList: parseVarList, parseOutputList: parseOutputList,
    evaluate: evaluate, fmt: fmt, parseInputValue: parseInputValue, Runner: Runner, run: run, validate: validate, analyze: analyze,
    autoLayout: autoLayout, checkTask: checkTask, buildFromDSL: buildFromDSL, indexChart: indexChart, findStart: findStart,
  };
  root.FlowEngine = api;
  if (typeof module === 'object' && module.exports) module.exports = api;
})(typeof self !== 'undefined' ? self : this);
