<template>
  <div :class="['node-shell', `node-${data.type}`, { active: data.active }]">
    <Handle type="target" :position="Position.Top" class="flow-handle" />
    <Handle
      v-if="data.type === 'decision'"
      id="yes"
      type="source"
      :position="Position.Right"
      class="flow-handle flow-handle-yes"
    />
    <Handle
      v-if="data.type === 'decision'"
      id="no"
      type="source"
      :position="Position.Bottom"
      class="flow-handle flow-handle-no"
    />
    <Handle
      v-if="data.type !== 'end' && data.type !== 'decision'"
      type="source"
      :position="Position.Bottom"
      class="flow-handle"
    />
    <div class="node-title">{{ title }}</div>
    <div class="node-text">{{ data.text || placeholder }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Handle, Position } from '@vue-flow/core';

const props = defineProps({
  data: { type: Object, required: true },
});

const title = computed(() => {
  const map = {
    start: 'Start',
    end: 'End',
    process: 'Process',
    io: 'Input / Output',
    decision: 'Decision',
  };
  return map[props.data.type] || 'Node';
});

const placeholder = computed(() => {
  if (props.data.type === 'process') return 'x = x + 1';
  if (props.data.type === 'decision') return 'x > 10';
  if (props.data.type === 'io') return 'output x';
  return '...';
});
</script>

<style scoped>
.node-shell { min-width:160px; max-width:220px; border:2px solid #cbd5e1; background:#fff; border-radius:16px; box-shadow:0 8px 20px rgba(15,23,42,.12); padding:10px 12px; }
.node-title { font-weight:800; font-size:12px; text-transform:uppercase; color:#334155; margin-bottom:4px; }
.node-text { font-size:13px; color:#0f172a; line-height:1.3; }
.node-start,.node-end { border-radius:999px; }
.node-decision { transform:rotate(45deg); width:150px; height:150px; display:grid; place-items:center; padding:0; }
.node-decision .node-title,.node-decision .node-text { transform:rotate(-45deg); text-align:center; max-width:110px; }
.active { border-color:#14b8a6; box-shadow:0 0 0 4px rgba(20,184,166,.2); }
.flow-handle { width:10px; height:10px; border:2px solid #0f172a; background:#fff; }
.flow-handle-yes { background:#86efac; border-color:#15803d; }
.flow-handle-no { background:#fecaca; border-color:#b91c1c; }
</style>
