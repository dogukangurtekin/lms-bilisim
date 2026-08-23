<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$build = $rc->getMethod('buildCoursePackage');
$build->setAccessible(true);

$makeSplit = function (string $title, string $text, string $imageLabel, string $imageNote, array $cards, string $c1, string $c2): array {
    $items = array_map(fn ($card) => ['title' => $card[0], 'desc' => $card[1]], $cards);
    $svg = function () use ($imageLabel, $imageNote, $items, $c1, $c2) {
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
            . '<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="100%" stop-color="#172554"/></linearGradient><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient><filter id="blur"><feGaussianBlur stdDeviation="22"/></filter></defs>'
            . '<rect width="1280" height="720" fill="url(#bg)"/>'
            . '<circle cx="1080" cy="118" r="180" fill="' . $c2 . '" opacity=".22" filter="url(#blur)"/>'
            . '<circle cx="220" cy="606" r="220" fill="' . $c1 . '" opacity=".18" filter="url(#blur)"/>'
            . '<rect x="68" y="62" width="1144" height="596" rx="34" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.14)"/>'
            . '<text x="92" y="126" fill="#fff" font-size="46" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($imageLabel) . '</text>'
            . '<text x="92" y="166" fill="#cbd5e1" font-size="22" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($imageNote) . '</text>'
            . '<rect x="842" y="108" width="300" height="164" rx="28" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.14)"/>'
            . '<text x="992" y="174" text-anchor="middle" fill="#fff" font-size="26" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">MIT</text>'
            . '<text x="992" y="210" text-anchor="middle" fill="#e2e8f0" font-size="18" font-family="Inter,Segoe UI,Arial,sans-serif">APP INVENTOR</text>'
            . '<text x="992" y="242" text-anchor="middle" fill="#94a3b8" font-size="14" font-family="Inter,Segoe UI,Arial,sans-serif">Tasarım + Kodlama</text>';
        foreach ($items as $i => $item) {
            $col = $i % 2;
            $row = intdiv($i, 2);
            $bx = 92 + ($col * 532);
            $by = 214 + ($row * 126);
            $html .= '<g transform="translate(' . $bx . ',' . $by . ')">'
                . '<rect x="0" y="0" width="472" height="92" rx="22" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.16)"/>'
                . '<circle cx="40" cy="46" r="22" fill="url(#g)"/>'
                . '<text x="40" y="52" text-anchor="middle" fill="#fff" font-size="18" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">' . ($i + 1) . '</text>'
                . '<text x="76" y="36" fill="#fff" font-size="18" font-weight="900" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($item['title']) . '</text>'
                . '<text x="76" y="61" fill="#dbeafe" font-size="14" font-family="Inter,Segoe UI,Arial,sans-serif">' . $e($item['desc']) . '</text>'
                . '</g>';
        }
        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($html . '</svg>');
    };

    return [
        'title' => $title,
        'layout' => 'split',
        'layout_meta' => [
            'split_ratio' => '70-30',
            'left' => ['type' => 'text', 'text' => $text],
            'right' => ['type' => 'image', 'image_url' => $svg()],
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
    'App Inventor ile Mobil Kodlama',
    '<h2>Mobil uygulama geliştirmeyi düşünme biçimi olarak görmek</h2><p>MIT App Inventor, mobil uygulama geliştirmeyi görsel bloklarla öğreten bir platformdur. Burada amaç sadece buton eklemek değil, kullanıcı deneyimini ve uygulama mantığını birlikte kurmaktır. Öğrenciler, telefon ekranında çalışacak bir fikri önce tasarlar, sonra davranışını bloklarla tanımlar.</p><p>Bu ders boyunca arayüz tasarımı, blok tabanlı kodlama ve bileşenlerin görevleri birlikte ele alınır. Böylece mobil yazılım geliştirme süreci soyut olmaktan çıkar, somut ve izlenebilir hale gelir.</p>',
    'App Inventor Nedir?',
    'Görsel bloklarla mobil uygulama',
    [['Bulut Tabanlı', 'Tarayıcıdan erişilir'], ['Sürükle-Bırak', 'Bileşenler yerleştirilir'], ['Hızlı Prototip', 'Fikir çabuk görünür'], ['Eğitsel', 'Öğrenmeye uygundur']],
    '#0f766e',
    '#2563eb'
);
$slides[] = $makeSplit(
    'Platforma Güvenli Giriş',
    '<h2>Hesap güvenliğiyle başlamak neden önemli?</h2><p>App Inventor’da güvenli giriş yapmak, projenin kaybolmaması ve çalışmanın farklı cihazlardan sürdürülebilmesi için önemlidir. Öğrenciler kendi hesaplarıyla oturum açtıklarında projelerini bulutta saklayabilir ve çalışma düzenini koruyabilir. Bu, dijital güvenlik alışkanlığının da bir parçasıdır.</p><p>Platformu tanımadan önce hesap güvenliğini konuşmak, yazılım üretiminde sorumluluk bilinci kazandırır. Güçlü şifre kullanımı, kişisel hesap yönetimi ve oturum kapatma alışkanlığı burada öne çıkar.</p>',
    'Güvenli Giriş',
    'Hesap ve proje güvenliği',
    [['Hesap', 'Kişisel proje alanı'], ['Şifre', 'Güçlü olmalı'], ['Bulut', 'Projeyi saklar'], ['Oturum', 'Çalışmayı sürdürür']],
    '#1d4ed8',
    '#7c3aed'
);
$slides[] = $makeSplit(
    'Designer Ekranı',
    '<h2>Tasarım alanı ne işe yarar?</h2><p>Designer ekranı, uygulamanın kullanıcıya görünen yüzünün kurulduğu bölümdür. Butonlar, etiketler, resimler, metin kutuları ve diğer bileşenler burada yerleştirilir. Bu ekran uygulamanın iskeletini oluşturur; renk, konum ve boyut gibi ayrıntılar da burada belirlenir.</p><p>İyi bir mobil uygulama, önce düzenli bir arayüzle başlar. Designer ekranı, bir sınıf panosunu düzenlemek gibi düşünülebilir: önce parçaların yeri belirlenir, sonra içerik yerleştirilir.</p>',
    'Designer',
    'Arayüz kurma alanı',
    [['Bileşenler', 'Buton, etiket, resim'], ['Yerleşim', 'Ekranda konum'], ['Özellik', 'Renk, yazı, boyut'], ['Görünüm', 'Kullanıcıya görünen']],
    '#0891b2',
    '#0f766e'
);
$slides[] = $makeSplit(
    'Blocks Ekranı',
    '<h2>Davranış ve mantık burada kurulur</h2><p>Blocks ekranı, uygulamanın nasıl tepki vereceğini belirleyen bölümdür. Öğrenci burada olay, koşul ve işlem bloklarını birleştirir. Bir butona basıldığında mesaj gösterme, girilen metni işleme ya da bir bileşeni görünür yapma gibi davranışlar bu alanda kurulur.</p><p>Blocks ekranı, kodu yazı satırlarından çıkarıp mantıksal yapı taşlarına dönüştürür. Bu nedenle başlangıç seviyesi öğrenciler için anlaşılır ve kontrollü bir kodlama deneyimi sunar.</p>',
    'Blocks',
    'Kodlama mantığı',
    [['Olay', 'Tıklandığında'], ['Koşul', 'Eğer ise'], ['İşlem', 'Yap ve göster'], ['Bağlama', 'Blokları birleştir']],
    '#16a34a',
    '#2563eb'
);
$slides[] = $makeSplit(
    'Palette Bölümü',
    '<h2>Palette bir bileşen kataloğu gibi çalışır</h2><p>Palette bölümünde uygulamaya eklenebilecek tüm bileşenler listelenir. Butonlar, metin alanları, medya araçları, düzen öğeleri ve sensörler burada bulunur. Öğrenci ihtiyaç duyduğu bileşeni buradan seçip tasarım alanına taşır.</p><p>Doğru bileşeni tanımak, doğru aracı kullanmak anlamına gelir. Bu yüzden Palette, yalnızca bir liste değil; uygulama geliştirme kararlarının başlangıç noktasıdır.</p>',
    'Palette',
    'Bileşen kataloğu',
    [['Interface', 'Buton ve metinler'], ['Layout', 'Düzen araçları'], ['Media', 'Ses, görüntü'], ['Sensors', 'Telefon sensörleri']],
    '#dc2626',
    '#1d4ed8'
);
$slides[] = $makeSplit(
    'Viewer Alanı',
    '<h2>Uygulama ekranı nasıl görünür?</h2><p>Viewer, eklenen bileşenlerin uygulama ekranında nasıl görüneceğini gösteren önizleme alanıdır. Öğrenci bu bölümde yerleşimi test eder, boşlukları gözden geçirir ve uygulamanın görsel dengesini kontrol eder. Böylece arayüz daha okunaklı ve düzenli hale gelir.</p><p>Viewer aynı zamanda deneme alanıdır. Öğrenci bileşenleri yerleştirirken ekranın gerçek kullanımına yaklaşır ve tasarımını adım adım iyileştirir.</p>',
    'Viewer',
    'Canlı önizleme',
    [['Düzen', 'Ekran yerleşimi'], ['Deneme', 'Anında görür'], ['Denge', 'Boşluk kontrolü'], ['Kullanıcı', 'Ekranı hisseder']],
    '#7c3aed',
    '#0f766e'
);
$slides[] = $makeSplit(
    'Properties Paneli',
    '<h2>Bir bileşenin karakteri burada belirlenir</h2><p>Properties paneli, seçilen bileşenin özelliklerini düzenlemek için kullanılır. Yazı tipi, renk, genişlik, yükseklik, hizalama ve görünürlük gibi ayrıntılar burada ayarlanır. Aynı bileşen, farklı özelliklerle bambaşka bir kullanım deneyimi oluşturabilir.</p><p>İyi bir arayüz yalnızca bileşen sayısıyla değil, özellik ayarlarının doğru yapılmasıyla ortaya çıkar. Properties paneli bu ince ayarın merkezidir.</p>',
    'Properties',
    'Özellik ayarları',
    [['Renk', 'Görsel kimlik'], ['Boyut', 'Yerleşim kontrolü'], ['Yazı Tipi', 'Okunabilirlik'], ['Görünürlük', 'İhtiyaca göre']],
    '#0f766e',
    '#7c3aed'
);
$slides[] = $makeSplit(
    'Ekranlar Arası İş Akışı',
    '<h2>Designer ve Blocks birlikte çalışır</h2><p>App Inventor’da tasarım ve davranış birbirinden ayrı düşünülmez. Önce arayüz kurulur, sonra bloklarla etkileşim tanımlanır. Eğer yalnızca tasarım yapılırsa uygulama güzel görünür ama çalışmaz; yalnızca blok yazılırsa da kullanıcıyı karşılayan bir ekran oluşmaz.</p><p>Bu nedenle mobil projelerde ekranlar arası geçişi ve görev dağılımını doğru anlamak gerekir. Uygulama geliştirme, görsel ve mantıksal kararların birlikte verilmesidir.</p>',
    'Akış',
    'Tasarım ve kod birlikte',
    [['Önce', 'Arayüz kurulur'], ['Sonra', 'Bloklar eklenir'], ['Test', 'Çalışma kontrol edilir'], ['İyileştir', 'Hata varsa düzeltilir']],
    '#2563eb',
    '#0891b2'
);
$slides[] = $makeSplit(
    'Mini Uygulama Örneği',
    '<h2>“Adını Yaz ve Selamla” uygulaması</h2><p>Bir metin kutusu, bir buton ve bir etiket kullanarak basit bir karşılama uygulaması tasarlanabilir. Kullanıcı adını yazar, butona basar ve uygulama kişisel bir selamlama mesajı gösterir. Bu örnek, Designer ve Blocks ekranlarının nasıl birlikte çalıştığını açıkça gösterir.</p><p>Bu tip mini uygulamalar, öğrencinin “ne yapacağım?” sorusuna somut bir cevap verir ve mobil kodlama sürecini hızlandırır.</p>',
    'Mini Uygulama',
    'Arayüz + davranış',
    [['Girdi', 'Adın girilmesi'], ['İşlem', 'Butona basma'], ['Çıktı', 'Selamlama mesajı'], ['Mantık', 'Blok akışı']],
    '#1d4ed8',
    '#16a34a'
);
$slides[] = $makeSplit(
    'Öğrenme Özeti',
    '<h2>Bugün ne öğrendik?</h2><p>App Inventor ile mobil kodlama, görsel arayüz kurmayı ve bloklarla davranış tanımlamayı birlikte ele alır. Designer ekranı görünümü kurarken Blocks ekranı uygulamanın aklını oluşturur. Palette bileşen seçmeyi, Viewer önizlemeyi, Properties ise ayrıntılı düzenlemeyi sağlar.</p><p>Bu yapı, öğrencinin uygulamayı sadece tüketen değil, aynı zamanda üreten tarafta konumlanmasını hedefler.</p>',
    'Özet',
    'Temel kavramların birleşimi',
    [['Designer', 'Arayüz'], ['Blocks', 'Mantık'], ['Palette', 'Bileşen'], ['Properties', 'Ayar']],
    '#7c3aed',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Birinci Proje Adımı',
    '<h2>İlk uygulamayı planlamak</h2><p>Öğrenciler çoğu zaman doğrudan kod yazmak ister; ancak mobil projelerde en verimli başlangıç, küçük bir hedef seçmektir. Mesela bir karşılama ekranı, bir not hesaplayıcı ya da bir renk değiştirme uygulaması ile başlamak hem öğreticidir hem de başarı hissi verir.</p><p>App Inventor’da ilk proje, kullanıcıyı şaşırtmayacak kadar sade, ama mantığı öğretecek kadar anlamlı olmalıdır.</p>',
    'İlk Adım',
    'Basit hedef, net sonuç',
    [['Hedef', 'Küçük ve anlaşılır'], ['Arayüz', 'Az bileşen'], ['Mantık', 'Tek davranış'], ['Test', 'Hızlı kontrol']],
    '#2563eb',
    '#7c3aed'
);

$slides[] = $makeSplit(
    'Hataları Ayıklama',
    '<h2>Uygulama çalışmıyorsa neye bakılır?</h2><p>Bir blok doğru yerleştirilmemiş olabilir, bir bileşenin adı yanlış seçilmiş olabilir ya da özelliklerden biri eksik bırakılmış olabilir. Bu yüzden test etmek ve adım adım kontrol etmek, App Inventor çalışmalarının doğal parçasıdır.</p><p>Hata ayıklama, öğrenciye yalnızca problemi bulmayı değil, problemi sakin biçimde çözmeyi de öğretir. Kodlama sürecinde hata görmek başarısızlık değil, öğrenme fırsatıdır.</p>',
    'Hata Ayıklama',
    'Kontrol ve düzeltme',
    [['Blok', 'Doğru bağlandı mı?'], ['İsim', 'Bileşen adı doğru mu?'], ['Özellik', 'Değerler uygun mu?'], ['Test', 'Tekrar dene']],
    '#dc2626',
    '#0f766e'
);

$slides[] = $makeSplit(
    'Gelecek Bağlantısı',
    '<h2>Bu ders neden önemli?</h2><p>App Inventor ile çalışmak, gelecekte daha gelişmiş yazılım ortamlarına geçiş için sağlam bir temel oluşturur. Öğrenci arayüz, olay, koşul, giriş ve çıktı kavramlarını kavrar; bu bilgiler daha sonra farklı programlama dillerinde yeniden kullanılabilir.</p><p>Mobil uygulama mantığını erken öğrenen öğrenciler, teknolojiye yalnızca kullanıcı olarak değil, üretici olarak da yaklaşmaya başlar.</p>',
    'Gelecek',
    'Temelden ileriye',
    [['Temel', 'Arayüz mantığı'], ['Geçiş', 'Blok düşünme'], ['Gelişim', 'Projeyi büyütme'], ['Yol', 'Yazılıma devam']],
    '#0f766e',
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
    'question_prompt' => 'App Inventor’a giriş yaparken en doğru yaklaşım hangisidir?',
    'question' => ['options' => [
        ['text' => 'Kendi hesabıyla güvenli oturum açmak', 'correct' => true],
        ['text' => 'Projeleri kaydetmeden kapatmak', 'correct' => false],
        ['text' => 'Şifreyi herkesle paylaşmak', 'correct' => false],
        ['text' => 'Her oturumda farklı hesap denemek', 'correct' => false],
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
    'question_prompt' => 'Designer ekranının temel görevi nedir?',
    'question' => ['options' => [
        ['text' => 'Arayüz bileşenlerini yerleştirmek', 'correct' => true],
        ['text' => 'Cihazın işletim sistemini güncellemek', 'correct' => false],
        ['text' => 'Sadece kod hatalarını görmek', 'correct' => false],
        ['text' => 'Uygulamayı mağazaya göndermek', 'correct' => false],
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
    'question_prompt' => 'Blocks ekranında hangi işlem yapılır?',
    'question' => ['options' => [
        ['text' => 'Uygulamanın davranışı kurulur', 'correct' => true],
        ['text' => 'Ekranın camı değiştirilir', 'correct' => false],
        ['text' => 'Sadece resim boyutu ayarlanır', 'correct' => false],
        ['text' => 'Telefonun bataryası yenilenir', 'correct' => false],
    ]],
];
$slides[] = [
    'title' => 'Soru 4',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Palette bölümünde ne bulunur?',
    'question' => ['options' => [
        ['text' => 'Eklenebilir bileşenler', 'correct' => true],
        ['text' => 'Hazır öğrenci notları', 'correct' => false],
        ['text' => 'Yalnızca arka plan renkleri', 'correct' => false],
        ['text' => 'Sadece internet bağlantısı', 'correct' => false],
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
    'question_prompt' => 'Viewer alanı, eklenen bileşenlerin uygulama ekranındaki görünümünü önizler.',
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
    'question_prompt' => 'Properties paneli yalnızca renk değiştirmek için kullanılır.',
    'question' => ['options' => [['text' => 'Yanlış', 'correct' => true]]],
];
$slides[] = [
    'title' => 'Doğru Yanlış 3',
    'layout' => 'text',
    'layout_meta' => [],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'true_false',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Designer ve Blocks ekranları mobil uygulama geliştirme sürecinde birlikte kullanılır.',
    'question' => ['options' => [['text' => 'Doğru', 'correct' => true]]],
];

$payload = [
    'course' => [
        'name' => 'APP Inventor ile Mobil Kodlama',
        'code' => 'APP-INVENTOR-MOBIL-KODLAMA',
        'weekly_hours' => 2,
    ],
    'curriculum' => [
        'title' => 'APP Inventor ile Mobil Kodlama',
        'lesson_number' => 1,
        'konu' => 'App Inventor kullanımı, arayüzü ve özellikleri',
        'kazanimlar' => [
            'Öğrenci, App Inventor hesabına güvenli biçimde ulaşmanın önemini kavrar.',
            'Arayüz kurma ve bloklarla davranış belirleme aşamalarını ayırt eder.',
            'Palette, Viewer ve Properties alanlarının görevlerini açıklayabilir.',
        ],
        'etkinlikler' => [
            'Basit bir mobil uygulama arayüzü tasarlama',
            'Bir butona basıldığında çalışan blok oluşturma',
            'Bileşen özelliklerini değiştirerek sonuçları gözlemleme',
        ],
        'progress' => 0,
    ],
    'lesson_description' => 'App Inventor ile mobil uygulama geliştirme, arayüz tasarlama ve blok tabanlı kodlama.',
    'difficulty' => 'Orta',
    'category' => 'Kodlama',
    'slides' => $slides,
];

$file = storage_path('app/app-inventor-ile-mobil-kodlama.coursepkg');
file_put_contents($file, $build->invoke($controller, $payload));
echo $file, PHP_EOL;
