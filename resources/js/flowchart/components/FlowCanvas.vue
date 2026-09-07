<template>
  <div class="canvas-wrap" @dragover="onDragOver" @drop="onDrop">
    <VueFlow
      ref="flowRef"
      v-model:nodes="localNodes"
      v-model:edges="localEdges"
      class="canvas"
      :connection-mode="VFConnectionMode.Loose"
      :connect-on-click="false"
      :snap-to-grid="true"
      :snap-grid="[20, 20]"
      :min-zoom="0.4"
      :max-zoom="2.2"
      :fit-view-on-init="true"
      :node-types="nodeTypes"
      :edge-types="edgeTypes"
      @connect="onConnect"
      @node-click="onNodeClick"
      @pane-click="$emit('clear-selection')"
      @node-drag-stop="onNodeDragStop"
    >
      <Background pattern-color="#e2e8f0" :gap="20" />
      <MiniMap />
      <Controls />
    </VueFlow>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ConnectionMode as VFConnectionMode, MarkerType, VueFlow, useVueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import FlowNode from './FlowNode.vue';
import FlowEdge from './FlowEdge.vue';

const props = defineProps({
  nodes: { type: Array, required: true },
  edges: { type: Array, required: true },
  selectedNodeId: { type: String, default: '' },
  executingNodeId: { type: String, default: '' },
  waitingNodeId: { type: String, default: '' },
});

const emit = defineEmits([
  'update:nodes',
  'update:edges',
  'connect',
  'select-node',
  'clear-selection',
  'node-move',
  'add-node-at',
]);

const nodeTypes = { default: FlowNode };
const edgeTypes = { default: FlowEdge };
const flowRef = ref(null);
const { screenToFlowCoordinate } = useVueFlow();

const localNodes = computed({
  get() {
    return props.nodes.map((node) => ({
      ...node,
      data: {
        ...(node.data || {}),
        type: node.type,
        text: node.text,
        code: node.code,
        active: node.id === props.selectedNodeId,
        executing: node.id === props.executingNodeId,
        waiting: node.id === props.waitingNodeId,
      },
      type: 'default',
    }));
  },
  set(value) {
    emit(
      'update:nodes',
      value.map((node) => ({
        id: node.id,
        type: node.data?.type || node.type,
        text: node.data?.text ?? node.text ?? '',
        code: node.data?.code ?? node.code ?? '',
        position: node.position,
      }))
    );
  },
});

const localEdges = computed({
  get() {
      return props.edges.map((edge) => ({
        ...edge,
        type: 'default',
        markerEnd: MarkerType.ArrowClosed,
      data: { condition: edge.condition || null },
      label: edge.condition ? edge.condition.toUpperCase() : '',
    }));
  },
  set(value) {
    emit(
      'update:edges',
      value.map((edge) => ({
        id: edge.id,
        from: edge.source ?? edge.from,
        to: edge.target ?? edge.to,
        condition: edge.data?.condition ?? edge.condition ?? null,
      }))
    );
  },
});

function onConnect(connection) {
  emit('connect', connection);
}

function onNodeClick({ node }) {
  emit('select-node', node.id);
}

function onNodeDragStop({ node }) {
  emit('node-move', { id: node.id, position: node.position });
}

function onDragOver(event) {
  event.preventDefault();
  if (event?.dataTransfer) {
    event.dataTransfer.dropEffect = 'copy';
  }
}

function onDrop(event) {
  event.preventDefault();
  const type = event?.dataTransfer?.getData('application/flowchart-node-type');
  if (!type) return;
  const position = screenToFlowCoordinate({
    x: event.clientX,
    y: event.clientY,
  });
  emit('add-node-at', { type, position });
}
</script>

<style scoped>
.canvas-wrap {
  border: 1px solid #dbe4ee;
  border-radius: 20px;
  overflow: hidden;
  background:
    radial-gradient(circle at 0% 0%, rgba(91,61,245,.06), transparent 40%),
    radial-gradient(circle at 100% 100%, rgba(14,165,122,.06), transparent 40%),
    #f8fafc;
  min-height: 640px;
  box-shadow: 0 20px 44px rgba(15,23,42,.06);
}
.canvas { width:100%; height:640px; }
.canvas :deep(.vue-flow__minimap) { border-radius: 12px; overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.14); }
.canvas :deep(.vue-flow__controls) { border-radius: 12px; overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.14); }
.canvas :deep(.vue-flow__controls-button) { border-color: #e2e8f0; }
.canvas :deep(.vue-flow__edge-path) { stroke: #94a3b8; stroke-width: 2.2; }
.canvas :deep(.vue-flow__edge.selected .vue-flow__edge-path) { stroke: #5B3DF5; }
</style>
