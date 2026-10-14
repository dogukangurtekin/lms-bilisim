import * as THREE from 'three';
import { createOrbitControls } from './controls';

function blockKey(x, y, z) {
  return `${x}:${y}:${z}`;
}

function toWorldCellCenter(v) {
  return Number(v) + 0.5;
}

// Desenler NÖTR (beyaz tabanlı) üretilir: seçilen renk malzemeye çarpılarak doğrudan görünür,
// desen yalnızca koyu/açık ince detay ekler. (Eskiden desenlerin kendi renkleri vardı ve seçilen rengi bulanıklaştırıyordu.)
function makePatternTexture(type = 'plain') {
  const size = 64;
  const canvas = document.createElement('canvas');
  canvas.width = size;
  canvas.height = size;
  const ctx = canvas.getContext('2d');
  if (!ctx) return null;

  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, size, size);

  const grain = (lines, dark, width, wobble) => {
    for (let i = 0; i < lines; i += 1) {
      ctx.strokeStyle = `rgba(0,0,0,${dark * (0.55 + (i % 3) * 0.25)})`;
      ctx.lineWidth = width;
      const y = ((i + 0.5) / lines) * size;
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.bezierCurveTo(size * 0.3, y + wobble, size * 0.7, y - wobble, size, y + wobble * 0.5);
      ctx.stroke();
    }
  };
  const specks = (count, maxSize, dark) => {
    for (let i = 0; i < count; i += 1) {
      const s = 1 + Math.random() * maxSize;
      ctx.fillStyle = `rgba(0,0,0,${dark * (0.4 + Math.random() * 0.6)})`;
      ctx.fillRect(Math.random() * size, Math.random() * size, s, s);
    }
  };
  const waves = (lines, dark, width, amp) => {
    for (let i = 0; i < lines; i += 1) {
      ctx.strokeStyle = `rgba(0,0,0,${dark})`;
      ctx.lineWidth = width;
      const y = ((i + 0.5) / lines) * size;
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.bezierCurveTo(size * 0.25, y - amp, size * 0.75, y + amp, size, y);
      ctx.stroke();
    }
  };
  const blades = (count, dark) => {
    for (let i = 0; i < count; i += 1) {
      ctx.fillStyle = `rgba(0,0,0,${dark * (0.5 + Math.random() * 0.5)})`;
      ctx.fillRect(Math.random() * size, Math.random() * size, 1.2, 2 + Math.random() * 3.5);
    }
  };

  if (type === 'wood_oak') grain(8, 0.17, 2, 2);
  else if (type === 'wood_walnut') grain(10, 0.22, 2, 2);
  else if (type === 'wood_pine') grain(7, 0.13, 1.8, 1.5);
  else if (type === 'concrete') specks(110, 3, 0.13);
  else if (type === 'concrete_dark') specks(150, 2.6, 0.2);
  else if (type === 'concrete_light') specks(90, 2.2, 0.08);
  else if (type === 'sea_shallow') waves(8, 0.1, 1.6, 2);
  else if (type === 'sea_deep') waves(9, 0.16, 1.4, 2.4);
  else if (type === 'sea_foam') {
    for (let i = 0; i < 70; i += 1) {
      ctx.strokeStyle = `rgba(0,0,0,${0.06 + Math.random() * 0.08})`;
      ctx.lineWidth = 1.2;
      ctx.beginPath();
      ctx.arc(Math.random() * size, Math.random() * size, 1 + Math.random() * 3, 0, Math.PI * 2);
      ctx.stroke();
    }
  } else if (type === 'grass') blades(150, 0.16);
  else if (type === 'grass_fresh') blades(130, 0.12);
  else if (type === 'grass_dark') blades(190, 0.22);
  else if (type === 'flat_soft') {
    const grad = ctx.createLinearGradient(0, 0, size, size);
    grad.addColorStop(0, '#ffffff');
    grad.addColorStop(1, '#f1f1f1');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, size, size);
  }
  // flat_matte / flat_gloss / plain: düz beyaz = seçilen renk aynen görünür

  const texture = new THREE.CanvasTexture(canvas);
  texture.colorSpace = THREE.SRGBColorSpace;
  texture.wrapS = THREE.RepeatWrapping;
  texture.wrapT = THREE.RepeatWrapping;
  texture.repeat.set(1, 1);
  texture.needsUpdate = true;
  return texture;
}

// Doku türüne göre yüzey parlaklığı (renk her durumda aynen kalır)
function surfaceFor(textureType) {
  if (textureType === 'flat_gloss') return { roughness: 0.22, metalness: 0.12 };
  if (textureType === 'flat_matte') return { roughness: 1, metalness: 0 };
  if (textureType === 'flat_soft') return { roughness: 0.7, metalness: 0 };
  if (textureType.startsWith('sea_')) return { roughness: 0.35, metalness: 0.05 };
  return { roughness: 0.82, metalness: 0.02 };
}

export class BlockScene {
  constructor({ container, onHoverCell, onBlocksChanged }) {
    this.container = container;
    this.onHoverCell = onHoverCell;
    this.onBlocksChanged = onBlocksChanged;
    this.mode = 'add';
    this.theme = 'light';
    this.activeColor = '#38bdf8';
    this.activeTexture = 'plain';
    this.blocks = new Map();
    this.animations = [];

    this.raycaster = new THREE.Raycaster();
    this.pointer = new THREE.Vector2();
    this.mouseEvent = null;

    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color('#dbe8ff');
    this.scene.fog = new THREE.Fog('#dbe8ff', 55, 150);

    this.camera = new THREE.PerspectiveCamera(52, 1, 0.1, 400);
    this.camera.position.set(22, 24, 22);

    this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    this.container.appendChild(this.renderer.domElement);
    this.controls = createOrbitControls(this.camera, this.renderer.domElement);
    this.controls.target.set(0, 0, 0);
    this.controls.update();

    this.grid = new THREE.GridHelper(64, 64, '#8fb0ea', '#c3d6f5');
    this.grid.position.y = 0;
    this.scene.add(this.grid);

    const planeGeo = new THREE.PlaneGeometry(64, 64);
    const planeMat = new THREE.MeshStandardMaterial({ color: '#e9f1ff', metalness: 0.02, roughness: 0.95, side: THREE.DoubleSide });
    this.groundPlane = new THREE.Mesh(planeGeo, planeMat);
    this.groundPlane.rotation.x = -Math.PI / 2;
    this.groundPlane.receiveShadow = true;
    this.scene.add(this.groundPlane);

    const ambient = new THREE.AmbientLight('#ffffff', 0.34);
    this.scene.add(ambient);
    this.ambientLight = ambient;

    const hemi = new THREE.HemisphereLight('#dbeafe', '#c7d2fe', 0.34);
    this.scene.add(hemi);
    this.hemiLight = hemi;

    const key = new THREE.DirectionalLight('#fff4e0', 0.78);
    key.position.set(18, 30, 12);
    key.shadow.normalBias = 0.03;
    key.shadow.radius = 3;
    key.castShadow = true;
    key.shadow.mapSize.width = 2048;
    key.shadow.mapSize.height = 2048;
    key.shadow.camera.left = -48;
    key.shadow.camera.right = 48;
    key.shadow.camera.top = 48;
    key.shadow.camera.bottom = -48;
    this.scene.add(key);
    this.keyLight = key;

    const hoverGeo = new THREE.BoxGeometry(1.02, 1.02, 1.02);
    const hoverMat = new THREE.MeshStandardMaterial({
      color: '#38bdf8',
      transparent: true,
      opacity: 0.3,
      emissive: '#38bdf8',
      emissiveIntensity: 0.25,
      depthWrite: false,
    });
    this.hoverCube = new THREE.Mesh(hoverGeo, hoverMat);
    this.hoverCube.visible = false;
    this.scene.add(this.hoverCube);

    const previewGeo = new THREE.BoxGeometry(1, 1, 1);
    const previewMat = new THREE.MeshStandardMaterial({
      color: this.activeColor,
      transparent: true,
      opacity: 0.45,
      emissive: this.activeColor,
      emissiveIntensity: 0.1,
    });
    this.previewCube = new THREE.Mesh(previewGeo, previewMat);
    this.previewCube.visible = false;
    this.scene.add(this.previewCube);

    this.baseGeometry = new THREE.BoxGeometry(1, 1, 1);
    this.baseGeometry.computeVertexNormals();
    this.textureCache = {
      plain: makePatternTexture('plain'),
      wood_oak: makePatternTexture('wood_oak'),
      wood_walnut: makePatternTexture('wood_walnut'),
      wood_pine: makePatternTexture('wood_pine'),
      concrete: makePatternTexture('concrete'),
      concrete_dark: makePatternTexture('concrete_dark'),
      concrete_light: makePatternTexture('concrete_light'),
      sea_shallow: makePatternTexture('sea_shallow'),
      sea_deep: makePatternTexture('sea_deep'),
      sea_foam: makePatternTexture('sea_foam'),
      grass: makePatternTexture('grass'),
      grass_fresh: makePatternTexture('grass_fresh'),
      grass_dark: makePatternTexture('grass_dark'),
      flat_matte: makePatternTexture('flat_matte'),
      flat_soft: makePatternTexture('flat_soft'),
      flat_gloss: makePatternTexture('flat_gloss'),
    };

    this.meshGroup = new THREE.Group();
    this.scene.add(this.meshGroup);

    this.instanceMeshes = [];
    this.timer = new THREE.Timer();
    this.timer.connect?.(document);
    this.isPointerDown = false;
    this.activePointerId = null;
    this.lastDragActionKey = '';
    this.pointerDownButton = -1;
    this.pointerDownPos = { x: 0, y: 0 };
    this.pointerDownAt = 0;
    this.didDragSincePointerDown = false;
    this.pointerDragDistance = 0;
    this.pointerDownCameraPos = new THREE.Vector3();
    this.pointerDownCameraQuat = new THREE.Quaternion();
    this.clickDragThreshold = 6;

    this.onPointerMove = this.onPointerMove.bind(this);
    this.onPointerDown = this.onPointerDown.bind(this);
    this.onPointerUp = this.onPointerUp.bind(this);
    this.onResize = this.onResize.bind(this);
    this.animate = this.animate.bind(this);

    this.renderer.domElement.addEventListener('pointermove', this.onPointerMove);
    this.renderer.domElement.addEventListener('pointerdown', this.onPointerDown);
    window.addEventListener('pointerup', this.onPointerUp);
    this.renderer.domElement.addEventListener('contextmenu', (ev) => ev.preventDefault());
    window.addEventListener('resize', this.onResize);
    this.resizeObserver = new ResizeObserver(() => this.onResize());
    this.resizeObserver.observe(this.container);

    this.onResize();
    this.setTheme('light');
    this.animate();
  }

  dispose() {
    this.renderer.domElement.removeEventListener('pointermove', this.onPointerMove);
    this.renderer.domElement.removeEventListener('pointerdown', this.onPointerDown);
    window.removeEventListener('pointerup', this.onPointerUp);
    window.removeEventListener('resize', this.onResize);
    this.resizeObserver?.disconnect();
    this.controls.dispose();
    this.renderer.dispose();
  }

  setMode(mode) {
    this.mode = mode;
  }

  setTheme(theme = 'light') {
    this.theme = theme === 'dark' ? 'dark' : 'light';
    const isDark = this.theme === 'dark';

    const sky = isDark ? '#0a1738' : '#dbe8ff';
    this.scene.background.set(sky);
    if (this.scene.fog) this.scene.fog.color.set(sky);
    const gridMaterial = this.grid.material;
    if (Array.isArray(gridMaterial)) {
      if (gridMaterial[0]) gridMaterial[0].color.set(isDark ? '#3a5db0' : '#8fb0ea');
      if (gridMaterial[1]) gridMaterial[1].color.set(isDark ? '#1f3470' : '#c3d6f5');
      gridMaterial.forEach((m) => { m.needsUpdate = true; });
    } else if (gridMaterial) {
      gridMaterial.color.set(isDark ? '#3a5db0' : '#8fb0ea');
      gridMaterial.needsUpdate = true;
    }
    this.groundPlane.material.color.set(isDark ? '#0d1d44' : '#e9f1ff');

    if (this.ambientLight) {
      this.ambientLight.color.set(isDark ? '#c9d8ff' : '#ffffff');
      this.ambientLight.intensity = isDark ? 0.34 : 0.34;
    }
    if (this.hemiLight) {
      this.hemiLight.color.set(isDark ? '#9db8f5' : '#dbeafe');
      this.hemiLight.groundColor.set(isDark ? '#08112b' : '#c7d2fe');
      this.hemiLight.intensity = isDark ? 0.3 : 0.34;
    }
    if (this.keyLight) {
      this.keyLight.color.set(isDark ? '#d6e2ff' : '#fff4e0');
      this.keyLight.intensity = isDark ? 0.8 : 0.78;
    }
  }

  setActiveMaterial({ color, texture }) {
    if (color) this.activeColor = color;
    if (texture) this.activeTexture = texture;
    this.previewCube.material.color.set(this.activeColor);
    this.previewCube.material.emissive.set(this.activeColor);
    const t = this.textureCache[this.activeTexture] || null;
    this.previewCube.material.map = t;
    this.previewCube.material.opacity = 0.7;
    this.previewCube.material.needsUpdate = true;
  }

  clearAll() {
    this.blocks.clear();
    this.rebuildInstances();
    this.emitChange();
  }

  setBlocks(blocks = []) {
    this.blocks.clear();
    for (const b of blocks) {
      const x = Number(b.x);
      const y = Number(b.y);
      const z = Number(b.z);
      if (!Number.isFinite(x) || !Number.isFinite(y) || !Number.isFinite(z)) continue;
      this.blocks.set(blockKey(x, y, z), {
        x,
        y,
        z,
        color: String(b.color || '#38bdf8'),
        texture: String(b.texture || 'plain'),
      });
    }
    this.rebuildInstances();
    this.emitChange();
  }

  getBlocks() {
    return Array.from(this.blocks.values());
  }

  resetCamera() {
    this.camera.position.set(22, 24, 22);
    this.controls.target.set(0, 0, 0);
    this.controls.update();
  }

  onResize() {
    const rect = this.container.getBoundingClientRect();
    const width = Math.max(1, Math.round(window.innerWidth || rect.width || this.container.clientWidth || 800));
    const height = Math.max(1, Math.round(window.innerHeight || rect.height || this.container.clientHeight || 600));
    this.camera.aspect = width / height;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(width, height);
  }

  getHighestY(x, z) {
    let highestY = -1;
    for (const b of this.blocks.values()) {
      if (b.x === x && b.z === z && b.y > highestY) highestY = b.y;
    }
    return highestY;
  }

  getHitBlockFromIntersection(hit) {
    const mesh = hit?.object;
    if (!(mesh instanceof THREE.InstancedMesh)) return null;
    const id = Number(hit.instanceId);
    if (!Number.isInteger(id) || id < 0) return null;
    const matrix = new THREE.Matrix4();
    const pos = new THREE.Vector3();
    mesh.getMatrixAt(id, matrix);
    pos.setFromMatrixPosition(matrix);
    const x = Math.floor(pos.x);
    const y = Math.floor(pos.y);
    const z = Math.floor(pos.z);
    return { x, y, z };
  }

  resolveFaceNormal(hit) {
    if (!hit?.face?.normal) return new THREE.Vector3(0, 1, 0);
    const n = hit.face.normal.clone();
    const absX = Math.abs(n.x);
    const absY = Math.abs(n.y);
    const absZ = Math.abs(n.z);
    if (absY >= absX && absY >= absZ) return new THREE.Vector3(0, Math.sign(n.y) || 1, 0);
    if (absX >= absY && absX >= absZ) return new THREE.Vector3(Math.sign(n.x) || 1, 0, 0);
    return new THREE.Vector3(0, 0, Math.sign(n.z) || 1);
  }

  raycastCell(event) {
    const rect = this.renderer.domElement.getBoundingClientRect();
    this.pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    this.pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    this.raycaster.setFromCamera(this.pointer, this.camera);
    const targets = [...this.instanceMeshes, this.groundPlane];
    const intersects = this.raycaster.intersectObjects(targets, false);
    if (!intersects.length) return null;

    const first = intersects[0];
    const hitBlock = this.getHitBlockFromIntersection(first);

    if (hitBlock) {
      const n = this.resolveFaceNormal(first);
      const addCell = {
        x: hitBlock.x + n.x,
        y: hitBlock.y + n.y,
        z: hitBlock.z + n.z,
      };
      if (addCell.y < 0) addCell.y = 0;
      return {
        addCell,
        eraseCell: hitBlock,
      };
    }

    const p = first.point;
    const x = Math.floor(p.x);
    const z = Math.floor(p.z);
    const highestY = this.getHighestY(x, z);
    return {
      addCell: { x, y: highestY + 1, z },
      eraseCell: highestY >= 0 ? { x, y: highestY, z } : null,
    };
  }

  applyActionAtCell(rayResult, modeOverride = this.mode) {
    if (!rayResult) return;
    if (modeOverride === 'erase') {
      const target = rayResult.eraseCell;
      if (target) this.deleteBlockAt(target.x, target.y, target.z);
      return;
    }
    const target = rayResult.addCell;
    if (!target) return;
    this.addBlock({
      x: target.x,
      y: target.y,
      z: target.z,
      color: this.activeColor,
      texture: this.activeTexture,
    });
  }

  onPointerMove(event) {
    this.mouseEvent = event;
    const rayResult = this.raycastCell(event);
    if (!rayResult) {
      this.hoverCube.visible = false;
      this.previewCube.visible = false;
      return;
    }
    const addCell = rayResult.addCell;
    const eraseCell = rayResult.eraseCell;
    const hudCell = this.mode === 'erase' && eraseCell ? eraseCell : addCell;

    this.hoverCube.visible = true;
    this.hoverCube.position.set(toWorldCellCenter(hudCell.x), hudCell.y + 0.5, toWorldCellCenter(hudCell.z));

    this.previewCube.visible = this.mode === 'add';
    this.previewCube.position.set(toWorldCellCenter(addCell.x), addCell.y + 0.5, toWorldCellCenter(addCell.z));

    if (this.onHoverCell) {
      this.onHoverCell(hudCell);
    }

    if (this.isPointerDown && this.activePointerId === event.pointerId) {
      const dx = Number(event.clientX || 0) - this.pointerDownPos.x;
      const dy = Number(event.clientY || 0) - this.pointerDownPos.y;
      this.pointerDragDistance = Math.max(this.pointerDragDistance, Math.hypot(dx, dy));
      if (this.pointerDragDistance > 1.5) this.didDragSincePointerDown = true;
    }
  }

  onPointerDown(event) {
    if (event.button === 1) return;
    this.isPointerDown = true;
    this.activePointerId = event.pointerId;
    this.pointerDownButton = Number(event.button);
    this.pointerDownPos = { x: Number(event.clientX || 0), y: Number(event.clientY || 0) };
    this.pointerDownAt = performance.now();
    this.didDragSincePointerDown = false;
    this.pointerDragDistance = 0;
    this.pointerDownCameraPos.copy(this.camera.position);
    this.pointerDownCameraQuat.copy(this.camera.quaternion);
    this.lastDragActionKey = '';
  }

  onPointerUp(event) {
    if (this.activePointerId !== null && event.pointerId !== this.activePointerId) return;
    const button = this.pointerDownButton;
    const pressDurationMs = Math.max(0, performance.now() - Number(this.pointerDownAt || 0));
    const movedByPointer = this.didDragSincePointerDown || this.pointerDragDistance > this.clickDragThreshold;
    const movedByCamera =
      this.camera.position.distanceTo(this.pointerDownCameraPos) > 0.001
      || (1 - Math.abs(this.camera.quaternion.dot(this.pointerDownCameraQuat))) > 0.00001;
    const isQuickClick = pressDurationMs <= 220;
    const isClickAction = !movedByPointer && !movedByCamera && isQuickClick;

    if (isClickAction && (button === 0 || button === 2)) {
      const rayResult = this.raycastCell(event);
      if (rayResult) {
        if (button === 2) this.applyActionAtCell(rayResult, 'erase');
        else this.applyActionAtCell(rayResult, this.mode);
      }
    }

    this.isPointerDown = false;
    this.activePointerId = null;
    this.pointerDownButton = -1;
    this.pointerDownAt = 0;
    this.didDragSincePointerDown = false;
    this.pointerDragDistance = 0;
    this.lastDragActionKey = '';
  }

  addBlock(block) {
    const key = blockKey(block.x, block.y, block.z);
    if (this.blocks.has(key)) return;
    this.blocks.set(key, { ...block });
    this.animations.push({
      key,
      startAt: performance.now(),
      duration: 180,
    });
    this.rebuildInstances();
    this.emitChange();
  }

  deleteTopBlock(x, z) {
    const highestY = this.getHighestY(x, z);
    if (highestY < 0) return;
    this.blocks.delete(blockKey(x, highestY, z));
    this.rebuildInstances();
    this.emitChange();
  }

  deleteBlockAt(x, y, z) {
    const key = blockKey(x, y, z);
    if (!this.blocks.has(key)) return;
    this.blocks.delete(key);
    this.rebuildInstances();
    this.emitChange();
  }

  emitChange() {
    if (this.onBlocksChanged) this.onBlocksChanged(this.getBlocks());
  }

  rebuildInstances() {
    for (const mesh of this.instanceMeshes) {
      this.meshGroup.remove(mesh);
      mesh.dispose?.();
      mesh.material?.dispose?.();
    }
    this.instanceMeshes = [];

    const grouped = new Map();
    for (const block of this.blocks.values()) {
      const gKey = `${block.texture}::${block.color}`;
      if (!grouped.has(gKey)) grouped.set(gKey, []);
      grouped.get(gKey).push(block);
    }

    const dummy = new THREE.Object3D();
    for (const [gKey, entries] of grouped.entries()) {
      const [textureType, color] = gKey.split('::');
      const map = this.textureCache[textureType] || null;
      const mat = new THREE.MeshStandardMaterial({
        color,
        map,
        ...surfaceFor(textureType),
      });
      const mesh = new THREE.InstancedMesh(this.baseGeometry, mat, entries.length);
      mesh.castShadow = true;
      mesh.receiveShadow = true;

      entries.forEach((block, index) => {
        dummy.position.set(toWorldCellCenter(block.x), block.y + 0.5, toWorldCellCenter(block.z));
        dummy.scale.set(1, 1, 1);
        dummy.updateMatrix();
        mesh.setMatrixAt(index, dummy.matrix);
      });
      mesh.instanceMatrix.needsUpdate = true;
      this.meshGroup.add(mesh);
      this.instanceMeshes.push(mesh);
    }
  }

  applyAnimations() {
    if (!this.animations.length) return;
    const now = performance.now();
    const byKeyScale = new Map();

    this.animations = this.animations.filter((anim) => {
      const elapsed = now - anim.startAt;
      const t = Math.min(1, elapsed / anim.duration);
      const eased = t < 1 ? (0.2 + 0.8 * (1 - Math.pow(1 - t, 3))) : 1;
      byKeyScale.set(anim.key, eased);
      return t < 1;
    });

    if (!byKeyScale.size) return;

    for (const mesh of this.instanceMeshes) {
      const dummy = new THREE.Object3D();
      const color = mesh.material?.color?.getStyle?.() || '';
      const texture = Object.keys(this.textureCache).find((k) => mesh.material?.map === this.textureCache[k]) || 'plain';

      const blocks = Array.from(this.blocks.values()).filter((b) => {
        const textureMatch = b.texture === texture;
        const c = new THREE.Color(b.color).getStyle();
        return textureMatch && c === color;
      });

      blocks.forEach((block, idx) => {
        const s = byKeyScale.get(blockKey(block.x, block.y, block.z)) || 1;
        dummy.position.set(toWorldCellCenter(block.x), block.y + 0.5, toWorldCellCenter(block.z));
        dummy.scale.set(s, s, s);
        dummy.updateMatrix();
        mesh.setMatrixAt(idx, dummy.matrix);
      });
      mesh.instanceMatrix.needsUpdate = true;
    }
  }

  animate() {
    requestAnimationFrame(this.animate);
    this.timer.update();
    const delta = this.timer.getDelta();
    if (delta > 0.3) return;
    this.controls.update();
    this.applyAnimations();
    this.renderer.render(this.scene, this.camera);
  }
}

