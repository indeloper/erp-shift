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
Добавить константу в NotificationType и возврат добавленного класса, при вызове добавленной константы.
Например добавим: <code>OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION</code>

```
...
const OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION = 106;
...
public static function determinateNotificationClassByType(int $type): string
    {
        switch ($type) {
        ...
            case self::OPERATIONS_WITHOUT_CERTIFICATES_NOTIFICATION:
                return OperationsWithoutCertificatesNotice::class;
        ...
```

Добавление уведомления в коде:

```
dispatchNotify(
    user_id (id получателя),
    name    (Содержание уведомления),
    discription (Можно оставить пустой => '',),
    type (указывается констанка класса NotificationType, которая возвращает класс уведомления),
    [] *
)
```

* Можно передать массив, который по ключам будет пытаться записать в базу в таблицу Notification, если такие поля есть в
  бд. Например:

```
    dispatchNotify(
        1,
        'Заявка на формирование приказов',
        'Заявка на формирование приказов',
        NotificationType::LABOR_SAFETY,
        [
            'additional_info' => 'Дополнительная информация',
            'url' => 'Ссылка'
            'target_id' => $requestRow->id,
            'status' => 7,
            'orderRequestId' => $requestRow->id,
            'orderRequestAuthor' => $orderRequestAuthor,
            'company' => $company,
            'projectObject' => $projectObject
        ]
    );
```

<code>additional_info</code> добавляется после основной информации в тексте уведомления

<code>url</code> при наличии создаёт ссылку (в виде кнопки в письме и ссылка в телеграмме)
При тестировании уведомлений в telegram, нельзя в адресе ссылки использовать localhost - ссылка не создастся
