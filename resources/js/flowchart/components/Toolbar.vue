<template>
  <div class="toolbar">
    <div class="palette-group">
      <span class="group-label">Blok Ekle</span>
      <div class="palette-list">
        <button
          v-for="item in nodeTypes"
          :key="item.type"
          :class="['palette-btn', `palette-${item.type}`]"
          draggable="true"
          type="button"
          :title="`${item.label} bloğu eklemek için sürükleyin ya da tıklayın`"
          @click="$emit('add-node', item.type)"
          @dragstart="onDragStart($event, item.type)"
        >
          <span class="palette-shape" :class="`shape-${item.type}`"></span>
          <span class="palette-text">{{ item.label }}</span>
        </button>
      </div>
    </div>

    <div class="example-group">
      <span class="group-label">Örnekler</span>
      <select class="example-select" @change="onExampleChange">
        <option value="">Bir örnek yükle…</option>
        <option v-for="ex in examples" :key="ex.key" :value="ex.key">{{ ex.label }}</option>
      </select>
    </div>

    <div class="action-group">
      <button class="toolbar-btn secondary" type="button" @click="$emit('validate')">
        <span class="btn-icon">✓</span> Doğrula
      </button>
      <button class="toolbar-btn secondary" type="button" @click="$emit('step')">
        <span class="btn-icon">⏭</span> Adım Adım
      </button>
      <button class="toolbar-btn primary" type="button" @click="$emit('run')">
        <span class="btn-icon">▶</span> Tam Çalıştır
      </button>
      <button class="toolbar-btn secondary" type="button" @click="$emit('save')">
        <span class="btn-icon">💾</span> Kaydet
      </button>
      <button class="toolbar-btn secondary" type="button" @click="$emit('export-json')">
        <span class="btn-icon">⇩</span> Dışa Aktar
      </button>
      <label class="toolbar-btn secondary file-input">
        <span class="btn-icon">⇧</span> İçe Aktar
        <input type="file" accept=".json,application/json" @change="$emit('import-json', $event)" />
      </label>
    </div>
  </div>
</template>

<script setup>
import { examples } from '../examples';

const emit = defineEmits([
  'add-node',
  'validate',
  'step',
  'run',
  'save',
  'export-json',
  'import-json',
  'load-example',
]);

function onDragStart(event, type) {
  if (!event?.dataTransfer) return;
  event.dataTransfer.setData('application/flowchart-node-type', type);
  event.dataTransfer.effectAllowed = 'copy';
}

function onExampleChange(event) {
  const key = event.target.value;
  if (!key) return;
  const example = examples.find((e) => e.key === key);
  if (example) emit('load-example', example);
  event.target.value = '';
}

const nodeTypes = [
  { type: 'start', label: 'Başla' },
  { type: 'process', label: 'İşlem' },
  { type: 'io', label: 'Giriş/Çıkış' },
  { type: 'decision', label: 'Koşul' },
  { type: 'end', label: 'Bitir' },
];
</script>

<style scoped>
.toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 18px;
  padding: 14px 16px;
  background: #fff;
  border: 1px solid #dbe4ee;
  border-radius: 18px;
  box-shadow: 0 10px 26px rgba(15,23,42,.05);
}
.group-label { display:block; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; margin-bottom:8px; }

.palette-group { flex: 1 1 auto; min-width: 320px; }
.palette-list { display: flex; gap: 8px; flex-wrap: wrap; }
.palette-btn {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  width: 84px; padding: 10px 6px 8px;
  border: 1.5px solid #e2e8f0; border-radius: 14px; background: #f8fafc;
  cursor: grab; transition: transform .12s, box-shadow .12s, border-color .12s;
}
.palette-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(15,23,42,.10); border-color: #cbd5e1; }
.palette-btn:active { cursor: grabbing; }
.palette-text { font-size: 10.5px; font-weight: 700; color: #334155; }

.palette-shape { display:block; width: 46px; height: 30px; border: 2px solid; }
.shape-start { border-radius: 999px; border-color:#0EA57A; background:#E4F7EF; }
.shape-end { border-radius: 999px; border-color:#DC2626; background:#FEE2E2; }
.shape-process { border-radius: 6px; border-color:#5B3DF5; background:#EEEBFD; }
.shape-io { border-color:#D97706; background:#FEF3E2; clip-path: polygon(14% 0%, 100% 0%, 86% 100%, 0% 100%); }
.shape-decision { border-color:#EA580C; background:#FFEEE4; clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%); height: 38px; }

.example-group { flex: 0 0 auto; }
.example-select {
  height: 40px; min-width: 200px; padding: 0 12px;
  border: 1.5px solid #e2e8f0; border-radius: 10px; background: #f8fafc;
  font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;
}

.action-group { display:flex; gap:8px; flex-wrap:wrap; margin-left:auto; }
.toolbar-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 14px;
  border: 1px solid #d1d5db; border-radius: 10px; background: #fff;
  cursor: pointer; font-weight: 700; font-size: 13px; color: #334155;
  transition: transform .12s, box-shadow .12s, filter .12s;
}
.toolbar-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(15,23,42,.10); }
.btn-icon { font-size: 13px; line-height: 1; }
.toolbar-btn.primary { background: linear-gradient(135deg,#0EA57A,#0c8c66); color: #fff; border-color: #0c8c66; }
.toolbar-btn.secondary { background: #f8fafc; }
.file-input input { display: none; }

@media (max-width: 900px) {
  .toolbar { flex-direction: column; align-items: stretch; }
  .action-group { margin-left: 0; }
}
</style>
