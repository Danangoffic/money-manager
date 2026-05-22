#!/bin/sh
set -e

DB_NAME="${DB_DATABASE:-money_manager}"
DB_USER="${DB_USERNAME:-laravel}"
DB_PASS="${DB_PASSWORD:-secret}"

echo "==> Starting Money Manager container..."

# Generate APP_KEY if not provided via environment variable
if [ -z "$APP_KEY" ]; then
    echo "==> APP_KEY not set, generating new key..."
    APP_KEY=$(cd /var/www/html && php artisan key:generate --show)
    export APP_KEY
    echo "==> Generated APP_KEY: ${APP_KEY}"
fi

# Initialize MySQL data directory if not already done
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "==> Initializing MySQL data directory..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null
fi

# Start MySQL temporarily to set up database and user
echo "==> Starting MySQL temporarily for setup..."
mysqld --user=mysql --skip-networking --socket=/run/mysqld/mysqld.sock &
MYSQL_PID=$!

# Wait for MySQL to be ready (with timeout)
WAIT_COUNT=0
MAX_WAIT=30
until mysqladmin --socket=/run/mysqld/mysqld.sock ping --silent 2>/dev/null; do
    WAIT_COUNT=$((WAIT_COUNT + 1))
    if [ $WAIT_COUNT -ge $MAX_WAIT ]; then
        echo "ERROR: MySQL failed to start within ${MAX_WAIT} seconds"
        exit 1
    fi
    sleep 1
done
echo "==> MySQL is ready."

# Create DB and user
echo "==> Creating database '${DB_NAME}' and user '${DB_USER}'..."
mysql --socket=/run/mysqld/mysqld.sock -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# Stop the temporary MySQL
kill $MYSQL_PID
wait $MYSQL_PID 2>/dev/null || true
echo "==> MySQL setup complete."

# Generate .env for internal MySQL via socket
echo "==> Writing .env configuration..."
cat > /var/www/html/.env <<ENV
APP_NAME="${APP_NAME:-MoneyManager}"
APP_ENV="${APP_ENV:-production}"
APP_KEY=${APP_KEY}
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}
DB_SOCKET=/run/mysqld/mysqld.sock

SESSION_DRIVER=${SESSION_DRIVER:-database}
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID:-}
GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET:-}
GOOGLE_REDIRECT_URI=${GOOGLE_REDIRECT_URI:-}
ENV

# Cache Laravel config (now .env is guaranteed to exist with valid APP_KEY)
cd /var/www/html
echo "==> Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting services (MySQL, PHP-FPM, Nginx)..."
# Start all services via supervisord (MySQL, php-fpm, nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
