# School Management System (Laravel 12)

Production-ready okul yonetim sistemi.

## Ozellikler
- Admin panel + moduller: students, teachers, classes, courses, grades, attendance, announcements, users
- Controller + Service + Repository mimarisi
- Role based access (`admin`, `teacher`, `student`)
- REST API (`/api/v1/*`)
- AJAX hizli ekleme modal akisi
- Raporlar: CSV (Excel uyumlu) + yazdirilabilir PDF gorunumu

## Local Kurulum
1. `composer install`
2. `copy .env.example .env`
3. `.env` icin MySQL ayarlari:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=school_management`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=`
4. `php artisan key:generate`
5. `php artisan migrate:fresh --seed`
6. `php artisan serve --host=127.0.0.1 --port=8089`

## Varsayilan Giris
- Email: `admin@school.local`
- Sifre: `Admin1234!`

## Docker ile Calistirma
1. `copy .env.docker .env`
2. `docker compose up -d --build`
3. `docker compose exec app php artisan key:generate`
4. `docker compose exec app php artisan migrate:fresh --seed --force`
5. Uygulama: `http://localhost:8088`

Servisler:
- App (php-fpm)
- Nginx
- MySQL 8.4
- Redis 7
- Queue worker
- Scheduler worker

## CI/CD
- Workflow: `.github/workflows/ci.yml`
- Push/PR'da:
  - dependency install
  - migration
  - test calistirilir

## Production Komutlari
- Optimizasyon:
  - `composer run prod:optimize`
- Deploy script:
  - `bash scripts/deploy.sh`
- DB backup script:
  - `bash scripts/backup.sh`

## Dogrulama
- `php artisan route:list`
- `php artisan test`

## Guvenlik ve Performans
- FormRequest validation
- CSRF + auth middleware + role middleware
- Policy kayitlari
- Dashboard cache (`Cache::remember`)
- Eager loading
- Queue + scheduler ayri process
