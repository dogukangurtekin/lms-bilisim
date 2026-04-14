export const sampleFlowchart = {
  name: 'Yeni Flowchart',
  nodes: [
    { id: 'n-start', type: 'start', text: 'Başla', code: '', position: { x: 160, y: 180 } },
    { id: 'n-end', type: 'end', text: 'Bitir', code: '', position: { x: 460, y: 180 } },
  ],
  edges: [
    { id: 'e1', from: 'n-start', to: 'n-end', condition: null },
  ],
};
