<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(App\Http\Controllers\CourseController::class);
$rc = new ReflectionClass($controller);
$build = $rc->getMethod('buildCoursePackage');
$build->setAccessible(true);

$svgDataUri = static function (string $title, string $subtitle, array $lines, string $accent1, string $accent2): string {
    $safe = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $y = 184;
    $lineHtml = '';
    foreach ($lines as $line) {
        $lineHtml .= '<text x="88" y="' . $y . '" fill="#e2e8f0" font-size="28" font-family="Inter,Segoe UI,Arial,sans-serif">' . $safe($line) . '</text>';
        $y += 42;
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="720" viewBox="0 0 1280 720">'
        . '<defs>'
        . '<linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="' . $accent1 . '"/>'
        . '<stop offset="100%" stop-color="' . $accent2 . '"/>'
        . '</linearGradient>'
        . '<filter id="blur"><feGaussianBlur stdDeviation="18"/></filter>'
        . '</defs>'
        . '<rect width="1280" height="720" fill="#0f172a"/>'
        . '<circle cx="1030" cy="120" r="180" fill="' . $accent2 . '" opacity=".22" filter="url(#blur)"/>'
        . '<circle cx="210" cy="620" r="220" fill="' . $accent1 . '" opacity=".18" filter="url(#blur)"/>'
        . '<rect x="72" y="70" width="1136" height="580" rx="34" fill="rgba(255,255,255,.05)" stroke="rgba(255,255,255,.12)"/>'
        . '<rect x="88" y="100" width="180" height="42" rx="21" fill="url(#g)"/>'
        . '<text x="178" y="129" fill="#fff" font-size="18" text-anchor="middle" font-family="Inter,Segoe UI,Arial,sans-serif">Lise Düzeyi</text>'
        . '<text x="88" y="220" fill="#fff" font-size="54" font-weight="800" font-family="Inter,Segoe UI,Arial,sans-serif">' . $safe($title) . '</text>'
        . '<text x="88" y="272" fill="#cbd5e1" font-size="26" font-family="Inter,Segoe UI,Arial,sans-serif">' . $safe($subtitle) . '</text>'
        . $lineHtml
        . '<rect x="832" y="158" width="314" height="314" rx="28" fill="rgba(255,255,255,.08)" stroke="rgba(255,255,255,.14)"/>'
        . '<circle cx="990" cy="282" r="76" fill="url(#g)"/>'
        . '<path d="M965 282h50M990 257v50" stroke="#fff" stroke-width="14" stroke-linecap="round"/>'
        . '<text x="989" y="392" fill="#e2e8f0" font-size="24" text-anchor="middle" font-family="Inter,Segoe UI,Arial,sans-serif">Teknoloji - Bilgi - İletişim</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
};

$slides = [];

$slides[] = [
    'title' => 'BT\'nin Temel Kavramları',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Bilgi, teknoloji ve iletişim neden birlikte düşünülür?</h2><p><strong>Bilişim teknolojileri</strong> yalnızca cihazlar topluluğu değildir; bilgiyi toplama, işleme, depolama ve paylaşma biçimidir. <strong>Bilgi</strong>, ham verinin anlamlı hale gelmiş biçimidir. <strong>Teknoloji</strong>, bir problemi çözmek için geliştirilen yöntem, araç ve sistemlerin bütünüdür. <strong>İletişim</strong> ise insanların, cihazların ve sistemlerin birbirine veri aktarabilmesidir.</p><p>Günlük yaşamda telefonla mesaj atarken, bir okul duyurusunu e-Okul üzerinden kontrol ederken veya bir bankacılık uygulamasında işlem yaparken bu üç kavram birlikte çalışır. Bu yüzden BT okuryazarlığı, sadece cihaz kullanmak değil, <strong>doğru bilgiye erişmek</strong> ve <strong>güvenli biçimde paylaşmak</strong> anlamına da gelir.</p><p><strong>Sorgulama sorusu:</strong> Bir bilgiyi “değerli” yapan şey ne olabilir: çok olması mı, doğru olması mı, zamanında ulaşması mı?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('BT\'nin Temel Kavramları', 'Bilgi, teknoloji ve iletişim ortak bir sistem gibi çalışır.', ['Veri -> Bilgi', 'Araç -> Teknoloji', 'Paylaşım -> İletişim'], '#0f766e', '#2563eb'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Bilgi, Veri ve Enformasyon',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Veri tek başına neden yetmez?</h2><p><strong>Veri</strong>, tek başına anlamı sınırlı işaretlerdir. Örneğin “23, 24, 26” bir veri dizisidir. Bu sayılar bir sınıfın haftalık çevrimiçi kullanım süresini anlatıyorsa <strong>bilgi</strong>ye dönüşür. Veriyi anlamlı hale getiren şey bağlamdır.</p><p>Bir öğrenci için sınav notunun yanında “ders çalışma süresi”, “konu tekrar sayısı” ve “deneme performansı” birlikte değerlendirildiğinde daha güçlü bir yorum yapılabilir. Bu nedenle bilişim teknolojileri, veriyi yalnızca saklamaz; onu anlamlı, karşılaştırılabilir ve kullanılabilir hale getirir.</p><p><strong>Örnek veri:</strong> Bir mobil uygulamanın günlük açılma sayısı, internet kullanım saati, adım sayısı veya akıllı saat verileri.</p><p><strong>Derin düşünme sorusu:</strong> Bir okul yönetimi sadece notlara bakarsa hangi önemli ayrıntıları kaçırabilir?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Veri ve Bilgi', 'Veri bağlam kazandığında bilgiye dönüşür.', ['23, 24, 26 -> veri', 'Sınıfın haftalık süresi -> bilgi', 'Karar verme -> teknoloji çıktısı'], '#1d4ed8', '#7c3aed'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Ülkemizde Kullanılan BT Araçları',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Teknolojik araçlar yalnızca tüketim aracı değil, üretim aracıdır.</h2><p>Ülkemizde bilişim teknolojileri çok farklı alanlarda kullanılıyor: bilgisayar laboratuvarları, akıllı tahtalar, tablet destekli eğitim, hastane bilgi sistemleri, e-devlet uygulamaları, trafik takip sistemleri ve yerli teknoloji projeleri bunlardan bazılarıdır.</p><p>Örneğin <strong>Türk uzay yolcusu ve bilim misyonu</strong> gibi projeler, teknoloji üretiminin yalnızca cihaz kullanmak olmadığını; aynı zamanda bilimsel hedefler koymak, mühendislik çözümleri geliştirmek ve yeni bilgi üretmek anlamına geldiğini gösterir. Yerli otomobil, insansız hava araçları ve akıllı şehir çözümleri de bu yaklaşımın örnekleridir.</p><p>Bilişim araçlarının kullanım alanı sabit değildir. Aynı cihaz evde eğlence için, okulda öğrenme için, iş yerinde üretim için kullanılabilir. Bu esneklik, teknolojiyi güçlü kılar.</p><p><strong>Yorum sorusu:</strong> Bir akıllı telefon aynı anda hem eğitim hem sağlık hem de ulaşım alanına nasıl hizmet edebilir?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('BT Araçları', 'Bilgisayar, tablet, akıllı tahta, telefon, sensör ve yazılım.', ['Eğitim', 'Sağlık', 'Ulaşım', 'Güvenlik'], '#0891b2', '#0f766e'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Cihazların Tarihsel Gelişimi',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Bugünkü cihazlar bir anda ortaya çıkmadı.</h2><p>Bilgisayarlar oda büyüklüğündeki makinelerden taşınabilir cihazlara dönüştü. Telefonlar sabit hatlardan kablosuz ve akıllı yapılara evrildi. Tabletler, hesap makineleri ve giyilebilir cihazlar da bu dönüşümün parçalarıdır.</p><p>Gelişim çizgisinde temel eğilimler hep benzer oldu: cihazlar küçüldü, hızlandı, daha az enerji tüketti ve daha çok iş yapabilir hale geldi. Eski mekanik sistemlerden dijital sistemlere geçiş, otomotiv sektöründen sağlık cihazlarına kadar her alanda hissedildi. Yerli otomobil gibi projeler de bu dönüşümün ülkemizdeki yansımalarıdır.</p><p><strong>Önemli fikir:</strong> Bir teknolojiyi takip etmek sadece merak değil, gelecekteki mesleki becerileri hazırlamaktır.</p><p><strong>Beklenti sorusu:</strong> Sence 10 yıl sonra bugün kullandığımız hangi cihazlar tamamen değişmiş olabilir?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Cihazların Evrimi', 'Büyük makinelerden taşınabilir akıllı sistemlere.', ['Mekanik sistemler', 'Masaüstü bilgisayarlar', 'Telefon ve tabletler', 'Yapay zeka destekli cihazlar'], '#7c3aed', '#2563eb'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Keşifler ve Dönüm Noktaları',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Teknoloji tarihini değiştiren şey çoğu zaman tek bir buluş değil, bir zincirdir.</h2><p>Bilişim teknolojilerinin gelişiminde telgraf, telefon, radyo, bilgisayar, internet ve mobil ağlar gibi dönüm noktaları vardır. Bu aşamaların her biri insanın bilgiye ulaşma hızını artırdı.</p><p>Tarihte <strong>Cezeri\'nin şifreli kilit sistemleri</strong>, güvenlik ve mekanik tasarım düşüncesinin erken örnekleri olarak önemlidir. Günümüzde kullanılan kriptoloji yaklaşımı, bilgiyi yetkisiz erişime karşı koruma fikrini çok daha gelişmiş biçimde sürdürüyor.</p><p>Telefonun icadı ile iletişimde mesafe duygusu zayıfladı. Sonra mobil uygulamalar, görüntülü görüşme ve anlık bildirimler geldi. Bu da kullanıcı beklentilerini ve yazılım geliştirme alanlarını kökten değiştirdi.</p><p><strong>Tartışma sorusu:</strong> Bir buluşun değerini belirleyen şey sadece “ilk olması” mı, yoksa insanlar üzerinde yarattığı etki mi?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Dönüm Noktaları', 'Telgraf, telefon, internet, mobil uygulama, yapay zeka.', ['Cezeri', 'Telefon', 'İnternet', 'Mobil çağ'], '#dc2626', '#1d4ed8'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'BT Kullanım Alanları',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>BT her yerde ama her yerde aynı amaçla kullanılmaz.</h2><p><strong>Eğitimde</strong> içerik sunumu, dijital kitaplar, simülasyonlar ve ölçme-değerlendirme araçları kullanılır. <strong>Sağlıkta</strong> randevu sistemleri, tıbbi görüntüleme ve hasta kayıtları öne çıkar. <strong>İletişimde</strong> mesajlaşma, e-posta, görüntülü görüşme ve sosyal ağlar yer alır.</p><p><strong>Güvenlikte</strong> kameralar, sensörler ve veri analitiği kullanılır. <strong>Ulaşımda</strong> navigasyon, trafik yönetimi, akıllı ulaşım sistemleri ve sürüş destek teknolojileri karşımıza çıkar.</p><p>Bu alanlar arasındaki ortak nokta şudur: teknoloji, insanın işini hızlandırır, hatayı azaltır ve karar vermeyi destekler. Ancak bunun için doğru amaçla, doğru veriyle ve doğru etik çerçevede kullanılmalıdır.</p><p><strong>Örnek durum sorusu:</strong> Bir hastanedeki dijital sistem ile bir okulun çevrim içi sınav sistemi arasında hangi benzerlikler vardır?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Kullanım Alanları', 'Eğitim, sağlık, iletişim, güvenlik ve ulaşım.', ['Eğitim', 'Sağlık', 'İletişim', 'Ulaşım'], '#0f766e', '#2563eb'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Olumlu Yönler',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Teknolojinin güçlü tarafı hayatı görünmez biçimde kolaylaştırmasıdır.</h2><p>BT\'nin olumlu yönleri arasında hızlı erişim, zaman tasarrufu, uzaktan iletişim, üretkenlik artışı, bireyselleştirilmiş öğrenme ve veri temelli karar verme vardır. Ayrıca engelli bireylerin hayata katılımını artıran erişilebilirlik araçları da önemli bir kazanımdır.</p><p>Bir öğretmen, dijital içerik kullanarak farklı öğrenme hızlarına uygun ders hazırlayabilir. Bir doktor, sistemler sayesinde hastanın geçmiş kayıtlarına hızla ulaşabilir. Bir öğrenci de kaynaklara kısa sürede erişerek araştırma yapabilir.</p><p><strong>Sonuç:</strong> Doğru kullanılan teknoloji, yalnızca rahatlık sağlamaz; fırsat eşitliğini de güçlendirebilir.</p><p><strong>Değerlendirme sorusu:</strong> Senin günlük hayatında teknoloji hangi işi gerçekten hızlandırıyor?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Olumlu Etkiler', 'Hız, erişim, verimlilik, öğrenme ve kapsayıcılık.', ['Hızlı erişim', 'Zaman tasarrufu', 'Uzaktan iletişim', 'Erişilebilirlik'], '#16a34a', '#0f766e'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Olumsuz Yönler',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Teknolojinin bedeli, kontrol edilmediğinde görünür hale gelir.</h2><p>Olumsuz yönler arasında dikkat dağınıklığı, aşırı ekran süresi, yanlış bilgiye maruz kalma, veri güvenliği sorunları, bağımlılık, sosyal izolasyon ve gizlilik ihlalleri sayılabilir. Teknoloji nötrdür; onu nasıl kullandığımız sonuçları belirler.</p><p>Örneğin sosyal medya algoritmaları, kullanıcının ilgisini çekmek için içerik akışını daraltabilir. Bu durum yanlış bilgiye daha hızlı maruz kalınmasına veya zamanın verimsiz kullanılmasına yol açabilir. Ayrıca bilinçsiz paylaşım, kişisel verilerin kötüye kullanım riskini artırır.</p><p><strong>Önemli uyarı:</strong> Her teknolojik gelişme otomatik olarak yararlı değildir; etik, güvenlik ve denetim gerekir.</p><p><strong>Yorum sorusu:</strong> Sence bir teknolojinin zararlı olup olmadığı tasarıma mı, kullanıcıya mı, yoksa kurallara mı daha çok bağlıdır?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Olumsuz Etkiler', 'Aşırı kullanım, yanlış bilgi, bağımlılık ve güvenlik riski.', ['Dikkat dağınıklığı', 'Gizlilik riski', 'Bağımlılık', 'Yanlış bilgi'], '#dc2626', '#7c2d12'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Denge ve Sorumluluk',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Teknolojiyi akıllıca kullanmak, onu sadece tüketmemekten geçer.</h2><p>BT\'nin olumlu ve olumsuz yönleri birlikte düşünülmelidir. Amaç, teknolojiyi tamamen reddetmek değil; onu bilinçli, güvenli ve üretken biçimde kullanmaktır. Bir öğrenci için bunun karşılığı; doğru kaynak seçmek, ekran süresini yönetmek ve dijital ayak izinin farkında olmaktır.</p><p>Gelecekte bazı teknolojiler daha da yaygınlaşacak: yapay zeka destekli asistanlar, giyilebilir sağlık sistemleri, otonom ulaşım ve kişiselleştirilmiş öğrenme platformları. Bu nedenle teknoloji gelişimini takip etmek, yalnızca bugünü değil, meslek hayatını da doğrudan etkiler.</p><p><strong>Kapanış sorusu:</strong> Teknolojiyi kullanırken “ne kadar kullandığın” mı, “nasıl kullandığın” mı daha önemlidir?</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Sorumlu Kullanım', 'Bilinçli, güvenli ve üretken teknoloji kullanımı.', ['Denge', 'Güvenlik', 'Üretkenlik', 'Etik'], '#2563eb', '#0f766e'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Kısa Değerlendirme',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<h2>Bugün neyi öğrendik?</h2><p>Bilgi, teknoloji ve iletişim kavramlarının birbirini tamamladığını; bilişim araçlarının eğitimden sağlığa, ulaşımdan güvenliğe kadar çok geniş bir alanda kullanıldığını; cihazların zaman içinde nasıl geliştiğini ve teknolojinin hem olumlu hem olumsuz sonuçlar doğurabildiğini tartıştık.</p><p><strong>Hatırlatma:</strong> Bir teknoloji aracını değerlendirmek için yalnızca işlevine değil, etkisine, etik kullanımına ve güvenliğine de bakmak gerekir.</p><p><strong>Mini görev:</strong> Günlük hayatında kullandığın bir BT aracını seç ve onun 1 olumlu, 1 olumsuz yönünü yaz.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Özet', 'Kavramlar, kullanım alanları ve etkiler.', ['Bilgi', 'Teknoloji', 'İletişim', 'Sorumluluk'], '#1d4ed8', '#16a34a'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'none',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question' => ['options' => []],
];

$slides[] = [
    'title' => 'Çıkış Soruları',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<p>Bu bölümde bilgiler artık doğrudan soru kartlarıyla ölçülür. Her soru, konuyu ezberden değil, yorum ve ilişkilendirme üzerinden kontrol edecek biçimde hazırlanmıştır.</p><p><strong>İpucu:</strong> Soruyu yanıtlamadan önce kavramlar arasındaki ilişkiyi düşün: veri, bilgi, teknoloji, kullanım alanı ve etki.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Değerlendirme', 'Bilgi, yorum ve örnek üzerinden ölçme.', ['Çoktan seçmeli', 'Açık uçlu', 'Günlük yaşam bağlantısı'], '#7c3aed', '#0f766e'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Aşağıdakilerden hangisi bilişim teknolojilerinin olumlu yönlerinden biridir?',
    'question' => [
        'options' => [
            ['text' => 'Bilgiye hızlı erişim sağlaması', 'correct' => true],
            ['text' => 'Kişisel verileri korumasız bırakması', 'correct' => false],
            ['text' => 'Dikkat dağınıklığını artırması', 'correct' => false],
            ['text' => 'Yanlış bilgi yaymayı kolaylaştırması', 'correct' => false],
        ],
    ],
];

$slides[] = [
    'title' => 'Çoktan Seçmeli 2',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<p>Bu soru, kavramlar arasındaki temel ilişkiyi ölçer. Tek bir doğru cevap vardır.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Soru 2', 'Kavramlar arası ilişki.', ['Veri', 'Bilgi', 'Teknoloji'], '#1d4ed8', '#7c3aed'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Bir bilginin değerli olmasını sağlayan en önemli unsur aşağıdakilerden hangisidir?',
    'question' => [
        'options' => [
            ['text' => 'Doğru ve güvenilir olması', 'correct' => true],
            ['text' => 'Çok uzun olması', 'correct' => false],
            ['text' => 'Her yerde yazılması', 'correct' => false],
            ['text' => 'Renkli olması', 'correct' => false],
        ],
    ],
];

$slides[] = [
    'title' => 'Çoktan Seçmeli 3',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<p>Bu soru, teknolojinin kullanım alanlarını ölçer.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Soru 3', 'Kullanım alanı.', ['Eğitim', 'Sağlık', 'Ulaşım'], '#16a34a', '#2563eb'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'multiple_choice',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Aşağıdakilerden hangisi bilişim teknolojilerinin kullanım alanlarından biridir?',
    'question' => [
        'options' => [
            ['text' => 'Sağlık', 'correct' => true],
            ['text' => 'Tatlı', 'correct' => false],
            ['text' => 'Kumaş', 'correct' => false],
            ['text' => 'Spor ayakkabısı', 'correct' => false],
        ],
    ],
];

$slides[] = [
    'title' => 'Kısa Cevap',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<p>Bu soru tek kelimelik cevap bekler. Amaç, kavram eşleştirmesini hızlı ve net biçimde ölçmektir.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Kısa Cevap', 'Tek kelime.', ['Bilgi', 'Veri', 'Teknoloji'], '#0f766e', '#2563eb'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'short_answer',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Veri ile bilgi arasındaki farkı tek kelimeyle belirt: Bilgi mi, veri mi?',
    'question' => [
        'answer' => 'bilgi',
    ],
];

$slides[] = [
    'title' => 'Eşleştirme',
    'layout' => 'split',
    'layout_meta' => [
        'split_ratio' => '70-30',
        'left' => [
            'type' => 'text',
            'text' => '<p>Aşağıdaki kavramları doğru tanımlarla eşleştir.</p>',
        ],
        'right' => [
            'type' => 'image',
            'image_url' => $svgDataUri('Eşleştirme', 'Kavram ve tanım.', ['Veri', 'İletişim', 'Teknoloji'], '#7c3aed', '#0f766e'),
        ],
    ],
    'xp' => 10,
    'kind' => 'topic',
    'interaction_type' => 'matching',
    'points' => 5,
    'time_limit' => 10,
    'double_points' => false,
    'question_prompt' => 'Kavramları doğru tanımlarla eşleştir.',
    'question' => [
        'pairs' => [
            ['left' => 'Bilgi', 'right' => 'Anlam kazanmış veri'],
            ['left' => 'Teknoloji', 'right' => 'Sorun çözmek için geliştirilen araç ve yöntemler'],
            ['left' => 'İletişim', 'right' => 'Bilginin aktarılması'],
        ],
    ],
];

$curriculum = [
    'title' => 'BT\'nin Temel Kavramları, Olumlu ve Olumsuz Yönleri',
    'lesson_number' => 1,
    'konu' => 'BT\'nin temel kavramları, olumlu olumsuz yönleri',
    'kazanimlar' => [
        'Bilgi, teknoloji ve iletişim kavramlarının açıklamasını yapar.',
        'Bilişim teknolojisi araçlarını listeler ve örnekler verir.',
        'Bilişim teknolojisi cihazlarının gelişimini açıklar.',
        'Bilişim teknolojilerinin gelişiminde rol oynayan keşif, buluş ve dönüm noktalarını tartışır.',
        'Bilişim teknolojilerinin kullanım alanlarını açıklar.',
        'Bilişim teknolojilerinin olumlu ve olumsuz yönlerini tartışır.',
    ],
    'etkinlikler' => [
        'Günlük yaşamdan BT örneklerini sınıflandırma çalışması.',
        'Tarihsel gelişim çizelgesi oluşturma etkinliği.',
        'Olumlu ve olumsuz yönleri karşılaştırma tartışması.',
        'Çıkış soruları ile kısa değerlendirme.',
    ],
    'progress' => 100,
];

$lessonPayload = [
    'slides' => $slides,
    'theme_template' => 'default',
    'lesson_title' => 'BT\'nin Temel Kavramları, Olumlu ve Olumsuz Yönleri',
    'category' => 'Bilişim Teknolojileri ve Yazılım',
    'difficulty' => 'Orta',
    'lesson_description' => 'Bilişim teknolojilerinin temel kavramlarını, kullanım alanlarını, tarihsel gelişimini ve olumlu-olumsuz yönlerini lise düzeyinde çok yönlü biçimde ele alan ders.',
    'cover_image' => 'kapak-gorseli/bt-temel-kavramlar-olumlu-olumsuz.jpg',
    'global_theme_css' => ".slide-theme, .slide-theme *{box-sizing:border-box}\n.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}\n.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.025em;line-height:1.12;font-weight:900;margin:0 0 .75rem}\n.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}\n.slide-theme :where(strong,b){color:#0f172a;font-weight:800}\n.slide-theme :where(a){color:#0f766e;text-decoration:none;border-bottom:1px solid rgba(15,118,110,.2)}\n.slide-theme :where(code,pre,kbd,samp){background:#dbeafe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}\n.slide-theme pre{padding:14px 16px;overflow:auto}\n.slide-theme :where(blockquote){border-left:6px solid #0f766e;background:#ecfeff;padding:14px 16px;border-radius:0 16px 16px 0}\n.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}\n.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}\n.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}\n.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}\n.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}\n.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#475569;text-align:center}\n.slide-theme :where(section,article,aside,main,header,footer,nav,div){border-radius:16px}\n.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(14,116,144,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.9))}\n.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}",
    'curriculum' => $curriculum,
    'target_scope' => '9-10',
];

$payload = [
    'exported_at' => now()->toIso8601String(),
    'course' => [
        'name' => 'BT\'nin Temel Kavramları, Olumlu ve Olumsuz Yönleri',
        'code' => 'DERS' . strtoupper(Str::random(10)),
        'weekly_hours' => 2,
        'lesson_payload' => $lessonPayload,
    ],
];

$pkg = $build->invoke($controller, $payload, '', 'image/png');
$out = storage_path('app/bt-temel-kavramlar-olumlu-olumsuz.coursepkg');
file_put_contents($out, $pkg);

echo $out . PHP_EOL;
