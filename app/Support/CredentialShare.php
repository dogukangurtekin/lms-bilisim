<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Yöneticinin öğrenci / öğretmene giriş bilgilerini WhatsApp ile tek tek göndermesi için hazır mesaj ve bağlantı üretir.
 * Herkese açık ödev paylaşımıyla (PublicHomework) aynı mantık: telefon numarası eklenmez, "https://wa.me/?text=" ile
 * WhatsApp açılır ve alıcıyı (veli / öğretmen) yönetici kendisi seçer.
 */
class CredentialShare
{
    /** Öğrencinin kullanıcı adı: kayıtlı giriş bilgisi, yoksa e-posta alanının kullanıcı adı kısmı. */
    public static function studentUsername(User $user): string
    {
        return (string) ($user->student?->credential?->username ?: Str::before((string) $user->email, '@'));
    }

    /** Hazır mesaj: veli (öğrenci için) ya da öğretmene hitapla, giriş sayfası + kullanıcı adı + şifre; imza ödev mesajıyla aynı. */
    public static function message(User $user, string $username, string $password): string
    {
        $login = route('login');
        $isStudent = $user->hasRole('student');

        $intro = $isStudent
            ? "Değerli velim, {$user->name} adlı öğrencimizin Bilişim Kod sistemine giriş bilgileri aşağıdadır."
            : "Sayın {$user->name}, Bilişim Kod sistemine giriş bilgileriniz aşağıdadır.";

        return $intro . "\n\n"
            . "Giriş sayfası: {$login}\n"
            . "Kullanıcı adı: {$username}\n"
            . "Şifre: {$password}\n\n"
            . ($isStudent ? 'İlk girişte şifrenin yenilenmesi istenebilir. ' : '')
            . "Bu bilgileri kimseyle paylaşmamanızı rica ederim. Bilgilerinize\n"
            . "Doğukan Gürtekin\n"
            . 'Bilişim Teknolojileri Öğrt.';
    }

    /** Alıcıyı seçmek üzere WhatsApp'ı hazır mesajla açar (telefon numarası eklenmez). */
    public static function url(string $text): string
    {
        return 'https://wa.me/?text=' . rawurlencode($text);
    }

    /** Öğrenci için hazır bağlantı; kayıtlı giriş bilgisi yoksa null. */
    public static function studentUrl(User $user): ?string
    {
        $cred = $user->student?->credential;
        if (! $cred || (string) $cred->plain_password === '') {
            return null;
        }

        return self::url(self::message($user, self::studentUsername($user), (string) $cred->plain_password));
    }
}
