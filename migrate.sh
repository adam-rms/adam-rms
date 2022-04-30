#!/bin/sh

echo "Starting Container"
echo "$PORT"

echo "Running Migrations"
cd /var/www/
php vendor/bin/phinx migrate -e production
echo "Migrations Run"