<p align="center">
  <h3 align="center">СК город</h3>
</p>

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
- Install Laravel globally with composer, if it is first laravel project
- Install dadata_module: `composer require "fomvasss/laravel-dadata"` (already in composer.json, skip)
- Install laravel-passport: `composer require laravel/passport` (already in composer.json, skip)
- Install all composer packages (for MacOS Catalina need to update php via brew)
- Install all npm packages
- Creating database, then run command: `php artisan migrate` -- Doesn`t work. Migration have data dependencies.

- Comment again creating of table users (just in case, unknown why it was commented out)
- Uncomment boot(): in `App/Http/Providers/AuthServiceProvider.php` uncomment public function boot()
- Run npm: `npm run dev`
- Generate Laravel application key: php artisan key:generate
- Run project: php artisan serve
