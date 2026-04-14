<template>
  <div class="toolbar">
    <button
      v-for="item in nodeTypes"
      :key="item.type"
      class="toolbar-btn"
      draggable="true"
      @click="$emit('add-node', item.type)"
      @dragstart="onDragStart($event, item.type)"
    >
      {{ item.label }}
    </button>
    <button class="toolbar-btn secondary" @click="$emit('validate')">Doğrula</button>
    <button class="toolbar-btn secondary" @click="$emit('step')">Adım Adım</button>
    <button class="toolbar-btn primary" @click="$emit('run')">Tam Çalıştır</button>
    <button class="toolbar-btn secondary" @click="$emit('save')">Kaydet</button>
    <button class="toolbar-btn secondary" @click="$emit('export-json')">JSON Dışa Aktar</button>
    <label class="toolbar-btn secondary file-input">
      JSON İçe Aktar
      <input type="file" accept=".json,application/json" @change="$emit('import-json', $event)" />
    </label>
  </div>
</template>

<script setup>
function onDragStart(event, type) {
  if (!event?.dataTransfer) return;
  event.dataTransfer.setData('application/flowchart-node-type', type);
  event.dataTransfer.effectAllowed = 'copy';
}

const nodeTypes = [
  { type: 'start', label: 'Start' },
  { type: 'process', label: 'Process' },
  { type: 'io', label: 'Input/Output' },
  { type: 'decision', label: 'Decision' },
  { type: 'end', label: 'End' },
];
</script>

<style scoped>
.toolbar { display:flex; gap:8px; flex-wrap:wrap; }
.toolbar-btn { border:1px solid #d1d5db; background:#fff; border-radius:10px; padding:8px 10px; cursor:pointer; font-weight:700; }
.toolbar-btn.primary { background:#0f766e; color:#fff; border-color:#0f766e; }
.toolbar-btn.secondary { background:#f8fafc; }
.file-input input { display:none; }
</style>
