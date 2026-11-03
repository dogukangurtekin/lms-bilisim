<?php

namespace App\Support;

use App\Models\User;

/**
 * Giriş sonrası bir kereliğe mahsus gösterilen "okudum, onayladım" ekranının içeriği.
 * Metinler değiştiğinde config/consent.php içindeki sürüm artırılır; herkes yeni sürümü yeniden onaylar.
 *
 * Not: Ayrıntılı hukuki metinler legal.* sayfalarındadır; buradakiler yalnızca ekrandaki sade özetlerdir.
 */
class ConsentPolicy
{
    public static function version(): int
    {
        return max(1, (int) config('consent.version', 1));
    }

    /** Bu kullanıcıdan şu an onay istenmeli mi? (Misafir quiz katılımcıları hariç.) */
    public static function required(?User $user): bool
    {
        if (! $user || ! config('consent.enforced', true) || $user->hasRole('guest')) {
            return false;
        }

        return (int) $user->consent_version < self::version();
    }

    /** Kullanıcının onay durumu: 'ok' (güncel sürümü onayladı), 'old' (eski sürümü onayladı), 'none' (hiç onaylamadı). */
    public static function status(?User $user): string
    {
        $v = (int) ($user?->consent_version ?? 0);

        return $v >= self::version() ? 'ok' : ($v > 0 ? 'old' : 'none');
    }

    /** Bir kullanıcı sorgusunu onay durumuna göre süzer: 'yes' = güncel sürümü onaylayanlar, 'no' = onaylamayanlar. */
    public static function scopeQuery($query, string $consent)
    {
        if ($consent === 'yes') {
            return $query->where('consent_version', '>=', self::version());
        }
        if ($consent === 'no') {
            return $query->where(fn ($q) => $q->whereNull('consent_version')->orWhere('consent_version', '<', self::version()));
        }

        return $query;
    }

    /** Onaylanan belgeler: ne içerir, neden isteniyor. */
    public static function documents(): array
    {
        return [
            [
                'icon' => '🛡️',
                'title' => 'Kişisel Verilerin Korunması (KVKK) Aydınlatma Metni',
                'route' => 'legal.privacy',
                'what' => 'Hangi bilgilerinin (ad, sınıf, ders ve quiz sonuçları, mesajlar, IP ve cihaz bilgisi) neden saklandığı, kimlerle paylaşıldığı, ne kadar süre tutulduğu ve haklarının neler olduğu.',
                'why' => 'Kanun, verilerin işlenmesinden önce herkesin bu konuda açıkça bilgilendirilmesini ister.',
            ],
            [
                'icon' => '📜',
                'title' => 'Kullanım Şartları',
                'route' => 'legal.terms',
                'what' => 'Hesabın nasıl kullanılacağı, şifrenin korunması, yapılmaması gerekenler ve içeriklerin hakları.',
                'why' => 'Herkesin kuralları baştan bilmesi ve platformun güvenli, adil kalması için.',
            ],
            [
                'icon' => '🍪',
                'title' => 'Çerez Politikası',
                'route' => 'legal.cookies',
                'what' => 'Yalnızca giriş ve güvenlik için zorunlu çerezler kullanılır; reklam ve izleme çerezi yoktur.',
                'why' => 'Tarayıcında hangi küçük dosyaların saklandığını bilmen için.',
            ],
        ];
    }

    /** Ekrandaki sade özet maddeleri; öğrenci için çocuk diliyle, yönetici/öğretmen için resmi dille. */
    public static function summary(User $user): array
    {
        if ($user->hasRole('student')) {
            return [
                '👤 Okulun senin için bir hesap açtı. Adın, sınıfın ve kullanıcı adın kayıtlıdır.',
                '📊 Derslerin, ödevlerin, XP ve quiz sonuçların öğretmenin ve okul yönetimi tarafından görülebilir.',
                '💬 Öğretmenine veya yöneticiye yazdığın mesajları onlar okur.',
                '🔒 Bilgilerin reklam için kullanılmaz ve kimseye satılmaz.',
                '🔑 Şifreni kimseyle paylaşma; başkasının hesabına girme, kimseye kötü söz yazma.',
                '👨‍👩‍👧 Haklarını ve sorularını velinle birlikte okulun yönetimine iletebilirsin.',
            ];
        }

        return [
            'Okul veri sorumlusudur; Bilişim Kod, okulun talimatı doğrultusunda verileri işleyen taraftır.',
            'Platforma tanımlanan öğrenci, öğretmen ve yönetici verileri yalnızca eğitim amacıyla kullanılır; reklam veya ticari amaçla paylaşılmaz.',
            'Öğrencilerin velilerinden gerekli KVKK izinlerinin okul tarafından alınmış olması okulun sorumluluğundadır.',
            'Öğrenci giriş bilgilerine erişebilirsiniz; bu bilgileri yalnızca ilgili öğrenciyle paylaşmak ve güvende tutmak sizin sorumluluğunuzdadır.',
            'Hesabınızla yapılan işlemler sizin adınıza kaydedilir; hesabınızı başkasına kullandırmayın.',
            'Giriş, IP ve cihaz bilgileri güvenlik amacıyla kayıt altına alınır.',
        ];
    }
}
