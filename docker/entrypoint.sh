#!/bin/sh
set -e

# Wait for database connection if host database configuration is provided
echo "Checking database connection..."
# We can run a simple php script to wait for mysql connection if host/port is set
php -r '
$db_host = getenv("DB_HOST");
$db_port = getenv("DB_PORT") ?: 3306;
$db_user = getenv("DB_USERNAME");
$db_pass = getenv("DB_PASSWORD");
$db_name = getenv("DB_DATABASE");

if (!$db_host || !$db_name) {
    echo "DB_HOST or DB_DATABASE not set, skipping DB connection check.\n";
    exit(0);
}

echo "Attempting to connect to database at $db_host:$db_port...\n";
$max_attempts = 15;
$attempts = 0;
while ($attempts < $max_attempts) {
    try {
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2
        ]);
        echo "Database connection successful!\n";
        exit(0);
    } catch (PDOException $e) {
        echo "Database connection failed (" . $e->getMessage() . "). Retrying in 2 seconds...\n";
        $attempts++;
        sleep(2);
    }
}
echo "Database connection timed out!\n";
exit(1);
' || echo "Database check skipped or failed."

# Run migrations if enabled
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Optimize Laravel configurations
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Execute CMD (starts supervisor)
exec "$@"
