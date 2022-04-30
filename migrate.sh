#!/bin/sh

echo "Starting Container"

echo "Running Migrations"
cd /var/www/
php vendor/bin/phinx migrate -e production
echo "Migrations Run - Apache to takeover now"

if [ "$RAILWAY_ENVIRONMENT" != "production" ]
then
  echo "Running Seed"
  php vendor/bin/phinx seed:run
fi

echo "Booting Apache"
/usr/sbin/apache2 -D FOREGROUND