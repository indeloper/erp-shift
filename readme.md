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
Команда для создания уведомлений php artisan make:epr-notification <имя> <описание>
[Подробное описание](https://gitlab.sk-gorod.com/dev/erp/-/wikis/%D0%A3%D0%B2%D0%B5%D0%B4%D0%BE%D0%BC%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F)
