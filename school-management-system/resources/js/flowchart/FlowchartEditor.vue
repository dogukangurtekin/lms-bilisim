<template>
  <div class="editor-shell">
    <div class="editor-top">
      <input v-model="store.name" class="name-input" placeholder="Flowchart adı" />
      <Toolbar
        @add-node="store.addNode"
        @validate="onValidate"
        @run="onRun"
        @step="onStep"
        @save="onSave"
        @export-json="onExport"
        @import-json="onImport"
      />
    </div>

    <div class="editor-grid">
      <FlowCanvas
        :nodes="store.nodes"
        :edges="store.edges"
        :selected-node-id="store.selectedNodeId"
        @update:nodes="store.setNodes"
        @update:edges="store.setEdges"
        @connect="store.connectNodes"
        @select-node="store.setSelectedNode"
        @clear-selection="store.clearSelection"
        @node-move="store.moveNode"
        @add-node-at="onAddNodeAt"
      />

      <div class="side-stack">
        <PropertiesPanel
          :node="store.selectedNode"
          @update-node="store.updateSelectedNode"
          @delete-node="store.deleteSelectedNode"
        />
        <ConsoleOutput :lines="store.logs" @clear="store.resetExecution" />
        <section class="error-box" v-if="store.errors.length">
          <h3>Hatalar</h3>
          <ul>
            <li v-for="err in store.errors" :key="err">{{ err }}</li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import Toolbar from './components/Toolbar.vue';
import FlowCanvas from './components/FlowCanvas.vue';
import PropertiesPanel from './components/PropertiesPanel.vue';
import ConsoleOutput from './components/ConsoleOutput.vue';
import { useFlowchartStore } from './stores/flowchartStore';

const store = useFlowchartStore();

function onValidate() {
  store.validate();
}

function onRun() {
  store.runFull();
}

function onStep() {
  if (!store.executionState) store.resetExecution();
  store.runStep();
}

function onAddNodeAt(payload) {
  store.addNodeAt(payload.type, payload.position);
}

async function onSave() {
  try {
    await store.saveToApi();
    store.errors = [];
  } catch (error) {
    store.errors = [error?.response?.data?.message || error.message || 'Kaydetme hatası'];
  }
}

function onExport() {
  const data = JSON.stringify(store.exportJson(), null, 2);
  const blob = new Blob([data], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `${store.name || 'flowchart'}.json`;
  a.click();
  URL.revokeObjectURL(a.href);
}

function onImport(event) {
  const file = event.target?.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    try {
      const parsed = JSON.parse(String(reader.result || '{}'));
      store.importJson(parsed);
    } catch (error) {
      store.errors = [`JSON okunamadı: ${error.message}`];
    }
  };
  reader.readAsText(file);
  event.target.value = '';
}
</script>

<style scoped>
.editor-shell { display:grid; gap:12px; }
.editor-top { display:grid; gap:10px; }
.name-input { width:340px; border:1px solid #cbd5e1; border-radius:10px; padding:8px 10px; font-size:14px; }
.editor-grid { display:grid; grid-template-columns:minmax(0, 1fr) 340px; gap:12px; }
.side-stack { display:grid; gap:10px; align-content:start; }
.error-box { border:1px solid #fecaca; border-radius:12px; background:#fff1f2; color:#9f1239; padding:10px; }
.error-box h3 { margin:0 0 8px; font-size:14px; }
.error-box ul { margin:0; padding-left:18px; }
@media (max-width: 1200px) {
  .editor-grid { grid-template-columns:1fr; }
}
</style>
