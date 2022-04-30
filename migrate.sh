#!/bin/sh

echo "Starting Container"
echo "$RAILWAY_ENVIRONMENT"

echo "Running Migrations"
cd /var/www/
php vendor/bin/phinx migrate -e production
echo "Migrations Run - Apache to takeover now"

/usr/sbin/apache2 -D FOREGROUND