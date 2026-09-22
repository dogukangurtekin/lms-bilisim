<?php

namespace App\Support;

class Brand
{
    /**
     * public/logo.png'nin tam URL'sini, dosyanin son degisiklik zamanina
     * gore bir "?v=" sorgu parametresiyle doner. Boylece logo dosyasi
     * icerigi degistiginde (ayni dosya adi kalsa bile) tarayicilarin ve
     * CDN'in eski onbellege alinmis gorseli gostermeye devam etmesi
     * onlenir - URL degisince onbellek otomatik olarak "kirilir".
     */
    public static function logoUrl(): string
    {
        $path = public_path('logo.png');
        $version = is_file($path) ? filemtime($path) : time();

        return asset('logo.png') . '?v=' . $version;
    }
}
