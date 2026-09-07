<template>
  <div class="editor-shell">
    <div class="editor-top">
      <div class="name-row">
        <input v-model="store.name" class="name-input" placeholder="Flowchart adı" />
        <span :class="['status-pill', statusClass]">{{ statusText }}</span>
      </div>
      <Toolbar
        @add-node="store.addNode"
        @validate="onValidate"
        @run="onRun"
        @step="onStep"
        @save="onSave"
        @export-json="onExport"
        @import-json="onImport"
        @load-example="onLoadExample"
      />
    </div>

    <div class="editor-grid">
      <div class="canvas-column">
        <FlowCanvas
          :nodes="store.nodes"
          :edges="store.edges"
          :selected-node-id="store.selectedNodeId"
          :executing-node-id="store.executingNodeId"
          :waiting-node-id="store.pendingInput ? store.executingNodeId : ''"
          @update:nodes="store.setNodes"
          @update:edges="store.setEdges"
          @connect="store.connectNodes"
          @select-node="store.setSelectedNode"
          @clear-selection="store.clearSelection"
          @node-move="store.moveNode"
          @add-node-at="onAddNodeAt"
        />

        <transition name="input-pop">
          <form v-if="store.pendingInput" class="input-prompt" @submit.prevent="onSubmitInput">
            <span class="input-prompt-icon">⏳</span>
            <span class="input-prompt-text">
              <strong>{{ store.pendingInput }}</strong> için bir değer girin:
            </span>
            <input v-model="inputValue" ref="inputRef" class="input-prompt-field" autofocus placeholder="değer..." />
            <button type="submit" class="input-prompt-submit">Devam Et ▶</button>
          </form>
        </transition>
      </div>

      <div class="side-stack">
        <PropertiesPanel
          :node="store.selectedNode"
          @update-node="store.updateSelectedNode"
          @delete-node="store.deleteSelectedNode"
        />
        <ConsoleOutput :lines="store.logs" @clear="store.resetExecution" />
        <section class="error-box" v-if="store.errors.length">
          <h3>⚠ Hatalar</h3>
          <ul>
            <li v-for="err in store.errors" :key="err">{{ err }}</li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import Toolbar from './components/Toolbar.vue';
import FlowCanvas from './components/FlowCanvas.vue';
import PropertiesPanel from './components/PropertiesPanel.vue';
import ConsoleOutput from './components/ConsoleOutput.vue';
import { useFlowchartStore } from './stores/flowchartStore';

const store = useFlowchartStore();
const inputValue = ref('');
const inputRef = ref(null);

const statusText = computed(() => {
  if (store.pendingInput) return 'Girdi bekleniyor';
  if (store.executingNodeId) return 'Çalışıyor';
  if (store.errors.length) return 'Hata var';
  return 'Hazır';
});
const statusClass = computed(() => {
  if (store.pendingInput) return 'status-waiting';
  if (store.executingNodeId) return 'status-running';
  if (store.errors.length) return 'status-error';
  return 'status-idle';
});

function onValidate() {
  store.validate();
}

function onRun() {
  store.resetExecution();
  store.runFull();
}

function onStep() {
  store.runStep();
}

function onSubmitInput() {
  store.provideInput(inputValue.value);
  inputValue.value = '';
  nextTick(() => inputRef.value?.focus?.());
}

function onAddNodeAt(payload) {
  store.addNodeAt(payload.type, payload.position);
}

function onLoadExample(example) {
  store.loadExample(example);
}

async function onSave() {
  try {
    await store.saveToApi();
    store.errors = [];
    if (window.appToast) window.appToast('success', 'Flowchart kaydedildi.');
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
.editor-shell { display:grid; gap:14px; }
.editor-top { display:grid; gap:12px; }
.name-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.name-input { width:340px; max-width:100%; border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; font-size:14.5px; font-weight:700; color:#0f172a; }
.status-pill { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:800; padding:5px 12px; border-radius:999px; text-transform:uppercase; letter-spacing:.03em; }
.status-pill::before { content:''; width:7px; height:7px; border-radius:999px; background:currentColor; }
.status-idle { background:#f1f5f9; color:#64748b; }
.status-running { background:#E4F7EF; color:#0EA57A; animation: status-blink 1.1s ease-in-out infinite; }
.status-waiting { background:#FEF3E2; color:#D97706; }
.status-error { background:#FEE2E2; color:#DC2626; }
@keyframes status-blink { 0%,100%{opacity:1} 50%{opacity:.55} }

.editor-grid { display:grid; grid-template-columns:minmax(0, 1fr) 340px; gap:14px; }
.canvas-column { position:relative; min-width:0; }
.side-stack { display:grid; gap:12px; align-content:start; }
.error-box { border:1px solid #fecaca; border-radius:14px; background:#fff1f2; color:#9f1239; padding:12px; }
.error-box h3 { margin:0 0 8px; font-size:13.5px; }
.error-box ul { margin:0; padding-left:18px; font-size:12.5px; line-height:1.6; }

.input-prompt {
  position:absolute; left:50%; bottom:24px; transform:translateX(-50%);
  display:flex; align-items:center; gap:10px;
  background:#1e1b3a; color:#fff;
  border-radius:999px; padding:10px 10px 10px 18px;
  box-shadow:0 20px 44px rgba(15,23,42,.35);
  z-index:50;
}
.input-prompt-icon { font-size:16px; }
.input-prompt-text { font-size:13px; white-space:nowrap; }
.input-prompt-field { width:120px; border:0; border-radius:999px; padding:8px 14px; font-size:13px; font-weight:700; background:#fff; color:#0f172a; }
.input-prompt-submit { border:0; border-radius:999px; padding:8px 16px; background:linear-gradient(135deg,#0EA57A,#0c8c66); color:#fff; font-weight:800; font-size:12.5px; cursor:pointer; white-space:nowrap; }
.input-pop-enter-active, .input-pop-leave-active { transition: opacity .18s, transform .18s; }
.input-pop-enter-from, .input-pop-leave-to { opacity:0; transform:translate(-50%, 10px); }

@media (max-width: 1200px) {
  .editor-grid { grid-template-columns:1fr; }
}
</style>
