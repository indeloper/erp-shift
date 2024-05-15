<h3 align="center">СК город</h3>

Start:

- Clone the repo
- Copy .env.example, past & rename it to .env
- In .env write: `DB_DATABASE=db_name, DB_USERNAME=db_username, DB_PASSWORD=db_password`
- Comment boot(): in `App/Http/Providers/AuthServiceProvider.php` comment public function boot() by `/* */`
- Install composer: `composer install`
- Install npm: ` npm install`
- Install dadata_module: `composer require "fomvasss/laravel-dadata"`
- Install laravel-passport: `composer require laravel/passport`
- Creating database, then run command: `php artisan migrate`
- Uncomment boot(): in `App/Http/Providers/AuthServiceProvider.php` uncomment public function boot()
- Run project: `npm run dev`
<hr>
- Clone the repo
- Copy .env.example, past & rename it to .env
- In .env write: `DB_DATABASE=db_name, DB_USERNAME=db_username, DB_PASSWORD=db_password`
- Comment boot(): in `app/Providers/AuthServiceProvider.php` comment public function boot() by `/* */`
- Install composer: `composer install` (https://getcomposer.org)
- Install npm: `npm install` (https://nodejs.org/en/ as pkg file, type `npm -v` for test in terminal)
- Uncomment boot(): in `App/Http/Providers/AuthServiceProvider.php` uncomment public function boot()
- Run npm: `npm run dev`
- Generate Laravel application key: php artisan key:generate
<hr>

### Уведомления

--- 
команда для создания уведомлений php artisan make:epr-notification имя описание

Предусмотрены 3 канала уведомлений:

1. Почта
2. ERP уведомления
3. Телеграмм

#### Управление уведомлениями:

Если у пользователя есть пермишн, то он может получать и управлять этим уведомлением.
Может выбрать в какой канал отправлять (поставить галочку) ему конкретный тип уведомлений.
По умолчанию все галочки отмечены.

#### Создать уведомление:

Добавить класс в App/Notifications/
Если blade для mail создать blade (resources/views/mail/) и наименованием как у класса, в кебаб кейсе
Если blade для telegram, создать blade (resources/views/telegram/) и наименованием как у класса, в кебаб кейсе

Добавление уведомления в коде:

```
UserTestCreateNotice::send(
    $user_ids,
    [
        'name' => 'test',
        'additional_info' => 'Ссылка на задачу',
        'url' => route($task->id)
    ]
);
```

* Можно передать массив, который по ключам будет пытаться записать в базу в таблицу Notification, если такие поля есть в
  бд. Например:

<code>additional_info</code> добавляется после основной информации в тексте уведомления

<code>url</code> при наличии создаёт ссылку (в виде кнопки в письме и ссылка в телеграмме)
При тестировании уведомлений в telegram, нельзя в адресе ссылки использовать localhost - ссылка не создастся
