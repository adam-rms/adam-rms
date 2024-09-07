#!/bin/bash

cd /var/www

# Validate expected environment variables
echo "AdamRMS - Checking for Environment Variables"
if [[ ! -v DB_HOSTNAME ]] || [[ ! -v DB_DATABASE ]] || [[ ! -v DB_USERNAME ]] || [[ ! -v DB_PASSWORD ]]; then
    echo "AdamRMS - Expected Environment Variables not set"
    exit 1
fi

# Database migration & seed
echo "AdamRMS - Starting Migration Script"

php vendor/bin/phinx migrate -e production
php vendor/bin/phinx seed:run -e production

if [[ -v DEV_MODE ]] && [[ "${DEV_MODE}" == 'true' ]]; then
    echo "AdamRMS - Running in DEV MODE"
fi

cd html/

# Start Server
echo "AdamRMS - Starting Apache2"
apache2-foreground