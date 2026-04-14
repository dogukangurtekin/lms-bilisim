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
  }),
  getters: {
    selectedNode(state) {
      return state.nodes.find((node) => node.id === state.selectedNodeId) || null;
    },
    canStep(state) {
      return Boolean(state.executionState?.nextNodeId);
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

      this.nodes.push({
        id: uid('n'),
        type,
        text: `${type.toUpperCase()} ${count}`,
        code: type === 'process' ? 'x = 0' : type === 'decision' ? 'x > 0' : '',
        position: snappedPosition,
      });
      this.errors = [];
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
    runFull(inputs = []) {
      const result = executeFlowchart({
        nodes: this.nodes,
        edges: this.edges,
        inputs,
        state: null,
        stepMode: false,
      });
      this.logs = result.logs || [];
      this.errors = result.errors || [];
      this.executionState = result.state || null;
      if (result.steps?.length) {
        this.selectedNodeId = result.steps[result.steps.length - 1].nodeId;
      }
      return result;
    },
    runStep(inputs = []) {
      const result = executeFlowchart({
        nodes: this.nodes,
        edges: this.edges,
        inputs,
        state: this.executionState,
        stepMode: true,
      });
      this.logs = result.logs || [];
      this.errors = result.errors || [];
      this.executionState = result.state || null;
      if (result.steps?.length) {
        this.selectedNodeId = result.steps[result.steps.length - 1].nodeId;
      }
      return result;
    },
    resetExecution() {
      this.executionState = null;
      this.logs = [];
      this.errors = [];
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
