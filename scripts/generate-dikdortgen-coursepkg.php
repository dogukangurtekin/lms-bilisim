<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$build = $rc->getMethod('buildCoursePackage');
$build->setAccessible(true);

$svg = function (string $title, string $subtitle, array $items, string $c1, string $c2): string {
    $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $blocks = '';
    $x = 82;
    $y = 188;
    foreach ($items as $i => $item) {
        $col = $i % 2;
        $row = intdiv($i, 2);
        $bx = $x + ($col * 350);
        $by = $y + ($row * 124);
        $blocks .= '<g transform="translate(' . $bx . ',' . $by . ')">';
        $blocks .= '<rect x="0" y="0" width="294" height="94" rx="22" fill="rgba(255,255,255,.92)" stroke="rgba(255,255,255,.98)" stroke-width="2"/>';
        $blocks .= '<rect x="18" y="24" width="44" height="44" rx="12" fill="url(#accent)"/>';
        $blocks .= '<text x="40" y="53" text-anchor="middle" fill="#fff" font-size="18" font-weight="800" font-family="Arial,sans-serif">' . ($i + 1) . '</text>';
        $blocks .= '<text x="76" y="37" fill="#0f172a" font-size="18" font-weight="800" font-family="Arial,sans-serif">' . $e($item['title']) . '</text>';
        $blocks .= '<text x="76" y="61" fill="#475569" font-size="14" font-family="Arial,sans-serif">' . $e($item['desc']) . '</text>';
        $blocks .= '</g>';
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
        . '<defs>'
        . '<linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient>'
        . '<linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f8fbff"/><stop offset="100%" stop-color="#eef6ff"/></linearGradient>'
        . '<filter id="soft"><feDropShadow dx="0" dy="18" stdDeviation="18" flood-color="#3b82f6" flood-opacity=".12"/></filter>'
        . '</defs>'
        . '<rect width="1280" height="720" fill="url(#bg)"/>'
        . '<circle cx="1060" cy="110" r="140" fill="' . $c2 . '" opacity=".18"/>'
        . '<circle cx="200" cy="610" r="180" fill="' . $c1 . '" opacity=".14"/>'
        . '<rect x="62" y="56" width="1156" height="608" rx="36" fill="#ffffff" stroke="#cfe1ff" stroke-width="4" filter="url(#soft)"/>'
        . '<text x="88" y="124" fill="#0f172a" font-size="52" font-weight="900" font-family="Arial,sans-serif">' . $e($title) . '</text>'
        . '<text x="88" y="170" fill="#475569" font-size="24" font-family="Arial,sans-serif">' . $e($subtitle) . '</text>'
        . '<g transform="translate(868,96)">'
        . '<rect x="0" y="0" width="264" height="176" rx="28" fill="#eff6ff" stroke="#3b82f6" stroke-width="4"/>'
        . '<rect x="44" y="40" width="176" height="96" rx="18" fill="none" stroke="url(#accent)" stroke-width="10"/>'
        . '<rect x="60" y="56" width="144" height="64" rx="12" fill="#dbeafe" stroke="#93c5fd" stroke-width="3"/>'
        . '<text x="132" y="158" fill="#1d4ed8" font-size="18" text-anchor="middle" font-family="Arial,sans-serif">Dikdörtgen örneği</text>'
        . '</g>'
        . '<rect x="120" y="276" width="132" height="88" rx="18" fill="#dbeafe" opacity=".85"/>'
        . '<rect x="276" y="260" width="170" height="110" rx="18" fill="#bfdbfe" opacity=".9"/>'
        . '<rect x="112" y="412" width="200" height="112" rx="18" fill="#fef3c7" opacity=".82"/>'
        . $blocks
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
};

$makeSplit = function (string $title, string $html, string $imgTitle, string $subtitle, array $items, string $c1, string $c2) use ($svg): array {
    return [
        'title' => $title,
        'layout' => 'split',
        'layout_meta' => [
            'split_ratio' => '70-30',
            'left' => ['type' => 'text', 'text' => $html],
            'right' => ['type' => 'image', 'image_url' => $svg($imgTitle, $subtitle, $items, $c1, $c2)],
        ],
        'xp' => 10,
        'kind' => 'topic',
        'interaction_type' => 'none',
        'points' => 5,
        'time_limit' => 10,
        'double_points' => false,
        'question' => ['options' => []],
    ];
};

$slides = [];
$slides[] = $makeSplit(
    'Dikdörtgeni Tanıyalım',
    '<h2>Dikdörtgen nasıl bir şekildir?</h2><p><strong>Dikdörtgen</strong>, dört kenarı olan ve karşılıklı kenarları eşit olan bir şekildir. Dört köşesi vardır. Kapı, pencere, kitap, tahta ve masa üstü gibi nesneler dikdörtgene örnek olabilir.</p><p>Bu derste öğrenciler verilen nesneleri karşılaştırarak dikdörtgen olanları boşluklara sürükleyecek. Ayrıca günlük hayattan dikdörtgen örneklerini konuşacaklar.</p><p><strong>Hatırlatma:</strong> Bir nesnenin şekli bazen adından daha önemlidir.</p>',
    'Dikdörtgen Şekli',
    'Dört kenarlı şekil',
    [
        ['title' => '4 kenar', 'desc' => 'Karşılıklı kenarlar eşit'],
        ['title' => '4 köşe', 'desc' => 'Köşeleri vardır'],
        ['title' => 'Düz çizgi', 'desc' => 'Yuvarlak değildir'],
        ['title' => 'Örnekler', 'desc' => 'Kapı, kitap, defter'],
    ],
    '#3b82f6',
    '#2563eb'
);

$slides[] = $makeSplit(
    'Dikdörtgen Olan Nesneler',
    '<h2>Hangileri dikdörtgen şeklindedir?</h2><p>Bir nesnenin dikdörtgen olup olmadığını anlamak için kenar ve köşelerine dikkat etmek gerekir. Öğrencilerden sınıfta gördükleri nesneler arasından dikdörtgen olanları seçmeleri istenir. Sınıf kapısı, pencere, sıra üstü, kitap ve tahta bu şekle örnek olabilir.</p><p><strong>Sınıf konuşması:</strong> “Bu nesnenin dört köşesi var mı? Karşılıklı kenarları eşit mi?”</p>',
    'Günlük Örnekler',
    'Sınıfta tartışma',
    [
        ['title' => 'Kapı', 'desc' => 'Dikdörtgen olabilir'],
        ['title' => 'Pencere', 'desc' => 'Çoğu dikdörtgendir'],
        ['title' => 'Kitap', 'desc' => 'Dikdörtgen şekilli'],
        ['title' => 'Tahta', 'desc' => 'Sıklıkla dikdörtgendir'],
    ],
    '#10b981',
    '#0ea5e9'
);

$slides[] = $makeSplit(
    'Şekillerine Göre Gruplama',
    '<h2>Nesneleri nasıl gruplarız?</h2><p>Nesneleri şekillerine göre gruplamak, dikkat ve karşılaştırma becerisini geliştirir. Öğrenciler dikdörtgen olan nesneleri diğerlerinden ayırırken aynı zamanda gözlem yapmayı öğrenir. Bu etkinlikte amaç, benzer şekilli nesneleri bir araya getirmektir.</p><p><strong>Önemli nokta:</strong> Nesnenin renginden çok şekline bakmak gerekir.</p>',
    'Gruplama',
    'Benzerleri ayır',
    [
        ['title' => 'Dikdörtgenler', 'desc' => 'Köşeli nesneler'],
        ['title' => 'Dikdörtgen değil', 'desc' => 'Yuvarlak nesneler'],
        ['title' => 'Dikkat', 'desc' => 'Kenar ve köşe say'],
        ['title' => 'Sınıflama', 'desc' => 'Doğru gruba koy'],
    ],
    '#f59e0b',
    '#f97316'
);

$slides[] = $makeSplit(
    'Fare Kullanımı',
    '<h2>Sürükle-bırak yaparken fareyi nasıl kullanırız?</h2><p>Sürükle-bırak işlemi, fare ile bir nesneye tıklayıp onu basılı tutarak başka bir yere taşımaktır. Bu işlem bilgisayar kullanımının temel becerilerindendir. Öğrenciler dikdörtgen nesneleri doğru boşluklara sürükleyerek fare kullanımında pratik yaparlar.</p><p>Bu etkinlik, küçük yaş gruplarında el-göz koordinasyonunu güçlendirir ve öğrenmeyi eğlenceli hale getirir.</p>',
    'Sürükle-Bırak',
    'Fareyle taşı',
    [
        ['title' => 'Tıkla', 'desc' => 'Nesneyi seç'],
        ['title' => 'Basılı tut', 'desc' => 'Taşımaya başla'],
        ['title' => 'Taşı', 'desc' => 'Doğru yere götür'],
        ['title' => 'Bırak', 'desc' => 'Yerine yerleştir'],
    ],
    '#ec4899',
    '#f43f5e'
);

$slides[] = $makeSplit(
    'Dikdörtgen Değil',
    '<h2>Her düz görünen şey dikdörtgen midir?</h2><p>Hayır. Bazı nesneler düz kenarlı gibi görünse de farklı şekiller olabilir. Bu yüzden dikkatle incelemek gerekir. Öğrencilerden bir nesnenin dikdörtgen olup olmadığını söylerken nedenini de açıklamaları istenir.</p><p><strong>Soru örneği:</strong> “Sınıfımızın kapısı dikdörtgen şekline örnek olabilir mi?”</p>',
    'Karşılaştırma',
    'Dikdörtgen mi değil mi?',
    [
        ['title' => 'Köşe', 'desc' => '4 köşe varsa bak'],
        ['title' => 'Kenar', 'desc' => 'Düz ve uzun olabilir'],
        ['title' => 'Karşılık', 'desc' => 'Karşılıklı kenar eşit'],
        ['title' => 'Kontrol', 'desc' => 'Dikkatli bak'],
    ],
    '#14b8a6',
    '#06b6d4'
);

$slides[] = $makeSplit(
    'Ders Özeti',
    '<h2>Bugün ne öğrendik?</h2><p>Dikdörtgen şeklini tanıdık, çevremizdeki nesneleri şekillerine göre ayırdık ve fare ile sürükle-bırak yapmayı öğrendik. Artık sınıfta ve evde gördüğümüz nesneleri daha dikkatli inceleyebiliriz.</p><p><strong>Mini görev:</strong> Evinden 5 nesne seç ve hangilerinin dikdörtgen olduğunu söyle.</p>',
    'Özet',
    'Kapanış',
    [
        ['title' => 'Tanı', 'desc' => 'Dikdörtgeni fark et'],
        ['title' => 'Sınıflandır', 'desc' => 'Benzerleri ayır'],
        ['title' => 'Sürükle', 'desc' => 'Fareyi kullan'],
        ['title' => 'Uygula', 'desc' => 'Günlük hayatta göster'],
    ],
    '#f59e0b',
    '#22c55e'
);

$curriculum = [
    'title' => 'Dikdörtgen Şeklini Tanıma ve Nesneleri Gruplama',
    'lesson_number' => 1,
    'konu' => 'Dikdörtgen şekli ve sürükle-bırak etkinliği',
    'kazanimlar' => [
        'Dikdörtgen şeklini tanır.',
        'Nesneleri şekillerine uygun olarak gruplar.',
        'Sürükle bırak işlemini yaparak fareyi etkin bir şekilde kullanır.',
    ],
    'etkinlikler' => [
        'Dikdörtgen şekline günlük hayattan örnekler söyleme.',
        'Verilen nesneleri dikdörtgen olanlar ve olmayanlar diye ayırma.',
        'Fare ile sürükle-bırak uygulaması yapma.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'Dikdörtgen Şeklini Tanıma',
    'category' => 'İlkokul',
    'difficulty' => 'Kolay',
    'lesson_description' => 'Dikdörtgen şekli, nesne gruplama ve sürükle-bırak becerileri için ilkokul 3-4. sınıf düzeyi ders.',
    'cover_image' => 'kapak-gorseli/dikdortgen-sekli.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Arial,system-ui,sans-serif;background:linear-gradient(180deg,#f8fbff 0%,#eef6ff 100%);color:#0f172a}",
    'curriculum' => $curriculum,
    'target_scope' => '3-4',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'Dikdörtgen Şeklini Tanıyalım',
        'code' => 'ILK-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'weekly_hours' => 1,
        'lesson_payload' => $lessonPayload,
        'sub_courses' => [],
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/dikdortgen-sekli-coursepkg.coursepkg');
file_put_contents($out, $pkg);
echo $out . PHP_EOL;
