<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin/ogretmen panelindeki "Aktif Siniflar" widget'indaki "Cikis Yap"
 * butonu, o sinifin tum ogrencilerinin User.force_logout_at alanini "simdi"
 * olarak isaretliyor. Oturumlar dosya tabanli (SESSION_DRIVER=file) oldugu
 * icin sunucu tarafinda dogrudan bir oturumu silmek mumkun degil - bunun
 * yerine her istekte, kullanicinin GIRIS YAPTIGI an (session('auth_at'))
 * ile force_logout_at karsilastiriliyor: force_logout_at daha yeniyse
 * oturum o an gecersiz sayilip kullanici otomatik cikis yaptiriliyor.
 */
class CheckForcedLogout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && $user->force_logout_at) {
            $authAt = (int) $request->session()->get('auth_at', 0);
            $forceAt = $user->force_logout_at->timestamp;
            if ($forceAt > $authAt) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Not: bu yonlendirme cogu zaman bir fetch() "canlilik" istegi
                // tarafindan otomatik takip ediliyor (bkz. layout/app.blade.php),
                // ardindan istemci ayrica GERCEK bir sayfa navigasyonu daha
                // tetikliyor. Bu iki asamali dolayli yonlendirme yuzunden
                // Laravel'in tek seferlik session flash mekanizmasi (with())
                // ikinci (gercek) navigasyona ulasmadan "eskimis" sayiliyor ve
                // mesaj kayboluyor. Bunun yerine mesaji URL query parametresi
                // olarak tasiyoruz - bu, kac kez yonlendirme takip edilirse
                // edilsin degismeden kaliyor.
                return redirect()->route('login', ['force_logout' => 1]);
            }
        }

        return $next($request);
    }
}
