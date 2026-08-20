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
        $blocks .= '<circle cx="40" cy="47" r="22" fill="url(#accent)"/>';
        $blocks .= '<text x="40" y="53" text-anchor="middle" fill="#fff" font-size="18" font-weight="800" font-family="Arial,sans-serif">' . ($i + 1) . '</text>';
        $blocks .= '<text x="76" y="37" fill="#0f172a" font-size="18" font-weight="800" font-family="Arial,sans-serif">' . $e($item['title']) . '</text>';
        $blocks .= '<text x="76" y="61" fill="#475569" font-size="14" font-family="Arial,sans-serif">' . $e($item['desc']) . '</text>';
        $blocks .= '</g>';
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
        . '<defs>'
        . '<linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient>'
        . '<linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fff8e7"/><stop offset="100%" stop-color="#fff2dc"/></linearGradient>'
        . '<filter id="soft"><feDropShadow dx="0" dy="18" stdDeviation="18" flood-color="#f59e0b" flood-opacity=".14"/></filter>'
        . '</defs>'
        . '<rect width="1280" height="720" fill="url(#bg)"/>'
        . '<circle cx="1080" cy="100" r="150" fill="' . $c2 . '" opacity=".18"/>'
        . '<circle cx="180" cy="620" r="180" fill="' . $c1 . '" opacity=".14"/>'
        . '<circle cx="1040" cy="560" r="60" fill="rgba(255,255,255,.5)"/>'
        . '<circle cx="1040" cy="560" r="40" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="10"/>'
        . '<rect x="62" y="56" width="1156" height="608" rx="36" fill="#ffffff" stroke="#fde68a" stroke-width="4" filter="url(#soft)"/>'
        . '<text x="88" y="124" fill="#0f172a" font-size="52" font-weight="900" font-family="Arial,sans-serif">' . $e($title) . '</text>'
        . '<text x="88" y="170" fill="#475569" font-size="24" font-family="Arial,sans-serif">' . $e($subtitle) . '</text>'
        . '<g transform="translate(880,106)">'
        . '<rect x="0" y="0" width="246" height="196" rx="32" fill="#fff7ed" stroke="#f59e0b" stroke-width="4"/>'
        . '<circle cx="123" cy="92" r="64" fill="url(#accent)"/>'
        . '<circle cx="123" cy="92" r="44" fill="rgba(255,255,255,.18)" stroke="#fff" stroke-width="10"/>'
        . '<path d="M123 58l8 16 18 3-13 13 3 18-16-8-16 8 3-18-13-13 18-3z" fill="#fff" opacity=".95"/>'
        . '<text x="123" y="170" fill="#b45309" font-size="18" text-anchor="middle" font-family="Arial,sans-serif">Daire şekli</text>'
        . '</g>'
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
    'Daireyi Tanıyalım',
    '<h2>Daire nasıl bir şekildir?</h2><p><strong>Daire</strong>, kenarı ve köşesi olmayan, yuvarlak bir şekildir. Gözümüzle baktığımızda tam bir çember gibi görünür. Günlük hayatta saat, tabak, para ve kapaklar gibi birçok nesne daire şeklini andırır.</p><p>Bu derste önce daireyi tanıyacak, sonra çevremizdeki nesneleri şekillerine göre ayıracak ve sürükle-bırak işlemiyle fareyi kullanarak etkinlik yapacağız.</p><p><strong>Hatırlatma:</strong> Daireyi görmek kadar, daireye benzeyen nesneleri ayırt etmek de önemlidir.</p>',
    'Daire Şekli',
    'Yuvarlak nesneler',
    [
        ['title' => 'Kenar yok', 'desc' => 'Dairede düz çizgi olmaz'],
        ['title' => 'Köşe yok', 'desc' => 'Uç noktası bulunmaz'],
        ['title' => 'Yuvarlak', 'desc' => 'Çevresi kavisli görünür'],
        ['title' => 'Örnekler', 'desc' => 'Saat, tabak, bozuk para'],
    ],
    '#f59e0b',
    '#f97316'
);

$slides[] = $makeSplit(
    'Daire Olan Nesneler',
    '<h2>Hangileri daire şeklindedir?</h2><p>Bir nesnenin daire olup olmadığını anlamak için onu dikkatle incelemek gerekir. Çocukların çevrelerinden örnekler bulmaları, şekilleri daha iyi öğrenmelerine yardımcı olur. Sınıfta su şişesi kapağı, bozuk para, topun yuvarlak yüzeyi ya da saat gibi örnekler tartışılabilir.</p><p><strong>Örnek konuşma:</strong> “Bu nesne daireye benziyor mu? Köşesi var mı? Yuvarlak mı?”</p>',
    'Günlük Örnekler',
    'Sınıfta tartışma',
    [
        ['title' => 'Bozuk para', 'desc' => 'Daireye örnek olabilir'],
        ['title' => 'Kapak', 'desc' => 'Yuvarlak olabilir'],
        ['title' => 'Saat', 'desc' => 'Çoğu daire biçimlidir'],
        ['title' => 'Tabak', 'desc' => 'Sıklıkla dairedir'],
    ],
    '#10b981',
    '#0ea5e9'
);

$slides[] = $makeSplit(
    'Şekillerine Göre Gruplama',
    '<h2>Nesneleri nasıl gruplarız?</h2><p>Nesneleri şekillerine göre gruplamak, sınıflandırma becerisini geliştirir. Aynı özellikteki nesneleri bir araya getirmek, karşılaştırma yapmayı ve dikkatli gözlem yapmayı kolaylaştırır. Bu etkinlikte öğrenciler daire olan nesneleri diğerlerinden ayıracak.</p><p>Sınıflama yaparken en önemli nokta, nesnenin adından çok <strong>şekline</strong> bakmaktır. Bazen bir nesnenin adı farklı olsa da görünüşü daire olabilir.</p>',
    'Gruplama',
    'Benzer olanları ayır',
    [
        ['title' => 'Daireler', 'desc' => 'Yuvarlak nesneler'],
        ['title' => 'Daire değil', 'desc' => 'Köşeli nesneler'],
        ['title' => 'Dikkat', 'desc' => 'Özelliğe bak'],
        ['title' => 'Sınıflama', 'desc' => 'Aynı gruba koy'],
    ],
    '#3b82f6',
    '#8b5cf6'
);

$slides[] = $makeSplit(
    'Fare Kullanımı',
    '<h2>Sürükle-bırak yaparken fareyi nasıl kullanırız?</h2><p>Sürükle-bırak işlemi, fare ile bir nesneye tıklayıp onu basılı tutarak başka bir yere taşımaktır. Bu işlem, bilgisayarı etkin kullanmanın temel becerilerinden biridir. Özellikle küçük yaş gruplarında el-göz koordinasyonunu güçlendirir.</p><p>Bu derste öğrenci, daire şeklindeki nesneleri boşluklara sürükleyerek yerleştirir. Böylece hem şekilleri öğrenir hem de fare kullanımında pratik kazanır.</p>',
    'Sürükle-Bırak',
    'Fareyle taşı',
    [
        ['title' => 'Tıkla', 'desc' => 'Nesneyi seç'],
        ['title' => 'Basılı tut', 'desc' => 'Sürüklemeye hazır ol'],
        ['title' => 'Taşı', 'desc' => 'Doğru yere götür'],
        ['title' => 'Bırak', 'desc' => 'Yerine yerleştir'],
    ],
    '#ec4899',
    '#f43f5e'
);

$slides[] = $makeSplit(
    'Daire Değil',
    '<h2>Her yuvarlak görünen şey daire midir?</h2><p>Hayır. Bazı nesneler yuvarlak görünse de farklı biçimlere sahip olabilir. Bu yüzden sadece “benziyor” demek yerine dikkatlice bakmak gerekir. Öğrencilerden nesnenin tam daire olup olmadığını anlamaları istenir.</p><p><strong>Sınıf etkinliği:</strong> Öğretmen çeşitli nesneler gösterir; öğrenciler bunların daire olup olmadığını söyler ve nedenini açıklar.</p>',
    'Karşılaştırma',
    'Daire mi değil mi?',
    [
        ['title' => 'Köşe', 'desc' => 'Varsa daire değildir'],
        ['title' => 'Kenar', 'desc' => 'Düz çizgi olabilir'],
        ['title' => 'Yuvarlaklık', 'desc' => 'Benzerlik gösterebilir'],
        ['title' => 'Kontrol', 'desc' => 'Dikkatli bak'],
    ],
    '#14b8a6',
    '#06b6d4'
);

$slides[] = $makeSplit(
    'Ders Özeti',
    '<h2>Bugün ne öğrendik?</h2><p>Daire şeklini tanıdık, günlük hayattan daire örnekleri düşündük, nesneleri şekillerine göre grupladık ve fare ile sürükle-bırak yapmayı öğrendik. Şimdi çevrendeki nesnelere daha dikkatli bakabilir ve onları şekillerine göre sınıflandırabilirsin.</p><p><strong>Mini görev:</strong> Evinden 5 nesne seç ve hangilerinin daire olduğunu söyle.</p>',
    'Özet',
    'Kapanış',
    [
        ['title' => 'Tanı', 'desc' => 'Daireyi fark et'],
        ['title' => 'Sınıflandır', 'desc' => 'Benzerleri ayır'],
        ['title' => 'Sürükle', 'desc' => 'Fareyi kullan'],
        ['title' => 'Uygula', 'desc' => 'Günlük hayatta göster'],
    ],
    '#f59e0b',
    '#22c55e'
);

$curriculum = [
    'title' => 'Daire Şeklini Tanıma ve Nesneleri Gruplama',
    'lesson_number' => 1,
    'konu' => 'Daire şekli ve sürükle-bırak etkinliği',
    'kazanimlar' => [
        'Daire şeklini tanır.',
        'Nesneleri şekillerine uygun olarak gruplar.',
        'Sürükle bırak işlemini yaparak fareyi etkin bir şekilde kullanır.',
    ],
    'etkinlikler' => [
        'Daire şeklindeki nesneleri sınıfta tartışma.',
        'Verilen nesneleri daire olanlar ve olmayanlar diye ayırma.',
        'Fare ile sürükle-bırak uygulaması yapma.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'Daire Şeklini Tanıma',
    'category' => 'İlkokul',
    'difficulty' => 'Kolay',
    'lesson_description' => 'Daire şekli, nesne gruplama ve sürükle-bırak becerileri için ilkokul 3-4. sınıf düzeyi ders.',
    'cover_image' => 'kapak-gorseli/daire-sekli.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Arial,system-ui,sans-serif;background:linear-gradient(180deg,#fff8e7 0%,#fff1d6 100%);color:#0f172a}",
    'curriculum' => $curriculum,
    'target_scope' => '3-4',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'Daire Şeklini Tanıyalım',
        'code' => 'ILK-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'weekly_hours' => 1,
        'lesson_payload' => $lessonPayload,
        'sub_courses' => [],
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/daire-sekli-coursepkg.coursepkg');
file_put_contents($out, $pkg);
echo $out . PHP_EOL;
