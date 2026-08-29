(() => {
    const codeEl = document.getElementById('peCode');
    const lineNumbersEl = document.getElementById('peLineNumbers');
    const outputEl = document.getElementById('peOutput');
    const hintsEl = document.getElementById('peHints');
    const hintDotEl = document.getElementById('peHintDot');
    const statusEl = document.getElementById('peStatus');
    const runBtn = document.getElementById('peRunBtn');
    const resetBtn = document.getElementById('peResetBtn');
    const templateSelect = document.getElementById('peTemplateSelect');
    const tabs = document.querySelectorAll('.pe-tab');
    const panels = document.querySelectorAll('.pe-tab-panel');

    const TEMPLATES = [
        {
            id: 'merhaba',
            name: 'Merhaba Dünya',
            code: '# İlk Python programın\nprint("Merhaba Dünya!")\n'
        },
        {
            id: 'degiskenler',
            name: 'Değişkenler',
            code: '# Değişkenler ile deneme yap\nad = "Ayşe"\nyas = 12\nboy = 1.45\n\nprint(ad, "isimli öğrenci", yas, "yaşında.")\nprint("Boyu:", boy, "metre")\n'
        },
        {
            id: 'kosullar',
            name: 'Koşullu İfadeler',
            code: '# if / elif / else kullanımı\nnot_ = 78\n\nif not_ >= 85:\n    print("Aferin, harika iş!")\nelif not_ >= 50:\n    print("Geçtin, tebrikler.")\nelse:\n    print("Biraz daha çalışman gerekiyor.")\n'
        },
        {
            id: 'donguler',
            name: 'Döngüler',
            code: '# for döngüsü ile 1den 10a kadar sayma\nfor sayi in range(1, 11):\n    print(sayi)\n\ntoplam = 0\nfor sayi in range(1, 11):\n    toplam += sayi\nprint("Toplam:", toplam)\n'
        },
        {
            id: 'fonksiyonlar',
            name: 'Fonksiyonlar',
            code: '# Basit bir fonksiyon tanımlama\ndef kare_al(sayi):\n    return sayi * sayi\n\nfor i in range(1, 6):\n    print(i, "in karesi:", kare_al(i))\n'
        },
        {
            id: 'listeler',
            name: 'Listeler',
            code: '# Liste oluşturma ve gezinme\nmeyveler = ["elma", "armut", "çilek", "muz"]\n\nfor meyve in meyveler:\n    print(meyve.title())\n\nmeyveler.append("kiraz")\nprint("Toplam meyve sayisi:", len(meyveler))\n'
        }
    ];

    const DEFAULT_CODE = TEMPLATES[0].code;

    const HINTS = [
        {
            match: /SyntaxError/,
            title: 'Söz Dizimi (Syntax) Hatası',
            explain: 'Python kodunun yazım kuralına uymuyor. Genellikle bir parantez, iki nokta üst üste (:) ya da tırnak işareti unutulmuş olabilir.',
            example: 'if yas > 10:\n    print("Büyüksün")'
        },
        {
            match: /IndentationError/,
            title: 'Girinti (Indentation) Hatası',
            explain: 'Python, blokları (if, for, def gibi) girinti (boşluk) ile ayırır. Bir satırın başındaki boşluk sayısı yanlış olabilir.',
            example: 'def selamla():\n    print("Merhaba")  # 4 boşluk girinti'
        },
        {
            match: /NameError/,
            title: 'Tanımsız İsim (NameError)',
            explain: 'Kullanmaya çalıştığın bir değişken veya fonksiyon henüz tanımlanmamış, ya da adı yanlış yazılmış olabilir.',
            example: 'sayi = 5\nprint(sayi)  # sayi önce tanımlanmalı'
        },
        {
            match: /TypeError/,
            title: 'Tür (Type) Uyuşmazlığı',
            explain: 'Birbiriyle uyumsuz türleri (ör. sayı ile metni) birleştirmeye çalışıyorsun. Metne çevirmek için str() kullanabilirsin.',
            example: 'yas = 12\nprint("Yaşım: " + str(yas))'
        },
        {
            match: /ZeroDivisionError/,
            title: 'Sıfıra Bölme Hatası',
            explain: 'Bir sayıyı sıfıra bölmeye çalıştın. Matematikte bu tanımsızdır, bölen sayının sıfır olmadığından emin ol.',
            example: 'bolen = 4\nif bolen != 0:\n    print(10 / bolen)'
        },
        {
            match: /IndexError/,
            title: 'Dizin (Index) Hatası',
            explain: 'Bir listede olmayan bir sıradaki elemana erişmeye çalıştın. Liste uzunluğunu len() ile kontrol edebilirsin.',
            example: 'liste = [1, 2, 3]\nprint(liste[0])  # ilk eleman, indeks 0dan başlar'
        },
        {
            match: /KeyError/,
            title: 'Anahtar (Key) Hatası',
            explain: 'Bir sözlükte (dictionary) olmayan bir anahtara erişmeye çalıştın.',
            example: 'ogrenci = {"ad": "Ali"}\nprint(ogrenci.get("yas", "bilinmiyor"))'
        },
        {
            match: /AttributeError/,
            title: 'Öznitelik (Attribute) Hatası',
            explain: 'Bir nesnenin sahip olmadığı bir metodu ya da özelliği çağırmaya çalıştın. Yazım hatası olabilir.',
            example: 'metin = "merhaba"\nprint(metin.upper())'
        },
        {
            match: /ModuleNotFoundError|ImportError/,
            title: 'Modül Bulunamadı',
            explain: 'İçe aktarmaya (import) çalıştığın modül bu ortamda desteklenmiyor olabilir. Standart kütüphaneleri (math, random, time gibi) kullanmayı dene.',
            example: 'import random\nprint(random.randint(1, 6))'
        },
        {
            match: /EOFError|input\(\)/,
            title: 'Klavyeden Girdi Desteklenmiyor',
            explain: 'Bu tarayıcı içi editör input() ile klavyeden veri almayı desteklemiyor. Bunun yerine kod içinde sabit değerlerle deneme yapabilirsin.',
            example: 'ad = "Öğrenci"  # input(...) yerine\nprint("Merhaba", ad)'
        }
    ];

    let pyodideReady = false;
    let pyodide = null;

    function setStatus(text, cls) {
        statusEl.textContent = text;
        statusEl.className = 'pe-status' + (cls ? ' ' + cls : '');
    }

    function fillTemplates() {
        TEMPLATES.forEach((t) => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            templateSelect.appendChild(opt);
        });
    }

    function syncLineNumbers() {
        const lines = codeEl.value.split('\n').length;
        let out = '';
        for (let i = 1; i <= lines; i++) out += i + '\n';
        lineNumbersEl.textContent = out;
    }

    codeEl.addEventListener('input', syncLineNumbers);
    codeEl.addEventListener('scroll', () => {
        lineNumbersEl.scrollTop = codeEl.scrollTop;
    });
    codeEl.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = codeEl.selectionStart;
            const end = codeEl.selectionEnd;
            codeEl.value = codeEl.value.slice(0, start) + '    ' + codeEl.value.slice(end);
            codeEl.selectionStart = codeEl.selectionEnd = start + 4;
            syncLineNumbers();
        }
    });

    templateSelect.addEventListener('change', () => {
        const id = templateSelect.value;
        if (!id) return;
        const t = TEMPLATES.find((item) => item.id === id);
        if (t) {
            codeEl.value = t.code;
            syncLineNumbers();
        }
    });

    resetBtn.addEventListener('click', () => {
        const id = templateSelect.value;
        const t = TEMPLATES.find((item) => item.id === id) || TEMPLATES[0];
        codeEl.value = t.code;
        syncLineNumbers();
        clearOutput();
        clearHints();
    });

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((t) => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.getAttribute('data-tab');
            panels.forEach((p) => {
                p.hidden = p.getAttribute('data-panel') !== target;
            });
            if (target === 'hints') hintDotEl.hidden = true;
        });
    });

    function clearOutput() {
        outputEl.innerHTML = '<span class="pe-console-placeholder">Kodunu yaz ve "Çalıştır" butonuna bas. Çıktı burada görünecek.</span>';
    }

    function clearHints() {
        hintsEl.innerHTML = '<p class="pe-hints-empty">Bir hata oluşursa, olası nedeni ve doğru kullanım örneği burada gösterilecek.</p>';
        hintDotEl.hidden = true;
    }

    function appendOutputLine(text, isError) {
        if (outputEl.querySelector('.pe-console-placeholder')) {
            outputEl.innerHTML = '';
        }
        const span = document.createElement('span');
        span.className = isError ? 'pe-line-error' : 'pe-line-stdout';
        span.textContent = text;
        outputEl.appendChild(span);
    }

    function showHintForError(errorText) {
        const found = HINTS.find((h) => h.match.test(errorText));
        hintsEl.innerHTML = '';
        if (!found) {
            const card = document.createElement('div');
            card.className = 'pe-hint-card';
            card.innerHTML = '<span class="pe-hint-label">Hata</span>' +
                '<h4>Bir hata oluştu</h4>' +
                '<p>Hata mesajını dikkatlice oku, genellikle satır numarasını ve hatanın türünü belirtir.</p>';
            hintsEl.appendChild(card);
        } else {
            const card = document.createElement('div');
            card.className = 'pe-hint-card';
            card.innerHTML = '<span class="pe-hint-label">Öneri</span>' +
                '<h4>' + found.title + '</h4>' +
                '<p>' + found.explain + '</p>' +
                '<pre>' + found.example.replace(/</g, '&lt;') + '</pre>';
            hintsEl.appendChild(card);
        }
        hintDotEl.hidden = false;
    }

    async function initPyodide() {
        try {
            setStatus('Python ortamı hazırlanıyor…');
            pyodide = await loadPyodide({ indexURL: 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/' });
            pyodideReady = true;
            setStatus('Hazır', 'is-ready');
            runBtn.disabled = false;
        } catch (err) {
            setStatus('Python ortamı yüklenemedi. İnternet bağlantını kontrol et.', 'is-error');
            runBtn.disabled = true;
        }
    }

    async function runCode() {
        if (!pyodideReady || !pyodide) return;
        runBtn.disabled = true;
        runBtn.classList.add('is-running');
        clearOutput();
        outputEl.innerHTML = '';
        clearHints();

        const code = codeEl.value;
        let stdoutBuf = '';
        let stderrBuf = '';

        try {
            pyodide.setStdout({ batched: (s) => { stdoutBuf += s + '\n'; } });
            pyodide.setStderr({ batched: (s) => { stderrBuf += s + '\n'; } });

            await pyodide.runPythonAsync(code);

            if (stdoutBuf) appendOutputLine(stdoutBuf.replace(/\n$/, ''), false);
            if (!stdoutBuf && !stderrBuf) appendOutputLine('(Çıktı yok — program çalıştı ama ekrana bir şey yazdırılmadı)', false);
        } catch (err) {
            if (stdoutBuf) appendOutputLine(stdoutBuf.replace(/\n$/, ''), false);
            const message = (err && err.message) ? err.message : String(err);
            const shortMessage = message.split('\n').slice(-4).join('\n');
            appendOutputLine(shortMessage, true);
            showHintForError(message);
            const tabsHints = document.querySelector('.pe-tab[data-tab="hints"]');
            if (tabsHints) hintDotEl.hidden = false;
        } finally {
            runBtn.disabled = false;
            runBtn.classList.remove('is-running');
        }
    }

    runBtn.addEventListener('click', runCode);

    // init
    fillTemplates();
    codeEl.value = DEFAULT_CODE;
    syncLineNumbers();
    runBtn.disabled = true;
    initPyodide();
})();
