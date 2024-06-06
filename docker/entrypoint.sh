#!/bin/bash

#if [ ! -f "vendor/autoload.php" ]; then
#    composer install --no-progress --no-interaction
#fi

#if [ $# -gt 0 ]; then
#    exec gosu $WWWUSER "$@"
#else
#    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
#fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
else
    echo "env file exists."
fi

php artisan key:generate
php artisan config:clear
php artisan view:clear
php artisan cache:clear

chgrp -R www-data .

php-fpm -D
nginx -g "daemon off;"

