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
    $lines = '';
    $x = 88;
    $y = 190;
    foreach ($items as $idx => $item) {
        $col = $idx % 2;
        $row = intdiv($idx, 2);
        $bx = $x + ($col * 356);
        $by = $y + ($row * 126);
        $lines .= '<g transform="translate(' . $bx . ',' . $by . ')">';
        $lines .= '<rect x="0" y="0" width="300" height="94" rx="22" fill="rgba(255,255,255,.10)" stroke="rgba(255,255,255,.18)"/>';
        $lines .= '<circle cx="38" cy="47" r="22" fill="url(#g)"/>';
        $lines .= '<text x="38" y="53" text-anchor="middle" fill="#fff" font-size="18" font-weight="800" font-family="Inter,Segoe UI,Arial,sans-serif">' . ($idx + 1) . '</text>';
        $lines .= '<text x="74" y="37" fill="#fff" font-size="18" font-weight="800" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($item['title']) . '</text>';
        $lines .= '<text x="74" y="62" fill="#dbeafe" font-size="14" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($item['desc']) . '</text>';
        $lines .= '</g>';
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
        . '<defs>'
        . '<linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient>'
        . '<linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0b1120"/><stop offset="100%" stop-color="#0f172a"/></linearGradient>'
        . '<filter id="blur"><feGaussianBlur stdDeviation="18"/></filter>'
        . '</defs>'
        . '<rect width="1280" height="720" fill="url(#bg)"/>'
        . '<circle cx="1060" cy="120" r="170" fill="' . $c2 . '" opacity=".22" filter="url(#blur)"/>'
        . '<circle cx="210" cy="610" r="210" fill="' . $c1 . '" opacity=".18" filter="url(#blur)"/>'
        . '<rect x="68" y="64" width="1144" height="592" rx="34" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.12)"/>'
        . '<text x="88" y="128" fill="#fff" font-size="50" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($title) . '</text>'
        . '<text x="88" y="172" fill="#cbd5e1" font-size="24" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($subtitle) . '</text>'
        . '<rect x="858" y="110" width="286" height="176" rx="28" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.14)"/>'
        . '<path d="M904 206h138M973 137v138" stroke="url(#g)" stroke-width="16" stroke-linecap="round"/>'
        . '<circle cx="972" cy="206" r="54" fill="url(#g)"/>'
        . '<text x="972" y="214" fill="#fff" font-size="18" text-anchor="middle" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">VERI</text>'
        . '<text x="1010" y="270" fill="#e2e8f0" font-size="18" text-anchor="middle" font-family="Inter,Segoe UI,Arial,sans-serif">TÜRÜ</text>'
        . $lines
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
    'Veri Türlerine Giriş',
    '<h2>Veri türü neden önemlidir?</h2><p>Programlamada veri, işlenecek bilginin kendisidir. Ancak her veri aynı biçimde saklanmaz. Bazı veriler sayı, bazıları metin, bazıları ise doğru/yanlış gibi mantıksal değerlerdir. Bir değişkende hangi türün tutulacağı, programın amacına göre seçilmelidir.</p><p>Örneğin öğrenci notu sayısal bir veri iken, öğrenci adı metinsel bir veridir. Telefon numarası ise sayıya benzese de çoğu zaman metin olarak tutulur; çünkü üzerinde toplama yapmak değil, aynen saklamak istenir.</p><p><strong>Ana fikir:</strong> Yanlış veri türü, doğru görünen ama yanlış çalışan programlar üretir.</p>',
    'Veri Türü',
    'Veriyi doğru sınıflandır',
    [
        ['title' => 'Sayı', 'desc' => 'Hesaplama için'],
        ['title' => 'Metin', 'desc' => 'İsim ve açıklama'],
        ['title' => 'Mantıksal', 'desc' => 'Evet / hayır'],
        ['title' => 'Dizi', 'desc' => 'Çoklu veri saklama'],
    ],
    '#0f766e',
    '#2563eb'
);

$slides[] = $makeSplit(
    'Doğru Tanımlama',
    '<h2>Veri türünü doğru seçmek neden kritiktir?</h2><p>Veri türü doğru tanımlandığında program hem daha güvenilir hem de daha anlaşılır olur. Örneğin bir fiyat ondalıklı sayıdır, bir yaş tam sayıdır, bir şifre ise çoğu zaman metin olarak tutulur. Veri türü seçimi, veri üzerinde yapılabilecek işlemleri de belirler.</p><p>Bir sayıyı metin olarak saklarsanız toplama işlemi yapamazsınız. Bir metni sayı olarak tanımlarsanız da biçim kaybı oluşabilir. Bu yüzden veri türü, sadece bir etiket değil, programın davranışını belirleyen önemli bir karar noktasıdır.</p>',
    'Doğru Seçim',
    'Program davranışı',
    [
        ['title' => 'Yaş', 'desc' => 'Tam sayı'],
        ['title' => 'Fiyat', 'desc' => 'Ondalıklı sayı'],
        ['title' => 'İsim', 'desc' => 'Metin'],
        ['title' => 'Onay', 'desc' => 'Boolean'],
    ],
    '#1d4ed8',
    '#7c3aed'
);

$slides[] = $makeSplit(
    'Dil Farkları',
    '<h2>Veri türlerinin isimleri her dilde aynı olmayabilir</h2><p>Farklı programlama dillerinde veri türlerinin isimleri değişebilir. Bir dilde <strong>integer</strong> denilen tür, başka bir dilde <strong>int</strong> olarak yazılabilir. Benzer şekilde metin için <strong>string</strong>, mantıksal değer için <strong>boolean</strong> veya <strong>bool</strong> kullanılabilir.</p><p>Bu farklılık kavramı değiştirmez; sadece dilin sözdizimini değiştirir. Öğrencinin önce kavramı anlaması, sonra o kavramın dildeki karşılığını öğrenmesi gerekir.</p>',
    'Dil Sözdizimi',
    'Aynı kavram farklı ad',
    [
        ['title' => 'int', 'desc' => 'Tam sayı'],
        ['title' => 'float', 'desc' => 'Ondalıklı sayı'],
        ['title' => 'string', 'desc' => 'Metin'],
        ['title' => 'bool', 'desc' => 'Doğru / yanlış'],
    ],
    '#0891b2',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Sayısal ve Metinsel',
    '<h2>Sayısal ve metinsel veri örnekleri</h2><p>Sayısal veri türleri hesaplama yapmak için kullanılır. Tam sayı, kesir içermeyen değerleri temsil ederken; float ya da ondalıklı sayı türleri küsuratlı değerleri tutar. Metinsel veri türü ise harf, boşluk, sembol ve rakamların bir arada yer alabildiği yapıdadır.</p><p>Örnek olarak okul numarası, isim, sınıf şubesi, puan ve boy bilgisi farklı veri türleriyle saklanabilir. Aynı veri bazen bir amaçla sayısal, başka bir amaçla metinsel olabilir. Örneğin “2026” tarihi sayı gibi görünse de tarih etiketi olarak metin biçiminde de saklanabilir.</p>',
    'Veri Örnekleri',
    'Sayı ve metin',
    [
        ['title' => 'Integer', 'desc' => 'Tam sayı'],
        ['title' => 'Float', 'desc' => 'Küsuratlı sayı'],
        ['title' => 'String', 'desc' => 'Karakter dizisi'],
        ['title' => 'Char', 'desc' => 'Tek karakter'],
    ],
    '#16a34a',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Alt Veri Türleri',
    '<h2>Seçilen türün alt yapıları olabilir</h2><p>Her programın ihtiyaç duyduğu veri türü aynı değildir. Sayısal veri türünün de kendi içinde tam sayı ve ondalıklı sayı gibi alt ayrımları vardır. Bu ayrım, yapılacak işlem sonucunu doğrudan etkiler.</p><p>Örneğin 5 / 2 işlemi tam sayı mantığında farklı, ondalıklı sayı mantığında farklı sonuç verir. Bu nedenle veri türünü seçmek yalnızca depolama kararı değil, aynı zamanda hesaplama kararıdır.</p>',
    'Alt Türler',
    'Sayıların iç yapısı',
    [
        ['title' => 'Byte', 'desc' => 'Küçük aralık'],
        ['title' => 'Integer', 'desc' => 'Standart tam sayı'],
        ['title' => 'Long', 'desc' => 'Büyük tam sayı'],
        ['title' => 'Float', 'desc' => 'Kesirli değer'],
    ],
    '#dc2626',
    '#1d4ed8'
);

$slides[] = $makeSplit(
    'Program İçinde Kullanım',
    '<h2>Bir program aynı anda farklı veri türlerini nasıl kullanır?</h2><p>Bir öğrenci kayıt sisteminde ad, soyad, yaş, sınıf, aktiflik durumu ve ders notu birlikte tutulabilir. Burada metin, sayı, mantıksal değer ve dizi yapıları birlikte çalışır. Programın amacı neyse, veri türleri de o amaca hizmet edecek şekilde düzenlenir.</p><p>Bu yaklaşım sayesinde veriler daha düzenli saklanır, daha hızlı işlenir ve hata ihtimali azalır. Veri türleri arasında bilinçli seçim yapmak, programcıyı daha profesyonel hale getirir.</p>',
    'Program Verisi',
    'Birden fazla tür birlikte',
    [
        ['title' => 'Ad', 'desc' => 'String'],
        ['title' => 'Not', 'desc' => 'Integer / Float'],
        ['title' => 'Aktif', 'desc' => 'Boolean'],
        ['title' => 'Dersler', 'desc' => 'Dizi'],
    ],
    '#7c3aed',
    '#2563eb'
);

$slides[] = [
    'title' => 'Soru 1',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Bir öğrencinin adı hangi veri türüyle temsil edilmelidir?',
    'question' => ['options' => [
        ['text' => 'String', 'correct' => true],
        ['text' => 'Integer', 'correct' => false],
        ['text' => 'Boolean', 'correct' => false],
        ['text' => 'Float', 'correct' => false],
    ]],
];

$slides[] = [
    'title' => 'Soru 2',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Aşağıdakilerden hangisi mantıksal veri türüne örnektir?',
    'question' => ['options' => [
        ['text' => 'Doğru / yanlış', 'correct' => true],
        ['text' => 'Metin', 'correct' => false],
        ['text' => 'Ondalıklı sayı', 'correct' => false],
        ['text' => 'Dizi', 'correct' => false],
    ]],
];

$slides[] = [
    'title' => 'Soru 3',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Fiyat bilgisi için en uygun veri türü hangisidir?',
    'question' => ['options' => [
        ['text' => 'Float', 'correct' => true],
        ['text' => 'Char', 'correct' => false],
        ['text' => 'Boolean', 'correct' => false],
        ['text' => 'String', 'correct' => false],
    ]],
];

$slides[] = [
    'title' => 'Doğru Yanlış 1',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'true_false',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Veri türü doğru seçilmezse programın davranışı etkilenebilir.',
    'question' => ['options' => [['text' => 'Doğru', 'correct' => true]]],
];

$slides[] = [
    'title' => 'Doğru Yanlış 2',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'true_false',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Aynı veri her programlama dilinde aynı isimle tanımlanmak zorundadır.',
    'question' => ['options' => [['text' => 'Yanlış', 'correct' => true]]],
];

$slides[] = [
    'title' => 'Kısa Cevap',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'short_answer',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Birden fazla aynı tür veriyi saklayan yapı hangi kavramdır?',
    'question' => ['answer' => 'dizi'],
];

$slides[] = [
    'title' => 'Eşleştirme',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'matching',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Veri türlerini uygun örnekleriyle eşleştir.',
    'question' => ['pairs' => [
        ['left' => 'Integer', 'right' => 'Tam sayı'],
        ['left' => 'Float', 'right' => 'Ondalıklı sayı'],
        ['left' => 'String', 'right' => 'Metin'],
        ['left' => 'Boolean', 'right' => 'Doğru / yanlış'],
    ]],
];

$slides[] = $makeSplit(
    'Ders Özeti',
    '<h2>Bugün ne öğrendik?</h2><p>Veri türünün ne olduğunu, doğru seçimin neden önemli olduğunu, farklı programlama dillerinde isimlerin değişebileceğini ve sayı, metin, mantıksal değer ile dizi kavramlarını inceledik. Ayrıca aynı verinin farklı amaçlarla farklı türlerde saklanabileceğini gördük.</p><p><strong>Mini görev:</strong> Günlük hayattan 5 veri yaz ve her biri için en uygun veri türünü belirle.</p>',
    'Ders Özeti',
    'Kapanış',
    [
        ['title' => 'Tanım', 'desc' => 'Veri türü belirlenir'],
        ['title' => 'Önem', 'desc' => 'Doğru seçim davranışı değiştirir'],
        ['title' => 'Türler', 'desc' => 'Sayı, metin, mantık, dizi'],
        ['title' => 'Kullanım', 'desc' => 'Program amacına göre seçilir'],
    ],
    '#2563eb',
    '#0f766e'
);

$curriculum = [
    'title' => 'Veri Türleri ve Program İçinde Kullanımı',
    'lesson_number' => 3,
    'konu' => 'Veri türleri, sayısal ve metinsel veri kullanımı',
    'kazanimlar' => [
        'Veri türlerini amacına uygun şekilde programa tanımlar.',
        'Farklı veri türlerini program içerisinde kullanır.',
    ],
    'etkinlikler' => [
        'Veri türü seçme ve örnekleme çalışması.',
        'Aynı verinin farklı amaçlarla kullanılmasını tartışma.',
        'Sayısal, metinsel ve mantıksal örnekler üzerinden uygulama.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'Veri Türleri ve Program İçinde Kullanımı',
    'category' => 'Bilişim Teknolojileri ve Yazılım',
    'difficulty' => 'Orta',
    'lesson_description' => 'Veri türleri, değişkenler ve program içinde doğru kullanım odağında lise düzeyi ders.',
    'cover_image' => 'kapak-gorseli/veri-turleri.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fbff 0%,#eef6ff 100%);color:#0f172a}",
    'curriculum' => $curriculum,
    'target_scope' => '9-10',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'Veri Türleri ve Program İçinde Kullanımı',
        'code' => 'BT-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'weekly_hours' => 2,
        'lesson_payload' => $lessonPayload,
        'sub_courses' => [],
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/veri-turleri-ve-program-icinde-kullanimi.coursepkg');
file_put_contents($out, $pkg);
echo $out . PHP_EOL;
