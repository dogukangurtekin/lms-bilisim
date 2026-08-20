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
    $y = 188;
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
    'Karar Yapılarının Temeli',
    '<h2>Programın akışına neden karar veririz?</h2><p>Her program tek düz bir çizgide ilerlemez. Kullanıcının verdiği cevaba, verinin değerine ya da bir koşulun gerçekleşmesine göre program farklı yollara gidebilir. Karar yapıları bu noktada programın akışını yönlendirir.</p><p><strong>if</strong>, <strong>else</strong> ve <strong>else if</strong> yapıları sayesinde bir problemde birden fazla olası durum düzenli biçimde kontrol edilir. Böylece aynı program farklı senaryolara doğru tepki verebilir.</p><p><strong>Önemli fikir:</strong> Koşul yoksa karar da yoktur; karar yoksa uyarlanabilir program da yoktur.</p>',
    'Karar Yapısı',
    'Koşula göre yol seç',
    [
        ['title' => 'Koşul', 'desc' => 'Evet / hayır kontrolü'],
        ['title' => 'if', 'desc' => 'İlk karar noktası'],
        ['title' => 'else if', 'desc' => 'Ek koşul'],
        ['title' => 'else', 'desc' => 'Varsayılan yol'],
    ],
    '#0f766e',
    '#2563eb'
);

$slides[] = $makeSplit(
    'Mantıksal Operatörler',
    '<h2>Birden fazla koşulu nasıl birlikte kontrol ederiz?</h2><p>Mantıksal operatörler, koşulları birleştirmemize yardım eder. <strong>ve (and)</strong> operatörü iki koşulun birlikte doğru olmasını ister. <strong>veya (or)</strong> operatörü koşullardan birinin doğru olmasını yeterli görür. <strong>değil (not)</strong> operatörü ise bir durumun tersini alır.</p><p>Örneğin bir etkinliğe katılım için yaşın uygun olması <em>ve</em> kayıtlı olunması gerekebilir. Ya da ders için ödev yapılmış olması <em>veya</em> izin alınmış olması farklı bir yol açabilir. Bu yapıların programı gerçek hayata yaklaştırdığı söylenebilir.</p>',
    'Mantıksal Operatörler',
    'Birden fazla koşul',
    [
        ['title' => 'AND', 'desc' => 'Hepsi doğru olmalı'],
        ['title' => 'OR', 'desc' => 'Biri doğruysa yeter'],
        ['title' => 'NOT', 'desc' => 'Tersini alır'],
        ['title' => 'Koşul', 'desc' => 'Akışı belirler'],
    ],
    '#1d4ed8',
    '#7c3aed'
);

$slides[] = $makeSplit(
    'If - Else Yapısı',
    '<h2>Tek bir koşul iki farklı yol oluşturabilir</h2><p><strong>if-else</strong> yapısı, koşul doğruysa bir blokun; yanlışsa başka bir blokun çalışmasını sağlar. Bu yapı özellikle iki seçenekli karar süreçlerinde kullanılır. Öğrenci sınavı geçti mi, kullanıcı giriş yaptı mı, not yeterli mi gibi sorular bu yapıyla kontrol edilir.</p><p>Bir programda hata mesajı göstermek ya da başarılı işlem mesajı vermek için en temel karar yapılarından biridir.</p>',
    'If-Else',
    'İki yollu seçim',
    [
        ['title' => 'Doğru', 'desc' => 'İlk blok çalışır'],
        ['title' => 'Yanlış', 'desc' => 'Alternatif yol'],
        ['title' => 'Sonuç', 'desc' => 'Net çıktı'],
        ['title' => 'Kontrol', 'desc' => 'Koşula göre değişir'],
    ],
    '#0891b2',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Else If Yapısı',
    '<h2>Birden fazla koşul varsa ne olur?</h2><p><strong>else if</strong> yapısı, tek bir karar içinde birden çok koşulu sırayla denemeye yarar. Koşullar yukarıdan aşağıya kontrol edilir ve ilk doğru olan blok çalıştırılır. Bu, not aralığı, yaş grubu, seviye belirleme ve puan sınıflandırma gibi durumlarda çok kullanışlıdır.</p><p>Örneğin 90 ve üzeri “çok iyi”, 70-89 arası “iyi”, 50-69 arası “orta”, diğerleri “geliştirilmeli” şeklinde bir sınıflandırma yapılabilir.</p>',
    'Else If',
    'Çoklu koşul',
    [
        ['title' => 'İlk koşul', 'desc' => 'Önce o denenir'],
        ['title' => 'İkinci koşul', 'desc' => 'Sıra ile kontrol'],
        ['title' => 'Sınıflama', 'desc' => 'Aralık seçimi'],
        ['title' => 'Çıkış', 'desc' => 'İlk doğru yol çalışır'],
    ],
    '#dc2626',
    '#1d4ed8'
);

$slides[] = $makeSplit(
    'Döngü Yapılarının Temeli',
    '<h2>Tekrar eden işleri neden döngüyle yaparız?</h2><p>Döngüler, aynı işlemi tekrar tekrar yazmak yerine bir kez tanımlayıp belirli sayıda ya da koşul sağlanana kadar çalıştırmamıza yarar. Böylece kod daha kısa, daha okunur ve daha yönetilebilir hale gelir.</p><p><strong>for</strong>, <strong>while</strong> ve benzeri döngüler, programcının iş yükünü azaltır. Bir tabloyu yazdırmak, 1’den 100’e kadar saymak ya da bir menüyü kullanıcı çıkana kadar döndürmek döngü ile çok daha kolay olur.</p>',
    'Döngü Yapısı',
    'Tekrar eden işlemler',
    [
        ['title' => 'For', 'desc' => 'Sayıma uygun'],
        ['title' => 'While', 'desc' => 'Koşul temelli'],
        ['title' => 'Tekrar', 'desc' => 'Kod kısalır'],
        ['title' => 'Anlaşılır', 'desc' => 'Sade program'],
    ],
    '#16a34a',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Döngü Bileşenleri',
    '<h2>Bir döngüde hangi taşlar vardır?</h2><p>Bir döngüyü kurarken başlangıç değeri, bitiş değeri, artış miktarı ve devam etme koşulu açıkça belirlenmelidir. Bu dört unsur net değilse döngü ya hiç çalışmaz ya da sonsuz döngüye dönüşebilir.</p><p>Örneğin 1’den 10’a kadar sayan bir döngüde başlangıç 1, bitiş 10, artış 1’dir. Koşul doğru biçimde kurulmazsa program beklenmeyen davranış gösterebilir.</p>',
    'Döngü Taşları',
    'Başlangıçtan bitişe',
    [
        ['title' => 'Başlangıç', 'desc' => 'İlk değer'],
        ['title' => 'Bitiş', 'desc' => 'Son sınır'],
        ['title' => 'Artış', 'desc' => 'Kaçar kaçar'],
        ['title' => 'Koşul', 'desc' => 'Devam kararını verir'],
    ],
    '#7c3aed',
    '#2563eb'
);

$slides[] = $makeSplit(
    'Döngü ile ve Döngüsüz',
    '<h2>Aynı problemi döngüyle ve döngüsüz çözmek ne fark yaratır?</h2><p>Döngüsüz çözümde aynı satırlar tekrar tekrar yazılabilir. Bu hem kodu uzatır hem de hata ihtimalini artırır. Döngü kullanıldığında aynı işlem tek bir yapıda toplanır ve gerektiğinde sadece sınırlar değiştirilir.</p><p>Bir öğretmenin 30 öğrenciye tek tek aynı işlemi yaptığını düşünün. Döngü, bu tekrarın yazılım karşılığını çok daha temiz biçimde sunar.</p>',
    'Karşılaştırma',
    'Tekrarın maliyeti',
    [
        ['title' => 'Döngüsüz', 'desc' => 'Kod uzar'],
        ['title' => 'Döngülü', 'desc' => 'Kod sadeleşir'],
        ['title' => 'Hata', 'desc' => 'Daha az tekrar'],
        ['title' => 'Bakım', 'desc' => 'Daha kolay'],
    ],
    '#dc2626',
    '#7c3aed'
);

$slides[] = $makeSplit(
    'Döngü ve Karar Birlikte',
    '<h2>Bir program hem karar hem döngü içeriyorsa ne olur?</h2><p>Gerçek problemler çoğu zaman yalnızca karar ya da yalnızca döngü ile çözülmez. Bir problemin içinde belirli bir şart sağlanana kadar tekrar eden kısımlar ve bu tekrarların içinde değişen koşullar olabilir. Bu yüzden program tasarımı çoğu zaman iki yapının birlikte kullanılmasını gerektirir.</p><p>Örneğin bir kullanıcı doğru şifreyi girene kadar döngü sürer; her denemede yanlışsa uyarı verir, doğruysa girişe izin verir. Bu tür yapılar akış diyagramı ile çizildiğinde çok daha kolay anlaşılır.</p>',
    'Birleşik Yapı',
    'Döngü + karar',
    [
        ['title' => 'Koşul', 'desc' => 'Karar verir'],
        ['title' => 'Tekrar', 'desc' => 'Döngü sürer'],
        ['title' => 'Çıkış', 'desc' => 'Doğru yol bulunur'],
        ['title' => 'Akış', 'desc' => 'Birlikte çalışır'],
    ],
    '#2563eb',
    '#0f766e'
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
    'question_prompt' => 'if-else yapısı hangi durumda kullanılır?',
    'question' => ['options' => [
        ['text' => 'Koşula göre iki farklı yol gerektiğinde', 'correct' => true],
        ['text' => 'Sadece metin yazmak için', 'correct' => false],
        ['text' => 'Döngü kurmadan çizim yapmak için', 'correct' => false],
        ['text' => 'Veriyi silmek için', 'correct' => false],
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
    'question_prompt' => 'Aşağıdakilerden hangisi döngünün işlevine örnektir?',
    'question' => ['options' => [
        ['text' => 'Aynı işlemi tekrar etmek', 'correct' => true],
        ['text' => 'Sadece bir kez karar vermek', 'correct' => false],
        ['text' => 'Metin türünü değiştirmek', 'correct' => false],
        ['text' => 'Girdi almak yerine beklemek', 'correct' => false],
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
    'question_prompt' => 'Bir döngüde artış miktarı neyi ifade eder?',
    'question' => ['options' => [
        ['text' => 'Her turda değerin ne kadar değişeceğini', 'correct' => true],
        ['text' => 'Programın adını', 'correct' => false],
        ['text' => 'Karar sembolünü', 'correct' => false],
        ['text' => 'Girdi türünü', 'correct' => false],
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
    'question_prompt' => 'Mantıksal operatörler birden fazla koşulu birlikte kontrol etmek için kullanılır.',
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
    'question_prompt' => 'Döngüler kod tekrarını azaltmaz, sadece süsleme yapar.',
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
    'question_prompt' => 'for ve while gibi yapılara genel olarak ne ad verilir?',
    'question' => ['answer' => 'döngü'],
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
    'question_prompt' => 'Yapıları uygun açıklamalarıyla eşleştir.',
    'question' => ['pairs' => [
        ['left' => 'if', 'right' => 'Koşul doğruysa çalışır'],
        ['left' => 'else', 'right' => 'Koşul yanlışsa çalışır'],
        ['left' => 'for', 'right' => 'Sayım temelli döngü'],
        ['left' => 'while', 'right' => 'Koşul devam ettiği sürece'],
    ]],
];

$slides[] = $makeSplit(
    'Ders Özeti',
    '<h2>Bugün ne öğrendik?</h2><p>Karar yapılarını, mantıksal operatörleri, if-else ve else if kullanımını, döngülerin kod tekrarını nasıl azalttığını ve başlangıç-bitiş-artış-koşul bileşenlerini ele aldık. Ayrıca döngü ile karar yapılarının birlikte kullanıldığı problemleri nasıl tasarlayabileceğimizi gördük.</p><p><strong>Mini görev:</strong> Günlük hayatından bir problemi seç ve hem karar hem döngü içeren 4-5 adımlık bir algoritma yaz.</p>',
    'Ders Özeti',
    'Kapanış',
    [
        ['title' => 'Karar', 'desc' => 'Akışı yönlendirir'],
        ['title' => 'Operatör', 'desc' => 'Koşulları birleştirir'],
        ['title' => 'Döngü', 'desc' => 'Tekrarı yönetir'],
        ['title' => 'Tasarım', 'desc' => 'İkisi birlikte çalışır'],
    ],
    '#2563eb',
    '#16a34a'
);

$curriculum = [
    'title' => 'Karar Yapıları, Döngüler ve Birlikte Program Tasarlama',
    'lesson_number' => 3,
    'konu' => 'Karar yapıları, döngüler ve program tasarlama',
    'kazanimlar' => [
        'Karar yapılarını kullanarak programı geliştirir.',
        'Döngü yapılarını kullanarak programı geliştirir.',
        'Döngü ve karar yapıları ile program tasarlar.',
    ],
    'etkinlikler' => [
        'if-else ve else if örnekleri çözme.',
        'for ve while döngülerini karşılaştırma.',
        'Döngü ve karar içeren algoritma tasarlama ve akış diyagramı çizme.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'Karar Yapıları ve Döngüler',
    'category' => 'Bilişim Teknolojileri ve Yazılım',
    'difficulty' => 'Orta',
    'lesson_description' => 'Karar yapıları, döngüler ve birlikte program tasarlama odaklı lise düzeyi ders.',
    'cover_image' => 'kapak-gorseli/karar-ve-dongu.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fbff 0%,#eef6ff 100%);color:#0f172a}",
    'curriculum' => $curriculum,
    'target_scope' => '9-10',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'Karar Yapıları ve Döngüler',
        'code' => 'BT-' . strtoupper(\Illuminate\Support\Str::random(8)),
        'weekly_hours' => 2,
        'lesson_payload' => $lessonPayload,
        'sub_courses' => [],
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/karar-yapilari-ve-donguler.coursepkg');
file_put_contents($out, $pkg);
echo $out . PHP_EOL;
