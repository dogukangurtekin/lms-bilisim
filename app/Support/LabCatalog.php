<?php

namespace App\Support;

/** Kod Laboratuvarı kataloğu: hazır laboratuvarlar ve yakında gelecekler. Her laboratuvar bir runner-assets uygulamasıdır. */
class LabCatalog
{
    public const LABS = [
        'lab-kural-makinesi' => [
            'title' => 'Kural Makinesi',
            'category' => 'Kodlama Mantığı',
            'grades' => '5-8. sınıf',
            'difficulty' => 'Kolay - Orta',
            'tasks' => 5,
            'icon' => '/lab-kural-makinesi.svg',
            'summary' => 'Sensör verilerini oku, EĞER-İSE kurallarıyla akıllı bir serayı yönet. VE / VEYA mantığını ve kural sırasının önemini keşfet.',
        ],
    ];

    public const UPCOMING = [
        ['title' => 'Şifre Kalesi', 'category' => 'Siber Güvenlik', 'summary' => 'Şifrenin gücünü dene, sahte mesajları ayıkla.'],
        ['title' => 'Bit Panosu', 'category' => 'Sayı Sistemleri', 'summary' => 'Anahtarlarla ikilik sayıları ve harfleri kodla.'],
        ['title' => 'Hücre Avı', 'category' => 'Tablo ve Formül', 'summary' => 'Tablolarda doğru formülü bul, sonucu hesapla.'],
        ['title' => 'Sıralama Pisti', 'category' => 'Algoritma', 'summary' => 'Kartları en az adımda sırala, yöntemleri yarıştır.'],
        ['title' => 'Veri Eğit', 'category' => 'Yapay Zekâ', 'summary' => 'Örnekleri etiketle, modelin neden yanıldığını gör.'],
    ];

    public static function slugs(): array
    {
        return array_keys(self::LABS);
    }

    public static function isLab(string $slug): bool
    {
        return isset(self::LABS[$slug]);
    }
}
