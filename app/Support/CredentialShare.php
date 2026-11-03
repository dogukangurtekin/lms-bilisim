<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/** Yöneticinin öğrenci / öğretmene giriş bilgilerini WhatsApp ile tek tek göndermesi için mesaj ve bağlantı üretir. */
class CredentialShare
{
    /** Öğrencinin kullanıcı adı: kayıtlı giriş bilgisi, yoksa e-posta alanının kullanıcı adı kısmı. */
    public static function studentUsername(User $user): string
    {
        return (string) ($user->student?->credential?->username ?: Str::before((string) $user->email, '@'));
    }

    public static function message(User $user, string $username, string $password): string
    {
        $login = route('login');
        $isStudent = $user->hasRole('student');
        $who = $isStudent ? "{$user->name} adlı öğrencimizin" : 'Sizin';

        return "Merhaba,\n"
            . "Bilişim Kod sistemine {$who} giriş bilgileri aşağıdadır.\n\n"
            . "Giriş sayfası: {$login}\n"
            . "Kullanıcı adı: {$username}\n"
            . "Şifre: {$password}\n\n"
            . ($isStudent ? 'İlk girişte şifrenin yenilenmesi istenebilir. ' : '')
            . 'Bu bilgileri kimseyle paylaşmayınız.';
    }

    /** Telefonu WhatsApp biçimine (905xxxxxxxxx) çevirir; geçersizse null. */
    public static function phone(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '') {
            return null;
        }
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (str_starts_with($d, '0')) {
            $d = '90' . substr($d, 1);
        } elseif (strlen($d) === 10 && str_starts_with($d, '5')) {
            $d = '90' . $d;
        }

        return strlen($d) >= 11 && strlen($d) <= 15 ? $d : null;
    }

    /** Telefon varsa o kişiye, yoksa kişi seçmek üzere WhatsApp'ı açar. */
    public static function url(?string $phone, string $text): string
    {
        $p = self::phone($phone);

        return 'https://wa.me/' . ($p ?? '') . '?text=' . rawurlencode($text);
    }

    /** Öğrenci için hazır bağlantı (veli telefonuna); giriş bilgisi yoksa null. */
    public static function studentUrl(User $user): ?string
    {
        $cred = $user->student?->credential;
        if (! $cred || (string) $cred->plain_password === '') {
            return null;
        }

        return self::url($user->student?->parent_phone, self::message($user, self::studentUsername($user), (string) $cred->plain_password));
    }
}
