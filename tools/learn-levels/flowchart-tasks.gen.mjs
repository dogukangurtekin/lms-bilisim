// Flowchart Programlama — 40 görev üretici ve doğrulayıcı.
// Çalıştır: node tools/learn-levels/flowchart-tasks.gen.mjs  →  public/runner-assets/flowchart-runner/tasks.js
// Her görevin REFERANS ÇÖZÜMÜ (DSL ile yazılmış akış şeması) motorla çalıştırılıp tüm testleri geçmeli; boş şema geçmemeli.
// Beklenen çıktılar bağımsız JS fonksiyonlarıyla (ref) üretilir; çözümler yalnızca burada kalır (istemciye gönderilmez).
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const ENGINE = path.resolve(here, '../../public/runner-assets/flowchart-runner/engine.js');
const OUT = path.resolve(here, '../../public/runner-assets/flowchart-runner/tasks.js');
const mod = { exports: {} };
new Function('module', 'exports', 'self', fs.readFileSync(ENGINE, 'utf8'))(mod, mod.exports, undefined);
const E = mod.exports;

const XP = { easy: 10, medium: 15, hard: 20, expert: 25 };
const tasks = [];
const S = String;
// t(cat, başlık, hikâye, ipuçları, {ref, ins:[görünür girdiler], hid:[gizli girdiler], need, starter, sol})
function t(cat, title, story, hints, o) {
  tasks.push({ cat, title, story, hints, ...o });
}
const lines = (arr) => arr.map(S);

// ===================================== KOLAY (12)
t('easy', 'Merhaba Dünya', 'İlk algoritman: ekrana bir yazı yazdır. **Başla** bloğu seçiliyken soldaki **Çıktı** düğmesine bas, sonra içine `"Merhaba Dünya"` yaz (tırnaklarla), ardından **Bitir** ekle.',
  ['Palette Çıktı\'ya tıkla; blok Başla\'nın altına kendiliğinden bağlanır.', 'Çıktı bloğunun içine tırnaklı metin yaz: "Merhaba Dünya"', 'Son olarak Bitir bloğunu ekle.'],
  { ref: () => ['Merhaba Dünya'], ins: [[]], hid: [], sol: ['başla', 'çıktı "Merhaba Dünya"', 'bitir'] });
t('easy', 'Adını Söyle', 'Kullanıcıdan adını **Girdi** ile al ve `Merhaba Ayşe` biçiminde karşıla.',
  ['Girdi bloğuna değişken adı yaz: ad', 'Çıktı bloğuna şunu yaz: "Merhaba", ad', 'Çıktıdaki virgül, parçaları boşlukla birleştirir.'],
  { ref: ([a]) => ['Merhaba ' + a], ins: [['Ayşe']], hid: [['Ali'], ['Zeynep']], need: { input: true }, sol: ['başla', 'girdi ad', 'çıktı "Merhaba", ad', 'bitir'] });
t('easy', 'İki Sayının Toplamı', 'İki sayı oku ve toplamını yazdır.',
  ['Girdi: a, b', 'Çıktı: a + b', 'Girdi bloğu iki değişkeni sırayla okur.'],
  { ref: ([a, b]) => [S(a + b)], ins: [[3, 4]], hid: [[10, 25], [-2, 7], [0.5, 0.25]], need: { input: true }, sol: ['başla', 'girdi a, b', 'çıktı a + b', 'bitir'] });
t('easy', 'Dikdörtgenin Alanı', 'Kısa ve uzun kenarı oku, alanı yazdır.',
  ['Girdi: en, boy', 'Alan = en * boy', 'Sonucu çıktı bloğuna doğrudan yazabilirsin: en * boy'],
  { ref: ([a, b]) => [S(a * b)], ins: [[3, 5]], hid: [[7, 8], [1, 1], [12, 0.5]], need: { input: true }, sol: ['başla', 'girdi en, boy', 'çıktı en * boy', 'bitir'] });
t('easy', 'Karenin Çevresi ve Alanı', 'Bir kenar uzunluğu oku. Önce çevreyi, sonra alanı iki ayrı satırda yazdır.',
  ['Çevre: 4 * a', 'Alan: a * a', 'İki Çıktı bloğu kullan: biri çevre, biri alan.'],
  { ref: ([a]) => [S(4 * a), S(a * a)], ins: [[5]], hid: [[2], [10], [3.5]], need: { input: true }, sol: ['başla', 'girdi a', 'çıktı 4 * a', 'çıktı a * a', 'bitir'] });
t('easy', 'Üç Notun Ortalaması', 'Üç sınav notu oku ve ortalamasını yazdır.',
  ['Girdi: n1, n2, n3', 'Ortalama: (n1 + n2 + n3) / 3', 'Parantezi unutma!'],
  { ref: ([a, b, c]) => [S((a + b + c) / 3)], ins: [[60, 70, 80]], hid: [[100, 90, 80], [50, 50, 51], [0, 0, 0]], need: { input: true }, sol: ['başla', 'girdi n1, n2, n3', 'işlem ort = (n1 + n2 + n3) / 3', 'çıktı ort', 'bitir'] });
t('easy', 'Yaşını Hesapla', 'Doğum yılını oku, 2025 yılına göre yaşını yazdır.',
  ['Girdi: yıl', 'Yaş = 2025 - yıl', 'İşlem bloğu ile yas = 2025 - yıl yazabilirsin.'],
  { ref: ([y]) => [S(2025 - y)], ins: [[2012]], hid: [[2000], [2015], [1990]], need: { input: true }, sol: ['başla', 'girdi yıl', 'işlem yas = 2025 - yıl', 'çıktı yas', 'bitir'] });
t('easy', 'Büyük Sayıyı Bul', 'İki sayıdan **büyüğünü** yazdır. İlk **Karar** bloğunu kullanacaksın!',
  ['Karar bloğuna koşul yaz: a > b', 'Evet çıkışı → a\'yı yazdır; Hayır çıkışı → b\'yi yazdır.', 'İki yol da Bitir bloğunda birleşebilir (iki ayrı Bitir de olabilir).'],
  { ref: ([a, b]) => [S(Math.max(a, b))], ins: [[3, 9]], hid: [[10, 2], [5, 5], [-4, -9]], need: { decision: true, input: true },
    sol: ['başla', 'girdi a, b', 'karar a > b -> A | B', 'A: çıktı a', 'bitir', 'B: çıktı b', 'bitir'] });
t('easy', 'Çift mi Tek mi?', 'Bir sayı oku. Çiftse `çift`, tekse `tek` yazdır.',
  ['Bir sayı çiftse 2\'ye bölümünden kalan 0\'dır.', 'Koşul: n % 2 == 0 (iki eşittir!)', 'Evet → "çift", Hayır → "tek".'],
  { ref: ([n]) => [n % 2 === 0 ? 'çift' : 'tek'], ins: [[4]], hid: [[7], [0], [15], [100]], need: { decision: true, input: true },
    sol: ['başla', 'girdi n', 'karar n % 2 == 0 -> E | T', 'E: çıktı "çift"', 'bitir', 'T: çıktı "tek"', 'bitir'] });
t('easy', 'Pozitif, Negatif, Sıfır', 'Bir sayı oku: `pozitif`, `negatif` veya `sıfır` yazdır. (İki karar gerekir.)',
  ['Önce n > 0 diye sor.', 'Hayır çıkışından ikinci bir Karar bloğuna git: n < 0', 'Üç sonuç, üç ayrı Çıktı bloğu.'],
  { ref: ([n]) => [n > 0 ? 'pozitif' : n < 0 ? 'negatif' : 'sıfır'], ins: [[5]], hid: [[-3], [0], [100], [-0.5]], need: { decision: true, input: true },
    sol: ['başla', 'girdi n', 'karar n > 0 -> P | K', 'P: çıktı "pozitif"', 'bitir', 'K: karar n < 0 -> N | Z', 'N: çıktı "negatif"', 'bitir', 'Z: çıktı "sıfır"', 'bitir'] });
t('easy', 'Geçti mi Kaldı mı?', 'Bir not oku. 50 veya üstüyse `geçti`, değilse `kaldı` yazdır.',
  ['Koşul: not >= 50', 'Değişken adı olarak "not" kullanma (anahtar sözcük); "puan" de.', 'Evet → "geçti", Hayır → "kaldı".'],
  { ref: ([p]) => [p >= 50 ? 'geçti' : 'kaldı'], ins: [[75]], hid: [[50], [49], [0], [100]], need: { decision: true, input: true },
    sol: ['başla', 'girdi puan', 'karar puan >= 50 -> G | K', 'G: çıktı "geçti"', 'bitir', 'K: çıktı "kaldı"', 'bitir'] });
t('easy', 'Santigrattan Fahrenheit\'a', 'Santigrat derece oku. Fahrenheit = C * 9 / 5 + 32 formülüyle çevirip yazdır.',
  ['Girdi: c', 'İşlem: f = c * 9 / 5 + 32', 'Sonucu çıktı ile yazdır.'],
  { ref: ([c]) => [S((c * 9) / 5 + 32)], ins: [[100]], hid: [[0], [37], [-40], [25]], need: { input: true }, sol: ['başla', 'girdi c', 'işlem f = c * 9 / 5 + 32', 'çıktı f', 'bitir'] });

// ===================================== ORTA (12)
t('medium', '1\'den 5\'e Say', 'Ekrana 1, 2, 3, 4, 5 sayılarını alt alta yazdır. **Döngü** kuracaksın: bir bağlantıyı geriye, daha önceki bir bloğa bağla.',
  ['İşlem: i = 1', 'Karar: i <= 5 — Evet: yazdır ve i\'yi artır, sonra Karar\'a geri dön; Hayır: Bitir.', 'Geri dönen bağlantıyı İşlem bloğunun çıkış halkasından Karar bloğuna sürükle.'],
  { ref: () => [1, 2, 3, 4, 5].map(S), ins: [[]], hid: [], need: { decision: true, loop: true },
    sol: ['başla', 'işlem i = 1', 'L: karar i <= 5 -> sonraki | Son', 'çıktı i', 'işlem i = i + 1', 'git L', 'Son: bitir'] });
t('medium', 'Geri Sayım', 'Bir sayı oku (n). n\'den 1\'e kadar geriye say, sonra `Başla!` yaz.',
  ['İşlem: i = n', 'Karar: i >= 1', 'Döngüden çıkınca "Başla!" yazdır.'],
  { ref: ([n]) => [...Array.from({ length: n }, (_, k) => S(n - k)), 'Başla!'], ins: [[3]], hid: [[5], [1], [10]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem i = n', 'L: karar i >= 1 -> sonraki | Son', 'çıktı i', 'işlem i = i - 1', 'git L', 'Son: çıktı "Başla!"', 'bitir'] });
t('medium', '1\'den n\'e Toplam', 'n oku, 1+2+…+n toplamını yazdır.',
  ['İki değişken: toplam = 0 ve i = 1', 'Döngüde toplam = toplam + i, i = i + 1', 'Döngü bitince toplamı yazdır.'],
  { ref: ([n]) => [S((n * (n + 1)) / 2)], ins: [[5]], hid: [[10], [1], [100], [0]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem toplam = 0; i = 1', 'L: karar i <= n -> sonraki | Son', 'işlem toplam = toplam + i', 'işlem i = i + 1', 'git L', 'Son: çıktı toplam', 'bitir'] });
t('medium', 'Faktöriyel', 'n oku, n! (1·2·…·n) değerini yazdır. 0! = 1 kabul edilir.',
  ['Çarpım değişkeni 1\'den başlar: f = 1', 'Döngüde f = f * i', 'n = 0 için döngü hiç çalışmaz ve f = 1 kalır.'],
  { ref: ([n]) => [S([...Array(n)].reduce((p, _, i) => p * (i + 1), 1))], ins: [[5]], hid: [[0], [1], [6], [10]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem f = 1; i = 1', 'L: karar i <= n -> sonraki | Son', 'işlem f = f * i', 'işlem i = i + 1', 'git L', 'Son: çıktı f', 'bitir'] });
t('medium', 'Çarpım Tablosu', 'n oku, `n x 1 = ...` den `n x 10 = ...` a kadar çarpım tablosunu satır satır yazdır. Örnek satır: `3 x 4 = 12`',
  ['Çıktı bloğuna şunu yazabilirsin: n, "x", i, "=", n * i', 'i 1\'den 10\'a kadar gider.', 'Her turda yazdır ve i\'yi artır.'],
  { ref: ([n]) => Array.from({ length: 10 }, (_, k) => `${n} x ${k + 1} = ${n * (k + 1)}`), ins: [[3]], hid: [[7], [10], [1]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem i = 1', 'L: karar i <= 10 -> sonraki | Son', 'çıktı n, "x", i, "=", n * i', 'işlem i = i + 1', 'git L', 'Son: bitir'] });
t('medium', 'Çift Sayıları Yaz', 'n oku, 2\'den n\'e kadar olan **çift** sayıları alt alta yazdır.',
  ['i = 2 ile başla.', 'Her turda i\'yi 2 artır (i = i + 2) — karar bloğu gerekmeden çiftleri bulursun!', 'Koşul: i <= n'],
  { ref: ([n]) => Array.from({ length: Math.floor(n / 2) }, (_, k) => S(2 * (k + 1))), ins: [[10]], hid: [[7], [2], [1], [20]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem i = 2', 'L: karar i <= n -> sonraki | Son', 'çıktı i', 'işlem i = i + 2', 'git L', 'Son: bitir'] });
t('medium', 'Üs Al', 'Taban `a` ve pozitif tam sayı üs `b` oku, a üzeri b değerini **döngüyle** (art arda çarparak) hesapla.',
  ['sonuç = 1 ile başla.', 'b kez sonuç = sonuç * a yap.', 'Sayaç: i = 1, koşul: i <= b'],
  { ref: ([a, b]) => [S(a ** b)], ins: [[2, 5]], hid: [[3, 3], [5, 1], [10, 4], [2, 10]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi a, b', 'işlem sonuc = 1; i = 1', 'L: karar i <= b -> sonraki | Son', 'işlem sonuc = sonuc * a', 'işlem i = i + 1', 'git L', 'Son: çıktı sonuc', 'bitir'] });
t('medium', 'n Sayının Ortalaması', 'Önce kaç sayı gireceğini (n) oku, sonra n tane sayı oku ve ortalamasını yazdır.',
  ['Girdi bloğunu döngünün **içine** koy: her turda bir sayı okunur.', 'toplam = toplam + x', 'Ortalama: toplam / n'],
  { ref: ([n, ...xs]) => [S(xs.slice(0, n).reduce((p, x) => p + x, 0) / n)], ins: [[3, 10, 20, 30]], hid: [[2, 5, 6], [4, 1, 2, 3, 4], [1, 7]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem toplam = 0; i = 1', 'L: karar i <= n -> sonraki | Son', 'girdi x', 'işlem toplam = toplam + x', 'işlem i = i + 1', 'git L', 'Son: çıktı toplam / n', 'bitir'] });
t('medium', 'En Büyük Sayı', 'n oku, sonra n tane sayı oku ve içlerinden **en büyüğünü** yazdır.',
  ['İlk sayıyı oku ve en_buyuk yap.', 'Sonraki sayıları oku; en_buyuk\'ten büyükse güncelle.', 'Döngünün içinde bir Karar bloğu olacak.'],
  { ref: ([n, ...xs]) => [S(Math.max(...xs.slice(0, n)))], ins: [[4, 3, 9, 2, 7]], hid: [[3, -5, -2, -9], [1, 4], [5, 1, 1, 1, 1, 2]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'girdi en_buyuk', 'işlem i = 2', 'L: karar i <= n -> sonraki | Son', 'girdi x', 'karar x > en_buyuk -> G | D', 'G: işlem en_buyuk = x', 'D: işlem i = i + 1', 'git L', 'Son: çıktı en_buyuk', 'bitir'] });
t('medium', 'Pozitifleri Say', 'n oku, sonra n tane sayı oku. Kaç tanesinin **pozitif** (0\'dan büyük) olduğunu yazdır.',
  ['sayac = 0 ile başla.', 'Her sayı için: x > 0 ise sayac = sayac + 1', 'Sonunda sayacı yazdır.'],
  { ref: ([n, ...xs]) => [S(xs.slice(0, n).filter((x) => x > 0).length)], ins: [[4, 5, -2, 3, 0]], hid: [[3, -1, -2, -3], [5, 1, 2, 3, 4, 5], [1, 0]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem sayac = 0; i = 1', 'L: karar i <= n -> sonraki | Son', 'girdi x', 'karar x > 0 -> A | B', 'A: işlem sayac = sayac + 1', 'B: işlem i = i + 1', 'git L', 'Son: çıktı sayac', 'bitir'] });
t('medium', '0 Girilene Kadar Topla', 'Sayılar okumaya devam et ve **0 girilince** durup o ana kadar girilen sayıların toplamını yazdır.',
  ['Döngü: sayı oku → 0 mı diye sor.', 'Sayı 0 ise döngüyü bitir; değilse toplama ekle ve tekrar oku.', 'Girdi bloğu döngünün içinde olmalı.'],
  { ref: (xs) => { let s = 0; for (const x of xs) { if (x === 0) break; s += x; } return [S(s)]; }, ins: [[3, 5, 2, 0]], hid: [[0], [10, 0], [1, 2, 3, 4, 0], [-5, 5, 7, 0]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'işlem toplam = 0', 'L: girdi x', 'karar x == 0 -> Son | sonraki', 'işlem toplam = toplam + x', 'git L', 'Son: çıktı toplam', 'bitir'] });
t('medium', 'Kaç Basamaklı?', 'Pozitif bir tam sayı oku, kaç basamaklı olduğunu yazdır. (İpucu: 10\'a böle böle sayıyı küçült.)',
  ['sayi = taban(sayi / 10) ile son basamağı atabilirsin.', 'Her bölmede sayac\'ı artır.', 'sayi 0 olunca dur: koşul sayi > 0'],
  { ref: ([n]) => [S(String(n).length)], ins: [[1234]], hid: [[7], [10], [99999], [100]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem sayac = 0', 'L: karar n > 0 -> sonraki | Son', 'işlem n = taban(n / 10)', 'işlem sayac = sayac + 1', 'git L', 'Son: çıktı sayac', 'bitir'] });

// ===================================== ZOR (10)
const isPrime = (n) => { if (n < 2) return false; for (let i = 2; i * i <= n; i++) if (n % i === 0) return false; return true; };
t('hard', 'Asal mı?', 'Bir sayı (≥ 2) oku. Asalsa `asal`, değilse `asal değil` yazdır.',
  ['2\'den n-1\'e kadar her sayıya böl; hiçbiri tam bölmezse asaldır.', 'Tam bölünme: n % i == 0 — bulursan hemen "asal değil" yaz ve bitir.', 'Döngü bitip hiç bulamazsan "asal" yaz.'],
  { ref: ([n]) => [isPrime(n) ? 'asal' : 'asal değil'], ins: [[7]], hid: [[2], [9], [15], [29], [49], [97]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem i = 2', 'L: karar i < n -> sonraki | Asal', 'karar n % i == 0 -> Deg | sonraki', 'işlem i = i + 1', 'git L', 'Asal: çıktı "asal"', 'bitir', 'Deg: çıktı "asal değil"', 'bitir'] });
const gcd = (a, b) => { while (b) [a, b] = [b, a % b]; return a; };
t('hard', 'OBEB (Ortak Bölenlerin En Büyüğü)', 'İki pozitif sayı oku, OBEB\'ini bul. (Öklid: b sıfır olana kadar a, b = b, a mod b)',
  ['Döngü: b != 0 olduğu sürece sürdür.', 'Geçici değişken kullan: t = a % b; a = b; b = t', 'Bitince a OBEB\'tir.'],
  { ref: ([a, b]) => [S(gcd(a, b))], ins: [[12, 18]], hid: [[100, 75], [7, 13], [48, 36], [9, 3]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi a, b', 'L: karar b != 0 -> sonraki | Son', 'işlem t = a % b', 'işlem a = b', 'işlem b = t', 'git L', 'Son: çıktı a', 'bitir'] });
const fib = (n) => { let a = 1, b = 1; for (let i = 3; i <= n; i++) [a, b] = [b, a + b]; return n <= 2 ? 1 : b; };
t('hard', 'Fibonacci', 'n oku, Fibonacci dizisinin n. terimini yazdır (1, 1, 2, 3, 5, 8, …; 1. ve 2. terim 1).',
  ['İki değişken tut: a = 1, b = 1', 'n > 2 ise (n - 2) kez: s = a + b; a = b; b = s', 'Sonuç b\'dir (n = 1 ve 2 için de 1).'],
  { ref: ([n]) => [S(fib(n))], ins: [[6]], hid: [[1], [2], [10], [3], [15]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem a = 1; b = 1; i = 3', 'L: karar i <= n -> sonraki | Son', 'işlem s = a + b', 'işlem a = b', 'işlem b = s', 'işlem i = i + 1', 'git L', 'Son: çıktı b', 'bitir'] });
const digitSum = (n) => String(n).split('').reduce((p, c) => p + Number(c), 0);
t('hard', 'Basamakları Topla', 'Pozitif tam sayı oku, basamaklarının toplamını yazdır. (Örnek: 1234 → 10)',
  ['Son basamak: n % 10', 'Son basamağı atmak: n = taban(n / 10)', 'n 0 olana kadar tekrarla.'],
  { ref: ([n]) => [S(digitSum(n))], ins: [[1234]], hid: [[5], [99], [1000], [987654]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem toplam = 0', 'L: karar n > 0 -> sonraki | Son', 'işlem toplam = toplam + n % 10', 'işlem n = taban(n / 10)', 'git L', 'Son: çıktı toplam', 'bitir'] });
const reverseNum = (n) => Number(String(n).split('').reverse().join(''));
t('hard', 'Sayıyı Ters Çevir', 'Pozitif tam sayı oku, rakamlarını ters çevirip yazdır. (Örnek: 1234 → 4321)',
  ['ters = 0 ile başla.', 'Her turda: ters = ters * 10 + n % 10', 'Sonra n = taban(n / 10)'],
  { ref: ([n]) => [S(reverseNum(n))], ins: [[1234]], hid: [[7], [120], [9876], [100]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem ters = 0', 'L: karar n > 0 -> sonraki | Son', 'işlem ters = ters * 10 + n % 10', 'işlem n = taban(n / 10)', 'git L', 'Son: çıktı ters', 'bitir'] });
t('hard', 'FizzBuzz', '1\'den n\'e kadar sayıları yazdır. 3\'e bölünenler yerine `Fizz`, 5\'e bölünenler yerine `Buzz`, ikisine birden bölünenler yerine `FizzBuzz` yaz.',
  ['Önce hem 3 hem 5\'e bölünme kontrolü: i % 15 == 0', 'Sonra i % 3 == 0, sonra i % 5 == 0.', 'Hiçbiri değilse sayıyı yazdır. Her yolda i\'yi artırmayı unutma.'],
  { ref: ([n]) => Array.from({ length: n }, (_, k) => { const i = k + 1; return i % 15 === 0 ? 'FizzBuzz' : i % 3 === 0 ? 'Fizz' : i % 5 === 0 ? 'Buzz' : S(i); }), ins: [[15]], hid: [[5], [3], [20], [1]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem i = 1', 'L: karar i <= n -> sonraki | Son', 'karar i % 15 == 0 -> FB | K3', 'FB: çıktı "FizzBuzz"', 'git Art', 'K3: karar i % 3 == 0 -> F | K5', 'F: çıktı "Fizz"', 'git Art', 'K5: karar i % 5 == 0 -> B | Sayi', 'B: çıktı "Buzz"', 'git Art', 'Sayi: çıktı i', 'Art: işlem i = i + 1', 'git L', 'Son: bitir'] });
t('hard', 'Artık Yıl mı?', 'Bir yıl oku. 4\'e bölünür **ama** 100\'e bölünmez ya da 400\'e bölünürse `artık`, değilse `artık değil` yazdır.',
  ['Koşul tek satırda: (y % 4 == 0 ve y % 100 != 0) veya y % 400 == 0', 'Ya da iç içe üç karar kullan.', 'Örnekler: 2000 artık, 1900 artık değil, 2024 artık.'],
  { ref: ([y]) => [(y % 4 === 0 && y % 100 !== 0) || y % 400 === 0 ? 'artık' : 'artık değil'], ins: [[2024]], hid: [[2000], [1900], [2023], [2100], [1600], [2012]], need: { decision: true, input: true },
    sol: ['başla', 'girdi y', 'karar (y % 4 == 0 ve y % 100 != 0) veya y % 400 == 0 -> A | D', 'A: çıktı "artık"', 'bitir', 'D: çıktı "artık değil"', 'bitir'] });
t('hard', 'Üçgenin Türü', 'Geçerli bir üçgenin üç kenarını oku. Üçü eşitse `eşkenar`, ikisi eşitse `ikizkenar`, hiçbiri eşit değilse `çeşitkenar` yazdır.',
  ['Önce a == b ve b == c mi? (eşkenar)', 'Değilse: a == b veya b == c veya a == c mi? (ikizkenar)', 'Değilse çeşitkenar.'],
  { ref: ([a, b, c]) => [a === b && b === c ? 'eşkenar' : (a === b || b === c || a === c ? 'ikizkenar' : 'çeşitkenar')], ins: [[3, 3, 3]], hid: [[5, 5, 3], [3, 4, 5], [4, 6, 4], [7, 7, 7], [2, 3, 3]], need: { decision: true, input: true },
    sol: ['başla', 'girdi a, b, c', 'karar a == b ve b == c -> E | K2', 'E: çıktı "eşkenar"', 'bitir', 'K2: karar a == b veya b == c veya a == c -> I | C', 'I: çıktı "ikizkenar"', 'bitir', 'C: çıktı "çeşitkenar"', 'bitir'] });
const isPerfect = (n) => { let s = 0; for (let i = 1; i < n; i++) if (n % i === 0) s += i; return s === n && n > 1; };
t('hard', 'Mükemmel Sayı', 'Bir sayı (≥ 2) oku. Kendisi hariç bölenlerinin toplamı kendisine eşitse `mükemmel`, değilse `mükemmel değil` yaz. (6 = 1 + 2 + 3)',
  ['i = 1\'den n - 1\'e kadar dön.', 'n % i == 0 ise toplam = toplam + i', 'Sonunda toplam == n mi?'],
  { ref: ([n]) => [isPerfect(n) ? 'mükemmel' : 'mükemmel değil'], ins: [[6]], hid: [[28], [12], [496], [2], [10]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem toplam = 0; i = 1', 'L: karar i < n -> sonraki | Son', 'karar n % i == 0 -> B | A', 'B: işlem toplam = toplam + i', 'A: işlem i = i + 1', 'git L', 'Son: karar toplam == n -> M | D', 'M: çıktı "mükemmel"', 'bitir', 'D: çıktı "mükemmel değil"', 'bitir'] });
const collatz = (n) => { let s = 0; while (n !== 1) { n = n % 2 === 0 ? n / 2 : 3 * n + 1; s++; } return s; };
t('hard', 'Collatz Adımları', 'Pozitif bir sayı oku. Sayı çiftse 2\'ye böl, tekse 3 katının 1 fazlasını al; 1\'e inene kadar kaç adım atıldığını yazdır. (6 → 3 → 10 → 5 → 16 → 8 → 4 → 2 → 1: 8 adım)',
  ['adim = 0; n 1 olana kadar döngü kur.', 'Çiftse n = n / 2, tekse n = 3 * n + 1', 'Her turda adim\'ı bir artır.'],
  { ref: ([n]) => [S(collatz(n))], ins: [[6]], hid: [[1], [7], [27], [16], [3]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem adim = 0', 'L: karar n != 1 -> sonraki | Son', 'karar n % 2 == 0 -> C | T', 'C: işlem n = n / 2', 'git Art', 'T: işlem n = 3 * n + 1', 'Art: işlem adim = adim + 1', 'git L', 'Son: çıktı adim', 'bitir'] });

// ===================================== UZMAN (6)
const isArm = (n) => { const d = String(n).split('').map(Number); return d.reduce((p, x) => p + x ** d.length, 0) === n; };
t('expert', 'Armstrong Sayısı', '3 basamaklı bir sayı oku. Basamaklarının küplerinin toplamı kendisine eşitse `armstrong`, değilse `değil` yazdır. (153 = 1³ + 5³ + 3³)',
  ['Basamakları n % 10 ve taban(n / 10) ile tek tek al.', 'Her basamağın küpünü toplama ekle: toplam = toplam + b * b * b', 'Orijinal sayıyı sakla: kopya = n'],
  { ref: ([n]) => [isArm(n) ? 'armstrong' : 'değil'], ins: [[153]], hid: [[370], [371], [407], [123], [100], [999]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem kopya = n; toplam = 0', 'L: karar kopya > 0 -> sonraki | Son', 'işlem b = kopya % 10', 'işlem toplam = toplam + b * b * b', 'işlem kopya = taban(kopya / 10)', 'git L', 'Son: karar toplam == n -> A | D', 'A: çıktı "armstrong"', 'bitir', 'D: çıktı "değil"', 'bitir'] });
t('expert', 'Palindrom Sayı', 'Bir sayı oku. Ters çevrilmişi kendisine eşitse `palindrom`, değilse `palindrom değil` yazdır. (12321 palindromdur)',
  ['Önce sayıyı ters çevir (basamak ters çevirme görevini hatırla).', 'Orijinali bir yere kaydet: kopya = n', 'Sonunda ters == kopya mı?'],
  { ref: ([n]) => [reverseNum(n) === n ? 'palindrom' : 'palindrom değil'], ins: [[12321]], hid: [[123], [1], [4004], [10], [9889], [1234]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem kopya = n; ters = 0', 'L: karar n > 0 -> sonraki | Son', 'işlem ters = ters * 10 + n % 10', 'işlem n = taban(n / 10)', 'git L', 'Son: karar ters == kopya -> P | D', 'P: çıktı "palindrom"', 'bitir', 'D: çıktı "palindrom değil"', 'bitir'] });
t('expert', 'Asalları Listele', 'n oku, 2\'den n\'e kadar tüm **asal sayıları** alt alta yazdır. (İç içe iki döngü gerekir.)',
  ['Dış döngü: s = 2..n; iç döngü: s asal mı diye bölenleri dene.', 'Her sayı için bir "asal_mi" bayrağı tut (doğru/yanlış değil, 1/0 kullan).', 'İç döngü bitince bayrak 1 ise s\'yi yazdır.'],
  { ref: ([n]) => Array.from({ length: Math.max(0, n - 1) }, (_, k) => k + 2).filter(isPrime).map(S), ins: [[20]], hid: [[2], [10], [30], [1], [50]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem s = 2', 'D: karar s <= n -> sonraki | Son', 'işlem i = 2; asal = 1', 'I: karar i < s -> sonraki | Bit', 'karar s % i == 0 -> Bol | sonraki', 'işlem i = i + 1', 'git I', 'Bol: işlem asal = 0', 'işlem i = s', 'git I', 'Bit: karar asal == 1 -> Yaz | Art', 'Yaz: çıktı s', 'Art: işlem s = s + 1', 'git D', 'Son: bitir'] });
const persistence = (n) => { let s = 0; while (n >= 10) { let p = 1; while (n > 0) { p *= n % 10; n = Math.floor(n / 10); } n = p; s++; } return s; };
t('expert', 'Çarpımsal Direnç', 'Bir sayı oku. Basamaklarını çarp, çıkan sayıyla aynısını tekrarla; tek basamağa inene kadar kaç tur gerektiğini yazdır. (39 → 27 → 14 → 4: 3 tur)',
  ['Dış döngü: n >= 10 olduğu sürece.', 'İç döngü: basamakları çarp (carpim = carpim * (n % 10)).', 'İç döngü bitince n = carpim ve tur = tur + 1'],
  { ref: ([n]) => [S(persistence(n))], ins: [[39]], hid: [[4], [10], [77], [999], [25]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem tur = 0', 'D: karar n >= 10 -> sonraki | Son', 'işlem carpim = 1', 'I: karar n > 0 -> sonraki | Bit', 'işlem carpim = carpim * (n % 10)', 'işlem n = taban(n / 10)', 'git I', 'Bit: işlem n = carpim', 'işlem tur = tur + 1', 'git D', 'Son: çıktı tur', 'bitir'] });
const toBinary = (n) => { let r = 0, p = 1; while (n > 0) { r += (n % 2) * p; p *= 10; n = Math.floor(n / 2); } return r; };
t('expert', 'İkili Sisteme Çevir', 'Pozitif bir tam sayı oku, ikili (binary) karşılığını bir sayı olarak yazdır. (10 → 1010)',
  ['Sayıyı 2\'ye böle böle kalanları topla: kalan = n % 2', 'Kalanı doğru basamağa koymak için bir çarpan tut: p = 1, 10, 100, …', 'sonuc = sonuc + kalan * p; p = p * 10; n = taban(n / 2)'],
  { ref: ([n]) => [S(toBinary(n))], ins: [[10]], hid: [[1], [2], [5], [37], [255]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi n', 'işlem sonuc = 0; p = 1', 'L: karar n > 0 -> sonraki | Son', 'işlem sonuc = sonuc + (n % 2) * p', 'işlem p = p * 10', 'işlem n = taban(n / 2)', 'git L', 'Son: çıktı sonuc', 'bitir'] });
const guesses = (x) => { let lo = 1, hi = 100, c = 0; for (;;) { const mid = Math.floor((lo + hi) / 2); c++; if (mid === x) return c; if (mid < x) lo = mid + 1; else hi = mid - 1; } };
t('expert', 'İkili Arama Tahmini', '1 ile 100 arasında tuttuğum sayıyı **ikiye bölerek** tahmin et: aralığın ortasını dene; küçükse alt sınırı, büyükse üst sınırı değiştir. Sayıyı (x) oku ve **kaçıncı tahminde** bulunduğunu yazdır.',
  ['alt = 1, ust = 100, tahmin_sayisi = 0', 'orta = taban((alt + ust) / 2); tahmin_sayisi + 1', 'orta == x ise bitir; orta < x ise alt = orta + 1; aksi halde ust = orta - 1'],
  { ref: ([x]) => [S(guesses(x))], ins: [[50]], hid: [[1], [100], [37], [75], [13], [99]], need: { decision: true, loop: true, input: true },
    sol: ['başla', 'girdi x', 'işlem alt = 1; ust = 100; adet = 0', 'L: işlem orta = taban((alt + ust) / 2)', 'işlem adet = adet + 1', 'karar orta == x -> Son | sonraki', 'karar orta < x -> K | B', 'K: işlem alt = orta + 1', 'git L', 'B: işlem ust = orta - 1', 'git L', 'Son: çıktı adet', 'bitir'] });

// ===================================== doğrulama + çıktı
const counts = tasks.reduce((a, x) => ((a[x.cat] = (a[x.cat] || 0) + 1), a), {});
if (tasks.length !== 40 || counts.easy !== 12 || counts.medium !== 12 || counts.hard !== 10 || counts.expert !== 6) throw new Error('Görev sayıları: ' + JSON.stringify(counts));

let bad = 0;
const out = tasks.map((tk, i) => {
  const tests = [];
  tk.ins.forEach((inp) => tests.push({ in: inp.map((v) => v), out: tk.ref(inp).map(S) }));
  tk.hid.forEach((inp) => tests.push({ in: inp.map((v) => v), out: tk.ref(inp).map(S), hidden: true }));
  const chart = E.buildFromDSL(tk.sol);
  const task = { tests, need: tk.need || {} };
  const res = E.checkTask(chart, task);
  if (!res.ok) { console.error(`#${i + 1} ${tk.title}: referans çözüm GEÇMİYOR`, JSON.stringify(res.tests.filter((r) => !r.ok).slice(0, 2)), res.structure, res.validation && res.validation.errors); bad++; }
  // boş şema (yalnız Başla→Bitir) geçmemeli
  const empty = E.buildFromDSL(['başla', 'bitir']);
  if (E.checkTask(empty, task).ok) { console.error(`#${i + 1} ${tk.title}: boş şema GEÇİYOR`); bad++; }
  return { id: i + 1, cat: tk.cat, title: tk.title, story: tk.story, hints: tk.hints, xp: XP[tk.cat], need: tk.need || {}, tests, nodeCount: chart.nodes.length };
});
if (bad) { console.error(bad + ' sorun'); process.exit(1); }

const js = `/* Flowchart Programlama — 40 görev (otomatik üretildi: tools/learn-levels/flowchart-tasks.gen.mjs, elle düzenlemeyin). */\nwindow.FLOWCHART_TASKS = ${JSON.stringify(out)};\n`;
fs.writeFileSync(OUT, js);
console.log(out.map((o) => `${String(o.id).padStart(2)} ${o.cat.padEnd(7)} ${o.title.padEnd(30)} test=${o.tests.length} (referans ${o.nodeCount} blok)`).join('\n'));
console.log(`\n40 görev ${JSON.stringify(counts)} -> ${path.relative(process.cwd(), OUT)} (${(js.length / 1024).toFixed(1)} KB)`);
