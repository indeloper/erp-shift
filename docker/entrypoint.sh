#!/bin/bash

#if [ ! -f "vendor/autoload.php" ]; then
#    composer install --no-progress --no-interaction
#fi

#if [ $# -gt 0 ]; then
#    exec gosu $WWWUSER "$@"
#else
#    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
#fi

#if [ ! -f ".env" ]; then
#    echo "Creating env file for env $APP_ENV"
#    cp .env.example .env
#else
#    echo "env file exists."
#fi

#echo "whoami and id:"
#whoami
#id

echo "Clearing config"
runuser -u www-data -- php artisan config:clear
echo "Caching config"
runuser -u www-data -- php artisan config:cache
echo "Generation key"
runuser -u www-data -- php artisan key:generate

chgrp -R www-data .
chmod 764 "./storage/logs/laravel.log"

php-fpm -D
nginx -g "daemon off;"