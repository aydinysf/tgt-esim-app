#!/bin/sh
set -e

echo "=== Starting POLO SIM Container ==="

# Create all necessary storage directories if they do not exist
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Set permissions for storage and bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# If .env does not exist, create from .env.example
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        echo "Creating .env from .env.example..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Ensure persistent APP_KEY exists
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
        echo "Generating persistent Application Key..."
        php artisan key:generate --force || true
    fi
fi

# Clear stale caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Link storage
php artisan storage:link --force || true

# Wait for database connection (up to 20 seconds) before running migrations
echo "Checking database connection..."
DB_READY=0
for i in $(seq 1 10); do
    if php -r "
        try {
            require __DIR__ . '/vendor/autoload.php';
            \$app = require_once __DIR__ . '/bootstrap/app.php';
            \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
            \$kernel->bootstrap();
            DB::connection()->getPdo();
            exit(0);
        } catch (\Throwable \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        echo "Database connection ready!"
        DB_READY=1
        break
    fi
    echo "Waiting for database to be ready (attempt $i/10)..."
    sleep 2
done

# Run migrations if DB is accessible
if [ $DB_READY -eq 1 ]; then
    echo "Running Database Migrations..."
    php artisan migrate --force || echo "Warning: Migration execution encountered an issue."
else
    echo "Warning: Database connection could not be established within timeout. Please check your DB credentials."
fi

# Final storage permissions fix for www-data
echo "Fixing storage permissions for www-data..."
touch /var/www/html/storage/logs/laravel.log || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
