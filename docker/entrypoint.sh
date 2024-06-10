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

sudo runuser -u www-data -- php composer dump-autoload
sudo runuser -u www-data -- php npm run dev
sudo runuser -u www-data -- php artisan key:generate
sudo runuser -u www-data -- php artisan optimize

chgrp -R www-data .
sudo chmod 764 "./storage/logs/laravel.log"

php-fpm -D
nginx -g "daemon off;"

