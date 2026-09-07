// Kullanıcının "Örnek Yükle" menüsünden seçebileceği, çalışır durumdaki hazır
// akış şemaları. Girdi/çıktı, karar (decision) ve döngü kalıplarını gösterir.

export const examples = [
  {
    key: 'cift-tek',
    label: 'Çift mi Tek mi?',
    description: 'Girilen sayının çift mi tek mi olduğunu bulur.',
    name: 'Çift mi Tek mi?',
    nodes: [
      { id: 'n-start', type: 'start', text: 'Başla', code: '', position: { x: 80, y: 260 } },
      { id: 'n-in', type: 'io', text: 'Sayıyı Al', code: 'input sayi', position: { x: 320, y: 260 } },
      { id: 'n-mod', type: 'process', text: 'Kalanı Hesapla', code: 'kalan = sayi % 2', position: { x: 560, y: 260 } },
      { id: 'n-dec', type: 'decision', text: 'Kalan sıfır mı?', code: 'kalan == 0', position: { x: 800, y: 240 } },
      { id: 'n-even', type: 'io', text: 'Çift Yazdır', code: 'output "Çift sayı"', position: { x: 1060, y: 120 } },
      { id: 'n-odd', type: 'io', text: 'Tek Yazdır', code: 'output "Tek sayı"', position: { x: 1060, y: 400 } },
      { id: 'n-end1', type: 'end', text: 'Bitir', code: '', position: { x: 1300, y: 120 } },
      { id: 'n-end2', type: 'end', text: 'Bitir', code: '', position: { x: 1300, y: 400 } },
    ],
    edges: [
      { id: 'e1', from: 'n-start', to: 'n-in', condition: null },
      { id: 'e2', from: 'n-in', to: 'n-mod', condition: null },
      { id: 'e3', from: 'n-mod', to: 'n-dec', condition: null },
      { id: 'e4', from: 'n-dec', to: 'n-even', condition: 'yes' },
      { id: 'e5', from: 'n-dec', to: 'n-odd', condition: 'no' },
      { id: 'e6', from: 'n-even', to: 'n-end1', condition: null },
      { id: 'e7', from: 'n-odd', to: 'n-end2', condition: null },
    ],
  },
  {
    key: 'toplam',
    label: "1'den N'e Kadar Toplam",
    description: 'Bir döngü kullanarak 1 ile N arasındaki sayıların toplamını bulur.',
    name: "1'den N'e Kadar Toplam",
    nodes: [
      { id: 'n-start', type: 'start', text: 'Başla', code: '', position: { x: 60, y: 260 } },
      { id: 'n-in', type: 'io', text: 'N Sayısını Al', code: 'input n', position: { x: 300, y: 260 } },
      { id: 'n-init1', type: 'process', text: 'Toplamı Sıfırla', code: 'toplam = 0', position: { x: 540, y: 260 } },
      { id: 'n-init2', type: 'process', text: 'Sayacı Başlat', code: 'i = 1', position: { x: 780, y: 260 } },
      { id: 'n-dec', type: 'decision', text: 'i, N’den küçük eşit mi?', code: 'i <= n', position: { x: 1020, y: 240 } },
      { id: 'n-add', type: 'process', text: 'Topla', code: 'toplam = toplam + i', position: { x: 1260, y: 100 } },
      { id: 'n-inc', type: 'process', text: 'Sayacı Artır', code: 'i = i + 1', position: { x: 1500, y: 100 } },
      { id: 'n-out', type: 'io', text: 'Sonucu Yazdır', code: 'output toplam', position: { x: 1260, y: 440 } },
      { id: 'n-end', type: 'end', text: 'Bitir', code: '', position: { x: 1500, y: 440 } },
    ],
    edges: [
      { id: 'e1', from: 'n-start', to: 'n-in', condition: null },
      { id: 'e2', from: 'n-in', to: 'n-init1', condition: null },
      { id: 'e3', from: 'n-init1', to: 'n-init2', condition: null },
      { id: 'e4', from: 'n-init2', to: 'n-dec', condition: null },
      { id: 'e5', from: 'n-dec', to: 'n-add', condition: 'yes' },
      { id: 'e6', from: 'n-add', to: 'n-inc', condition: null },
      { id: 'e7', from: 'n-inc', to: 'n-dec', condition: null },
      { id: 'e8', from: 'n-dec', to: 'n-out', condition: 'no' },
      { id: 'e9', from: 'n-out', to: 'n-end', condition: null },
    ],
  },
  {
    key: 'bos-sablon',
    label: 'Boş Şablon',
    description: 'Sadece Başla ve Bitir bloklarıyla temiz bir sayfa.',
    name: 'Yeni Flowchart',
    nodes: [
      { id: 'n-start', type: 'start', text: 'Başla', code: '', position: { x: 160, y: 180 } },
      { id: 'n-end', type: 'end', text: 'Bitir', code: '', position: { x: 460, y: 180 } },
    ],
    edges: [{ id: 'e1', from: 'n-start', to: 'n-end', condition: null }],
  },
];
