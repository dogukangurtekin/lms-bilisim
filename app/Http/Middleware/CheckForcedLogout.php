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

                return redirect()->route('login')->withErrors([
                    'email' => 'Oturumunuz sinif oturumlarini kapatma islemiyle sonlandirildi. Lutfen tekrar giris yapin.',
                ]);
            }
        }

        return $next($request);
    }
}
