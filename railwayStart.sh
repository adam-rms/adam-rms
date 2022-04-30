#!/bin/sh

echo "Starting Container"

echo "Setting Environment Vars"
export bCMS__ROOTURL="https://$RAILWAY_STATIC_URL"
export bCMS__DB_HOSTNAME="$MYSQLHOST"
export bCMS__DB_DATABASE="$MYSQLDATABASE"
export bCMS__DB_USERNAME="$MYSQLUSER"
export bCMS__DB_PASSWORD="$MYSQLPASSWORD"
export bCMS__DB_PORT="$MYSQLPORT"
echo "Vars set"

echo "Running Migrations"
cd /var/www/
php vendor/bin/phinx migrate -e production
echo "Migrations Run"

if [ "$RAILWAY_ENVIRONMENT" != "production" ]
then
  echo "Running Seed"
  php vendor/bin/phinx seed:run
  echo "Seed Run"
fi

echo "Booting Apache"
/usr/sbin/apache2 -D FOREGROUND