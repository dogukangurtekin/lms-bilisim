<template>
  <BaseEdge :id="id" :path="edgePath[0]" :marker-end="markerEnd" />
  <EdgeLabelRenderer>
    <div
      v-if="label"
      class="edge-label"
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
.edge-label { background:#0f172a; color:#fff; padding:2px 6px; border-radius:8px; font-size:11px; font-weight:800; }
</style>

