const MAX_ITERATION = 2000;

function evaluateExpression(expr, vars) {
  const raw = String(expr || '');

  // Tırnak içindeki metinleri (string literal) tek geçişte, alternatifli bir
  // regex ile koru: eşleşme tırnaklı bir metinse OLDUĞU GİBİ bırak, değilse
  // (bir identifier ise) değişken değeriyle değiştir. Böylece "Çift" gibi bir
  // metnin içindeki harfler bilinmeyen değişken sanılıp 0'a çevrilmiyor
  // (eski davranış: output "Çift" -> "0" gibi yanlış bir sonuç veriyordu).
  const restored = raw.replace(/('[^']*'|"[^"]*")|\b([a-zA-Z_]\w*)\b/g, (match, stringLiteral, identifier) => {
    if (stringLiteral) return stringLiteral;
    if (['true', 'false', 'null'].includes(identifier)) return identifier;
    if (Object.prototype.hasOwnProperty.call(vars, identifier)) return JSON.stringify(vars[identifier]);
    return '0';
  });

  if (!/^[0-9\s+\-*/%().<>=!&|'",a-zA-Z_çÇıİöÖüÜğĞşŞ]+$/.test(restored)) {
    throw new Error('İzin verilmeyen ifade karakteri');
  }

  // eslint-disable-next-line no-new-func
  return Function(`"use strict"; return (${restored});`)();
}

function executeAssignment(code, vars) {
  const m = String(code || '').match(/^\s*([a-zA-Z_]\w*)\s*=\s*(.+)\s*$/);
  if (!m) throw new Error(`Geçersiz process kodu: ${code}`);
  vars[m[1]] = evaluateExpression(m[2], vars);
}

function findEdge(edges, from, condition = null) {
  return edges.find((edge) => edge.from === from && (condition === null || edge.condition === condition)) || null;
}

export function validateFlowchart(nodes, edges) {
  const errors = [];
  const starts = nodes.filter((n) => n.type === 'start');
  if (starts.length !== 1) errors.push('Sadece 1 adet Start olmalıdır.');

  for (const node of nodes) {
    const out = edges.filter((edge) => edge.from === node.id);
    if (node.type === 'end' && out.length > 0) errors.push(`End bloğu (${node.id}) çıkış veremez.`);
    if (node.type === 'decision') {
      const yes = out.filter((e) => e.condition === 'yes').length;
      const no = out.filter((e) => e.condition === 'no').length;
      if (yes !== 1 || no !== 1) errors.push(`Decision (${node.id}) için YES/NO çıkışları zorunludur.`);
    }
  }

  if (starts.length === 1) {
    const visited = new Set();
    const queue = [starts[0].id];
    while (queue.length) {
      const current = queue.shift();
      if (!current || visited.has(current)) continue;
      visited.add(current);
      edges.filter((e) => e.from === current).forEach((e) => queue.push(e.to));
    }
    for (const node of nodes) {
      if (!visited.has(node.id)) errors.push(`Bağlantısız blok var: ${node.id}`);
    }
  }

  return errors;
}

/**
 * Bir akış şemasını çalıştırır. "io" bloğunda "input degisken" komutuyla
 * karşılaşıp henüz bir değer sağlanmamışsa, çalıştırmayı o blokta durdurup
 * `waitingInput` alanıyla hangi değişkenin beklendiğini bildirir; arayüz
 * kullanıcıdan değeri aldıktan sonra aynı node'dan (aynı state ile) tekrar
 * çağrılarak kaldığı yerden devam edilir.
 */
export function executeFlowchart({ nodes, edges, inputs = [], startNodeId = null, state = null, stepMode = false }) {
  const errors = validateFlowchart(nodes, edges);
  if (errors.length) return { ok: false, errors, logs: [], steps: [], variables: {} };

  const nodeMap = Object.fromEntries(nodes.map((node) => [node.id, node]));
  const defaultStart = nodes.find((node) => node.type === 'start')?.id || null;
  const vars = state?.variables ? { ...state.variables } : {};
  const logs = state?.logs ? [...state.logs] : [];
  const steps = [];
  let currentId = startNodeId || state?.nextNodeId || defaultStart;
  let inputIndex = state?.inputIndex || 0;
  let iteration = 0;

  while (currentId) {
    iteration += 1;
    if (iteration > MAX_ITERATION) {
      return { ok: false, errors: ['Sonsuz döngü tespit edildi (2000 adımı aştı).'], logs, steps, variables: vars };
    }

    const node = nodeMap[currentId];
    if (!node) return { ok: false, errors: [`Node bulunamadı: ${currentId}`], logs, steps, variables: vars };

    const step = { nodeId: currentId, type: node.type, text: node.text, variablesBefore: { ...vars } };
    let nextNodeId = null;

    try {
      if (node.type === 'process') {
        executeAssignment(node.code, vars);
      } else if (node.type === 'io') {
        const code = String(node.code || '');
        const inputMatch = code.match(/^\s*input\s+([a-zA-Z_]\w*)\s*$/);
        const outputMatch = code.match(/^\s*output\s+(.+)\s*$/);
        if (inputMatch) {
          if (inputIndex >= inputs.length) {
            // Girdi bekleniyor: aynı node'da dur, arayüz değeri toplayıp
            // tekrar çağıracak.
            return {
              ok: true,
              errors: [],
              logs,
              steps,
              variables: vars,
              waitingInput: inputMatch[1],
              state: { nextNodeId: currentId, variables: vars, logs, inputIndex },
            };
          }
          vars[inputMatch[1]] = inputs[inputIndex];
          inputIndex += 1;
        } else if (outputMatch) {
          const out = evaluateExpression(outputMatch[1], vars);
          logs.push(String(out));
        } else if (code.trim() !== '') {
          const out = evaluateExpression(code, vars);
          logs.push(String(out));
        }
      } else if (node.type === 'decision') {
        const result = Boolean(evaluateExpression(node.code, vars));
        step.decision = result;
        nextNodeId = findEdge(edges, currentId, result ? 'yes' : 'no')?.to || null;
      }
    } catch (error) {
      return { ok: false, errors: [`Çalıştırma hatası (${currentId}): ${error.message}`], logs, steps, variables: vars };
    }

    if (node.type === 'end') {
      step.variablesAfter = { ...vars };
      step.nextNodeId = null;
      steps.push(step);
      currentId = null;
      break;
    }

    if (node.type !== 'decision') {
      nextNodeId = findEdge(edges, currentId)?.to || null;
    }

    step.variablesAfter = { ...vars };
    step.nextNodeId = nextNodeId;
    steps.push(step);

    currentId = nextNodeId;
    if (stepMode) break;
  }

  return {
    ok: true,
    errors: [],
    logs,
    steps,
    variables: vars,
    state: { nextNodeId: currentId, variables: vars, logs, inputIndex },
  };
}
