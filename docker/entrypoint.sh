#!/bin/bash

# Wait for MariaDB to be available
echo "Waiting for MariaDB..."
while ! nc -z $DB_HOST $DB_PORT; do
    sleep 2
    echo "Waiting for MariaDB to be available..."
done
echo "MariaDB is up"

echo "Clearing config"
runuser -u www-data -- php artisan config:clear

echo "Caching config"
runuser -u www-data -- php artisan config:cache

runuser -u www-data -- php artisan migrate --force

chgrp -R www-data .
chmod 764 "./storage/logs/laravel.log"

php-fpm -D
nginx -g "daemon off;"
