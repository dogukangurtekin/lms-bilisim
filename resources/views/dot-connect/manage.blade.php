@extends('layout.app')
@section('title','Noktaları Birleştir Yönetimi')
@section('content')
<style>
  .dc-wrap{max-width:1200px;margin:0 auto;padding:16px}
  .dc-hero{border-radius:18px;padding:18px 20px;color:#fff;background:linear-gradient(120deg,#16a34a,#0ea5e9);display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start}
  .dc-hero h1{margin:0 0 4px;font-size:22px}
  .dc-layout{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(300px,.85fr);gap:18px;margin-top:18px;align-items:start}
  .dc-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px}
  .dc-card h2{margin:0 0 12px;font-size:17px}
  .dc-inp,.dc-sel{width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:9px 10px;box-sizing:border-box}
  .dc-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
  .dc-field{margin-bottom:10px}
  .dc-field label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:4px}
  .dc-check-group{display:flex;flex-wrap:wrap;gap:10px}
  .dc-check-group label{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#334155}
  .dc-editor-board{background:#16a34a;border-radius:14px;padding:18px;display:flex;justify-content:center;margin-bottom:12px}
  .dc-dotgrid{display:grid;gap:26px;position:relative}
  .dc-dot{width:16px;height:16px;border-radius:50%;background:rgba(255,255,255,.65);cursor:pointer;transition:transform .1s ease, background .1s ease}
  .dc-dot:hover{transform:scale(1.25)}
  .dc-dot.start{background:#facc15;box-shadow:0 0 0 3px rgba(250,204,21,.4)}
  .dc-dot.used{background:#fff}
  .dc-dotgrid svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;overflow:visible}
  .dc-editor-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
  .dc-btn{border:0;border-radius:10px;padding:9px 14px;font-weight:700;cursor:pointer}
  .dc-btn.primary{background:linear-gradient(90deg,#16a34a,#0ea5e9);color:#fff}
  .dc-btn.ghost{background:#e2e8f0;color:#334155}
  .dc-btn.danger{background:#fee2e2;color:#b91c1c}
  .dc-level-item{border:1px solid #e2e8f0;border-radius:14px;padding:12px;display:flex;gap:12px;align-items:center;margin-bottom:10px}
  .dc-level-item .mini{width:64px;height:64px;background:#16a34a;border-radius:10px;flex:0 0 auto;position:relative}
  .dc-level-item .mini svg{position:absolute;inset:6px}
  .dc-level-item .info{flex:1;min-width:0}
  .dc-level-item .info strong{display:block;font-size:14px}
  .dc-level-item .info span{font-size:12px;color:#64748b}
  .dc-level-actions{display:flex;gap:6px;flex-wrap:wrap}
  .dc-badge{display:inline-block;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;margin-left:6px}
  .dc-badge.on{background:#dcfce7;color:#166534}
  .dc-badge.off{background:#fee2e2;color:#991b1b}
  .dc-flash{margin-bottom:14px;padding:10px 14px;border-radius:10px;background:#dcfce7;color:#166534;font-weight:700}
  @media(max-width:960px){.dc-layout{grid-template-columns:1fr}}
</style>

<div class="dc-wrap">
  <div class="dc-hero">
    <div>
      <h1>Noktaları Birleştir — Bölüm Yönetimi</h1>
      <p style="margin:0;opacity:.9">Öğrencilerin blok komutlarla çizeceği hedef şekilleri buradan tasarlayın.</p>
    </div>
    <a href="{{ route('activities.index') }}" class="dc-btn ghost">Etkinlikler Sayfasına Dön</a>
  </div>

  @if(session('ok'))
    <div class="dc-flash" style="margin-top:16px">{{ session('ok') }}</div>
  @endif

  <div class="dc-layout">
    <div class="dc-card">
      <h2 id="dc-form-title">Yeni Bölüm Tasarla</h2>
      <form id="dc-form" method="POST" action="{{ route('dot-connect.store') }}">
        @csrf
        <input type="hidden" name="_method" id="dc-method" value="POST">
        <div class="dc-row">
          <div class="dc-field">
            <label>Bölüm Adı</label>
            <input class="dc-inp" type="text" name="name" id="dc-name" placeholder="Örn: Kare Çiz">
          </div>
          <div class="dc-field">
            <label>Izgara Boyutu</label>
            <select class="dc-sel" name="grid_size" id="dc-grid-size">
              @for($i=3;$i<=8;$i++)
                <option value="{{ $i }}" @selected($i===4)>{{ $i }} x {{ $i }}</option>
              @endfor
            </select>
          </div>
        </div>

        <div class="dc-field">
          <label>Hedef Şekil — noktalara sırayla tıklayarak çizgiyi oluşturun (ilk tıklama başlangıç/kalem konumu olur)</label>
          <div class="dc-editor-board">
            <div id="dc-dotgrid" class="dc-dotgrid"></div>
          </div>
          <div class="dc-editor-actions">
            <button type="button" id="dc-undo" class="dc-btn ghost">Son Noktayı Geri Al</button>
            <button type="button" id="dc-reset" class="dc-btn ghost">Şekli Temizle</button>
          </div>
        </div>

        <div class="dc-row">
          <div class="dc-field">
            <label>Kalemin Başlangıç Yönü</label>
            <select class="dc-sel" name="start_direction" id="dc-start-direction">
              <option value="up">Yukarı</option>
              <option value="right" selected>Sağ</option>
              <option value="down">Aşağı</option>
              <option value="left">Sol</option>
            </select>
          </div>
          <div class="dc-field">
            <label>Maksimum Komut Sayısı</label>
            <input class="dc-inp" type="number" name="max_commands" id="dc-max-commands" min="2" max="30" value="10">
          </div>
        </div>

        <div class="dc-row">
          <div class="dc-field">
            <label>XP Ödülü</label>
            <input class="dc-inp" type="number" name="xp" id="dc-xp" min="0" max="500" value="10">
          </div>
          <div class="dc-field">
            <label style="display:flex;align-items:center;gap:8px;margin-top:22px">
              <input type="checkbox" name="is_active" id="dc-active" value="1" checked style="width:auto"> Bölüm aktif
            </label>
          </div>
        </div>

        <div class="dc-field">
          <label>Kullanılabilir Komutlar</label>
          <div class="dc-check-group">
            <label><input type="checkbox" name="allowed_commands[]" value="move_up" checked> Yukarı</label>
            <label><input type="checkbox" name="allowed_commands[]" value="move_right" checked> Sağ</label>
            <label><input type="checkbox" name="allowed_commands[]" value="move_down" checked> Aşağı</label>
            <label><input type="checkbox" name="allowed_commands[]" value="move_left" checked> Sol</label>
            <label><input type="checkbox" name="allowed_commands[]" value="repeat"> Tekrarla (döngü)</label>
          </div>
        </div>

        <input type="hidden" name="target_dots" id="dc-target-dots" value="[]">
        <input type="hidden" name="start_point" id="dc-start-point" value="{}">

        <div style="display:flex;gap:10px;margin-top:14px">
          <button type="submit" class="dc-btn primary" id="dc-submit-btn">Bölümü Kaydet</button>
          <button type="button" class="dc-btn ghost" id="dc-cancel-edit" style="display:none">Vazgeç</button>
        </div>
      </form>
    </div>

    <div class="dc-card">
      <h2>Bölümler ({{ $levels->count() }})</h2>
      @forelse($levels as $level)
        <div class="dc-level-item">
          <div class="mini">
            <svg viewBox="0 0 {{ $level->grid_size - 1 }} {{ $level->grid_size - 1 }}" preserveAspectRatio="xMidYMid meet">
              @php $dots = $level->target_dots ?? []; @endphp
              @for($i=0;$i<count($dots)-1;$i++)
                <line x1="{{ $dots[$i]['x'] }}" y1="{{ $dots[$i]['y'] }}" x2="{{ $dots[$i+1]['x'] }}" y2="{{ $dots[$i+1]['y'] }}" stroke="#fff" stroke-width="0.18" stroke-linecap="round"></line>
              @endfor
            </svg>
          </div>
          <div class="info">
            <strong>{{ $level->name ?: ('Bölüm '.$level->id) }}
              <span class="dc-badge {{ $level->is_active ? 'on' : 'off' }}">{{ $level->is_active ? 'Aktif' : 'Pasif' }}</span>
            </strong>
            <span>{{ $level->grid_size }}x{{ $level->grid_size }} ızgara · {{ $level->xp }} XP · {{ count($dots) }} nokta</span>
          </div>
          <div class="dc-level-actions">
            <button type="button" class="dc-btn ghost dc-edit-btn"
              data-level="{{ json_encode([
                  'id' => $level->id,
                  'name' => $level->name,
                  'grid_size' => $level->grid_size,
                  'target_dots' => $level->target_dots,
                  'start_point' => $level->start_point,
                  'start_direction' => $level->start_direction,
                  'allowed_commands' => $level->allowed_commands,
                  'max_commands' => $level->max_commands,
                  'xp' => $level->xp,
                  'is_active' => $level->is_active,
              ]) }}">Düzenle</button>
            <form method="POST" action="{{ route('dot-connect.toggle', $level) }}">
              @csrf
              <button type="submit" class="dc-btn ghost">{{ $level->is_active ? 'Pasif Yap' : 'Aktif Yap' }}</button>
            </form>
            <form method="POST" action="{{ route('dot-connect.destroy', $level) }}" onsubmit="return confirm('Bu bölüm silinsin mi?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="dc-btn danger">Sil</button>
            </form>
          </div>
        </div>
      @empty
        <p style="color:#64748b">Henüz bölüm eklenmedi. Soldaki formu kullanarak ilk bölümü oluşturun.</p>
      @endforelse
    </div>
  </div>
</div>

<script>
(function () {
  const gridSizeSel = document.getElementById('dc-grid-size');
  const dotGridEl = document.getElementById('dc-dotgrid');
  const targetDotsInput = document.getElementById('dc-target-dots');
  const startPointInput = document.getElementById('dc-start-point');
  const form = document.getElementById('dc-form');
  const methodInput = document.getElementById('dc-method');
  const formTitle = document.getElementById('dc-form-title');
  const submitBtn = document.getElementById('dc-submit-btn');
  const cancelBtn = document.getElementById('dc-cancel-edit');

  let path = []; // ordered [{x,y}, ...]
  let svg = null;

  function buildGrid(size) {
    dotGridEl.innerHTML = '';
    dotGridEl.style.gridTemplateColumns = `repeat(${size}, 1fr)`;
    dotGridEl.style.gridTemplateRows = `repeat(${size}, 1fr)`;
    for (let y = 0; y < size; y++) {
      for (let x = 0; x < size; x++) {
        const dot = document.createElement('div');
        dot.className = 'dc-dot';
        dot.dataset.x = x;
        dot.dataset.y = y;
        dot.addEventListener('click', () => addPoint(x, y));
        dotGridEl.appendChild(dot);
      }
    }
    svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    dotGridEl.appendChild(svg);
  }

  function dotEl(x, y) {
    return dotGridEl.querySelector(`.dc-dot[data-x="${x}"][data-y="${y}"]`);
  }

  function center(el) {
    const gridRect = dotGridEl.getBoundingClientRect();
    const r = el.getBoundingClientRect();
    return { left: r.left - gridRect.left + r.width / 2, top: r.top - gridRect.top + r.height / 2 };
  }

  function redraw() {
    svg.innerHTML = '';
    dotGridEl.querySelectorAll('.dc-dot').forEach((d) => d.classList.remove('used', 'start'));
    path.forEach((p, i) => {
      const el = dotEl(p.x, p.y);
      if (el) el.classList.add(i === 0 ? 'start' : 'used');
    });
    for (let i = 0; i < path.length - 1; i++) {
      const a = dotEl(path[i].x, path[i].y);
      const b = dotEl(path[i + 1].x, path[i + 1].y);
      if (!a || !b) continue;
      const c1 = center(a);
      const c2 = center(b);
      const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
      line.setAttribute('x1', c1.left);
      line.setAttribute('y1', c1.top);
      line.setAttribute('x2', c2.left);
      line.setAttribute('y2', c2.top);
      line.setAttribute('stroke', '#fff');
      line.setAttribute('stroke-width', '3');
      line.setAttribute('stroke-linecap', 'round');
      svg.appendChild(line);
    }
    targetDotsInput.value = JSON.stringify(path);
    startPointInput.value = JSON.stringify(path[0] || {});
  }

  function addPoint(x, y) {
    const last = path[path.length - 1];
    if (last && last.x === x && last.y === y) return;
    path.push({ x, y });
    redraw();
  }

  document.getElementById('dc-undo').addEventListener('click', () => {
    path.pop();
    redraw();
  });
  document.getElementById('dc-reset').addEventListener('click', () => {
    path = [];
    redraw();
  });
  gridSizeSel.addEventListener('change', () => {
    path = [];
    buildGrid(Number(gridSizeSel.value));
    redraw();
  });

  function resetForm() {
    form.action = "{{ route('dot-connect.store') }}";
    methodInput.value = 'POST';
    formTitle.textContent = 'Yeni Bölüm Tasarla';
    submitBtn.textContent = 'Bölümü Kaydet';
    cancelBtn.style.display = 'none';
    document.getElementById('dc-name').value = '';
    document.getElementById('dc-start-direction').value = 'right';
    document.getElementById('dc-max-commands').value = 10;
    document.getElementById('dc-xp').value = 10;
    document.getElementById('dc-active').checked = true;
    form.querySelectorAll('input[name="allowed_commands[]"]').forEach((cb) => {
      cb.checked = cb.value !== 'repeat';
    });
    gridSizeSel.value = 4;
    path = [];
    buildGrid(4);
    redraw();
  }

  document.querySelectorAll('.dc-edit-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const data = JSON.parse(btn.dataset.level);
      form.action = "{{ url('/noktalari-birlestir') }}/" + data.id;
      methodInput.value = 'PUT';
      formTitle.textContent = 'Bölümü Düzenle: ' + (data.name || ('Bölüm ' + data.id));
      submitBtn.textContent = 'Değişiklikleri Kaydet';
      cancelBtn.style.display = '';
      document.getElementById('dc-name').value = data.name || '';
      document.getElementById('dc-start-direction').value = data.start_direction || 'right';
      document.getElementById('dc-max-commands').value = data.max_commands || 10;
      document.getElementById('dc-xp').value = data.xp || 0;
      document.getElementById('dc-active').checked = !!data.is_active;
      gridSizeSel.value = data.grid_size || 4;
      const allowed = data.allowed_commands || [];
      form.querySelectorAll('input[name="allowed_commands[]"]').forEach((cb) => {
        cb.checked = allowed.includes(cb.value);
      });
      buildGrid(Number(gridSizeSel.value));
      path = (data.target_dots || []).map((p) => ({ x: Number(p.x), y: Number(p.y) }));
      redraw();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  cancelBtn.addEventListener('click', resetForm);

  form.addEventListener('submit', (e) => {
    if (path.length < 2) {
      e.preventDefault();
      alert('Lütfen en az iki nokta seçerek bir şekil çizin.');
    }
  });

  buildGrid(4);
  redraw();
})();
</script>
@endsection
