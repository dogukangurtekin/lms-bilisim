import { defineStore } from 'pinia';
import axios from 'axios';
import { executeFlowchart, validateFlowchart } from '../engine/executionEngine';
import { sampleFlowchart } from '../sampleFlowchart';

function uid(prefix = 'id') {
  return `${prefix}-${Math.random().toString(36).slice(2, 9)}`;
}

export const useFlowchartStore = defineStore('flowchart', {
  state: () => ({
    flowchartId: null,
    name: sampleFlowchart.name,
    nodes: JSON.parse(JSON.stringify(sampleFlowchart.nodes)),
    edges: JSON.parse(JSON.stringify(sampleFlowchart.edges)),
    selectedNodeId: '',
    logs: [],
    errors: [],
    executionState: null,
    // Yürütme sırasında "şu an çalışan blok" vurgusu; kullanıcının panelden
    // seçtiği (düzenlediği) blok olan selectedNodeId'den bilerek ayrı tutulur.
    executingNodeId: '',
    // "io" bloğundaki "input degisken" komutu için beklenen değişken; dolu
    // olduğunda arayüz kullanıcıdan bir değer ister.
    pendingInput: null,
    providedInputs: [],
    runMode: 'full',
    _pausedForInput: false,
  }),
  getters: {
    selectedNode(state) {
      return state.nodes.find((node) => node.id === state.selectedNodeId) || null;
    },
    canStep(state) {
      return Boolean(state.executionState?.nextNodeId);
    },
    isRunning(state) {
      return Boolean(state.executingNodeId) || Boolean(state.pendingInput);
    },
  },
  actions: {
    addNode(type) {
      const count = this.nodes.length + 1;
      this.addNodeAt(type, {
        x: 120 + count * 20,
        y: 120 + count * 20,
      });
    },
    addNodeAt(type, position) {
      if (type === 'start' && this.nodes.some((n) => n.type === 'start')) {
        this.errors = ['Sadece 1 adet Start bloğu olabilir.'];
        return;
      }

      const count = this.nodes.length + 1;
      const snappedPosition = {
        x: Math.round((Number(position?.x ?? (120 + count * 20)) || 0) / 20) * 20,
        y: Math.round((Number(position?.y ?? (120 + count * 20)) || 0) / 20) * 20,
      };

      const defaultCode = { process: 'x = 0', decision: 'x > 0', io: 'output x' }[type] || '';
      const defaultText = { start: 'Başla', end: 'Bitir', process: 'İşlem', decision: 'Koşul', io: 'Giriş/Çıkış' }[type] || `${type} ${count}`;

      this.nodes.push({
        id: uid('n'),
        type,
        text: type === 'start' || type === 'end' ? defaultText : `${defaultText} ${count}`,
        code: defaultCode,
        position: snappedPosition,
      });
      this.errors = [];
      this.selectedNodeId = this.nodes[this.nodes.length - 1].id;
    },
    deleteSelectedNode() {
      if (!this.selectedNodeId) return;
      this.nodes = this.nodes.filter((n) => n.id !== this.selectedNodeId);
      this.edges = this.edges.filter((e) => e.from !== this.selectedNodeId && e.to !== this.selectedNodeId);
      this.selectedNodeId = '';
    },
    updateSelectedNode(payload) {
      this.nodes = this.nodes.map((node) => {
        if (node.id !== this.selectedNodeId) return node;
        return { ...node, ...payload };
      });
    },
    setNodes(nodes) {
      this.nodes = nodes;
    },
    setEdges(edges) {
      this.edges = edges;
    },
    setSelectedNode(id) {
      this.selectedNodeId = id;
    },
    clearSelection() {
      this.selectedNodeId = '';
    },
    moveNode({ id, position }) {
      const snapped = {
        x: Math.round((position.x || 0) / 20) * 20,
        y: Math.round((position.y || 0) / 20) * 20,
      };
      this.nodes = this.nodes.map((node) => (node.id === id ? { ...node, position: snapped } : node));
    },
    connectNodes(connection) {
      const from = connection.source;
      const to = connection.target;
      if (!from || !to) return;
      if (from === to) return;

      const fromNode = this.nodes.find((n) => n.id === from);
      const toNode = this.nodes.find((n) => n.id === to);
      if (!fromNode || !toNode) return;
      if (fromNode.type === 'end') {
        this.errors = ['End node çıkış veremez.'];
        return;
      }

      if (this.edges.some((e) => e.from === from && e.to === to)) {
        return;
      }

      const existing = this.edges.filter((e) => e.from === from);
      let condition = null;
      if (fromNode.type === 'decision') {
        const hasYes = existing.some((e) => e.condition === 'yes');
        const hasNo = existing.some((e) => e.condition === 'no');
        if (hasYes && hasNo) {
          this.errors = ['Decision bloğu için en fazla YES ve NO çıkışı olabilir.'];
          return;
        }
        condition = hasYes ? 'no' : 'yes';
      } else if (existing.length >= 1) {
        this.errors = ['Bu blok için tek bir çıkış bağlantısına izin verilir.'];
        return;
      }

      this.edges.push({ id: uid('e'), from, to, condition });
      this.errors = [];
    },
    validate() {
      const errors = validateFlowchart(this.nodes, this.edges);
      this.errors = errors;
      return errors;
    },
    _applyResult(result) {
      this.logs = result.logs || [];
      this.errors = result.errors || [];
      this.executionState = result.state || null;
      this._pausedForInput = Boolean(result.waitingInput);
      this.pendingInput = result.waitingInput || null;
      if (this.pendingInput) {
        // Bekleyen input node'u vurgula.
        this.executingNodeId = this.executionState?.nextNodeId || '';
      } else if (result.steps?.length) {
        this.executingNodeId = result.steps[result.steps.length - 1].nodeId;
      } else if (!result.ok) {
        this.executingNodeId = '';
      }
      return result;
    },
    runFull() {
      this.runMode = 'full';
      const state = this._pausedForInput ? this.executionState : null;
      this._pausedForInput = false;
      const result = executeFlowchart({
        nodes: this.nodes,
        edges: this.edges,
        inputs: this.providedInputs,
        state,
        stepMode: false,
      });
      return this._applyResult(result);
    },
    runStep() {
      this.runMode = 'step';
      const result = executeFlowchart({
        nodes: this.nodes,
        edges: this.edges,
        inputs: this.providedInputs,
        state: this.executionState,
        stepMode: true,
      });
      return this._applyResult(result);
    },
    // Kullanıcı bekleyen bir "input" isteğine değer girip gönderdiğinde çağrılır.
    provideInput(rawValue) {
      if (!this.pendingInput) return;
      const numeric = Number(rawValue);
      const value = rawValue !== '' && !Number.isNaN(numeric) ? numeric : rawValue;
      this.providedInputs.push(value);
      this.pendingInput = null;
      if (this.runMode === 'full') this.runFull();
      else this.runStep();
    },
    resetExecution() {
      this.executionState = null;
      this.logs = [];
      this.errors = [];
      this.executingNodeId = '';
      this.pendingInput = null;
      this.providedInputs = [];
      this._pausedForInput = false;
    },
    loadExample(example) {
      this.name = example.name;
      this.nodes = JSON.parse(JSON.stringify(example.nodes));
      this.edges = JSON.parse(JSON.stringify(example.edges));
      this.selectedNodeId = '';
      this.flowchartId = null;
      this.resetExecution();
    },
    exportJson() {
      return {
        name: this.name,
        nodes: this.nodes,
        edges: this.edges,
      };
    },
    importJson(data) {
      this.name = String(data?.name || 'Flowchart');
      this.nodes = Array.isArray(data?.nodes) ? data.nodes : [];
      this.edges = Array.isArray(data?.edges) ? data.edges : [];
      this.selectedNodeId = '';
      this.resetExecution();
    },
    async saveToApi() {
      const payload = this.exportJson();
      if (this.flowchartId) {
        const { data } = await axios.put(`/api/flowcharts/${this.flowchartId}`, payload);
        this.flowchartId = data.id;
        return data;
      }
      const { data } = await axios.post('/api/flowcharts', payload);
      this.flowchartId = data.id;
      return data;
    },
    async loadFromApi(id) {
      const { data } = await axios.get(`/api/flowcharts/${id}`);
      this.flowchartId = data.id;
      this.name = data.name;
      this.nodes = data.nodes;
      this.edges = data.edges;
      this.selectedNodeId = '';
      this.resetExecution();
      return data;
    },
  },
});
