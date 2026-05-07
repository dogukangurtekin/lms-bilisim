# public_html Kökten Laravel Çalıştırma

Bu proje, `public_html` kökünde çalışacak şekilde hazırlandı:
- Front controller: kökteki `index.php`
- URL yönlendirme: kökteki `.htaccess`
- Statik dosyalar: `public/` klasöründen otomatik servis edilir

## Sunucuya Yükleme (önerilen)
1. Projenin tamamını `public_html` içine yükleyin.
2. SSH varsa:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan key:generate --force` (ilk kurulumsa)
   - `php artisan migrate --force`
   - `php artisan storage:link` (gerekliyse)
   - `php artisan optimize`
3. `.env` içinde canlı ayarları doğrulayın:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://alanadiniz.com`
   - DB bilgileri doğru olmalı
4. `storage` ve `bootstrap/cache` yazma izni verin.

## Zorunlu Apache Modülleri
- `mod_rewrite` açık olmalı.
- Hosting panelinde `AllowOverride All` aktif olmalı (genelde paylaşımlı hostta açıktır).

## Hızlı Kontrol
- Ana sayfa açılıyor mu?
- `https://alanadiniz.com/build/...` dosyaları 200 dönüyor mu?
- Giriş/çıkış ve form gönderimleri çalışıyor mu?
