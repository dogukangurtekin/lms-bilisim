import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

export function createOrbitControls(camera, domElement) {
  const controls = new OrbitControls(camera, domElement);
  controls.enableDamping = true;
  controls.dampingFactor = 0.08;
  controls.enablePan = true;
  controls.screenSpacePanning = true;
  controls.minDistance = 6;
  controls.maxDistance = 120;
  controls.maxPolarAngle = Math.PI / 2.02;
  controls.mouseButtons.LEFT = 0;
  controls.mouseButtons.MIDDLE = 1;
  controls.mouseButtons.RIGHT = 2;
  return controls;
}

