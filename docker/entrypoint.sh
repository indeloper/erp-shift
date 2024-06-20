#!/bin/bash

# Wait for MariaDB to be available
echo "Waiting for MariaDB..."
while ! nc -z $DB_HOST $DB_PORT; do
    sleep 2
    echo "Waiting for MariaDB to be available..."
done
echo "MariaDB is up"

sleep 20

echo "Running migrations"
runuser -u www-data -- php artisan migrate --force

echo "Clearing Laravel cache"
runuser -u www-data -- php artisan config:clear
runuser -u www-data -- php artisan route:clear
runuser -u www-data -- php artisan cache:clear
runuser -u www-data -- php artisan event:clear
runuser -u www-data -- php artisan view:clear

echo "Caching Laravel"
runuser -u www-data -- php artisan config:cache
runuser -u www-data -- php artisan route:cache
runuser -u www-data -- php artisan event:cache
runuser -u www-data -- php artisan view:cache

echo "Running queue"
runuser -u www-data -- php artisan queue:work

chgrp -R www-data .
chmod 764 "./storage/logs/laravel.log"

php-fpm -D
nginx -g "daemon off;"
