const MAX_ITERATION = 1000;

function evaluateExpression(expr, vars) {
  const safe = String(expr || '').replace(/\b([a-zA-Z_]\w*)\b/g, (name) => {
    if (['true', 'false', 'null'].includes(name)) return name;
    if (Object.prototype.hasOwnProperty.call(vars, name)) return JSON.stringify(vars[name]);
    return '0';
  });

  if (!/^[0-9\s+\-*/().<>=!&|'",a-zA-Z_]+$/.test(safe)) {
    throw new Error('İzin verilmeyen ifade karakteri');
  }

  // eslint-disable-next-line no-new-func
  return Function(`"use strict"; return (${safe});`)();
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
      return { ok: false, errors: ['Sonsuz döngü tespit edildi'], logs, steps, variables: vars };
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
          vars[inputMatch[1]] = inputs[inputIndex] ?? null;
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

    if (node.type !== 'decision' && node.type !== 'end') {
      nextNodeId = findEdge(edges, currentId)?.to || null;
    }

    step.variablesAfter = { ...vars };
    step.nextNodeId = nextNodeId;
    steps.push(step);

    if (node.type === 'end') break;
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

