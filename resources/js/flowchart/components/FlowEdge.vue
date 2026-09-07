<template>
  <BaseEdge :id="id" :path="edgePath[0]" :marker-end="markerEnd" />
  <EdgeLabelRenderer>
    <div
      v-if="label"
      :class="['edge-label', `edge-label--${data?.condition}`]"
      :style="{ transform: `translate(-50%, -50%) translate(${edgePath[1]}px,${edgePath[2]}px)` }"
    >
      {{ label }}
    </div>
  </EdgeLabelRenderer>
</template>

<script setup>
import { computed } from 'vue';
import { BaseEdge, EdgeLabelRenderer, getBezierPath } from '@vue-flow/core';

const props = defineProps({
  id: { type: String, required: true },
  sourceX: { type: Number, required: true },
  sourceY: { type: Number, required: true },
  sourcePosition: { type: String, required: true },
  targetX: { type: Number, required: true },
  targetY: { type: Number, required: true },
  targetPosition: { type: String, required: true },
  markerEnd: { type: String, default: undefined },
  data: { type: Object, default: () => ({}) },
});

const edgePath = computed(() => getBezierPath(props));
const label = computed(() => {
  const c = props.data?.condition;
  if (c === 'yes') return 'YES';
  if (c === 'no') return 'NO';
  return '';
});
</script>

<style scoped>
.edge-label { background:#0f172a; color:#fff; padding:3px 9px; border-radius:999px; font-size:10.5px; font-weight:800; letter-spacing:.03em; box-shadow:0 4px 10px rgba(15,23,42,.25); }
.edge-label--yes { background:#0EA57A; }
.edge-label--no { background:#DC2626; }
</style>

