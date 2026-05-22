#!/bin/sh
set -e

DB_NAME="${DB_DATABASE:-money_manager}"
DB_USER="${DB_USERNAME:-laravel}"
DB_PASS="${DB_PASSWORD:-secret}"

# Initialize MySQL data directory if not already done
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MySQL..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null
fi

# Start MySQL temporarily to set up database and user
mysqld --user=mysql --skip-networking --socket=/run/mysqld/mysqld.sock &
MYSQL_PID=$!

# Wait for MySQL to be ready
until mysqladmin --socket=/run/mysqld/mysqld.sock ping --silent; do
    sleep 1
done

# Create DB and user
mysql --socket=/run/mysqld/mysqld.sock -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# Stop the temporary MySQL
kill $MYSQL_PID
wait $MYSQL_PID 2>/dev/null || true

# Update .env for internal MySQL via socket
cat > /var/www/html/.env <<ENV
APP_NAME="${APP_NAME:-MoneyManager}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
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
ENV

# Cache Laravel config
cd /var/www/html
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start all services via supervisord (MySQL, php-fpm, nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf
