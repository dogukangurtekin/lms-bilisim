import { createApp } from 'vue';
import { createPinia } from 'pinia';
import FlowchartEditor from './flowchart/FlowchartEditor.vue';
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';

const mountEl = document.getElementById('flowchart-editor-app');
if (mountEl) {
  const app = createApp(FlowchartEditor);
  app.use(createPinia());
  app.mount(mountEl);
}

