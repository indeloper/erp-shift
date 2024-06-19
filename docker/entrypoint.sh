#!/bin/bash

echo "Clearing config"
runuser -u www-data -- php artisan config:clear

echo "Caching config"
runuser -u www-data -- php artisan config:cache

runuser -u www-data -- php artisan migrate --force

chgrp -R www-data .
chmod 764 "./storage/logs/laravel.log"

php-fpm -D
nginx -g "daemon off;"
