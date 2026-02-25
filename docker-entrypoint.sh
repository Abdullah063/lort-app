#!/bin/bash
set -e

# MySQL'in hazır olmasını bekle
echo "MySQL bekleniyor..."
while ! php -r "new PDO('mysql:host=db;port=3306;dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
    echo "MySQL henüz hazır değil, bekleniyor..."
done
echo "MySQL hazır!"

# .env yoksa oluştur
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Queue worker arka planda başlat
php artisan queue:work --tries=3 --timeout=240&
php artisan queue:work --tries=3 --timeout=240&
php artisan queue:work --tries=3 --timeout=240&

# Scheduler arka planda başlat
while true; do
    php artisan schedule:run >> /dev/null 2>&1
    sleep 60
done &

# PHP-FPM başlat
php-fpm