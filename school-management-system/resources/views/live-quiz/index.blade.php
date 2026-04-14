@extends('layout.app')
@section('title','Canli Quiz')
@section('content')
<style>
.quiz-studio{display:grid;gap:12px}
.quiz-shell{display:grid;grid-template-columns:260px minmax(0,1fr) 320px;gap:12px;align-items:stretch}
.quiz-card{border:1px solid #dbe3ef;border-radius:14px;background:#fff}
.quiz-left{padding:12px;display:grid;gap:10px;align-content:start}
.quiz-left-head{display:flex;justify-content:space-between;align-items:center}
.quiz-left-head h3{margin:0;font-size:16px}
.quiz-left-list{display:grid;gap:8px;max-height:62vh;overflow:auto}
.quiz-left-item{border:1px solid #cbd5e1;background:#f8fafc;border-radius:10px;padding:10px;cursor:pointer;text-align:left;font-weight:700;color:#0f172a}
.quiz-left-item.active{border-color:#4f46e5;background:#eef2ff;box-shadow:0 0 0 2px rgba(79,70,229,.14)}
.quiz-left-add{width:100%}
.quiz-center{padding:14px;background:linear-gradient(145deg,#4c1d95,#6d28d9 40%,#7c3aed);position:relative;overflow:hidden}
.quiz-center::before{content:"";position:absolute;inset:auto -80px -120px auto;width:280px;height:280px;border-radius:999px;background:rgba(255,255,255,.08)}
.quiz-center-main{position:relative;z-index:2;display:grid;gap:12px}
.quiz-question-input{width:100%;font-size:30px;font-weight:800;background:#fff;border:0;border-radius:12px;padding:16px 18px}
.quiz-media-box{background:rgba(255,255,255,.18);border:1px dashed rgba(255,255,255,.55);border-radius:14px;min-height:180px;display:grid;place-items:center;color:#fff;font-weight:700}
.quiz-answers{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.answer-card{display:grid;grid-template-columns:38px 1fr 32px;gap:8px;align-items:center;background:#fff;border-radius:10px;padding:8px}
.answer-shape{width:30px;height:30px;border-radius:6px;display:grid;place-items:center;color:#fff;font-weight:800}
.answer-shape.red{background:#ef4444}
.answer-shape.blue{background:#3b82f6}
.answer-shape.yellow{background:#eab308}
.answer-shape.green{background:#22c55e}
.answer-input{margin:0;border:1px solid #d1d5db;border-radius:8px;padding:8px}
.answer-correct{width:18px;height:18px}
.quiz-right{padding:12px;display:grid;gap:10px;align-content:start}
.quiz-right h3{margin:0 0 2px;font-size:16px}
.quiz-row{display:grid;gap:6px}
.quiz-row label{font-size:12px;font-weight:700;color:#475569}
.quiz-row .form-control,.quiz-row input,.quiz-row select,.quiz-row textarea{margin:0}
.quiz-right .btn{width:100%}
.quiz-save{display:flex;justify-content:flex-end}
.quiz-table-wrap{overflow:auto}
@media (max-width:1200px){.quiz-shell{grid-template-columns:1fr}.quiz-left-list{max-height:220px}.quiz-answers{grid-template-columns:1fr}}
</style>

<div class="quiz-studio">
    <div class="top">
        <h1>Canli Quiz Merkezi</h1>
    </div>

    <form method="POST" action="{{ route('live-quiz.store') }}" id="quizBuilderForm" class="quiz-shell">
        @csrf
        <input type="hidden" name="questions_json" id="questions_json">

        <aside class="quiz-card quiz-left">
            <div class="quiz-left-head">
                <h3>Sorular</h3>
                <span class="badge" id="questionCountBadge">0</span>
            </div>
            <div id="questionList" class="quiz-left-list"></div>
            <button type="button" class="btn quiz-left-add" id="addQuestionBtn">+ Yeni Soru</button>
        </aside>

        <section class="quiz-card quiz-center">
            <div class="quiz-center-main">
                <input class="quiz-question-input" type="text" name="title" placeholder="Quiz basligi..." required>
                <div id="editorEmpty" class="quiz-media-box">Soldan bir soru secin veya yeni soru ekleyin</div>

                <div id="questionEditor" style="display:none;gap:12px">
                    <textarea class="quiz-question-input" id="qText" rows="2" placeholder="Soruyu girmeye basla"></textarea>
                    <div class="quiz-media-box">Medya kutusu (gorsel/video baglantilarini sag panelden ekleyin)</div>

                    <div id="multipleBox" class="quiz-answers"></div>

                    <div id="trueFalseBox" style="display:none;background:#fff;border-radius:10px;padding:12px">
                        <label style="font-weight:700;display:block;margin-bottom:6px">Dogru Cevap</label>
                        <select class="form-control" id="tfCorrect">
                            <option value="A">Dogru</option>
                            <option value="B">Yanlis</option>
                        </select>
                    </div>

                    <div id="dragDropBox" style="display:none;background:#fff;border-radius:10px;padding:12px">
                        <label style="font-weight:700;display:block;margin-bottom:6px">Surukle Birak Eslesmeleri</label>
                        <div id="dragRows" style="display:grid;gap:8px"></div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="quiz-card quiz-right">
            <h3>Soru Ozellikleri</h3>
            <div class="quiz-row">
                <label>Sinif / Sube</label>
                <select class="form-control" name="school_class_id">
                    <option value="">Tum siniflar</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}/{{ $class->section }}</option>
                    @endforeach
                </select>
            </div>
            <div class="quiz-row">
                <label>Katilim Yontemi</label>
                <select class="form-control" name="join_mode" required>
                    <option value="code">Kodla Katilim</option>
                    <option value="instant">Anlik Bildirim (Ekran Kilidi)</option>
                </select>
            </div>
            <div class="quiz-row">
                <label>Soru Turu</label>
                <select class="form-control" id="qType">
                    <option value="multiple">Coktan Secmeli</option>
                    <option value="truefalse">Dogru / Yanlis</option>
                    <option value="dragdrop">Surukle Birak</option>
                </select>
            </div>
            <div class="quiz-row">
                <label>Zaman Siniri (sn)</label>
                <input class="form-control" id="qDuration" type="number" min="5" value="30">
            </div>
            <div class="quiz-row">
                <label>XP Puani</label>
                <input class="form-control" id="qXp" type="number" min="1" value="10">
            </div>
            <div class="quiz-row">
                <label>2x Puan</label>
                <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" id="qDouble" style="width:auto;margin:0"> Etkin</label>
            </div>
            <div class="quiz-save">
                <button class="btn" type="submit">Quizi Kaydet</button>
            </div>
        </aside>
    </form>

    <div class="card">
        <h3>Canli Quiz Baslat</h3>
        <div class="quiz-table-wrap">
            <table>
                <thead><tr><th>Quiz</th><th>Sinif</th><th>Katilim</th><th>Soru</th><th>Islem</th></tr></thead>
                <tbody>
                @forelse($quizzes as $quiz)
                    <tr>
                        <td>{{ $quiz->title }}</td>
                        <td>{{ $quiz->schoolClass ? $quiz->schoolClass->name.'/'.$quiz->schoolClass->section : 'Tumu' }}</td>
                        <td>{{ ($quiz->join_mode ?? 'code') === 'instant' ? 'Anlik Bildirim' : 'Kodla' }}</td>
                        <td>{{ $quiz->questions_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('live-quiz.start', $quiz) }}">
                                @csrf
                                <button class="btn" type="submit">Canli Quizi Baslat</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Quiz yok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const questions = [];
    let selectedIndex = -1;

    const listEl = document.getElementById('questionList');
    const countBadge = document.getElementById('questionCountBadge');
    const addBtn = document.getElementById('addQuestionBtn');
    const editor = document.getElementById('questionEditor');
    const editorEmpty = document.getElementById('editorEmpty');
    const jsonInput = document.getElementById('questions_json');

    const qText = document.getElementById('qText');
    const qType = document.getElementById('qType');
    const qDuration = document.getElementById('qDuration');
    const qXp = document.getElementById('qXp');
    const qDouble = document.getElementById('qDouble');

    const multipleBox = document.getElementById('multipleBox');
    const trueFalseBox = document.getElementById('trueFalseBox');
    const tfCorrect = document.getElementById('tfCorrect');
    const dragDropBox = document.getElementById('dragDropBox');
    const dragRows = document.getElementById('dragRows');

    function baseQuestion() {
        return {
            type: 'multiple',
            question: 'Yeni soru',
            durationSec: 30,
            xp: 10,
            doubleXp: false,
            options: ['Cevap 1', 'Cevap 2', 'Cevap 3', 'Cevap 4'],
            correctIndex: 0,
            correct: 'A',
            leftItems: ['Soldaki 1', 'Soldaki 2'],
            rightItems: ['Sagdaki 1', 'Sagdaki 2'],
            correctMap: { '0': 0, '1': 1 },
        };
    }

    function typeLabel(type) {
        if (type === 'truefalse') return 'Dogru/Yanlis';
        if (type === 'dragdrop') return 'Surukle Birak';
        return 'Coktan Secmeli';
    }

    function renderList() {
        listEl.innerHTML = '';
        countBadge.textContent = String(questions.length);
        questions.forEach((q, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'quiz-left-item' + (i === selectedIndex ? ' active' : '');
            btn.textContent = `${i + 1}. ${q.question || 'Yeni soru'} (${typeLabel(q.type)})`;
            btn.onclick = () => selectQuestion(i);
            listEl.appendChild(btn);
        });
        if (!questions.length) {
            const empty = document.createElement('div');
            empty.style.color = '#64748b';
            empty.style.fontSize = '13px';
            empty.textContent = 'Soru yok.';
            listEl.appendChild(empty);
        }
    }

    function selectQuestion(i) {
        selectedIndex = i;
        const q = questions[i];
        editor.style.display = 'grid';
        editorEmpty.style.display = 'none';

        qText.value = q.question || '';
        qType.value = q.type || 'multiple';
        qDuration.value = q.durationSec || 30;
        qXp.value = q.xp || 10;
        qDouble.checked = !!q.doubleXp;

        renderTypeFields();
        renderList();
        syncJson();
    }

    function renderMultiple() {
        const q = questions[selectedIndex];
        const colors = ['red', 'blue', 'yellow', 'green'];
        const shapes = ['▲', '◆', '●', '■'];
        q.options = (q.options || []).slice(0, 4);
        while (q.options.length < 4) q.options.push('');
        multipleBox.innerHTML = '';

        q.options.forEach((opt, idx) => {
            const row = document.createElement('div');
            row.className = 'answer-card';
            const shape = document.createElement('div');
            shape.className = `answer-shape ${colors[idx]}`;
            shape.textContent = shapes[idx];
            const input = document.createElement('input');
            input.className = 'answer-input';
            input.value = opt;
            input.placeholder = `Cevap ${idx + 1}`;
            input.oninput = () => {
                q.options[idx] = input.value;
                renderList();
                syncJson();
            };
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'correctOpt';
            radio.className = 'answer-correct';
            radio.checked = Number(q.correctIndex || 0) === idx;
            radio.onchange = () => {
                q.correctIndex = idx;
                syncJson();
            };
            row.appendChild(shape);
            row.appendChild(input);
            row.appendChild(radio);
            multipleBox.appendChild(row);
        });
    }

    function renderTypeFields() {
        if (selectedIndex < 0) return;
        const q = questions[selectedIndex];

        multipleBox.style.display = q.type === 'multiple' ? 'grid' : 'none';
        trueFalseBox.style.display = q.type === 'truefalse' ? 'block' : 'none';
        dragDropBox.style.display = q.type === 'dragdrop' ? 'block' : 'none';

        if (q.type === 'multiple') {
            renderMultiple();
        }

        if (q.type === 'truefalse') {
            tfCorrect.value = q.correct || 'A';
        }

        if (q.type === 'dragdrop') {
            dragRows.innerHTML = '';
            const left = q.leftItems || [];
            const right = q.rightItems || [];
            const maxRows = Math.max(left.length, right.length, 2);
            while (left.length < maxRows) left.push('');
            while (right.length < maxRows) right.push('');
            q.leftItems = left;
            q.rightItems = right;
            if (!q.correctMap) q.correctMap = {};

            for (let i = 0; i < maxRows; i++) {
                const row = document.createElement('div');
                row.style.display = 'grid';
                row.style.gridTemplateColumns = '1fr 140px 1fr';
                row.style.gap = '8px';

                const leftIn = document.createElement('input');
                leftIn.className = 'form-control';
                leftIn.placeholder = 'Sol kutu';
                leftIn.value = q.leftItems[i] || '';
                leftIn.oninput = () => { q.leftItems[i] = leftIn.value; syncJson(); };

                const select = document.createElement('select');
                select.className = 'form-control';
                right.forEach((_, rIdx) => {
                    const op = document.createElement('option');
                    op.value = String(rIdx);
                    op.textContent = `Sag ${rIdx + 1}`;
                    select.appendChild(op);
                });
                select.value = String(q.correctMap[String(i)] ?? i);
                select.onchange = () => { q.correctMap[String(i)] = Number(select.value); syncJson(); };

                const rightIn = document.createElement('input');
                rightIn.className = 'form-control';
                rightIn.placeholder = 'Sag kutu';
                rightIn.value = q.rightItems[i] || '';
                rightIn.oninput = () => { q.rightItems[i] = rightIn.value; renderTypeFields(); syncJson(); };

                row.appendChild(leftIn);
                row.appendChild(select);
                row.appendChild(rightIn);
                dragRows.appendChild(row);
            }
        }

        syncJson();
    }

    function syncJson() {
        const payload = questions.map((q) => ({
            type: q.type,
            question: q.question,
            durationSec: Number(q.durationSec || 30),
            xp: Number(q.xp || 10),
            doubleXp: !!q.doubleXp,
            options: q.options || [],
            correctIndex: Number(q.correctIndex || 0),
            correct: q.correct || 'A',
            leftItems: q.leftItems || [],
            rightItems: q.rightItems || [],
            correctMap: q.correctMap || {},
        }));
        jsonInput.value = JSON.stringify(payload);
    }

    addBtn.addEventListener('click', () => {
        questions.push(baseQuestion());
        selectQuestion(questions.length - 1);
        renderList();
    });

    qText.addEventListener('input', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].question = qText.value;
        renderList();
        syncJson();
    });

    qType.addEventListener('change', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].type = qType.value;
        renderTypeFields();
        renderList();
    });

    qDuration.addEventListener('input', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].durationSec = Number(qDuration.value || 30);
        syncJson();
    });

    qXp.addEventListener('input', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].xp = Number(qXp.value || 10);
        syncJson();
    });

    qDouble.addEventListener('change', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].doubleXp = qDouble.checked;
        syncJson();
    });

    tfCorrect.addEventListener('change', () => {
        if (selectedIndex < 0) return;
        questions[selectedIndex].correct = tfCorrect.value;
        syncJson();
    });

    document.getElementById('quizBuilderForm').addEventListener('submit', async (e) => {
        if (!questions.length) {
            e.preventDefault();
            if (window.AppDialog?.alert) {
                await window.AppDialog.alert('En az bir soru eklemelisiniz.');
            }
            return;
        }
        syncJson();
    });

    questions.push(baseQuestion());
    selectQuestion(0);
    renderList();
})();
</script>
@endpush
