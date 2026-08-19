<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$build = $rc->getMethod('buildCoursePackage');
$build->setAccessible(true);

$svg = static function (string $title, string $subtitle, array $bullets, string $c1, string $c2): string {
    $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $y = 182;
    $lines = '';
    foreach ($bullets as $b) {
        $lines .= '<text x="86" y="' . $y . '" fill="#e2e8f0" font-size="28" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($b) . '</text>';
        $y += 42;
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient><filter id="b"><feGaussianBlur stdDeviation="18"/></filter></defs>'
        . '<rect width="1280" height="720" fill="#0f172a"/>'
        . '<circle cx="1030" cy="120" r="180" fill="' . $c2 . '" opacity=".22" filter="url(#b)"/>'
        . '<circle cx="210" cy="620" r="220" fill="' . $c1 . '" opacity=".18" filter="url(#b)"/>'
        . '<rect x="72" y="70" width="1136" height="580" rx="34" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.12)"/>'
        . '<rect x="88" y="100" width="190" height="42" rx="21" fill="url(#g)"/>'
        . '<text x="183" y="129" fill="#fff" font-size="18" text-anchor="middle" font-family="Inter,Segoe UI,Arial,sans-serif">Lise Duzeyi</text>'
        . '<text x="88" y="220" fill="#fff" font-size="54" font-weight="800" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($title) . '</text>'
        . '<text x="88" y="272" fill="#cbd5e1" font-size="26" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($subtitle) . '</text>'
        . $lines
        . '<rect x="832" y="158" width="314" height="314" rx="28" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.14)"/>'
        . '<circle cx="990" cy="282" r="76" fill="url(#g)"/>'
        . '<path d="M965 282h50M990 257v50" stroke="#fff" stroke-width="14" stroke-linecap="round"/>'
        . '<text x="989" y="392" fill="#e2e8f0" font-size="24" text-anchor="middle" font-family="Inter,Segoe UI,Arial,sans-serif">Algoritma - Problem - Cozum</text>'
        . '</svg>';
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
};

$slides = [];
$addText = function (string $title, string $content, string $subtitle, string $imgTitle, array $bullets, string $c1, string $c2) use (&$slides, $svg) {
    $slides[] = [
        'title' => $title,
        'layout' => 'split',
        'layout_meta' => [
            'split_ratio' => '70-30',
            'left' => ['type' => 'text', 'text' => $content],
            'right' => ['type' => 'image', 'image_url' => $svg($imgTitle, $subtitle, $bullets, $c1, $c2)],
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

$addText('Algoritmanın Temeli', '<h2>Algoritma nasıl başlar?</h2><p>Algoritma, bir problemi çözmek için izlenen düzenli ve sonlu adımlar dizisidir. İlk aşama, problemi gerçekten anlamaktır. Problem ne istiyor, nerede ortaya çıkıyor, kimi etkiliyor ve hangi çıktıya ulaşmak gerekiyor soruları cevaplanmadan iyi bir çözüm kurulamaz.</p><p>Günlük yaşamda sabah hazırlanma, servise yetişme, ödev tamamlama ya da bir uygulamaya giriş yapma gibi işler bile aslında adım adım ilerler. Bu dersin amacı, bu doğal sıralamayı bilinçli hale getirmektir.</p><p><strong>Kritik fikir:</strong> Çözümü hızlandıran şey çoğu zaman daha çok yazmak değil, problemi doğru okumaktır.</p>', 'Problemi anlamak', 'Problem kavramı', ['Sorunu tanı', 'Girdiyi belirle', 'Çıktıyı düşün'], '#0f766e', '#2563eb');
$addText('Problemi Kavramak', '<h2>Bir problemi anlamak ne demektir?</h2><p>Problem çözmede ilk iş, verilen bilgiyi ve istenen sonucu ayırt etmektir. Problemde geçen önemli kelimeler altı çizilecek kadar dikkatle okunmalı, gerekiyorsa alt basamaklara ayrılmalıdır. Karmaşık bir problem, küçük ve çözülmesi kolay parçalara bölündüğünde daha yönetilebilir olur.</p><p>Örneğin “okul gezisi planlama” problemi; tarih belirleme, ulaşım ayarlama, izin toplama ve bütçe hesaplama gibi parçalara ayrılabilir.</p>', 'Soruyu doğru okuma', 'Problem analizi', ['Verilen', 'İstenen', 'Alt basamak'], '#1d4ed8', '#7c3aed');
$addText('Benzer Problem Ornekleri', '<h2>Benzer çözümler neden önemlidir?</h2><p>İnsanlar çoğu zaman sıfırdan çözüm üretmez; daha önce çözülmüş benzer durumlara bakar. Benzer problemlerden yararlanmak, çözüm yaklaşımını daha hızlı kurmayı sağlar. Bir problemle ilk kez karşılaşsak bile, ona benzeyen bir düzeni fark etmek çözüm sürecini kolaylaştırır.</p><p>Bu yüzden algoritma geliştirme, sadece kod yazmak değil; düşünme biçimi kurmaktır.</p>', 'Örneklerden öğrenme', 'Benzer çözüm', ['Benzerlik', 'Yöntem', 'Tekrar kullanılabilirlik'], '#16a34a', '#0f766e');
$addText('Girdi, Cıktı, İşlem', '<h2>Bir çözümün üç temel parçası</h2><p><strong>Girdi</strong> problemi çözmek için sisteme giren bilgilerdir. <strong>İşlem</strong> bu bilgilerin nasıl dönüştürüldüğünü anlatır. <strong>Çıktı</strong> ise sürecin sonunda elde edilen sonuçtur. Algoritma tasarlarken bu üçü net değilse çözüm de net olmaz.</p><p>Örneğin bir toplama probleminde sayılar girdi, toplama işlemi işlem basamağı, bulunan sonuç ise çıktıdır.</p>', 'Girdi-işlem-çıktı', 'Temel kavramlar', ['Girdi', 'İşlem', 'Çıktı'], '#0891b2', '#2563eb');
$addText('Çözüm İçin Gereksinimler', '<h2>İyi bir çözüm için ne gerekir?</h2><p>Bir problemin çözümü için bazen bilgi eksik olabilir, bazen de şartlar net olmayabilir. Bu durumda problem açıklığa kavuşturulmalı, sınırlar belirlenmeli ve mümkünse farklı çözüm yolları karşılaştırılmalıdır. En kısa yol her zaman en doğru yol değildir; bazen en anlaşılır yol daha değerlidir.</p><p>Problem çözme kuramları, yöntemleri ve teknikleri de bu nedenle vardır: düşünmeyi sistemli hale getirmek için.</p>', 'Çözüm koşulları', 'Gereksinimler', ['Netlik', 'Yöntem', 'Karşılaştırma'], '#7c3aed', '#dc2626');
$addText('Algoritma Nedir?', '<h2>Algoritma kavramı</h2><p>Algoritma, bir problemi çözmek için sıralı, açık, anlaşılır ve sonlu adımların oluşturduğu yapıdır. Adımların sırası değişirse sonucun değişmesi mümkündür. Bu yüzden algoritmada düzen, doğruluk kadar önemlidir.</p><p>Günlük yaşamda bir çayın hazırlanması, sınıfta yoklama alınması ya da bir oyuna giriş yapılması bile algoritmik düşünceyle açıklanabilir.</p>', 'Sıralı adımlar', 'Algoritma tanımı', ['Sıra', 'Sonluluk', 'Açıklık'], '#dc2626', '#1d4ed8');
$addText('El Harezmi', '<h2>Algoritma kelimesinin kökeni</h2><p>Algoritma kelimesi, 9. yüzyılda yaşamış matematikçi Ebu Cafer Muhammed bin Musa el-Harezmi’nin adının Latince okunuşundan gelir. El-Harezmi’nin çalışmaları, sadece matematik için değil, bilgisayar biliminin düşünme biçimi için de temel kabul edilir.</p><p>Onun “Hisab el-cebir ve el-mukabala” adlı eseri, problem çözme ve sistematik düşünme açısından tarihsel bir dönüm noktasıdır.</p>', 'Tarihsel köken', 'El-Harezmi', ['El-Harezmi', 'Cebir', 'Dönüm noktası'], '#2563eb', '#0f766e');

$slides[] = ['title' => 'Soru 1', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'multiple_choice', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Problem çözmede ilk yapılması gereken aşağıdakilerden hangisidir?', 'question' => ['options' => [['text' => 'Problemi anlamak', 'correct' => true], ['text' => 'Hemen kod yazmak', 'correct' => false], ['text' => 'Sonucu rastgele seçmek', 'correct' => false], ['text' => 'Çözümü ezberlemek', 'correct' => false]]]];
$slides[] = ['title' => 'Soru 2', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'multiple_choice', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Karmaşık bir problemi küçük parçalara ayırmanın amacı nedir?', 'question' => ['options' => [['text' => 'Çözümü kolaylaştırmak', 'correct' => true], ['text' => 'Problemi büyütmek', 'correct' => false], ['text' => 'İşlemi durdurmak', 'correct' => false], ['text' => 'Sonucu gizlemek', 'correct' => false]]]];
$slides[] = ['title' => 'Soru 3', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'multiple_choice', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Bir algoritmada dışarıdan alınan veri hangi kavramla adlandırılır?', 'question' => ['options' => [['text' => 'Girdi', 'correct' => true], ['text' => 'Çıktı', 'correct' => false], ['text' => 'İşlem', 'correct' => false], ['text' => 'Sonuç', 'correct' => false]]]];
$slides[] = ['title' => 'Soru 4', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'multiple_choice', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Algoritmada adımların sırası neden önemlidir?', 'question' => ['options' => [['text' => 'Sonucu değiştirebilir', 'correct' => true], ['text' => 'Her zaman önemsizdir', 'correct' => false], ['text' => 'Sadece görsel içindir', 'correct' => false], ['text' => 'Problemi siler', 'correct' => false]]]];
$slides[] = ['title' => 'Soru 5', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'multiple_choice', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Algoritma kelimesinin kökeni hangi bilim insanına dayanır?', 'question' => ['options' => [['text' => 'El-Harezmi', 'correct' => true], ['text' => 'Newton', 'correct' => false], ['text' => 'Arşimet', 'correct' => false], ['text' => 'Tesla', 'correct' => false]]]];
$slides[] = ['title' => 'Doğru Yanlış 1', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'true_false', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Algoritma, bir problemi çözmek için izlenen sıralı adımlar dizisidir.', 'question' => ['options' => [['text' => 'Doğru', 'correct' => true]]]];
$slides[] = ['title' => 'Doğru Yanlış 2', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'true_false', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Bir algoritmada adım sırası sonucu değiştirmez.', 'question' => ['options' => [['text' => 'Doğru', 'correct' => false]]]];
$slides[] = ['title' => 'Tek Cevap', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'short_answer', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Algoritma kelimesi hangi isimden gelir?', 'question' => ['answer' => 'el-harezmi']];
$slides[] = ['title' => 'Eşleştirme', 'layout' => 'text', 'layout_meta' => [], 'xp' => 10, 'kind' => 'topic', 'interaction_type' => 'matching', 'points' => 5, 'time_limit' => 10, 'double_points' => false, 'question_prompt' => 'Kavramları doğru tanımlarla eşleştir.', 'question' => ['pairs' => [['left' => 'Girdi', 'right' => 'Sisteme giren veri'], ['left' => 'İşlem', 'right' => 'Verinin dönüştürülmesi'], ['left' => 'Çıktı', 'right' => 'Elde edilen sonuç']]]];
$addText('Ders Özeti', '<h2>Bugün ne öğrendik?</h2><p>Problemi anlamanın, girdi-çıktıyı ayırmanın, çözümü adımlara bölmenin ve algoritmayı düzenli bir düşünme biçimi olarak kurmanın önemini gördük. El-Harezmi’nin tarihsel katkısını da algoritma kavramıyla ilişkilendirdik.</p><p><strong>Mini görev:</strong> Günlük hayatından bir işi seç ve 3 adımlık küçük bir algoritma yaz.</p>', 'Özet', 'Problemden çözüme', ['Anla', 'Ayır', 'Sırala'], '#2563eb', '#16a34a');

$curriculum = [
    'title' => 'Algoritmanın Temeli',
    'lesson_number' => 2,
    'konu' => 'Algoritmanın temeli',
    'kazanimlar' => [
        'Çözümü istenen problemi kavrar.',
        'Çözüm için gereksinimlerini belirler.',
        'Problemin girdi, çıktı ve işlem aşamalarını belirler.',
        'Algoritma kavramını açıklar.',
    ],
    'etkinlikler' => [
        'Problem analizi ve alt basamaklara ayırma çalışması.',
        'Girdi-çıktı örnekleri üzerinde sınıf içi tartışma.',
        'Algoritma ve El-Harezmi üzerine kısa bilgi paylaşımı.',
        'Kısa değerlendirme soruları ve eşleştirme etkinliği.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'Algoritmanın Temeli',
    'category' => 'Bilişim Teknolojileri ve Yazılım',
    'difficulty' => 'Orta',
    'lesson_description' => 'Problem çözme, girdi-çıktı ayrımı ve algoritma kavramını lise düzeyinde dolu içerikle işleyen ders.',
    'cover_image' => 'kapak-gorseli/algoritmanin-temeli.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}",
    'curriculum' => $curriculum,
    'target_scope' => '9-10',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'Algoritmanın Temeli',
        'code' => 'DERS' . strtoupper(Str::random(10)),
        'weekly_hours' => 2,
        'lesson_payload' => $lessonPayload,
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/algoritmanin-temeli.coursepkg');
file_put_contents($out, $pkg);
echo $out . PHP_EOL;
