#!/bin/sh
set -e

echo "=== Starting POLO SIM Container ==="

# Set permissions for storage and bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Generate key if not present
if [ -z "$APP_KEY" ]; then
    echo "Generating Application Key..."
    php artisan key:generate --force || true
fi

# Clear stale caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Link storage
php artisan storage:link --force || true

# Auto-run migrations and seeds on startup
echo "Running Database Migrations and Seeders..."
php artisan migrate --force || echo "Migration skipped or failed (check DB credentials)"
php artisan db:seed --force || echo "Seeding skipped or failed"

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
