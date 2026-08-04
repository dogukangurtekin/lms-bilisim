# UTF-8 Standard

Bu projede Türkçe karakterlerin bozulmaması için şu kurallar uygulanır:

## Dosya standardı

- Tüm kaynak dosyaları `UTF-8` olarak kaydedilir.
- `BOM` kullanılmaz.
- `.editorconfig` ile charset zaten `utf-8` olarak sabitlenmiştir.
- VS Code için `.vscode/settings.json` kullanılır.

## Veritabanı standardı

- MySQL bağlantısı `utf8mb4` kullanır.
- Tablolar ve sütunlar `utf8mb4_unicode_ci` veya eşdeğeri collation ile tutulur.
- JSON alanları `JSON_UNESCAPED_UNICODE` ile yazılır.

## Uygulama standardı

- `utf8_encode()` ve `utf8_decode()` kullanılmaz.
- Eski bozuk metinler için yalnızca `App\Support\Utf8Text::normalize()` kullanılır.
- Yeni veri kaydında metinler çift encode edilmez.

## Kontrol komutları

```bash
php artisan app:utf8-db-check
php artisan app:utf8-audit
php artisan app:utf8-audit --fix
php artisan test --filter=Utf8StandardTest
```

## Yeni içerik eklerken

- Blade içine doğrudan Türkçe metin yaz.
- JSON üretirken `JSON_UNESCAPED_UNICODE` kullan.
- CSV / SQL import yaparken dosya encoding'ini doğrula.
- İçe aktarılan eski kayıtlar için audit komutunu çalıştır.
