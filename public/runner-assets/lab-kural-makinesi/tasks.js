/* Kural Makinesi - görev verileri.
   Yeni görev eklemek için bu diziye bir nesne eklemek yeterli: scenarios = (ortam değerleri -> beklenen araç durumu). */
(function () {
    'use strict';
    function rule(s, op, v, dev, act) { return { c1: { s: s, op: op, v: v }, join: '', c2: null, dev: dev, act: act, locked: true }; }
    function env(nem, sicak, isik) { return { nem: nem, sicak: sicak, isik: isik }; }

    window.KM_TASKS = [
        {
            id: 'km-1', title: 'Susayan Fideler', topic: 'Tek koşul',
            summary: 'Toprak kuruyunca sulamayı kendiliğinden açan ilk kuralını yaz.',
            age: 10, level: 'Kolay', xp: 20, kind: 'scenarios', watch: ['sulama'], starter: [],
            brief: 'Toprak nemi %30\'un altına düşünce sulama otomatik açılsın.',
            tip: 'Bir kural şöyle okunur: EĞER bir şey doğruysa İSE bir araç açılır ya da kapanır.',
            scenarios: [
                { env: env(20, 24, 50), expect: { sulama: true } },
                { env: env(29, 24, 50), expect: { sulama: true } },
                { env: env(30, 24, 50), expect: { sulama: false } },
                { env: env(65, 24, 50), expect: { sulama: false } }
            ],
            hints: [
                'EĞER kısmında sensör olarak "Toprak nemi"ni seç.',
                '"<" (küçüktür) işaretini ve 30 değerini seç. Tam 30 olunca sulama açılmamalı.',
                'İSE kısmında araç "Sulama", işlem "AÇ" olsun.'
            ]
        },
        {
            id: 'km-2', title: 'Kavurucu Öğle', topic: 'VE ile bağla',
            summary: 'İki şartın aynı anda doğru olması gerekince ne olur? Gölgeliği doğru zamanda indir.',
            age: 10, level: 'Kolay', xp: 30, kind: 'scenarios', watch: ['golge'], starter: [],
            brief: 'Sıcaklık 30°C\'den yüksek VE ışık %70\'ten fazlaysa gölgelik inmeli. Biri eksikse inmemeli.',
            tip: 'VE: iki koşulun ikisi de doğru olmalı. Koşul eklemek için "Tek koşul" kutusunu değiştir.',
            scenarios: [
                { env: env(60, 35, 80), expect: { golge: true } },
                { env: env(60, 31, 71), expect: { golge: true } },
                { env: env(60, 35, 70), expect: { golge: false } },
                { env: env(60, 30, 80), expect: { golge: false } },
                { env: env(60, 24, 50), expect: { golge: false } }
            ],
            hints: [
                'İlk koşul: Sıcaklık > 30. Sonra "Tek koşul" kutusundan VE\'yi seç.',
                'İkinci koşul: Işık > 70. "Büyüktür" (>) kullan.',
                'İSE kısmında araç "Gölgelik", işlem "AÇ" olsun.'
            ]
        },
        {
            id: 'km-3', title: 'Ya Biri Ya Öteki', topic: 'VEYA ile bağla',
            summary: 'İki sorundan biri bile yetiyorsa hangi bağlaç gerekir? Fanı çalıştır.',
            age: 11, level: 'Orta', xp: 40, kind: 'scenarios', watch: ['fan'], starter: [],
            brief: 'Sıcaklık 32°C\'den yüksekse YA DA toprak nemi %20\'nin altındaysa fan çalışsın.',
            tip: 'VEYA: iki koşuldan biri doğru olsa yeter.',
            scenarios: [
                { env: env(60, 36, 50), expect: { fan: true } },
                { env: env(15, 28, 50), expect: { fan: true } },
                { env: env(15, 36, 50), expect: { fan: true } },
                { env: env(60, 28, 50), expect: { fan: false } },
                { env: env(20, 32, 50), expect: { fan: false } }
            ],
            hints: [
                'Önce Sıcaklık > 32 koşulunu kur.',
                'Bağlacı VEYA yap ve ikinci koşula Toprak nemi < 20 yaz.',
                'İSE: araç "Fan", işlem "AÇ".'
            ]
        },
        {
            id: 'km-4', title: 'Gece Nöbeti', topic: 'Kural sırası',
            summary: 'Hazır bir sulama kuralı var ama geceleri de suluyor. Kuralların sırası sonucu değiştirir!',
            age: 11, level: 'Orta', xp: 50, kind: 'scenarios', watch: ['sulama'],
            starter: [rule('nem', '<', 30, 'sulama', 'on')],
            brief: 'Hazır kural toprak kuruyken sulamayı açıyor. Gece (ışık %20\'nin altı) sulama HİÇ açılmasın; mantar riski var.',
            tip: 'Kurallar yukarıdan aşağı çalışır; sonraki kural öncekinin kararını ezebilir.',
            scenarios: [
                { env: env(20, 24, 60), expect: { sulama: true } },
                { env: env(20, 24, 10), expect: { sulama: false } },
                { env: env(60, 24, 10), expect: { sulama: false } },
                { env: env(29, 24, 19), expect: { sulama: false } },
                { env: env(29, 24, 20), expect: { sulama: true } },
                { env: env(60, 24, 60), expect: { sulama: false } }
            ],
            hints: [
                'Hazır kuralı silemezsin; altına yeni bir kural ekle.',
                'Yeni kural: EĞER Işık < 20 İSE Sulama KAPAT.',
                'Yeni kural hazır kuralın ALTINDA olmalı, çünkü sonraki kural öncekini ezer. Gerekirse ↑ ↓ oklarıyla sırayı değiştir.'
            ]
        },
        {
            id: 'km-5', title: 'Kendi Seram', topic: 'Serbest tasarım',
            summary: 'Üç aracın hepsi için kendi kurallarını yaz, kaydırıcılarla dene ve tasarımını açıkla.',
            age: 12, level: 'Orta', xp: 60, kind: 'open', watch: ['sulama', 'fan', 'golge'], starter: [],
            brief: 'Sulama, Fan ve Gölgelik için en az birer kural yaz. Kaydırıcıları oynatıp her kuralın en az bir kez çalıştığını göster.',
            tip: 'Sonra "Kural çakıştı mı?" diye düşün: iki kural aynı aracı farklı yönde kullanıyor olabilir.',
            question: 'Tasarımını 1-2 cümleyle açıkla: Hangi kural senin için en önemli ve neden?',
            hints: [
                'Her araç için farklı bir sensör düşün: Sulama için nem, fan için sıcaklık, gölgelik için ışık.',
                'Kuralı kurduktan sonra ilgili kaydırıcıyı oynat; kural çalışınca kartı yeşile döner.',
                'Üç kuralın hepsi en az bir kez yeşile döndüğünde açıklamanı yaz ve "Kontrol et"e bas.'
            ]
        }
    ];
})();
