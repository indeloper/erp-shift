#!/bin/bash
# Переходим в директорию проекта
# cd /var/www/html

# Запускаем миграции (если их не было, он ничего не сделает)
php artisan migrate --force

# Собираем зависимости
composer dump-autoload

# Собираем фронтенд
npm run dev

# Чистим все кэши
php artisan optimize
