/*
 * Flex Göleti - 100 seviyelik Flexbox görev seti.
 * Her görevin çözümü ("sol") hedef nilüferlerin konumunu belirler; oyuncu aynı yere kendi yazdığı kuralla ulaşır.
 * Bu yüzden her seviye çözülebilir; aynı konuma giden alternatif çözümler de kabul edilir.
 *
 * Kısaltmalar (Türkçe kural adları):
 *   ana-hiza (justify-content)   çapraz-hiza (align-items)   yön (flex-direction)   sıra (order)
 *   kendi-hiza (align-self)      sarma (flex-wrap)           yön-sarma (flex-flow)  satır-hiza (align-content)
 *   boşluk (gap)                 büyüme (flex-grow)
 */
(function () {
    'use strict';

    const A = 'ana-hiza', C = 'çapraz-hiza', Y = 'yön', S = 'sıra', K = 'kendi-hiza';
    const R = 'sarma', YS = 'yön-sarma', SH = 'satır-hiza', B = 'boşluk', G = 'büyüme';

    const COLORS = ['yesil', 'sari', 'kirmizi', 'mavi', 'mor', 'turuncu', 'pembe', 'turkuaz', 'kahve', 'gri'];
    const alt = (n, a, b) => Array.from({ length: n }, (_, i) => (i % 2 === 0 ? a : b));
    const WRAP = { size: [110, 60] };

    // [kategori, kurbağa sayısı, çözüm, ek ayarlar]
    //  çözüm: { gol: {...}, <renk>: {...} }  (gol = göletin kendisi, renk = o renkteki kurbağa)
    //  ek: size [w,h] | sizes [[w,h]...] | base (her zaman uygulanan hazır kurallar)
    const RAW = [
        // ───────── KOLAY (1-25) ─────────
        ['kolay', 1, { gol: { [A]: 'son' } }],
        ['kolay', 1, { gol: { [A]: 'orta' } }],
        ['kolay', 2, { gol: { [A]: 'aralıklı' } }],
        ['kolay', 3, { gol: { [A]: 'çevre-aralıklı' } }],
        ['kolay', 3, { gol: { [A]: 'eşit-aralıklı' } }],
        ['kolay', 3, { gol: { [A]: 'orta' } }],
        ['kolay', 4, { gol: { [A]: 'son' } }],
        ['kolay', 1, { gol: { [C]: 'son' } }],
        ['kolay', 1, { gol: { [C]: 'orta' } }],
        ['kolay', 3, { gol: { [C]: 'son' } }],
        ['kolay', 3, { gol: { [C]: 'orta' } }],
        ['kolay', 2, { gol: { [C]: 'orta' } }, { sizes: [[56, 56], [56, 130]] }],
        ['kolay', 1, { gol: { [A]: 'son', [C]: 'son' } }],
        ['kolay', 1, { gol: { [A]: 'orta', [C]: 'orta' } }],
        ['kolay', 3, { gol: { [A]: 'aralıklı', [C]: 'son' } }],
        ['kolay', 3, { gol: { [A]: 'çevre-aralıklı', [C]: 'orta' } }],
        ['kolay', 3, { gol: { [A]: 'eşit-aralıklı', [C]: 'son' } }],
        ['kolay', 3, { gol: { [Y]: 'yatay-ters' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey-ters' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey', [A]: 'son' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey', [C]: 'son' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey', [A]: 'orta', [C]: 'orta' } }],
        ['kolay', 4, { gol: { [Y]: 'yatay-ters', [A]: 'son' } }],
        ['kolay', 3, { gol: { [Y]: 'dikey-ters', [A]: 'aralıklı', [C]: 'orta' } }],

        // ───────── ORTA (26-50) ─────────
        ['orta', 3, { yesil: { [S]: 1 } }],
        ['orta', 3, { kirmizi: { [S]: -1 } }],
        ['orta', 4, { sari: { [S]: 2 }, mavi: { [S]: 1 } }],
        ['orta', 3, { yesil: { [S]: 3 }, sari: { [S]: 2 }, kirmizi: { [S]: 1 } }],
        ['orta', 3, { gol: { [A]: 'son' }, kirmizi: { [S]: -1 } }],
        ['orta', 3, { sari: { [K]: 'son' } }],
        ['orta', 3, { kirmizi: { [K]: 'orta' } }],
        ['orta', 3, { yesil: { [K]: 'son' }, kirmizi: { [K]: 'orta' } }],
        ['orta', 3, { gol: { [A]: 'aralıklı' }, sari: { [K]: 'son' } }],
        ['orta', 4, { gol: { [C]: 'orta' }, yesil: { [K]: 'baş' }, mavi: { [K]: 'son' } }],
        ['orta', 3, { gol: { [Y]: 'dikey' }, sari: { [K]: 'son' } }],
        ['orta', 3, { gol: { [Y]: 'dikey', [C]: 'orta' }, kirmizi: { [K]: 'baş' } }],
        ['orta', 3, { gol: { [Y]: 'dikey-ters', [A]: 'orta' }, yesil: { [K]: 'son' } }],
        ['orta', 4, { gol: { [A]: 'çevre-aralıklı' }, sari: { [K]: 'son' }, mavi: { [K]: 'orta' } }],
        ['orta', 4, { gol: { [Y]: 'yatay-ters', [C]: 'son' }, kirmizi: { [K]: 'baş' } }],
        ['orta', 4, { gol: { [A]: 'son' }, yesil: { [S]: 1 }, mavi: { [S]: -1 } }],
        ['orta', 4, { gol: { [C]: 'orta' }, sari: { [S]: -1 }, kirmizi: { [K]: 'son' } }],
        ['orta', 5, { gol: { [A]: 'aralıklı' }, mor: { [S]: -1 }, yesil: { [S]: 1 } }],
        ['orta', 5, { gol: { [Y]: 'dikey' }, sari: { [S]: 2 }, mavi: { [S]: -1 } }],
        ['orta', 5, { gol: { [Y]: 'dikey', [C]: 'orta' }, yesil: { [S]: 1 }, kirmizi: { [K]: 'son' } }],
        ['orta', 5, { gol: { [A]: 'eşit-aralıklı', [C]: 'son' }, sari: { [S]: -1 }, mor: { [K]: 'baş' } }],
        ['orta', 5, { gol: { [Y]: 'dikey-ters', [A]: 'aralıklı' }, yesil: { [K]: 'son' }, mavi: { [S]: 1 } }],
        ['orta', 5, { gol: { [Y]: 'yatay-ters', [A]: 'orta', [C]: 'orta' }, sari: { [K]: 'baş' }, kirmizi: { [S]: -1 } }],
        ['orta', 5, { gol: { [A]: 'çevre-aralıklı', [C]: 'orta' }, yesil: { [S]: 2, [K]: 'son' }, mor: { [S]: -1, [K]: 'baş' } }],
        ['orta', 5, { gol: { [Y]: 'dikey', [A]: 'eşit-aralıklı', [C]: 'orta' }, sari: { [K]: 'son' }, mavi: { [K]: 'baş', [S]: -1 }, mor: { [S]: 1 } }],

        // ───────── ORTA-ZOR (51-70) ─────────
        ['orta-zor', 7, { gol: { [R]: 'var' } }, WRAP],
        ['orta-zor', 7, { gol: { [R]: 'var', [A]: 'aralıklı' } }, WRAP],
        ['orta-zor', 7, { gol: { [R]: 'ters' } }, WRAP],
        ['orta-zor', 7, { gol: { [R]: 'var', [A]: 'son' } }, WRAP],
        ['orta-zor', 7, { gol: { [R]: 'var', [C]: 'orta' } }, { sizes: alt(7, [110, 50], [110, 90]) }],
        ['orta-zor', 6, { gol: { [SH]: 'orta' } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['orta-zor', 6, { gol: { [SH]: 'son' } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['orta-zor', 6, { gol: { [SH]: 'aralıklı' } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['orta-zor', 6, { gol: { [SH]: 'çevre-aralıklı' } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['orta-zor', 6, { gol: { [SH]: 'eşit-aralıklı', [A]: 'son' } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['orta-zor', 6, { gol: { [YS]: 'dikey var' } }, WRAP],
        ['orta-zor', 6, { gol: { [YS]: 'yatay-ters ters' } }, WRAP],
        ['orta-zor', 6, { gol: { [YS]: 'dikey-ters var' } }, WRAP],
        ['orta-zor', 7, { gol: { [YS]: 'dikey var', [A]: 'orta' } }, WRAP],
        ['orta-zor', 7, { gol: { [YS]: 'dikey var', [C]: 'son' } }, { sizes: alt(7, [110, 60], [70, 60]) }],
        ['orta-zor', 7, { gol: { [R]: 'var', [A]: 'orta', [C]: 'orta', [SH]: 'orta' } }, { sizes: alt(7, [110, 50], [110, 80]) }],
        ['orta-zor', 7, { gol: { [R]: 'ters', [A]: 'aralıklı', [SH]: 'son' } }, WRAP],
        ['orta-zor', 8, { gol: { [YS]: 'dikey var', [SH]: 'aralıklı' } }, { size: [90, 60] }],
        ['orta-zor', 8, { gol: { [YS]: 'yatay-ters var', [A]: 'son', [SH]: 'orta' } }, WRAP],
        ['orta-zor', 8, { gol: { [YS]: 'dikey-ters ters', [A]: 'orta', [C]: 'orta', [SH]: 'orta' } }, { sizes: alt(8, [110, 60], [70, 60]) }],

        // ───────── ZOR (71-90) ─────────
        ['zor', 7, { gol: { [B]: '20px' } }, { size: [100, 60], base: { gol: { [R]: 'var' } } }],
        ['zor', 7, { gol: { [B]: '30px', [A]: 'orta' } }, { size: [100, 60], base: { gol: { [R]: 'var' } } }],
        ['zor', 4, { gol: { [A]: 'orta', [B]: '24px' } }, { size: [60, 60] }],
        ['zor', 4, { gol: { [Y]: 'dikey', [B]: '30px', [C]: 'orta' } }],
        ['zor', 3, { sari: { [G]: 1 } }],
        ['zor', 3, { yesil: { [G]: 1 }, kirmizi: { [G]: 1 } }],
        ['zor', 3, { yesil: { [G]: 1 }, sari: { [G]: 2 }, kirmizi: { [G]: 1 } }],
        ['zor', 3, { gol: { [Y]: 'dikey' }, sari: { [G]: 1 } }],
        ['zor', 4, { gol: { [C]: 'orta' }, sari: { [G]: 1 }, kirmizi: { [S]: -1 } }],
        ['zor', 4, { gol: { [B]: '20px' }, mavi: { [G]: 1 } }],
        ['zor', 7, { kirmizi: { [S]: -1 }, yesil: { [S]: 1 } }, { size: [110, 60], base: { gol: { [R]: 'var' } } }],
        ['zor', 7, { gol: { [SH]: 'son' }, yesil: { [K]: 'son' } }, { sizes: alt(7, [110, 40], [110, 80]), base: { gol: { [R]: 'var' } } }],
        ['zor', 7, { gol: { [R]: 'ters', [A]: 'orta' }, mavi: { [S]: -1 } }, WRAP],
        ['zor', 7, { gol: { [YS]: 'dikey var' }, kirmizi: { [S]: -1 }, sari: { [K]: 'son' } }, { sizes: alt(7, [110, 60], [70, 60]) }],
        ['zor', 8, { gol: { [R]: 'var', [A]: 'aralıklı', [B]: '12px' }, yesil: { [K]: 'son' } }, { sizes: alt(8, [100, 40], [100, 70]) }],
        ['zor', 7, { gol: { [R]: 'var', [B]: '16px', [SH]: 'orta' }, mor: { [S]: -1 } }, WRAP],
        ['zor', 7, { gol: { [YS]: 'yatay-ters var', [C]: 'orta' }, mavi: { [K]: 'baş' }, sari: { [S]: 2 } }, { sizes: alt(7, [110, 60], [70, 90]) }],
        ['zor', 8, { gol: { [YS]: 'dikey var', [A]: 'son', [B]: '10px' }, kirmizi: { [S]: -1 } }, { size: [80, 60] }],
        ['zor', 8, { gol: { [R]: 'var', [A]: 'çevre-aralıklı', [SH]: 'aralıklı' }, yesil: { [S]: 1 }, sari: { [K]: 'son' } }, { sizes: alt(8, [110, 50], [110, 70]) }],
        ['zor', 8, { gol: { [YS]: 'dikey-ters var', [A]: 'eşit-aralıklı', [SH]: 'orta' }, sari: { [S]: -1 }, mor: { [K]: 'baş' } }, { sizes: alt(8, [100, 60], [70, 60]) }],

        // ───────── UZMAN (91-100) ─────────
        ['uzman', 8, { gol: { [R]: 'var', [A]: 'aralıklı', [C]: 'orta', [SH]: 'orta', [B]: '10px' }, kirmizi: { [S]: -1 } }, { sizes: alt(8, [96, 40], [96, 70]) }],
        ['uzman', 8, { gol: { [YS]: 'dikey var', [C]: 'son', [SH]: 'aralıklı' }, yesil: { [S]: 1 }, sari: { [K]: 'baş' } }, { sizes: alt(8, [96, 56], [64, 56]) }],
        ['uzman', 9, { gol: { [R]: 'ters', [A]: 'orta', [B]: '8px', [SH]: 'son' }, mor: { [S]: -2 }, mavi: { [G]: 1 } }, { size: [80, 56] }],
        ['uzman', 9, { gol: { [YS]: 'yatay-ters ters', [A]: 'çevre-aralıklı', [SH]: 'orta' }, yesil: { [K]: 'son' }, kirmizi: { [S]: -1 }, mavi: { [G]: 1 } }, { sizes: alt(9, [80, 40], [80, 70]) }],
        ['uzman', 10, { gol: { [R]: 'var', [B]: '12px', [A]: 'eşit-aralıklı', [SH]: 'eşit-aralıklı' }, sari: { [S]: 3 }, mor: { [K]: 'son' } }, { size: [72, 56] }],
        ['uzman', 10, { gol: { [YS]: 'dikey var', [B]: '10px', [SH]: 'orta', [C]: 'orta' }, yesil: { [S]: -1 }, kirmizi: { [G]: 1 } }, { sizes: alt(10, [100, 50], [64, 50]) }],
        ['uzman', 9, { gol: { [YS]: 'dikey-ters var', [A]: 'orta', [C]: 'son', [B]: '8px' }, turuncu: { [S]: -1 }, sari: { [K]: 'baş' }, mor: { [G]: 1 } }, { sizes: alt(9, [90, 50], [60, 50]) }],
        ['uzman', 10, { gol: { [YS]: 'yatay-ters ters', [A]: 'son', [B]: '10px', [SH]: 'aralıklı' }, mavi: { [S]: 2 }, pembe: { [K]: 'baş' }, yesil: { [G]: 1 } }, { sizes: alt(10, [76, 40], [76, 70]) }],
        ['uzman', 10, { gol: { [R]: 'var', [A]: 'aralıklı', [C]: 'orta', [SH]: 'çevre-aralıklı', [B]: '14px' }, kirmizi: { [S]: -1, [K]: 'son' }, mor: { [S]: 1, [G]: 1 }, mavi: { [K]: 'baş' } }, { sizes: alt(10, [70, 40], [70, 70]) }],
        ['uzman', 10, { gol: { [YS]: 'dikey var', [A]: 'eşit-aralıklı', [C]: 'orta', [SH]: 'eşit-aralıklı', [B]: '12px' }, yesil: { [S]: 2 }, sari: { [S]: -1, [K]: 'son' }, kirmizi: { [G]: 2 }, mavi: { [G]: 1, [K]: 'baş' } }, { sizes: alt(10, [90, 50], [60, 50]) }],
    ];

    // ───────── Metinler ─────────
    const CAT = { kolay: 'Kolay', orta: 'Orta', 'orta-zor': 'Orta-Zor', zor: 'Zor', uzman: 'Uzman' };
    const XP = { kolay: 5, orta: 8, 'orta-zor': 12, zor: 16, uzman: 25 };
    const STORIES = {
        kolay: [
            'Zıpzıp nilüfere konmak istiyor! Kod kutusuna kuralı yaz, kurbağayı yerine zıplat.',
            'Küçük kurbağalar göletin düzenini bekliyor. Doğru kuralı bul, hepsi yerini alsın.',
            'Yağmurdan sonra nilüferler kaymış! Kurbağaları yeni yerlerine ulaştır.',
            'Kurbağa partisi başlıyor ve herkes kendi yaprağında oturmak istiyor.',
            'Akşam oldu; her kurbağa kendi renginin nilüferine yerleşmeli.',
            'Göletin bekçisi sana güveniyor: kurbağaları doğru yerlere hizala.',
        ],
        orta: [
            'Gölette kalabalık var! Kurbağaların sırası ve yerleri karışmış, düzeni sen kur.',
            'Bazı kurbağalar kendi başına buyruk; bazılarını ise tek kuralla yönetmek gerek.',
            'Nilüferler farklı yerlerde açmış. Kuralları dikkatle seç.',
            'Kurbağa orkestrası sahneye çıkıyor: herkes kendi koltuğuna oturmalı.',
            'Küçük bir değişiklik büyük fark yaratır. Hangi kuralı, kime yazacaksın?',
        ],
        'orta-zor': [
            'Gölet taştı! Kurbağalar tek satıra sığmıyor, düzeni yeniden kurman lazım.',
            'Satırlar arasında boşluklar var. Kurbağalar satırlara nasıl yerleşecek?',
            'Göletin düzeni yön ve satır kurallarına bağlı. Doğru birleşimi bul.',
            'Kalabalık bir kurbağa kafilesi geldi; hepsini nilüferlere yerleştir.',
        ],
        zor: [
            'Zorlu bir gölet! Boşluklar, büyüyen kurbağalar ve özel kurallar bir arada.',
            'Hem gölete hem tek tek kurbağalara kural yazman gerekebilir.',
            'Kurbağalar büyüyor, yer daralıyor… Düzeni sen kurtarırsın!',
            'Birden fazla kural birbirini etkiliyor. Sırayla düşün, adım adım çöz.',
        ],
        uzman: [
            'Uzman göleti! Tüm Flexbox bilgini birleştirmen gerekiyor.',
            'Şampiyonlar buraya gelir: çok kurbağa, çok kural, tek doğru düzen.',
            'Göletin efsanesi bu görevde saklı. Sakin kal, kuralları tek tek dene.',
        ],
    };
    const PROP_TITLES = {
        [A]: 'Ana eksende hizala', [C]: 'Çapraz eksende hizala', [Y]: 'Yönü çevir', [S]: 'Sırayı değiştir',
        [K]: 'Kendi yoluna git', [R]: 'Satıra sar', [YS]: 'Yön ve sarma', [SH]: 'Satırları hizala', [B]: 'Boşluk bırak', [G]: 'Büyü ve doldur',
    };
    const LEARN = {
        [A]: '<b>ana-hiza</b> (gerçek CSS: <code>justify-content</code>) kurbağaları <b>ana eksen</b> boyunca hizalar. Yatay düzende ana eksen soldan sağa doğrudur.<ul>' +
            '<li><code>baş</code>: Başlangıca (sola) yaslar</li><li><code>son</code>: Sona (sağa) yaslar</li><li><code>orta</code>: Ortalar</li>' +
            '<li><code>aralıklı</code>: Aralarda eşit boşluk, kenarlarda boşluk yok</li><li><code>çevre-aralıklı</code>: Her kurbağanın iki yanında eşit boşluk</li>' +
            '<li><code>eşit-aralıklı</code>: Tüm boşluklar birbirine eşit</li></ul>Örnek: <code>ana-hiza: son;</code> kurbağayı sağa taşır.',
        [C]: '<b>çapraz-hiza</b> (<code>align-items</code>) kurbağaları <b>çapraz eksende</b> (yatay düzende aşağı-yukarı) hizalar.<ul>' +
            '<li><code>baş</code>: Üste</li><li><code>son</code>: Alta</li><li><code>orta</code>: Ortaya</li><li><code>uzat</code>: Boyunu doldurur (boyu sabit değilse)</li></ul>Örnek: <code>çapraz-hiza: son;</code> kurbağayı aşağı indirir.',
        [Y]: '<b>yön</b> (<code>flex-direction</code>) ana ekseni belirler. Eksen değişince <b>ana-hiza</b> ile <b>çapraz-hiza</b> da yer değiştirir!<ul>' +
            '<li><code>yatay</code>: Soldan sağa (varsayılan)</li><li><code>yatay-ters</code>: Sağdan sola</li><li><code>dikey</code>: Yukarıdan aşağı</li><li><code>dikey-ters</code>: Aşağıdan yukarı</li></ul>',
        [S]: '<b>sıra</b> (<code>order</code>) bir kurbağanın sırasını değiştirir. Bu kural <b>kurbağanın kendi kutusuna</b> yazılır (ör. <code>.sari</code>). Varsayılan sıra 0\'dır; küçük sayı öne, büyük sayı arkaya geçer. Negatif sayı da yazabilirsin: <code>sıra: -1;</code>',
        [K]: '<b>kendi-hiza</b> (<code>align-self</code>) tek bir kurbağanın çapraz eksendeki hizasını, göletin <b>çapraz-hiza</b> kuralından bağımsız olarak değiştirir. Değerleri: <code>baş</code>, <code>son</code>, <code>orta</code>, <code>uzat</code>.',
        [R]: '<b>sarma</b> (<code>flex-wrap</code>) kurbağalar tek satıra sığmazsa ne olacağını belirler.<ul>' +
            '<li><code>yok</code>: Sarma, taşsın (varsayılan)</li><li><code>var</code>: Sığmayanlar alt satıra geçsin</li><li><code>ters</code>: Satırlar aşağıdan yukarı dizilsin</li></ul>',
        [YS]: '<b>yön-sarma</b> (<code>flex-flow</code>) hem <b>yön</b> hem <b>sarma</b> kurallarını tek satırda yazmanı sağlar. Örnek: <code>yön-sarma: dikey var;</code>',
        [SH]: '<b>satır-hiza</b> (<code>align-content</code>) birden çok satır olduğunda <b>satırların</b> göletteki konumunu belirler (tek satırda etkisi yoktur). Değerleri: <code>baş</code>, <code>son</code>, <code>orta</code>, <code>uzat</code>, <code>aralıklı</code>, <code>çevre-aralıklı</code>, <code>eşit-aralıklı</code>. Bu kural satırların <i>hepsini</i> hizalar; <b>çapraz-hiza</b> ise bir satırın <i>içindeki</i> kurbağaları hizalar.',
        [B]: '<b>boşluk</b> (<code>gap</code>) kurbağalar arasında sabit bir aralık bırakır. Örnek: <code>boşluk: 20px;</code>',
        [G]: '<b>büyüme</b> (<code>flex-grow</code>) bir kurbağanın boş yeri doldurmak için ne kadar büyüyeceğini belirler. Varsayılan 0\'dır. <code>büyüme: 1;</code> kurbağayı boş alana yayar; sayılar oranı belirler (2, 1\'in iki katı pay alır).',
    };
    const PICK = (arr, i) => arr[i % arr.length];

    const seen = new Set();
    const levels = RAW.map(([cat, n, sol, opt], i) => {
        opt = opt || {};
        const solProps = [];
        Object.values(sol).forEach((o) => Object.keys(o).forEach((p) => { if (!solProps.includes(p)) solProps.push(p); }));
        const baseProps = [];
        Object.values(opt.base || {}).forEach((o) => Object.keys(o).forEach((p) => { if (!baseProps.includes(p)) baseProps.push(p); }));
        const fresh = solProps.filter((p) => !seen.has(p));
        const freshBase = baseProps.filter((p) => !seen.has(p));
        const learn = [...freshBase, ...fresh].map((p) => LEARN[p]).filter(Boolean);
        [...freshBase, ...fresh].forEach((p) => seen.add(p));

        const frogs = COLORS.slice(0, n);
        const targets = Object.keys(sol);
        // Hedefte olmayan ama göletin düzenini belirlemek için gereken seçici yoksa gölet kutusu yine de gösterilir (display:flex).
        const title = i === 99 ? 'Şampiyon Göleti'
            : (solProps.length === 1 ? PROP_TITLES[solProps[0]] : (cat === 'zor' || cat === 'uzman' ? 'Zorlu karma görev' : 'Karma görev'));
        let story = PICK(STORIES[cat], i);
        if (i === 0) story = 'Flex Göleti\'ne hoş geldin! Bu kurbağayı sağdaki nilüfer yaprağının üstüne zıplat. Kod kutusundaki boş satıra aşağıdaki kuralı yazabilirsin: <code>ana-hiza: son;</code>';
        if (i === 99) story = 'Son görev! Gölet efsanesi olmak için tüm Flexbox kurallarını birleştir. Başarırsan Flex Efendisi unvanını alırsın!';
        return {
            id: i + 1, cat, catLabel: CAT[cat], title, story, xp: XP[cat], frogs,
            size: opt.size || [56, 56], sizes: opt.sizes || null, base: opt.base || {}, sol, targets, learn,
        };
    });

    window.FLEX_GOLETI_LEVELS = levels;
    window.FLEX_GOLETI_META = { CAT, COLORS };
})();
